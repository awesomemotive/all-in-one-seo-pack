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
	 * @param  int|null $objectId The object ID.
	 * @return array              The data.
	 */
	public function getVueDataEdit( $objectId = null ) {
		$objectId = $objectId ?: absint( get_the_ID() );

		return [
			'extend' => [
				'blockEditorInserterButton' => apply_filters( 'aioseo_ai_assistant_extend_block_editor_inserter_button', true, $objectId ),
			]
		];
	}
}