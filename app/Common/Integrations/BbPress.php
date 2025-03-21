<?php
namespace AIOSEO\Plugin\Common\Integrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to integrate with the bbPress plugin.
 *
 * @since 4.8.1
 */
class BbPress {
	/**
	 * Returns whether the current page is a bbPress component page.
	 *
	 * @since 4.8.1
	 *
	 * @return bool Whether the current page is a bbPress component page.
	 */
	public static function isComponentPage() {
		return ! empty( aioseo()->standalone->bbPress->component->templateType );
	}
}