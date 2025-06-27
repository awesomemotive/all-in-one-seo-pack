<?php
namespace AIOSEO\Plugin\Common\Standalone\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KeyPoints Block.
 *
 * @since 4.8.4
 */
class KeyPoints extends Blocks {
	/**
	 * Register the block.
	 *
	 * @since 4.8.4
	 *
	 * @return void
	 */
	public function register() {
		aioseo()->blocks->registerBlock( 'aioseo/key-points' );
	}
}