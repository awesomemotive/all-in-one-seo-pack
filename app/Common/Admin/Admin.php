<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Migration;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Admin {
	/**
	 * The page slug for the sidebar.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $pageSlug = 'aioseo';

	/**
	 * Sidebar menu name.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	public $menuName = 'All in One SEO';

	/**
	 * An array of pages for the admin.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $pages = [];

	/**
	 * The current page we are enqueuing.
	 *
	 * @since 4.1.3
	 *
	 * @var string
	 */
	protected $currentPage;

	/**
	 * An array of items to add to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $adminBarMenuItems = [];

	/**
	 * An array of asset slugs to use.
	 *
	 * @since 4.1.9
	 *
	 * @var array
	 */
	protected $assetSlugs = [
		'plugins' => 'src/app/plugins/main.js',
		'pages'   => 'src/vue/pages/{page}/main.js'
	];

	/**
	 * Connect class instance.
	 *
	 * @since 4.4.3
	 *
	 * @var \AIOSEO\Plugin\Lite\Admin\Connect|null
	 */
	public $connect = null;

	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		new SeoAnalysis();

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (
			is_network_admin() &&
			! is_plugin_active_for_network( plugin_basename( AIOSEO_FILE ) )
		) {
			return;
		}

		add_action( 'aioseo_unslash_escaped_data_posts', [ $this, 'unslashEscapedDataPosts' ] );

		add_action( 'wp_ajax_aioseo-dismiss-active-menu-tooltip', [ $this, 'dismissActiveMenuTooltips' ] );

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_filter( 'language_attributes', [ $this, 'alwaysAddHtmlDirAttribute' ], 3000 );

		add_action( 'sanitize_comment_cookies', [ $this, 'init' ], 20 );

		add_action( 'admin_menu', [ $this, 'deactivationSurvey' ], 100 );

		add_action( 'in_admin_header', [ $this, 'addActiveMenuTooltips' ] );
	}

	/**
	 * Runs the deactivation survey.
	 *
	 * @since 4.5.5
	 *
	 * @return void
	 */
	public function deactivationSurvey() {
		new DeactivationSurvey( AIOSEO_PLUGIN_NAME, dirname( plugin_basename( AIOSEO_FILE ) ) );
	}

	/**
	 * Always add dir attribute to HTML tag.
	 *
	 * @since 4.1.9
	 *
	 * @param  string $output The HTML language attribute.
	 * @return string         The possibly modified HTML language attribute.
	 */
	public function alwaysAddHtmlDirAttribute( $output ) {
		if ( is_rtl() || preg_match( '/dir=[\'"](ltr|rtl|auto)[\'"]/i', $output ) ) {
			return $output;
		}

		return 'dir="ltr" ' . $output;
	}

	/**
	 * Initialize the admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Add the admin bar menu.
		if ( is_user_logged_in() && ( ! is_multisite() || ! is_network_admin() ) ) {
			add_action( 'admin_bar_menu', [ $this, 'adminBarMenu' ], 1000 );
		}

		if ( is_admin() ) {
			// Add the menu to the sidebar.
			add_action( 'admin_menu', [ $this, 'addMenu' ] );
			add_action( 'admin_menu', [ $this, 'hideScheduledActionsMenu' ], 99999 );

			// Add Score to Publish metabox.
			add_action( 'post_submitbox_misc_actions', [ $this, 'addPublishScore' ] );

			add_action( 'admin_init', [ $this, 'addPluginScripts' ] );

			// Add redirects messages to trashed posts.
			add_filter( 'bulk_post_updated_messages', [ $this, 'appendTrashedMessage' ] );

			$this->registerLinkFormatHooks();

			add_action( 'admin_footer', [ $this, 'addAioseoModalPortal' ] );
		}

		$this->loadTextDomain();
		$this->setPages();
	}

	/**
	 * Sets our menu pages.
	 * It is important this runs AFTER we've loaded the text domain.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	protected function setPages() {
		// TODO: Remove this after a couple months.
		$newIndicator = '<span class="aioseo-menu-new-indicator">&nbsp;NEW!</span>';

		$this->pages = [
			$this->pageSlug            => [
				'menu_title' => esc_html__( 'Dashboard', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-settings'          => [
				'menu_title' => is_network_admin()
					? esc_html__( 'Network Settings', 'all-in-one-seo-pack' )
					: esc_html__( 'General Settings', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-search-appearance' => [
				'menu_title' => esc_html__( 'Search Appearance', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-social-networks'   => [
				'menu_title' => esc_html__( 'Social Networks', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-sitemaps'          => [
				'menu_title' => esc_html__( 'Sitemaps', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-link-assistant'    => [
				'menu_title' => esc_html__( 'Link Assistant', 'all-in-one-seo-pack' ),
				'capability' => 'aioseo_link_assistant_settings',
				'parent'     => $this->pageSlug
			],
			'aioseo-redirects'         => [
				'menu_title' => esc_html__( 'Redirects', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-local-seo'         => [
				'menu_title' => esc_html__( 'Local SEO', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-seo-analysis'      => [
				'menu_title' => esc_html__( 'SEO Analysis', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-search-statistics' => [
				'menu_title' => esc_html__( 'Search Statistics', 'all-in-one-seo-pack' ) . $newIndicator,
				'page_title' => esc_html__( 'Search Statistics', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-tools'             => [
				'menu_title' => is_network_admin()
					? esc_html__( 'Network Tools', 'all-in-one-seo-pack' )
					: esc_html__( 'Tools', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-feature-manager'   => [
				'menu_title' => esc_html__( 'Feature Manager', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-monsterinsights'   => [
				'menu_title'          => esc_html__( 'Analytics', 'all-in-one-seo-pack' ),
				'parent'              => 'aioseo-monsterinsights',
				'hide_admin_bar_menu' => true
			],
			'aioseo-about'             => [
				'menu_title' => esc_html__( 'About Us', 'all-in-one-seo-pack' ),
				'parent'     => $this->pageSlug
			],
			'aioseo-seo-revisions'     => [
				'menu_title'          => esc_html__( 'SEO Revisions', 'all-in-one-seo-pack' ),
				'parent'              => 'aioseo-seo-revisions',
				'hide_admin_bar_menu' => true
			],
		];
	}

	/**
	 * Registers our custom link format hooks.
	 *
	 * @since 4.0.16
	 *
	 * @return void
	 */
	private function registerLinkFormatHooks() {
		if ( apply_filters( 'aioseo_disable_link_format', false ) ) {
			return;
		}

		add_action( 'wp_enqueue_editor', [ $this, 'addClassicLinkFormatScript' ], 999999 );

		global $wp_version;
		if ( version_compare( $wp_version, '5.3', '>=' ) || is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			add_action( 'current_screen', [ $this, 'addGutenbergLinkFormatScript' ] );
			add_action( 'enqueue_block_editor_assets', [ $this, 'enqueueBlockEditorLinkFormat' ] );
		}
	}

	/**
	 * Enqueues the link format script for the Block Editor.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	public function enqueueBlockEditorLinkFormat() {
		wp_enqueue_script( 'aioseo-link-format' );

		if ( ! wp_style_is( 'aioseo-link-format', 'enqueued' ) ) {
			wp_enqueue_style(
				'aioseo-link-format',
				aioseo()->core->assets->getAssetsPath( false ) . '/link-format/link-format-block.css',
				[],
				aioseo()->version
			);
		}
	}

	/**
	 * Enqueues the plugins script.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addPluginScripts() {
		global $pagenow;

		if ( 'plugins.php' !== $pagenow && 'plugin-install.php' !== $pagenow ) {
			return;
		}

		aioseo()->core->assets->load( $this->assetSlugs['plugins'], [], [
			'basename'           => AIOSEO_PLUGIN_BASENAME,
			'conflictingPlugins' => aioseo()->conflictingPlugins->getConflictingPluginSlugs()
		], 'aioseoPlugins' );
	}

	/**
	 * Enqueues our link format for the Classic Editor.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addClassicLinkFormatScript() {
		wp_deregister_script( 'wplink' );

		wp_enqueue_script(
			'wplink',
			aioseo()->core->assets->getAssetsPath( false ) . '/link-format/link-format-classic.js',
			[ 'jquery', 'wp-a11y' ],
			aioseo()->version,
			true
		);

		wp_localize_script(
			'wplink',
			'aioseoL10n',
			[
				'title'          => esc_html__( 'Insert/edit link', 'all-in-one-seo-pack' ),
				'update'         => esc_html__( 'Update', 'all-in-one-seo-pack' ),
				'save'           => esc_html__( 'Add Link', 'all-in-one-seo-pack' ),
				'noTitle'        => esc_html__( '(no title)' ), // phpcs:ignore AIOSEO.Wp.I18n.MissingArgDomain
				'labelTitle'     => esc_html__( 'Title', 'all-in-one-seo-pack' ),
				'noMatchesFound' => esc_html__( 'No results found.', 'all-in-one-seo-pack' ),
				'linkSelected'   => esc_html__( 'Link selected.', 'all-in-one-seo-pack' ),
				'linkInserted'   => esc_html__( 'Link has been inserted.', 'all-in-one-seo-pack' ),
				// Translators: 1 - HTML whitespace character, 2 - Opening HTML code tag, 3 - Closing HTML code tag.
				'noFollow'       => sprintf( esc_html__( '%1$sAdd %2$srel="nofollow"%3$s to link', 'all-in-one-seo-pack' ), '&nbsp;', '<code>', '</code>' ),
				// Translators: 1 - HTML whitespace character, 2 - Opening HTML code tag, 3 - Closing HTML code tag.
				'sponsored'      => sprintf( esc_html__( '%1$sAdd %2$srel="sponsored"%3$s to link', 'all-in-one-seo-pack' ), '&nbsp;', '<code>', '</code>' ),
				// Translators: 1 - HTML whitespace character, 2 - Opening HTML code tag, 3 - Closing HTML code tag.
				'ugc'            => sprintf( esc_html__( '%1$sAdd %2$srel="UGC"%3$s to link', 'all-in-one-seo-pack' ), '&nbsp;', '<code>', '</code>' ),
				// Translators: Minimum input length in characters to start searching posts in the "Insert/edit link" modal.
				'minInputLength' => (int) _x( '3', 'minimum input length for searching post links', 'all-in-one-seo-pack' ),
			]
		);
	}

	/**
	 * Registers our link format for the Block Editor.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addGutenbergLinkFormatScript() {
		if ( ! aioseo()->helpers->isScreenBase( 'post' ) ) {
			return;
		}

		$linkFormat = 'block';
		if ( is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			$data = get_plugin_data( WP_CONTENT_DIR . '/plugins/gutenberg/gutenberg.php', false, false );
			if ( version_compare( $data['Version'], '7.4.0', '<' ) ) {
				$linkFormat = 'block-old';
			}
		} else {
			if ( version_compare( get_bloginfo( 'version' ), '5.4', '<' ) ) {
				$linkFormat = 'block-old';
			}
		}

		wp_register_script(
			'aioseo-link-format',
			aioseo()->core->assets->getAssetsPath( false ) . "link-format/link-format-$linkFormat.js",
			[
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-plugins',
				'wp-components',
				'wp-edit-post',
				'wp-api',
				'wp-editor',
				'wp-hooks',
				'lodash'
			],
			aioseo()->version,
			true
		);
	}

	/**
	 * Adds All in One SEO to the Admin Bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function adminBarMenu() {
		if ( false === apply_filters( 'aioseo_show_in_admin_bar', true ) ) {
			// API filter hook to disable showing SEO in admin bar.
			return;
		}

		$firstPageSlug = $this->getFirstAvailablePageSlug();
		if ( ! $firstPageSlug ) {
			return;
		}

		$classes    = is_admin()
			? 'wp-core-ui wp-ui-notification aioseo-menu-notification-counter'
			: 'aioseo-menu-notification-counter aioseo-menu-notification-counter-frontend';
		$count      = count( Models\Notification::getAllActiveNotifications() );
		$htmlCount  = 10 > $count ? $count : '!';
		$htmlCount  = $htmlCount ? "<div class=\"{$classes}\">" . $htmlCount . '</div>' : '';
		$htmlCount .= '<div id="aioseo-menu-new-notifications"></div>';

		$this->adminBarMenuItems[] = [
			'id'    => 'aioseo-main',
			'title' => '<div class="ab-item aioseo-logo svg"></div><span class="text">' . esc_html__( 'SEO', 'all-in-one-seo-pack' ) . '</span>' . wp_kses_post( $htmlCount ),
			'href'  => esc_url( admin_url( 'admin.php?page=' . $firstPageSlug ) )
		];

		if ( $count ) {
			$this->adminBarMenuItems[] = [
				'parent' => 'aioseo-main',
				'id'     => 'aioseo-notifications',
				'title'  => esc_html__( 'Notifications', 'all-in-one-seo-pack' ) . ' <div class="aioseo-menu-notification-indicator"></div>',
				'href'   => admin_url( 'admin.php?page=' . $firstPageSlug . '&notifications=true' ),
			];
		}

		$this->adminBarMenuItems[] = aioseo()->standalone->seoPreview->getAdminBarMenuItemNode();

		$htmlSitemapRequested = aioseo()->htmlSitemap->isDedicatedPage;
		if ( ! is_admin() && ! $htmlSitemapRequested ) {
			$this->addPageAnalyzerMenuItems();
		}

		if ( $htmlSitemapRequested ) {
			global $wp_admin_bar;
			$wp_admin_bar->remove_node( 'edit' );
		}

		$this->addSettingsMenuItems();
		$this->addEditSeoMenuItem();

		// Actually add in the menu bar items.
		$this->addAdminBarMenuItems();
	}

	/**
	 * Actually adds the menu items to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function addAdminBarMenuItems() {
		global $wp_admin_bar;
		foreach ( $this->adminBarMenuItems as $item ) {
			$wp_admin_bar->add_menu( $item );
		}
	}

	/**
	 * Adds the Analyze this Page menu item to the admin bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addPageAnalyzerMenuItems() {
		global $wp;
		// Make sure the trailing slash matches the site configuration.
		$url = user_trailingslashit( home_url( $wp->request ) );

		if ( ! $url ) {
			return;
		}

		$this->adminBarMenuItems[] = [
			'id'     => 'aioseo-analyze-page',
			'parent' => 'aioseo-main',
			'title'  => esc_html__( 'Analyze this page', 'all-in-one-seo-pack' ),
		];

		$url = urlencode( $url );

		$submenuItems = [
			[
				'id'    => 'aioseo-analyze-page-inlinks',
				'title' => esc_html__( 'Check links to this URL', 'all-in-one-seo-pack' ),
				'href'  => 'https://search.google.com/search-console/links/drilldown?resource_id=' . urlencode( get_option( 'siteurl' ) ) . '&type=EXTERNAL&target=' . $url . '&domain=',
			],
			[
				'id'    => 'aioseo-analyze-page-cache',
				'title' => esc_html__( 'Check Google Cache', 'all-in-one-seo-pack' ),
				'href'  => '//webcache.googleusercontent.com/search?strip=1&q=cache:' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-structureddata',
				'title' => esc_html__( 'Google Rich Results Test', 'all-in-one-seo-pack' ),
				'href'  => 'https://search.google.com/test/rich-results?url=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-facebookdebug',
				'title' => esc_html__( 'Facebook Debugger', 'all-in-one-seo-pack' ),
				'href'  => 'https://developers.facebook.com/tools/debug/?q=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-pinterestvalidator',
				'title' => esc_html__( 'Pinterest Rich Pins Validator', 'all-in-one-seo-pack' ),
				'href'  => 'https://developers.pinterest.com/tools/url-debugger/?link=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-htmlvalidation',
				'title' => esc_html__( 'HTML Validator', 'all-in-one-seo-pack' ),
				'href'  => '//validator.w3.org/check?uri=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-cssvalidation',
				'title' => esc_html__( 'CSS Validator', 'all-in-one-seo-pack' ),
				'href'  => '//jigsaw.w3.org/css-validator/validator?uri=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-pagespeed',
				'title' => esc_html__( 'Google Page Speed Test', 'all-in-one-seo-pack' ),
				'href'  => 'https://pagespeed.web.dev/report?url=' . $url,
			],
			[
				'id'    => 'aioseo-analyze-page-google-mobile-friendly',
				'title' => esc_html__( 'Mobile-Friendly Test', 'all-in-one-seo-pack' ),
				'href'  => 'https://www.google.com/webmasters/tools/mobile-friendly/?url=' . $url,
			],
			[
				'id'    => 'aioseo-external-tools-linkedin-post-inspector',
				'title' => esc_html__( 'LinkedIn Post Inspector', 'all-in-one-seo-pack' ),
				'href'  => "https://www.linkedin.com/post-inspector/inspect/$url"
			]
		];

		foreach ( $submenuItems as $item ) {
			$this->adminBarMenuItems[] = [
				'parent' => 'aioseo-analyze-page',
				'id'     => $item['id'],
				'title'  => $item['title'],
				'href'   => $item['href'],
				'meta'   => [ 'target' => '_blank' ],
			];
		}
	}

	/**
	 * Adds the current post menu items to the admin bar.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	protected function addEditSeoMenuItem() {
		// Don't show if we're on the home page and the home page is the latest posts or if we're not in a singular context.
		if ( aioseo()->helpers->isDynamicHomePage() || ! is_singular() ) {
			return;
		}

		$post = aioseo()->helpers->getPost();
		if ( empty( $post ) ) {
			return;
		}

		$this->adminBarMenuItems[] = [
			'id'     => 'aioseo-edit-' . $post->ID,
			'parent' => 'aioseo-main',
			'title'  => esc_html__( 'Edit SEO', 'all-in-one-seo-pack' ),
			'href'   => get_edit_post_link( $post->ID ) . '#aioseo-settings',
		];
	}

	/**
	 * Add the settings items to the menu bar.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function addSettingsMenuItems() {
		if ( ! is_admin() ) {
			$this->adminBarMenuItems[] = [
				'id'     => 'aioseo-settings-main',
				'parent' => 'aioseo-main',
				// Translators: This is an action link users can click to open the General Settings menu.
				'title'  => esc_html__( 'SEO Settings', 'all-in-one-seo-pack' )
			];
		}

		$parent = is_admin() ? 'aioseo-main' : 'aioseo-settings-main';
		foreach ( $this->pages as $id => $page ) {
			// Remove page from admin bar menu.
			if ( ! empty( $page['hide_admin_bar_menu'] ) ) {
				continue;
			}

			if ( ! current_user_can( $this->getPageRequiredCapability( $id ) ) ) {
				continue;
			}

			$this->adminBarMenuItems[] = [
				'id'     => $id,
				'parent' => $parent,
				'title'  => $page['menu_title'],
				'href'   => esc_url( admin_url( 'admin.php?page=' . $id ) )
			];
		}
	}

	/**
	 * Get the required capability for given admin page.
	 *
	 * @since 4.1.3
	 *
	 * @param  string $pageSlug The slug of the page.
	 * @return string           The required capability.
	 */
	public function getPageRequiredCapability( $pageSlug ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' );
	}

	/**
	 * Add the menu inside of WordPress.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addMenu() {
		$this->addMainMenu();

		foreach ( $this->pages as $slug => $page ) {
			$hook = add_submenu_page(
				$page['parent'],
				! empty( $page['page_title'] ) ? $page['page_title'] : $page['menu_title'],
				$page['menu_title'],
				$this->getPageRequiredCapability( $slug ),
				$slug,
				[ $this, 'page' ]
			);

			add_action( "load-{$hook}", [ $this, 'hooks' ] );
		}

		if ( ! current_user_can( $this->getPageRequiredCapability( $this->pageSlug ) ) ) {
			remove_submenu_page( $this->pageSlug, $this->pageSlug );
		}

		global $submenu;
		if ( current_user_can( $this->getPageRequiredCapability( 'aioseo-redirects' ) ) ) {
			$submenu['tools.php'][] = [
				esc_html__( 'Redirection Manager', 'all-in-one-seo-pack' ),
				$this->getPageRequiredCapability( 'aioseo-redirects' ),
				admin_url( '/admin.php?page=aioseo-redirects' )
			];
		}

		if ( current_user_can( $this->getPageRequiredCapability( 'aioseo-search-statistics' ) ) ) {
			$submenu['index.php'][] = [
				esc_html__( 'SEO Statistics', 'all-in-one-seo-pack' ),
				$this->getPageRequiredCapability( 'aioseo-search-statistics' ),
				admin_url( '/admin.php?page=aioseo-search-statistics' )
			];
		}

		if ( current_user_can( $this->getPageRequiredCapability( 'aioseo-search-appearance' ) ) ) {
			$submenu['users.php'][] = [
				esc_html__( 'Author SEO', 'all-in-one-seo-pack' ),
				$this->getPageRequiredCapability( 'aioseo-search-appearance' ),
				admin_url( '/admin.php?page=aioseo-search-appearance/#author-seo' )
			];
		}

		// We use the global submenu, because we are adding an external link here.
		$count         = count( Models\Notification::getAllActiveNotifications() );
		$firstPageSlug = $this->getFirstAvailablePageSlug();
		if (
			$count &&
			! empty( $submenu[ $this->pageSlug ] ) &&
			! empty( $firstPageSlug )
		) {
			array_unshift( $submenu[ $this->pageSlug ], [
				esc_html__( 'Notifications', 'all-in-one-seo-pack' ) . '<div class="aioseo-menu-notification-indicator"></div>',
				$this->getPageRequiredCapability( $firstPageSlug ),
				admin_url( 'admin.php?page=' . $firstPageSlug . '&notifications=true' )
			] );
		}
	}

	/**
	 * Add the main menu.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $slug which slug to use.
	 * @return void
	 */
	protected function addMainMenu( $slug = 'aioseo' ) {
		add_menu_page(
			$this->menuName,
			$this->menuName,
			$this->getPageRequiredCapability( $slug ),
			$slug,
			'__return_true',
			'data:image/svg+xml;base64,' . base64_encode( aioseo()->helpers->logo( 16, 16, '#A0A5AA' ) ),
			'80.01234567890'
		);
	}

	/**
	 * Hides the Scheduled Actions menu.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function hideScheduledActionsMenu() {
		if ( ! apply_filters( 'aioseo_hide_action_scheduler_menu', true ) ) {
			return;
		}

		global $submenu;
		if ( ! isset( $submenu['tools.php'] ) ) {
			return;
		}

		foreach ( $submenu['tools.php'] as $index => $props ) {
			if ( ! empty( $props[2] ) && 'action-scheduler' === $props[2] ) {
				unset( $submenu['tools.php'][ $index ] );

				return;
			}
		}
	}

	/**
	 * Output the HTML for the page.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function page() {
		echo '<div id="aioseo-app">';
		aioseo()->templates->getTemplate( 'admin/settings-page.php' );
		echo '</div>';

		if ( aioseo()->standalone->flyoutMenu->isEnabled() ) {
			echo '<div id="aioseo-flyout-menu"></div>';
		}
	}

	/**
	 * Hooks for loading our pages.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function hooks() {
		$currentScreen = aioseo()->helpers->getCurrentScreen();
		global $admin_page_hooks;

		if ( ! is_object( $currentScreen ) || empty( $currentScreen->id ) || empty( $admin_page_hooks ) ) {
			return;
		}

		$pages = [
			'dashboard',
			'settings',
			'search-appearance',
			'social-networks',
			'sitemaps',
			'link-assistant',
			'redirects',
			'local-seo',
			'seo-analysis',
			'search-statistics',
			'tools',
			'feature-manager',
			'monsterinsights',
			'about',
			'seo-revisions'
		];

		foreach ( $pages as $page ) {
			$addScripts = false;

			if ( 'toplevel_page_aioseo' === $currentScreen->id ) {
				$addScripts = true;
			}

			if ( ! empty( $admin_page_hooks['aioseo'] ) && $currentScreen->id === $admin_page_hooks['aioseo'] ) {
				$addScripts = true;
			}

			if ( strpos( $currentScreen->id, 'aioseo-' . $page ) !== false ) {
				$addScripts = true;
			}

			if ( ! $addScripts ) {
				continue;
			}

			if ( 'tools' === $page ) {
				$this->checkForRedirects();
			}

			// Redirect our Analytics page to the appropriate plugin page.
			if ( 'monsterinsights' === $page ) {

				$pluginData = aioseo()->helpers->getPluginData();

				if (
					(
						$pluginData['miLite']['activated'] ||
						$pluginData['miPro']['activated']
					) &&
					function_exists( 'MonsterInsights' ) &&
					function_exists( 'monsterinsights_get_ua' )
				) {
					if ( (bool) monsterinsights_get_ua() ) {
						wp_safe_redirect( $pluginData['miLite']['adminUrl'] );
						exit;
					}
				}

				if (
					(
						$pluginData['emLite']['activated'] ||
						$pluginData['emPro']['activated']
					) &&
					function_exists( 'ExactMetrics' ) &&
					function_exists( 'exactmetrics_get_ua' )
				) {
					if ( (bool) exactmetrics_get_ua() ) {
						wp_safe_redirect( $pluginData['emLite']['adminUrl'] );
						exit;
					}
				}
			}

			// We don't want any plugin adding notices to our screens. Let's clear them out here.
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

			$this->currentPage = $page;
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ], 11 );
			add_action( 'admin_enqueue_scripts', [ aioseo()->filters, 'dequeueThirdPartyAssets' ], 99999 );
			add_action( 'admin_enqueue_scripts', [ aioseo()->filters, 'dequeueThirdPartyAssetsEarly' ], 0 );

			add_action( 'in_admin_footer', [ $this, 'addFooterPromotion' ] );
			add_filter( 'admin_footer_text', [ $this, 'addFooterText' ] );

			// Only enqueue the media library if we need it in our module
			if ( in_array( $page, [
				'social-networks',
				'search-appearance',
				'local-seo'
			], true ) ) {
				wp_enqueue_media();
			}

			break;
		}
	}

	/**
	 * Checks whether the current page is an AIOSEO menu page.
	 *
	 * @since 4.2.0
	 *
	 * @return bool Whether the current page is an AIOSEO menu page.
	 */
	public function isAioseoScreen() {
		$currentScreen = aioseo()->helpers->getCurrentScreen();
		if ( empty( $currentScreen->id ) ) {
			return false;
		}

		$adminPages = array_keys( $this->pages );
		$adminPages = array_map( function( $slug ) {
			if ( 'aioseo' === $slug ) {
				return 'toplevel_page_aioseo';
			}

			return 'all-in-one-seo_page_' . $slug;
		}, $adminPages );

		return in_array( $currentScreen->id, $adminPages, true );
	}

	/**
	 * Enqueue admin assets for the current page.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function enqueueAssets() {
		$page = str_replace( '{page}', $this->currentPage, $this->assetSlugs['pages'] );
		aioseo()->core->assets->load( $page, [], aioseo()->helpers->getVueData( $this->currentPage ) );
	}

	/**
	 * Add footer text to the WordPress admin screens.
	 *
	 * @since 4.0.0
	 *
	 * @return string The footer text.
	 */
	public function addFooterText() {
		$linkText = esc_html__( 'Give us a 5-star rating!', 'all-in-one-seo-pack' );
		$href     = 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews/?filter=5#new-post';

		$link1 = sprintf(
			'<a href="%1$s" target="_blank" title="%2$s">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
			$href,
			$linkText
		);

		$link2 = sprintf(
			'<a href="%1$s" target="_blank" title="%2$s">WordPress.org</a>',
			$href,
			$linkText
		);

		printf(
			// Translators: 1 - The plugin name ("All in One SEO"), - 2 - This placeholder will be replaced with star icons, - 3 - "WordPress.org" - 4 - The plugin name ("All in One SEO").
			esc_html__( 'Please rate %1$s %2$s on %3$s to help us spread the word. Thank you!', 'all-in-one-seo-pack' ),
			sprintf( '<strong>%1$s</strong>', esc_html( AIOSEO_PLUGIN_NAME ) ),
			wp_kses_post( $link1 ),
			wp_kses_post( $link2 )
		);

		// Stop WP Core from outputting its version number and instead add both theirs & ours.
		global $wp_version;
		printf(
			wp_kses_post( '<p class="alignright">%1$s</p>' ),
			sprintf(
				// Translators: 1 - WP Core version number, 2 - AIOSEO version number.
				esc_html__( 'WordPress %1$s | AIOSEO %2$s', 'all-in-one-seo-pack' ),
				esc_html( $wp_version ),
				esc_html( AIOSEO_VERSION )
			)
		);

		remove_filter( 'update_footer', 'core_update_footer' );

		return '';
	}

	/**
	 * Renders the SEO Score button in the Publish metabox.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post object.
	 * @return void
	 */
	public function addPublishScore( $post ) {
		$pageAnalysisCapability = aioseo()->access->hasCapability( 'aioseo_page_analysis' );
		if ( empty( $pageAnalysisCapability ) ) {
			return;
		}

		if ( aioseo()->helpers->isTruSeoEligible( $post->ID ) ) {
			$score = (int) Models\Post::getPost( $post->ID )->seo_score;
			$path  = 'M10 20C15.5228 20 20 15.5228 20 10C20 4.47715 15.5228 0 10 0C4.47716 0 0 4.47715 0 10C0 15.5228 4.47716 20 10 20ZM8.40767 3.65998C8.27222 3.45353 8.02129 3.357 7.79121 3.43828C7.52913 3.53087 7.27279 3.63976 7.02373 3.76429C6.80511 3.87361 6.69542 4.12332 6.74355 4.36686L6.91501 5.23457C6.95914 5.45792 6.86801 5.68459 6.69498 5.82859C6.42152 6.05617 6.16906 6.31347 5.94287 6.59826C5.80229 6.77526 5.58046 6.86908 5.36142 6.82484L4.51082 6.653C4.27186 6.60473 4.02744 6.71767 3.92115 6.94133C3.86111 7.06769 3.80444 7.19669 3.75129 7.32826C3.69815 7.45983 3.64929 7.59212 3.60464 7.72495C3.52562 7.96007 3.62107 8.21596 3.82396 8.35351L4.54621 8.84316C4.73219 8.96925 4.82481 9.19531 4.80234 9.42199C4.7662 9.78671 4.76767 10.1508 4.80457 10.5089C4.82791 10.7355 4.73605 10.9619 4.55052 11.0886L3.82966 11.5811C3.62734 11.7193 3.53274 11.9753 3.61239 12.2101C3.70314 12.4775 3.80985 12.7391 3.93188 12.9932C4.03901 13.2163 4.28373 13.3282 4.5224 13.2791L5.37279 13.1042C5.59165 13.0591 5.8138 13.1521 5.95491 13.3287C6.17794 13.6077 6.43009 13.8653 6.70918 14.0961C6.88264 14.2396 6.97459 14.4659 6.93122 14.6894L6.76282 15.5574C6.71551 15.8013 6.8262 16.0507 7.04538 16.1591C7.16921 16.2204 7.29563 16.2782 7.42457 16.3324C7.55352 16.3867 7.68316 16.4365 7.81334 16.4821C8.19418 16.6154 8.72721 16.1383 9.1213 15.7855C9.31563 15.6116 9.4355 15.3654 9.43677 15.1018C9.43677 15.1004 9.43678 15.099 9.43678 15.0976L9.43677 13.6462C9.43677 13.6308 9.43736 13.6155 9.43852 13.6004C8.27454 13.3165 7.40918 12.248 7.40918 10.9732V9.43198C7.40918 9.31483 7.50224 9.21986 7.61706 9.21986H8.338V7.70343C8.338 7.49405 8.50433 7.32432 8.70952 7.32432C8.9147 7.32432 9.08105 7.49405 9.08105 7.70343V9.21986H11.0316V7.70343C11.0316 7.49405 11.1979 7.32432 11.4031 7.32432C11.6083 7.32432 11.7746 7.49405 11.7746 7.70343V9.21986H12.4956C12.6104 9.21986 12.7034 9.31483 12.7034 9.43198V10.9732C12.7034 12.2883 11.7825 13.3838 10.5628 13.625C10.5631 13.632 10.5632 13.6391 10.5632 13.6462L10.5632 15.0914C10.5632 15.36 10.6867 15.6107 10.8868 15.7853C11.2879 16.1351 11.8302 16.6079 12.2088 16.4742C12.4708 16.3816 12.7272 16.2727 12.9762 16.1482C13.1949 16.0389 13.3046 15.7891 13.2564 15.5456L13.085 14.6779C13.0408 14.4545 13.132 14.2278 13.305 14.0838C13.5785 13.8563 13.8309 13.599 14.0571 13.3142C14.1977 13.1372 14.4195 13.0434 14.6385 13.0876L15.4892 13.2595C15.7281 13.3077 15.9725 13.1948 16.0788 12.9711C16.1389 12.8448 16.1955 12.7158 16.2487 12.5842C16.3018 12.4526 16.3507 12.3204 16.3953 12.1875C16.4744 11.9524 16.3789 11.6965 16.176 11.559L15.4537 11.0693C15.2678 10.9432 15.1752 10.7171 15.1976 10.4905C15.2338 10.1258 15.2323 9.76167 15.1954 9.40357C15.1721 9.17699 15.2639 8.95062 15.4495 8.82387L16.1703 8.33141C16.3726 8.1932 16.4672 7.93715 16.3876 7.70238C16.2968 7.43495 16.1901 7.17337 16.0681 6.91924C15.961 6.69615 15.7162 6.58422 15.4776 6.63333L14.6272 6.8083C14.4083 6.85333 14.1862 6.76033 14.0451 6.58377C13.822 6.30474 13.5699 6.04713 13.2908 5.81632C13.1173 5.67287 13.0254 5.44652 13.0688 5.22301L13.2372 4.35503C13.2845 4.11121 13.1738 3.86179 12.9546 3.75334C12.8308 3.69208 12.7043 3.63424 12.5754 3.58002C12.4465 3.52579 12.3168 3.47593 12.1866 3.43037C11.9562 3.34974 11.7055 3.44713 11.5707 3.65416L11.0908 4.39115C10.9672 4.58093 10.7457 4.67543 10.5235 4.65251C10.1661 4.61563 9.80932 4.61712 9.45837 4.65477C9.23633 4.6786 9.01448 4.58486 8.89027 4.39554L8.40767 3.65998Z'; // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			?>
			<div class="misc-pub-section aioseo-score-settings">
				<svg viewBox="0 0 20 20" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="<?php echo esc_attr( $path ); ?>" fill="#82878C" />
				</svg>
				<span>
					<?php
						echo sprintf(
							// Translators: 1 - The short plugin name ("AIOSEO").
							esc_html__( '%1$s Score', 'all-in-one-seo-pack' ),
							esc_html( AIOSEO_PLUGIN_SHORT_NAME )
						);
					?>
				</span>
				<div id="aioseo-post-settings-sidebar-button" class="aioseo-score-button classic-editor <?php echo esc_attr( $this->getScoreClass( $score ) ); ?>">
					<span id="aioseo-post-score"><?php echo esc_attr( $score . '/100' ); ?></span>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Check the query args to see if we need to redirect to an external URL.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	protected function checkForRedirects() {}

	/**
	 * Starts the cleaning procedure to fix escaped, corrupted data.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function scheduleUnescapeData() {
		aioseo()->core->cache->update( 'unslash_escaped_data_posts', time(), WEEK_IN_SECONDS );
		aioseo()->actionScheduler->scheduleSingle( 'aioseo_unslash_escaped_data_posts', 120 );
	}

	/**
	 * Unlashes corrupted escaped data in posts.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function unslashEscapedDataPosts() {
		$postsToUnslash = apply_filters( 'aioseo_debug_unslash_escaped_posts', 200 );
		$timeStarted    = gmdate( 'Y-m-d H:i:s', aioseo()->core->cache->get( 'unslash_escaped_data_posts' ) );

		$posts = aioseo()->core->db->start( 'aioseo_posts' )
			->select( '*' )
			->whereRaw( "updated < '$timeStarted'" )
			->orderBy( 'updated ASC' )
			->limit( $postsToUnslash )
			->run()
			->result();

		if ( empty( $posts ) ) {
			aioseo()->core->cache->delete( 'unslash_escaped_data_posts' );

			return;
		}

		aioseo()->actionScheduler->scheduleSingle( 'aioseo_unslash_escaped_data_posts', 120, [], true );

		foreach ( $posts as $post ) {
			$aioseoPost = Models\Post::getPost( $post->post_id );
			foreach ( $this->getColumnsToUnslash() as $columnName ) {
				// Remove backslashes but preserve encoded unicode characters in JSON data.
				$aioseoPost->$columnName = aioseo()->helpers->pregReplace( '/\\\(?![uU][+]?[a-zA-Z0-9]{4})/', '', $post->$columnName );
			}
			$aioseoPost->images          = null;
			$aioseoPost->image_scan_date = null;
			$aioseoPost->videos          = null;
			$aioseoPost->video_scan_date = null;
			$aioseoPost->save();
		}
	}

	/**
	 * Returns a list of names of database columns that should be unslashed when cleaning the corrupted data.
	 *
	 * @since 4.1.2
	 *
	 * @return array The list of column names.
	 */
	protected function getColumnsToUnslash() {
		return [
			'title',
			'description',
			'keywords',
			'keyphrases',
			'page_analysis',
			'canonical_url',
			'og_title',
			'og_description',
			'og_image_custom_url',
			'og_image_custom_fields',
			'og_video',
			'og_custom_url',
			'og_article_section',
			'og_article_tags',
			'twitter_title',
			'twitter_description',
			'twitter_image_custom_url',
			'twitter_image_custom_fields',
			'schema_type_options',
			'local_seo',
			'options'
		];
	}

	/**
	 * Get the first available page item for the current user.
	 *
	 * @since 4.1.3
	 *
	 * @return bool|string The page slug.
	 */
	public function getFirstAvailablePageSlug() {
		foreach ( $this->pages as $slug => $page ) {
			// Ignore other pages.
			if ( $this->pageSlug !== $page['parent'] ) {
				continue;
			}

			if ( current_user_can( $this->getPageRequiredCapability( $slug ) ) ) {
				return $slug;
			}
		}

		return false;
	}

	/**
	 * Appends a message to the default WordPress "trashed" message.
	 *
	 * @since 4.1.2
	 *
	 * @param  array $messages The original messages.
	 * @return array           The modified messages.
	 */
	public function appendTrashedMessage( $messages ) {
		// Let advanced users override this.
		if ( apply_filters( 'aioseo_redirects_disable_trashed_posts_suggestions', false ) ) {
			return $messages;
		}

		if ( function_exists( 'aioseoRedirects' ) && aioseoRedirects()->options->monitor->trash ) {
			return $messages;
		}

		if ( empty( $_GET['ids'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended	
			return $messages;
		}

		$ids = array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_GET['ids'] ) ) ) ); // phpcs:ignore HM.Security.NonceVerification.Recommended, HM.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended, Generic.Files.LineLength.MaxExceeded

		$posts = [];
		foreach ( $ids as $id ) {
			// We need to clone the post here so we can get a real permalink for the post even if it is not published already.
			$post = aioseo()->helpers->getPost( $id );
			if ( ! is_a( $post, 'WP_Post' ) ) {
				continue;
			}

			$post->post_status = 'publish';
			$post->post_name   = sanitize_title(
				$post->post_name ? $post->post_name : $post->post_title,
				$post->ID
			);

			$posts[] = [
				'url'    => str_replace( '__trashed', '', get_permalink( $post ) ),
				'target' => '/',
				'type'   => 301
			];
		}

		if ( empty( $posts ) ) {
			return $messages;
		}

		$url         = aioseo()->slugMonitor->manualRedirectUrl( $posts );
		$addRedirect = _n( 'Add Redirect to improve SEO', 'Add Redirects to improve SEO', count( $posts ), 'all-in-one-seo-pack' );

		$messages['post']['trashed'] = $messages['post']['trashed'] . '&nbsp;<a href="' . $url . '" class="aioseo-redirects-trashed-post">' . $addRedirect . '</a> |';
		$messages['page']['trashed'] = $messages['page']['trashed'] . '&nbsp;<a href="' . $url . '" class="aioseo-redirects-trashed-post">' . $addRedirect . '</a> |';

		return $messages;
	}

	/**
	* Get the class name for the Score button.
	* Depending on the score the button should have different color.
	*
	* @since 4.0.0
	*
	* @param  int    $score The content to retrieve from the remote URL.
	* @return string        The class name for Score button.
	*/
	private function getScoreClass( $score ) {
		$scoreClass = 50 < $score ? 'score-orange' : 'score-red';

		if ( 0 === $score ) {
			$scoreClass = 'score-none';
		}

		if ( $score >= 80 ) {
			$scoreClass = 'score-green';
		}

		return $scoreClass;
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function loadTextDomain() {
		aioseo()->helpers->loadTextDomain( 'all-in-one-seo-pack' );
	}

	/**
	 * Add the div for the modal portal.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function addAioseoModalPortal() {
		echo '<div id="aioseo-modal-portal"></div>';
	}

	/**
	 * Outputs the element we can mount our footer promotion standalone Vue app on.
	 * Also enqueues the assets.
	 *
	 * @since   4.3.6
	 * @version 4.4.3
	 *
	 * @return void
	 */
	public function addFooterPromotion() {
		echo wp_kses_post( '<div id="aioseo-footer-links"></div>' );

		aioseo()->core->assets->load( 'src/vue/standalone/footer-links/main.js' );
	}

	/**
	 * Adds the active menu tooltips.
	 *
	 * @since 4.6.9
	 *
	 * @return void
	 */
	public function addActiveMenuTooltips() {
		// This pointer slug is set here so we can scale later if we add other pointers.
		$pointer = 'author-seo';

		// If the user has already dismissed this tooltip, don't show it again.
		if ( get_user_meta( get_current_user_id(), "_aioseo-$pointer-dismissed", true ) ) {
			return;
		}

		// If the Author SEO addon is already loaded, don't show the tooltip.
		if ( aioseo()->addons->getLoadedAddon( 'eeat' ) ) {
			return;
		}

		// If the user cannot access the menu page or activate plugins, bail.
		if (
			! current_user_can( 'aioseo_search_appearance_settings' ) ||
			! current_user_can( 'activate_plugins' )
		) {
			return;
		}

		// If the user is activating the Author SEO addon, dismiss the tooltip.
		if ( ! empty( $_GET['aioseo-action'] ) && 'activate-author-seo' === sanitize_text_field( wp_unslash( $_GET['aioseo-action'] ) ) ) { // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized, HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended, Generic.Files.LineLength.MaxExceeded
			update_user_meta( get_current_user_id(), "_aioseo-$pointer-dismissed", true );

			return;
		}

		// If we are already on the search appearance page, don't show the tooltip.
		if ( 'all-in-one-seo_page_aioseo-search-appearance' === aioseo()->helpers->getCurrentScreen()->id ) {
			return;
		}

		// Enqueue the pointer scripts and styles.
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		// Output the pointer script.
		$nonce    = wp_create_nonce( 'aioseo-dismiss-active-menu-tooltip' );
		$title    = esc_html__( 'NEW! Author SEO for E-E-A-T', 'all-in-one-seo-pack' );
		$subtitle = esc_html__( 'Boost your E-E-A-T with our latest Author SEO addon!', 'all-in-one-seo-pack' );
		$content  = esc_html__( 'Optimize your site for Google\'s E-E-A-T ranking factor by proving your writer\'s expertise through author schema markup and new UI elements.', 'all-in-one-seo-pack' ); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		$button   = sprintf(
			'<p><a class=\"button button-primary\" href=\"%s\">%s</a></p>',
			admin_url( 'admin.php?aioseo-action=activate-author-seo&page=aioseo-search-appearance#author-seo' ),
			esc_html__( 'Activate Author SEO', 'all-in-one-seo-pack' )
		);
		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				var isClosed = false;
				var pointer = $( '#toplevel_page_aioseo > a' ).pointer( {
					content : "<h3><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><\/h3>" +
						"<h4><?php echo $subtitle; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><\/h4>" +
						"<p><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>" +
						"<?php echo $button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>",
					position : {
						edge  : <?php echo is_rtl() ? "'right'" : "'left'"; ?>,
						align : 'center'
					},
					pointerWidth : 420,
					show: function(event, el) {
						el.pointer.css({'position':'fixed'});
						el.pointer.addClass('aioseo-wp-pointer');
					},
					close : function() {
						isClosed = true;
						jQuery.post(
							ajaxurl,
							{
								pointer     : '<?php echo esc_js( $pointer ); ?>',
								action      : 'aioseo-dismiss-active-menu-tooltip',
								_ajax_nonce : '<?php echo esc_js( $nonce ); ?>'
							}
						);
					}
				} ).pointer('open');
			} );
		</script>
		<?php
	}

	/**
	 * Dismisses the active menu tooltips.
	 *
	 * @since 4.6.9
	 *
	 * @return void
	 */
	public function dismissActiveMenuTooltips() {
		// Bail if the request is not an AJAX request or the action is not the one we expect.
		if ( ! isset( $_POST['action'] ) || 'aioseo-dismiss-active-menu-tooltip' !== $_POST['action'] || empty( $_POST['pointer'] ) ) {
			return;
		}

		// Check the nonce.
		check_ajax_referer( 'aioseo-dismiss-active-menu-tooltip', 'nonce' );

		// Permanently dismiss the tooltip for the current user.
		$pointer = sanitize_text_field( wp_unslash( $_POST['pointer'] ) );
		update_user_meta( get_current_user_id(), "_aioseo-$pointer-dismissed", true );
	}
}