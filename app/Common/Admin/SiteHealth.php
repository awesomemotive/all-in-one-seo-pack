<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Site Health class.
 *
 * @since 4.0.0
 */
class SiteHealth {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'site_status_tests', [ $this, 'registerTests' ], 0 );
		add_filter( 'debug_information', [ $this, 'addDebugInfo' ], 0 );
	}

	/**
	 * Add AIOSEO WP Site Health tests.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $tests The current filters array.
	 * @return array
	 */
	public function registerTests( $tests ) {
		$tests['direct']['aioseo_site_public'] = [
			'label' => 'AIOSEO Site Public',
			'test'  => [ $this, 'testCheckSitePublic' ],
		];
		$tests['direct']['aioseo_site_info'] = [
			'label' => 'AIOSEO Site Info',
			'test'  => [ $this, 'testCheckSiteInfo' ],
		];
		$tests['direct']['aioseo_google_search_console'] = [
			'label' => 'AIOSEO Google Search Console',
			'test'  => [ $this, 'testCheckGoogleSearchConsole' ],
		];
		$tests['direct']['aioseo_plugin_update'] = [
			'label' => 'AIOSEO Plugin Update',
			'test'  => [ $this, 'testCheckPluginUpdate' ],
		];

		$tests['direct']['aioseo_schema_markup'] = [
			'label' => 'AIOSEO Schema Markup',
			'test'  => [ $this, 'testCheckSchemaMarkup' ],
		];

		return $tests;
	}

	/**
	 * Adds our site health debug info.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $debugInfo The debug info.
	 * @return array $debugInfo The debug info.
	 */
	public function addDebugInfo( $debugInfo ) {
		$fields = [];

		$noindexed = $this->noindexed();
		if ( $noindexed ) {
			$fields['noindexed'] = $this->field(
				__( 'Noindexed content', 'all-in-one-seo-pack' ),
				implode( ', ', $noindexed )
			);
		}

		$nofollowed = $this->nofollowed();
		if ( $nofollowed ) {
			$fields['nofollowed'] = $this->field(
				__( 'Nofollowed content', 'all-in-one-seo-pack' ),
				implode( ', ', $nofollowed )
			);
		}

		if ( ! count( $fields ) ) {
			return $debugInfo;
		}

		$debugInfo['aioseo'] = [
			'label'       => __( 'SEO', 'all-in-one-seo-pack' ),
			'description' => sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'The fields below contain important SEO information from %1$s that may effect your site.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			),
			'private'     => false,
			'show_count'  => true,
			'fields'      => $fields,
		];

		return $debugInfo;
	}

	/**
	 * Checks whether the site is public.
	 *
	 * @since 4.0.0
	 *
	 * @return array The test result.
	 */
	public function testCheckSitePublic() {
		$test = 'aioseo_site_public';

		if ( ! get_option( 'blog_public' ) ) {
			return $this->result(
				$test,
				'critical',
				__( 'Your site does not appear in search results', 'all-in-one-seo-pack' ),
				__( 'Your site is set to private. This means WordPress asks search engines to exclude your website from search results.', 'all-in-one-seo-pack' ),
				$this->actionLink( admin_url( 'options-reading.php' ), __( 'Go to Settings > Reading', 'all-in-one-seo-pack' ) )
			);
		}

		return $this->result(
			$test,
			'good',
			__( 'Your site appears in search results', 'all-in-one-seo-pack' ),
			__( 'Your site is set to public. Search engines will index your website and it will appear in search results.', 'all-in-one-seo-pack' )
		);
	}

	/**
	 * Checks whether the site title and tagline are set.
	 *
	 * @since 4.0.0
	 *
	 * @return array The test result.
	 */
	public function testCheckSiteInfo() {
		$siteTitle   = get_bloginfo( 'name' );
		$siteTagline = get_bloginfo( 'description' );

		if ( ! $siteTitle || ! $siteTagline ) {
			return $this->result(
				'aioseo_site_info',
				'recommended',
				__( 'Your Site Title and/or Tagline are blank', 'all-in-one-seo-pack' ),
				sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__(
						'Your Site Title and/or Tagline are blank. We recommend setting both of these values as %1$s requires these for various features, including our schema markup',
						'all-in-one-seo-pack'
					),
					AIOSEO_PLUGIN_SHORT_NAME
				),
				$this->actionLink( admin_url( 'options-general.php' ), __( 'Go to Settings > General', 'all-in-one-seo-pack' ) )
			);
		}

		return $this->result(
			'aioseo_site_info',
			'good',
			__( 'Your Site Title and Tagline are set', 'all-in-one-seo-pack' ),
			sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'Great! These are required for %1$s\'s schema markup and are often used as fallback values for various other features.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			)
		);
	}

	/**
	 * Checks whether Google Search Console is connected.
	 *
	 * @since 4.6.2
	 *
	 * @return array The test result.
	 */
	public function testCheckGoogleSearchConsole() {
		$googleSearchConsole = aioseo()->searchStatistics->api->auth->isConnected();

		if ( ! $googleSearchConsole ) {
			return $this->result(
				'aioseo_google_search_console',
				'recommended',
				__( 'Connect Your Site with Google Search Console', 'all-in-one-seo-pack' ),
				__( 'Sync your site with Google Search Console and get valuable insights right inside your WordPress dashboard. Track keyword rankings and search performance for individual posts with actionable insights to help you rank higher in search results!', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				$this->actionLink( admin_url( 'admin.php?page=aioseo-settings&aioseo-scroll=google-search-console-settings&aioseo-highlight=google-search-console-settings#/webmaster-tools?activetool=googleSearchConsole' ), __( 'Connect to Google Search Console', 'all-in-one-seo-pack' ) ) // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			);
		}

		return $this->result(
			'aioseo_google_search_console',
			'good',
			__( 'Google Search Console is Connected', 'all-in-one-seo-pack' ),
			__( 'Awesome! Google Search Console is connected to your site. This will help you monitor and maintain your site\'s presence in Google Search results.', 'all-in-one-seo-pack' )
		);
	}

	/**
	 * Checks whether the required settings for our schema markup are set.
	 *
	 * @since 4.0.0
	 *
	 * @return array The test result.
	 */
	public function testCheckSchemaMarkup() {
		$menuPath = admin_url( 'admin.php?page=aioseo-search-appearance' );

		if ( 'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents ) {
			if (
				! aioseo()->options->searchAppearance->global->schema->organizationName ||
				(
					! aioseo()->options->searchAppearance->global->schema->organizationLogo &&
					! aioseo()->helpers->getSiteLogoUrl()
				)
			) {
				return $this->result(
					'aioseo_schema_markup',
					'recommended',
					__( 'Your Organization Name and/or Logo are blank', 'all-in-one-seo-pack' ),
					sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO").
						__( 'Your Organization Name and/or Logo are blank. These values are required for %1$s\'s Organization schema markup.', 'all-in-one-seo-pack' ),
						AIOSEO_PLUGIN_SHORT_NAME
					),
					$this->actionLink( $menuPath, __( 'Go to Schema Settings', 'all-in-one-seo-pack' ) )
				);
			}

			return $this->result(
				'aioseo_schema_markup',
				'good',
				__( 'Your Organization Name and Logo are set', 'all-in-one-seo-pack' ),
				sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__( 'Awesome! These are required for %1$s\'s Organization schema markup.', 'all-in-one-seo-pack' ),
					AIOSEO_PLUGIN_SHORT_NAME
				)
			);
		}

		if (
			! aioseo()->options->searchAppearance->global->schema->person ||
			(
				'manual' === aioseo()->options->searchAppearance->global->schema->person &&
				(
					! aioseo()->options->searchAppearance->global->schema->personName ||
					! aioseo()->options->searchAppearance->global->schema->personLogo
				)
			)
		) {
			return $this->result(
				'aioseo_schema_markup',
				'recommended',
				__( 'Your Person Name and/or Image are blank', 'all-in-one-seo-pack' ),
				sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__( 'Your Person Name and/or Image are blank. These values are required for %1$s\'s Person schema markup.', 'all-in-one-seo-pack' ),
					AIOSEO_PLUGIN_SHORT_NAME
				),
				$this->actionLink( $menuPath, __( 'Go to Schema Settings', 'all-in-one-seo-pack' ) )
			);
		}

		return $this->result(
			'aioseo_schema_markup',
			'good',
			__( 'Your Person Name and Image are set', 'all-in-one-seo-pack' ),
			sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'Awesome! These are required for %1$s\'s Person schema markup.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			)
		);
	}

	/**
	 * Checks whether the required settings for our schema markup are set.
	 *
	 * @since 4.0.0
	 *
	 * @return array The test result.
	 */
	public function testCheckPluginUpdate() {
		$response = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.0/all-in-one-seo-pack.json' );
		$body     = wp_remote_retrieve_body( $response );
		if ( ! $body ) {
			// Something went wrong.
			return;
		}

		$pluginData   = json_decode( $body );
		$shouldUpdate = version_compare( AIOSEO_VERSION, $pluginData->version, '<' );

		if ( $shouldUpdate ) {
			return $this->result(
				'aioseo_plugin_update',
				'critical',
				sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__( '%1$s needs to be updated', 'all-in-one-seo-pack' ),
					AIOSEO_PLUGIN_SHORT_NAME
				),
				sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__( 'An update is available for %1$s. Upgrade to the latest version to receive all the latest features, bug fixes and security improvements.', 'all-in-one-seo-pack' ),
					AIOSEO_PLUGIN_SHORT_NAME
				),
				$this->actionLink( admin_url( 'plugins.php' ), __( 'Go to Plugins', 'all-in-one-seo-pack' ) )
			);
		}

		return $this->result(
			'aioseo_plugin_update',
			'good',
			sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( '%1$s is updated to the latest version', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			),
			__( 'Fantastic! By updating to the latest version, you have access to all the latest features, bug fixes and security improvements.', 'all-in-one-seo-pack' )
		);
	}

	/**
	 * Returns a list of noindexed content.
	 *
	 * @since 4.0.0
	 *
	 * @return array $noindexed A list of noindexed content.
	 */
	protected function noindexed() {
		$globalDefault = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default;
		if (
			! $globalDefault &&
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindex
		) {
			return [
				__( 'Your entire site is set to globally noindex content.', 'all-in-one-seo-pack' )
			];
		}

		$noindexed = [];

		if (
			! $globalDefault &&
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->noindexPaginated
		) {
			$noindexed[] = __( 'Paginated Content', 'all-in-one-seo-pack' );
		}

		$archives = [
			'author' => __( 'Author Archives', 'all-in-one-seo-pack' ),
			'date'   => __( 'Date Archives', 'all-in-one-seo-pack' ),
			'search' => __( 'Search Page', 'all-in-one-seo-pack' )
		];

		// Archives.
		foreach ( $archives as $name => $type ) {
			if (
				! aioseo()->options->searchAppearance->archives->{ $name }->advanced->robotsMeta->default &&
				aioseo()->options->searchAppearance->archives->{ $name }->advanced->robotsMeta->noindex
			) {
				$noindexed[] = $type;
			}
		}

		foreach ( aioseo()->helpers->getPublicPostTypes() as $postType ) {
			if (
				aioseo()->dynamicOptions->searchAppearance->postTypes->has( $postType['name'] ) &&
				! aioseo()->dynamicOptions->searchAppearance->postTypes->{ $postType['name'] }->advanced->robotsMeta->default &&
				aioseo()->dynamicOptions->searchAppearance->postTypes->{ $postType['name'] }->advanced->robotsMeta->noindex
			) {
				$noindexed[] = $postType['label'] . ' (' . $postType['name'] . ')';
			}
		}

		foreach ( aioseo()->helpers->getPublicTaxonomies() as $taxonomy ) {
			if (
				aioseo()->dynamicOptions->searchAppearance->taxonomies->has( $taxonomy['name'] ) &&
				! aioseo()->dynamicOptions->searchAppearance->taxonomies->{ $taxonomy['name'] }->advanced->robotsMeta->default &&
				aioseo()->dynamicOptions->searchAppearance->taxonomies->{ $taxonomy['name'] }->advanced->robotsMeta->noindex
			) {
				$noindexed[] = $taxonomy['label'] . ' (' . $taxonomy['name'] . ')';
			}
		}

		return $noindexed;
	}

	/**
	 * Returns a list of nofollowed content.
	 *
	 * @since 4.0.0
	 *
	 * @return array $nofollowed A list of nofollowed content.
	 */
	protected function nofollowed() {
		$globalDefault = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->default;
		if (
			! $globalDefault &&
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->nofollow
		) {
			return [
				__( 'Your entire site is set to globally nofollow content.', 'all-in-one-seo-pack' )
			];
		}

		$nofollowed = [];

		if (
			! $globalDefault &&
			aioseo()->options->searchAppearance->advanced->globalRobotsMeta->nofollowPaginated
		) {
			$nofollowed[] = __( 'Paginated Content', 'all-in-one-seo-pack' );
		}

		$archives = [
			'author' => __( 'Author Archives', 'all-in-one-seo-pack' ),
			'date'   => __( 'Date Archives', 'all-in-one-seo-pack' ),
			'search' => __( 'Search Page', 'all-in-one-seo-pack' )
		];

		// Archives.
		foreach ( $archives as $name => $type ) {
			if (
				! aioseo()->options->searchAppearance->archives->{ $name }->advanced->robotsMeta->default &&
				aioseo()->options->searchAppearance->archives->{ $name }->advanced->robotsMeta->nofollow
			) {
				$nofollowed[] = $type;
			}
		}

		foreach ( aioseo()->helpers->getPublicPostTypes() as $postType ) {
			if (
				aioseo()->dynamicOptions->searchAppearance->postTypes->has( $postType['name'] ) &&
				! aioseo()->dynamicOptions->searchAppearance->postTypes->{ $postType['name'] }->advanced->robotsMeta->default &&
				aioseo()->dynamicOptions->searchAppearance->postTypes->{ $postType['name'] }->advanced->robotsMeta->nofollow
			) {
				$nofollowed[] = $postType['label'] . ' (' . $postType['name'] . ')';
			}
		}

		foreach ( aioseo()->helpers->getPublicTaxonomies() as $taxonomy ) {
			if (
				aioseo()->dynamicOptions->searchAppearance->taxonomies->has( $taxonomy['name'] ) &&
				! aioseo()->dynamicOptions->searchAppearance->taxonomies->{ $taxonomy['name'] }->advanced->robotsMeta->default &&
				aioseo()->dynamicOptions->searchAppearance->taxonomies->{ $taxonomy['name'] }->advanced->robotsMeta->nofollow
			) {
				$nofollowed[] = $taxonomy['label'] . ' (' . $taxonomy['name'] . ')';
			}
		}

		return $nofollowed;
	}

	/**
	 * Returns a debug info data field.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $label   The field label.
	 * @param  string  $value   The field value.
	 * @param  boolean $private Whether the field shouldn't be included if the debug info is copied.
	 * @return array            The debug info data field.
	 */
	private function field( $label, $value, $private = false ) {
		return [
			'label'   => $label,
			'value'   => $value,
			'private' => $private,
		];
	}

	/**
	 * Returns the test result.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name        The test name.
	 * @param  string $status      The result status.
	 * @param  string $header      The test header.
	 * @param  string $description The result description.
	 * @param  string $actions     The result actions.
	 * @return array               The test result.
	 */
	protected function result( $name, $status, $header, $description, $actions = '' ) {
		$color = 'blue';
		switch ( $status ) {
			case 'good':
				break;
			case 'recommended':
				$color = 'orange';
				break;
			case 'critical':
				$color = 'red';
				break;
			default:
				break;
		}

		return [
			'test'        => $name,
			'status'      => $status,
			'label'       => $header,
			'description' => $description,
			'actions'     => $actions,
			'badge'       => [
				'label' => AIOSEO_PLUGIN_SHORT_NAME,
				'color' => $color,
			],
		];
	}

	/**
	 * Returns an action link.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $path   The path.
	 * @param  string $anchor The anchor text.
	 * @return string         The action link.
	 */
	protected function actionLink( $path, $anchor ) {
		return sprintf(
			'<p><a href="%1$s">%2$s</a></p>',
			$path,
			$anchor
		);
	}
}