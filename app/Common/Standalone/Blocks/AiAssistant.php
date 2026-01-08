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
		if ( ! $this->isEnabled() ) {
			return;
		}

		aioseo()->blocks->registerBlock( 'ai-assistant' );
	}

	/**
	 * Returns whether the AI Assistant block is enabled.
	 *
	 * @since 4.9.3
	 *
	 * @return bool Whether the AI Assistant block is enabled.
	 */
	public function isEnabled() {
		return (bool) apply_filters( 'aioseo_ai_assistant_block_enabled', true );
	}
}