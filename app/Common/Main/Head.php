<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Meta;

/**
 * Outputs anything we need to the head of the site.
 *
 * @since 4.0.0
 */
class Head {
	/**
	 * The page title.
	 *
	 * @since 4.0.5
	 *
	 * @var string
	 */
	private static $pageTitle = null;

	/**
	 * Title class instance.
	 *
	 * @since 4.3.9
	 *
	 * @var Title
	 */
	private $title;

	/**
	 * Links class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Meta\Links
	 */
	protected $links = null;

	/**
	 * Keywords class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Meta\Keywords
	 */
	protected $keywords = null;

	/**
	 * Verification class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Meta\SiteVerification
	 */
	protected $verification = null;

	/**
	 * The views to output.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	protected $views = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'registerTitleHooks' ], 1000 );
		add_action( 'wp_head', [ $this, 'init' ], 1 );

		$this->title        = new Title();
		$this->links        = new Meta\Links();
		$this->keywords     = new Meta\Keywords();
		$this->verification = new Meta\SiteVerification();
		$this->views        = [
			'meta'    => AIOSEO_DIR . '/app/Common/Views/main/meta.php',
			'social'  => AIOSEO_DIR . '/app/Common/Views/main/social.php',
			'schema'  => AIOSEO_DIR . '/app/Common/Views/main/schema.php',
			'clarity' => AIOSEO_DIR . '/app/Common/Views/main/clarity.php'
		];
	}

	/**
	 * Registers our title hooks.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function registerTitleHooks() {
		if ( apply_filters( 'aioseo_disable', false ) || apply_filters( 'aioseo_disable_title_rewrites', false ) ) {
			return;
		}

		add_filter( 'pre_get_document_title', [ $this, 'getTitle' ], 99999 );
		add_filter( 'wp_title', [ $this, 'getTitle' ], 99999 );
		if ( ! current_theme_supports( 'title-tag' ) ) {
			add_action( 'template_redirect', [ $this->title, 'startOutputBuffering' ], 99999 );
			add_action( 'wp_head', [ $this->title, 'endOutputBuffering' ], 99999 );
		}
	}

	/**
	 * Initializes the class.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function init() {
		$included = new Meta\Included();
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ! $included->isIncluded() ) {
			return;
		}

		$this->output();
	}

	/**
	 * Returns the page title.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $wpTitle   The original page title from WordPress.
	 * @return string $pageTitle The page title.
	 */
	public function getTitle( $wpTitle = '' ) {
		if ( null !== self::$pageTitle ) {
			return self::$pageTitle;
		}
		self::$pageTitle = aioseo()->meta->title->filterPageTitle( $wpTitle );

		return self::$pageTitle;
	}

	/**
	 * The output function itself.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function output() {
		remove_action( 'wp_head', 'rel_canonical' );

		$views = apply_filters( 'aioseo_meta_views', $this->views );
		if ( empty( $views ) ) {
			return;
		}

		echo "\n\t\t<!-- " . sprintf(
			'%1$s %2$s',
			esc_html( AIOSEO_PLUGIN_NAME ),
			aioseo()->helpers->getAioseoVersion() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		) . " - aioseo.com -->\n";

		foreach ( $views as $view ) {
			require $view;
		}

		echo "\t\t<!-- " . esc_html( AIOSEO_PLUGIN_NAME ) . " -->\n\n";
	}
}