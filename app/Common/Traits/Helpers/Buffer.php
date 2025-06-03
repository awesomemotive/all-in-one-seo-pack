<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains buffer specific helper methods.
 *
 * @since 4.8.3
 */
trait Buffer {
	/**
	 * Clears all output buffers.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	public function clearBuffers() {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
	}
}