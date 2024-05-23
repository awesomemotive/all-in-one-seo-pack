<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serves stylesheets for sitemaps.
 *
 * @since 4.2.1
 */
class Xsl {
	/**
	 * Generates the XSL stylesheet for the current sitemap.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function generate() {
		aioseo()->sitemap->headers();

		$charset     = aioseo()->helpers->getCharset();
		$sitemapUrl  = wp_get_referer();
		$sitemapPath = aioseo()->helpers->getPermalinkPath( $sitemapUrl );

		// Figure out which sitemap we're serving.
		preg_match( '/\/(.*?)-?sitemap([0-9]*)\.xml/', $sitemapPath, $sitemapInfo );
		$sitemapName = ! empty( $sitemapInfo[1] ) ? strtoupper( $sitemapInfo[1] ) : '';

		// Remove everything after ? from sitemapPath to avoid caching issues.
		$sitemapPath = wp_parse_url( $sitemapPath, PHP_URL_PATH ) ?: '';

		if ( ! empty( $sitemapInfo[1] ) ) {
			switch ( $sitemapInfo[1] ) {
				case 'addl':
					$sitemapName = __( 'Additional Pages', 'all-in-one-seo-pack' );
					break;
				case 'post-archive':
					$sitemapName = __( 'Post Archive', 'all-in-one-seo-pack' );
					break;
				default:
					if ( post_type_exists( $sitemapInfo[1] ) ) {
						$postTypeObject = get_post_type_object( $sitemapInfo[1] );
						$sitemapName    = $postTypeObject->labels->singular_name;
					}
					if ( taxonomy_exists( $sitemapInfo[1] ) ) {
						$taxonomyObject = get_taxonomy( $sitemapInfo[1] );
						$sitemapName    = $taxonomyObject->labels->singular_name;
					}
					break;
			}
		}

		$currentPage = ! empty( $sitemapInfo[2] ) ? (int) $sitemapInfo[2] : 1;

		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$linksPerIndex = aioseo()->options->sitemap->general->linksPerIndex;
		$advanced      = aioseo()->options->sitemap->general->advancedSettings->enable;
		$excludeImages = aioseo()->options->sitemap->general->advancedSettings->excludeImages;
		$sitemapParams = aioseo()->helpers->getParametersFromUrl( $sitemapUrl );
		$xslParams     = aioseo()->core->cache->get( 'aioseo_sitemap_' . aioseo()->sitemap->requestParser->cleanSlug( $sitemapPath ) );
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		// Translators: 1 - The sitemap name, 2 - The current page.
		$title = sprintf( __( '%1$s Sitemap %2$s', 'all-in-one-seo-pack' ), $sitemapName, $currentPage > 1 ? $currentPage : '' );
		$title = trim( $title );

		echo '<?xml version="1.0" encoding="' . esc_attr( $charset ) . '"?>';
		include_once AIOSEO_DIR . '/app/Common/Views/sitemap/xsl/default.php';
		exit;
	}

	/**
	 * Save the data to use in the XSL.
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
	 * Retrieve the data to use on the XSL.
	 *
	 * @since 4.2.1
	 *
	 * @param  string $fileName The sitemap file name.
	 * @return array            The XSL data for the given file name.
	 */
	public function getXslData( $fileName ) {
		return aioseo()->core->cache->get( 'aioseo_sitemap_' . $fileName );
	}
}