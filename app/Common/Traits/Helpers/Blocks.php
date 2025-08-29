<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains block specific helper methods.
 *
 * @since 4.8.7
 */
trait Blocks {
	/**
	 * Blocks known to conflict with AIOSEO.
	 *
	 * @since 4.8.7
	 *
	 * @var array
	 */
	private $conflictingBlocks = [
		'edd/checkout' => 'EDD Checkout',
	];

	/**
	 * Returns the content with blocks replaced.
	 *
	 * @since 4.8.7
	 *
	 * @param  string $content    The content.
	 * @param  bool   $noConflict Whether to remove the conflicting blocks.
	 * @return string             The content with blocks replaced.
	 */
	public function doBlocks( $content, $noConflict = true ) {
		if ( $noConflict ) {
			$conflictingBlocks = apply_filters( 'aioseo_conflicting_blocks', $this->conflictingBlocks );

			static $preRenderBlockCallback = null;
			if ( null === $preRenderBlockCallback ) {
				$preRenderBlockCallback = function( $preRender, $parsedBlock ) use ( $conflictingBlocks ) {
					if ( isset( $conflictingBlocks[ $parsedBlock['blockName'] ] ) ) {
						return '';
					}

					return $preRender;
				};
			}

			add_filter( 'pre_render_block', $preRenderBlockCallback, 10, 2 );

			$content = do_blocks( $content );

			remove_filter( 'pre_render_block', $preRenderBlockCallback );

			return $content;
		}

		return do_blocks( $content );
	}
}