<?php
namespace AIOSEO\Plugin\Common\Standalone\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FaqPage Block.
 *
 * @since 4.2.3
 */
class FaqPage extends Blocks {
	/**
	 * Register the block.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function register() {
		aioseo()->blocks->registerBlock( 'faq',
			[
				'render_callback' => function( $attributes, $content ) {
					if ( isset( $attributes['hidden'] ) && true === $attributes['hidden'] ) {
						return '';
					}

					return $content;
				},
			]
		);
	}
}