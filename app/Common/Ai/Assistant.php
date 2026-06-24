<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Assistant handler for managing AI Assistant blocks.
 *
 * @since 4.9.1
 */
class Assistant {
	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.9.1
	 *
	 * @return array The data.
	 */
	public function getVueDataEdit() {
		$isEnabled = ! aioseo()->ai->isDisabled();

		return [
			'extend' => array_fill_keys( [
				'block',
				'blockEditorInserterButton',
				'paragraphPlaceholder'
			], $isEnabled )
		];
	}
}