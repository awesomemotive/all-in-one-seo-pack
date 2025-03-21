<?php
namespace AIOSEO\Plugin\Common\Standalone\BbPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the bbPress integration with AIOSEO.
 *
 * @since 4.8.1
 */
class BbPress {
	/**
	 * Instance of the Component class.
	 *
	 * @since 4.8.1
	 *
	 * @var Component
	 */
	public $component;

	/**
	 * Class constructor.
	 *
	 * @since 4.8.1
	 */
	public function __construct() {
		if (
			aioseo()->helpers->isAjaxCronRestRequest() ||
			! aioseo()->helpers->isPluginActive( 'bbpress' )
		) {
			return;
		}

		// Hook into `plugins_loaded` to ensure bbPress has loaded some necessary functions.
		add_action( 'plugins_loaded', [ $this, 'maybeLoad' ], 20 );
	}

	/**
	 * Hooked into `plugins_loaded` action hook.
	 *
	 * @since 4.8.1
	 *
	 * @return void
	 */
	public function maybeLoad() {
		// If the bbPress version is below 2 we bail.
		if ( ! function_exists( 'bbp_get_version' ) || version_compare( bbp_get_version(), '2', '<' ) ) {
			return;
		}

		add_action( 'wp', [ $this, 'setComponent' ] );
	}

	/**
	 * Hooked into `wp` action hook.
	 *
	 * @since 4.8.1
	 *
	 * @return void
	 */
	public function setComponent() {
		$this->component = new Component();
	}
}