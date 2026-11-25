<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serves the browser view of sitemaps.
 *
 * @since 4.2.1
 */
class Xsl {
	/**
	 * The URL the sitemap view script fetches its data from, set while a sitemap is printed.
	 *
	 * @since 5.0.6
	 *
	 * @var string
	 */
	private $viewDataUrl = '';

	/**
	 * Whether the current request gets the sitemap view script. Null until it's decided.
	 *
	 * @since 5.0.6.1
	 *
	 * @var bool|null
	 */
	private $showViewScript = null;

	/**
	 * Returns the name, title and saved data of the sitemap at the given URL.
	 *
	 * @since 5.0.6
	 *
	 * @param  string|false $sitemapUrl The sitemap URL.
	 * @return array                    The sitemap context.
	 */
	private function getContext( $sitemapUrl ) {
		$sitemapUrl  = (string) $sitemapUrl;
		$sitemapPath = aioseo()->helpers->getPermalinkPath( $sitemapUrl );

		// Figure out which sitemap we're serving.
		preg_match( '/\/(.*?)-?sitemap([0-9]*)\.xml/', (string) $sitemapPath, $sitemapInfo );
		$sitemapName = ! empty( $sitemapInfo[1] ) ? strtoupper( $sitemapInfo[1] ) : '';

		// Remove everything after ? from sitemapPath to avoid caching issues.
		$sitemapPath = wp_parse_url( $sitemapPath, PHP_URL_PATH ) ?: '';

		$excludeImages = aioseo()->sitemap->helpers->excludeImages();
		if ( ! empty( $sitemapInfo[1] ) ) {
			switch ( $sitemapInfo[1] ) {
				case 'addl':
					$sitemapName   = __( 'Additional Pages', 'all-in-one-seo-pack' );
					$excludeImages = true;
					break;
				case 'post-archive':
					$sitemapName   = __( 'Post Archive', 'all-in-one-seo-pack' );
					$excludeImages = true;
					break;
				case 'author':
					$sitemapName   = __( 'Author', 'all-in-one-seo-pack' );
					$excludeImages = true;
					break;
				case 'date':
					$sitemapName   = __( 'Date', 'all-in-one-seo-pack' );
					$excludeImages = true;
					break;
				case 'bp-activity':
				case 'bp-group':
				case 'bp-member':
					$bpFakePostTypes = aioseo()->standalone->buddyPress->getFakePostTypes();
					$labels          = array_column( wp_list_filter( $bpFakePostTypes, [ 'name' => $sitemapInfo[1] ] ), 'label' );
					$sitemapName     = ! empty( $labels[0] ) ? $labels[0] : $sitemapName;
					$excludeImages   = true;
					break;
				case 'product_attributes':
					$sitemapName   = __( 'Product Attributes', 'all-in-one-seo-pack' );
					$excludeImages = true;
					break;
				default:
					if ( post_type_exists( $sitemapInfo[1] ) ) {
						$postTypeObject = get_post_type_object( $sitemapInfo[1] );
						$sitemapName    = $postTypeObject->labels->singular_name;

						if ( 'attachment' === $sitemapInfo[1] ) {
							$excludeImages = true;
						}
					}
					if ( taxonomy_exists( $sitemapInfo[1] ) ) {
						$taxonomyObject = get_taxonomy( $sitemapInfo[1] );
						$sitemapName    = $taxonomyObject->labels->singular_name;
						$excludeImages  = true;
					}
					break;
			}
		}

		$currentPage = ! empty( $sitemapInfo[2] ) ? (int) $sitemapInfo[2] : 1;

		// Translators: 1 - The sitemap name, 2 - The current page.
		$title = sprintf( __( '%1$s Sitemap %2$s', 'all-in-one-seo-pack' ), $sitemapName, $currentPage > 1 ? $currentPage : '' );

		return [
			'sitemapUrl'    => $sitemapUrl,
			'sitemapPath'   => $sitemapPath,
			'linksPerIndex' => aioseo()->options->sitemap->general->linksPerIndex,
			'excludeImages' => $excludeImages,
			'xslParams'     => aioseo()->core->cache->get( 'aioseo_sitemap_' . aioseo()->helpers->cleanSlug( $sitemapPath ) ),
			'currentPage'   => $currentPage,
			'title'         => trim( $title )
		];
	}

	/**
	 * Prints the stylesheet processing instruction for a sitemap.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $dataUrl The URL that serves the sitemap's view data, without the "format" parameter.
	 * @param  bool   $show    Whether to print it.
	 * @return void
	 */
	public function printStylesheet( $dataUrl, $show = true ) {
		$this->viewDataUrl = '';
		if ( ! $show ) {
			return;
		}

		$this->viewDataUrl = $this->removeScheme( add_query_arg( 'format', 'json', $dataUrl ) );

		echo "\r\n\r\n<?xml-stylesheet type=\"text/css\" href=\"" . esc_url( $this->getAssetUrl( 'sitemap.css' ) ) . "\"?>\r\n";
	}

	/**
	 * Prints the script that renders the sitemap view. Must be the first child of the root element.
	 *
	 * NOTE: The sitemap schemas allow elements from other namespaces in that position, but Google Search Console still
	 * reports the tag as invalid, so only browsers get it.
	 *
	 * @since 5.0.6
	 *
	 * @return void
	 */
	public function printScript() {
		if ( ! $this->viewDataUrl || ! $this->showViewScript() ) {
			return;
		}

		printf(
			"\t<script xmlns=\"http://www.w3.org/1999/xhtml\" src=\"%1\$s\" data-view=\"%2\$s\" data-empty=\"%3\$s\"></script>\n",
			esc_url( $this->getAssetUrl( 'sitemap.js' ) ),
			esc_url( $this->viewDataUrl ),
			esc_attr__( 'There are no posts here', 'all-in-one-seo-pack' )
		);
	}

	/**
	 * Returns whether the sitemap view script should be printed for the current request.
	 *
	 * @since 5.0.6.1
	 *
	 * @return bool Whether to print the view script.
	 */
	public function showViewScript() {
		// Decide once, so the headers and the sitemap itself always agree.
		if ( null === $this->showViewScript ) {
			$this->showViewScript = (bool) apply_filters( 'aioseo_sitemap_show_view_script', $this->isBrowserNavigation() );
		}

		return $this->showViewScript;
	}

	/**
	 * Returns whether the current request is a browser navigating to the sitemap.
	 *
	 * NOTE: Browsers don't send Fetch Metadata headers over plain HTTP, and Safari before 16.4 never sends them.
	 *
	 * @since 5.0.6.1
	 *
	 * @return bool Whether the request is a browser navigation.
	 */
	private function isBrowserNavigation() {
		$userAgent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		if ( preg_match( '/bot|crawl|spider|slurp|google/i', (string) $userAgent ) ) {
			return false;
		}

		$mode = isset( $_SERVER['HTTP_SEC_FETCH_MODE'] ) ? sanitize_key( wp_unslash( $_SERVER['HTTP_SEC_FETCH_MODE'] ) ) : '';
		if ( $mode ) {
			return 'navigate' === $mode;
		}

		$upgradeInsecure = isset( $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] ) ) : '';

		return '1' === trim( $upgradeInsecure );
	}

	/**
	 * Sends the headers that keep the sitemap copy with the view script apart from the one crawlers get.
	 *
	 * @since 5.0.6.1
	 *
	 * @return void
	 */
	public function sendViewHeaders() {
		// Without the credits there's no view script, so every request gets the same sitemap.
		if ( ! apply_filters( 'aioseo_sitemap_show_credits', true ) ) {
			return;
		}

		header( 'Vary: Sec-Fetch-Mode, Upgrade-Insecure-Requests', false );

		// The copy with the view script must not be stored by a page cache or CDN and then served to a crawler.
		if ( $this->showViewScript() ) {
			nocache_headers();

			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
		}
	}

	/**
	 * Returns the URL of a sitemap view asset.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $fileName The asset file name.
	 * @return string           The asset URL.
	 */
	private function getAssetUrl( $fileName ) {
		return $this->removeScheme( add_query_arg( 'ver', aioseo()->version, plugin_dir_url( AIOSEO_FILE ) . 'app/Common/Views/sitemap/assets/' . $fileName ) );
	}

	/**
	 * Makes a URL scheme-relative, so it loads over the same protocol as the sitemap.
	 *
	 * NOTE: The site address setting can use another scheme than the request, e.g. "http" on a site served over HTTPS.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $url The URL.
	 * @return string      The scheme-relative URL.
	 */
	private function removeScheme( $url ) {
		return (string) preg_replace( '#^https?:#i', '', (string) $url );
	}

	/**
	 * Serves a stylesheet that shows the XML as is, for sitemaps that still link to the removed XSL stylesheet.
	 *
	 * NOTE: Cached pages, static sitemap files and older addons can still link to it, and browsers that run XSLT show an error when it's missing.
	 *
	 * @since 5.0.6
	 *
	 * @return void
	 */
	public function outputPlaceholderStylesheet() {
		aioseo()->sitemap->headers();

		printf(
			'<?xml version="1.0" encoding="%1$s"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml"/>
	<xsl:template match="/">
		<xsl:processing-instruction name="xml-stylesheet">type="text/css" href="%2$s"</xsl:processing-instruction>
		<xsl:copy-of select="*"/>
	</xsl:template>
</xsl:stylesheet>
',
			esc_attr( aioseo()->helpers->getCharset() ),
			esc_attr( $this->getAssetUrl( 'sitemap.css' ) )
		);
		exit;
	}

	/**
	 * Returns whether the current stylesheet request asks for the view data.
	 *
	 * @since 5.0.6
	 *
	 * @return bool Whether the view data is requested.
	 */
	public function isViewDataRequest() {
		$format = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : ''; // phpcs:ignore HM.Security.NonceVerification.Recommended

		return 'json' === $format;
	}

	/**
	 * Returns the URL of the sitemap the view data is requested for.
	 *
	 * @since 5.0.6
	 *
	 * @return string The sitemap URL.
	 */
	public function getViewDataSitemapUrl() {
		return isset( $_GET['url'] ) && is_string( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore HM.Security.NonceVerification.Recommended
	}

	/**
	 * Sends the data the sitemap view script renders.
	 *
	 * @since 5.0.6
	 *
	 * @param  array $data The view data.
	 * @return void
	 */
	public function sendViewData( $data ) {
		nocache_headers();
		header( 'Content-Type: application/json; charset=' . aioseo()->helpers->getCharset(), true );
		header( 'X-Robots-Tag: noindex, nofollow', true );

		echo wp_json_encode( array_merge( [ 'lang' => get_bloginfo( 'language' ) ], $data ) );
		exit;
	}

	/**
	 * Sends the view data for the general and RSS sitemaps.
	 *
	 * @since 5.0.6
	 *
	 * @return void
	 */
	public function outputViewData() {
		$context = $this->getContext( $this->getViewDataSitemapUrl() );
		$isRss   = in_array( strtolower( $context['sitemapPath'] ), [ '/sitemap.rss', '/sitemap.latest.rss' ], true );
		$medium  = $isRss ? 'rss-sitemap' : 'xml-sitemap';
		$crumb   = [
			'title' => $context['title'],
			'url'   => $context['sitemapUrl']
		];

		$this->sendViewData( [
			'type'          => 'general',
			'title'         => $context['title'],
			'indexTitle'    => __( 'Sitemap Index', 'all-in-one-seo-pack' ),
			'heading'       => $context['title'],
			'header'        => $this->getHeaderViewData( $medium, $isRss ),
			'breadcrumbs'   => [
				'index'   => $this->getBreadcrumbs( [], true ),
				'sitemap' => $this->getBreadcrumbs( [ $crumb ], ! $isRss ),
				'empty'   => $this->getBreadcrumbs( [ $crumb ], true )
			],
			'empty'         => $this->getEmptyViewData( $medium ),
			'strings'       => $this->getViewStrings(),
			'excludeImages' => (bool) $context['excludeImages'],
			'linksPerIndex' => (int) $context['linksPerIndex'],
			'counts'        => ! empty( $context['xslParams']['counts'] ) ? $context['xslParams']['counts'] : (object) [],
			'datetime'      => ! empty( $context['xslParams']['datetime'] ) ? $context['xslParams']['datetime'] : (object) []
		] );
	}

	/**
	 * Returns the strings for the header of the sitemap view.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $utmMedium The UTM medium for the plugin link.
	 * @param  bool   $isRss     Whether the sitemap is an RSS sitemap.
	 * @return array             The header data.
	 */
	public function getHeaderViewData( $utmMedium, $isRss = false ) {
		return [
			'generatedBy'   => __( 'Generated by', 'all-in-one-seo-pack' ),
			'pluginName'    => AIOSEO_PLUGIN_NAME,
			'pluginUrl'     => $this->decodeUrl( aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL, $utmMedium ) ),
			'description'   => $isRss
				? __( 'this is an RSS Sitemap, meant to be consumed by search engines like Google or Bing.', 'all-in-one-seo-pack' )
				: __( 'this is an XML Sitemap, meant to be consumed by search engines like Google or Bing.', 'all-in-one-seo-pack' ),
			'moreInfo'      => $isRss
				// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
				? __( 'You can find more information about RSS Sitemaps at %1$ssitemaps.org%2$s.', 'all-in-one-seo-pack' )
				// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
				: __( 'You can find more information about XML Sitemaps at %1$ssitemaps.org%2$s.', 'all-in-one-seo-pack' ),
			'moreInfoUrl'   => 'https://www.sitemaps.org/',
			'contains'      => __( 'This sitemap contains', 'all-in-one-seo-pack' ),
			'indexContains' => __( 'This sitemap index contains', 'all-in-one-seo-pack' ),
			'url'           => __( 'URL', 'all-in-one-seo-pack' ),
			'urls'          => __( 'URLs', 'all-in-one-seo-pack' ),
			'sitemap'       => __( 'sitemap', 'all-in-one-seo-pack' ),
			'sitemaps'      => __( 'sitemaps', 'all-in-one-seo-pack' ),
			'generatedOn'   => sprintf(
				// Translators: 1 - The generated date, 2 - The generated time.
				__( 'and was generated on %1$s at %2$s', 'all-in-one-seo-pack' ),
				date_i18n( get_option( 'date_format' ) ),
				date_i18n( get_option( 'time_format' ) )
			)
		];
	}

	/**
	 * Returns the data for the empty sitemap view.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $utmMedium The UTM medium for the links.
	 * @return array             The empty sitemap data.
	 */
	public function getEmptyViewData( $utmMedium ) {
		$canManageSitemap = is_user_logged_in() && aioseo()->access->hasCapability( 'aioseo_sitemap_settings' );
		$adminUrl         = admin_url( 'admin.php?page=aioseo-sitemaps' );
		if ( 'xml-sitemap' !== $utmMedium ) {
			$adminUrl .= '#/' . str_replace( 'aioseo-', '', $utmMedium );
		}

		return [
			'title'        => __( 'Whoops!', 'all-in-one-seo-pack' ),
			'subtitle'     => __( 'There are no posts here', 'all-in-one-seo-pack' ),
			'homeUrl'      => home_url(),
			'homeLabel'    => __( 'Back to Homepage', 'all-in-one-seo-pack' ),
			'canManage'    => $canManageSitemap,
			'adminUrl'     => $canManageSitemap ? $adminUrl : '',
			'adminLabel'   => __( 'Configure Sitemap', 'all-in-one-seo-pack' ),
			// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
			'alert'        => __( 'Didn\'t expect to see this? Make sure your sitemap is enabled and your content is set to be indexed. %1$sLearn More →%2$s', 'all-in-one-seo-pack' ),
			'learnMoreUrl' => $this->decodeUrl( aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'docs/how-to-fix-a-404-error-when-viewing-your-sitemap/', $utmMedium, 'learn-more' ) )
		];
	}

	/**
	 * Returns the shared strings of the sitemap view.
	 *
	 * @since 5.0.6
	 *
	 * @return array The strings.
	 */
	public function getViewStrings() {
		return [
			'url'             => __( 'URL', 'all-in-one-seo-pack' ),
			'urlCount'        => __( 'URL Count', 'all-in-one-seo-pack' ),
			'lastUpdated'     => __( 'Last Updated', 'all-in-one-seo-pack' ),
			'images'          => __( 'Images', 'all-in-one-seo-pack' ),
			'changeFrequency' => __( 'Change Frequency', 'all-in-one-seo-pack' ),
			'priority'        => __( 'Priority', 'all-in-one-seo-pack' ),
			'publicationDate' => __( 'Publication Date', 'all-in-one-seo-pack' ),
			'low'             => __( 'Low', 'all-in-one-seo-pack' ),
			'medium'          => __( 'Medium', 'all-in-one-seo-pack' ),
			'high'            => __( 'High', 'all-in-one-seo-pack' ),
			'back'            => __( 'Back', 'all-in-one-seo-pack' )
		];
	}

	/**
	 * Decodes the HTML entities of an escaped URL, since the view script sets it as an attribute.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $url The escaped URL.
	 * @return string      The decoded URL.
	 */
	private function decodeUrl( $url ) {
		return html_entity_decode( (string) $url, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Returns the breadcrumb items for a sitemap view.
	 *
	 * @since 5.0.6
	 *
	 * @param  array $items The breadcrumb items, each with a title and URL.
	 * @param  bool  $index Whether to start the trail at the general sitemap index.
	 * @return array        The breadcrumb items.
	 */
	public function getBreadcrumbs( $items, $index = false ) {
		// With the general sitemap off, /sitemap.xml redirects to the WordPress core sitemap.
		if ( $index && aioseo()->options->sitemap->general->enable ) {
			$sitemapIndex = aioseo()->sitemap->helpers->filename( 'general' );
			$sitemapIndex = $sitemapIndex ? $sitemapIndex : 'sitemap';
			array_unshift( $items, [
				'title' => __( 'Sitemap Index', 'all-in-one-seo-pack' ),
				'url'   => home_url( "/$sitemapIndex.xml" )
			] );
		}

		$items = array_values( array_filter( $items ) );

		// With sitemap indexes off, the index URL is the page itself, so a link to it only reloads the page.
		$lastKey    = count( $items ) - 1;
		$currentUrl = $this->getComparableUrl( $items[ $lastKey ]['url'] ?? '' );
		$items      = array_filter( $items, function ( $item, $key ) use ( $lastKey, $currentUrl ) {
			return $lastKey === $key || empty( $item['url'] ) || $this->getComparableUrl( $item['url'] ) !== $currentUrl;
		}, ARRAY_FILTER_USE_BOTH );

		return array_values( $items );
	}

	/**
	 * Returns the host and path of a URL in lowercase, without the trailing slash, to compare it with another URL.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $url The URL.
	 * @return string      The comparable URL.
	 */
	private function getComparableUrl( $url ) {
		$path = wp_parse_url( (string) $url, PHP_URL_HOST ) . wp_parse_url( (string) $url, PHP_URL_PATH );

		return untrailingslashit( strtolower( $path ) );
	}

	/**
	 * Saves the dates and counts the sitemap view shows.
	 *
	 * @since 4.1.5
	 *
	 * @param  string $fileName The sitemap file name.
	 * @param  array  $entries  The sitemap entries.
	 * @param  int    $total    The total sitemap entries count.
	 * @return void
	 */
	public function saveXslData( $fileName, $entries, $total ) {
		$counts     = [];
		$datetime   = [];
		$dateFormat = get_option( 'date_format' );
		$timeFormat = get_option( 'time_format' );

		$entries = aioseo()->sitemap->helpers->decodeSitemapEntries( $entries );

		foreach ( $entries as $index ) {
			$url = ! empty( $index['guid'] ) ? $index['guid'] : $index['loc'];

			if ( ! empty( $index['count'] ) && aioseo()->options->sitemap->general->linksPerIndex !== (int) $index['count'] ) {
				$counts[ $url ] = $index['count'];
			}

			if ( ! empty( $index['lastmod'] ) || ! empty( $index['publicationDate'] ) || ! empty( $index['pubDate'] ) ) {
				$date             = ! empty( $index['lastmod'] ) ? $index['lastmod'] : ( ! empty( $index['publicationDate'] ) ? $index['publicationDate'] : $index['pubDate'] );
				$isTimezone       = ! empty( $index['isTimezone'] ) && $index['isTimezone'];
				$datetime[ $url ] = [
					'date' => $isTimezone ? date_i18n( $dateFormat, strtotime( $date ) ) : get_date_from_gmt( $date, $dateFormat ),
					'time' => $isTimezone ? date_i18n( $timeFormat, strtotime( $date ) ) : get_date_from_gmt( $date, $timeFormat )
				];
			}
		}

		$data = [
			'counts'     => $counts,
			'datetime'   => $datetime,
			'pagination' => [
				'showing' => count( $entries ),
				'total'   => $total
			]
		];

		// Set a high expiration date so we still have the cache for static sitemaps.
		aioseo()->core->cache->update( 'aioseo_sitemap_' . $fileName, $data, MONTH_IN_SECONDS );
	}

	/**
	 * Returns the dates and counts the sitemap view shows.
	 *
	 * @since 4.2.1
	 *
	 * @param  string $fileName The sitemap file name.
	 * @return array            The view data for the given file name.
	 */
	public function getXslData( $fileName ) {
		return aioseo()->core->cache->get( 'aioseo_sitemap_' . $fileName );
	}
}