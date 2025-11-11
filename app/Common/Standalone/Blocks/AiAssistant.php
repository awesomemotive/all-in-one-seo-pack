<?php
namespace AIOSEO\Plugin\Common\Standalone\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Assistant Block.
 *
 * @since 4.8.8
 */
class AiAssistant extends Blocks {
	/**
	 * Register the block.
	 *
	 * @since 4.8.8
	 *
	 * @return void
	 */
	public function register() {
		aioseo()->blocks->registerBlock( 'ai-assistant' );
	}
}