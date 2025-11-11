<?php
namespace AIOSEO\Plugin\Common\Standalone\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Table of Contents Block.
 *
 * @since 4.2.3
 */
class TableOfContents extends Blocks {
	/**
	 * Initializes our blocks.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function init() {
		$this->register();

		// Enqueue the block's assets.
		add_action( 'enqueue_block_assets', [ $this, 'enqueueBlockAssets' ] );
	}

	/**
	 * Register the block.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function register() {
		aioseo()->blocks->registerBlock( 'table-of-contents', [
			'render_callback' => [ $this, 'render' ]
		] );
	}

	/**
	 * Enqueues the block's assets.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function enqueueBlockAssets() {
		aioseo()->core->assets->load( 'src/vue/standalone/blocks/table-of-contents/frontend.js' );
	}

	/**
	 * Get the default attributes for the block.
	 *
	 * @since 4.9.0
	 *
	 * @return array
	 */
	private function getDefaultAttributes() {
		return [
			'listStyle'       => 'ul',
			'collapsibleType' => 'off',
			'collapsed'       => false,
			'collapsedTitle'  => __( 'Show Table of Contents', 'all-in-one-seo-pack' ),
			'expandedTitle'   => __( 'Hide Table of Contents', 'all-in-one-seo-pack' ),
			'mode'            => null,
			'headings'        => [],
			'reOrdered'       => false
		];
	}

	/**
	 * Get the nested headings for the block.
	 *
	 * @since 4.9.0
	 *
	 * @param array  $headings  The headings to get.
	 * @param string $listStyle The list style to use.
	 *
	 * @return string
	 */
	private function getNestedHeadings( $headings, $listStyle ) {
		$htmlString = '<' . $listStyle . '>';

		foreach ( $headings as $heading ) {
			if ( $heading['hidden'] ) {
				continue;
			}

			$listItem = '<li>';

			$content = empty( $heading['editedContent'] ) ? $heading['content'] : $heading['editedContent'];

			$listItem .= '<a class="aioseo-toc-item" href="#' . esc_attr( $heading['anchor'] ) . '">' . esc_html( $content ) . '</a>';

			if ( ! empty( $heading['headings'] ) ) {
				$listItem .= $this->getNestedHeadings( $heading['headings'], $listStyle );
			}

			$listItem .= '</li>';

			$htmlString .= $listItem;
		}

		$htmlString .= '</' . $listStyle . '>';

		return $htmlString;
	}

	/**
	 * Get the collapsed icon for the block.
	 *
	 * @since 4.9.0
	 *
	 * @return string
	 */
	private function getCollapsedIcon() {
		return '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
		  <path d="M6 8H0V6H6V0H8V6H14V8H8V14H6V8Z" fill="#005AE0"/>
		</svg>';
	}

	/**
	 * Get the expanded icon for the block.
	 *
	 * @since 4.9.0
	 *
	 * @return string
	 */
	private function getExpandedIcon() {
		return '<svg width="14" height="2" viewBox="0 0 14 2" fill="none" xmlns="http://www.w3.org/2000/svg">
		  <path d="M0 2V0H14V2H0Z" fill="#005AE0"/>
		</svg>';
	}

	/**
	 * Get the HTML for the block.
	 *
	 * @since 4.9.0
	 *
	 * @param array $attributes The attributes for the block.
	 *
	 * @return string
	 */
	private function getHtml( $attributes ) {
		$htmlString       = $this->getNestedHeadings( $attributes['headings'], $attributes['listStyle'] );
		$class1           = 'open' === $attributes['collapsibleType'] ? 'aioseo-toc-collapsed' : '';
		$class2           = 'closed' === $attributes['collapsibleType'] ? 'aioseo-toc-collapsed' : '';
		$class3           = 'closed' === $attributes['collapsibleType'] ? 'aioseo-toc-collapsed' : '';
		$blockCustomClass = isset( $attributes['className'] ) ? $attributes['className'] : '';

		$fullHtmlString = '<div class="' . $blockCustomClass . '">
			<div class="aioseo-toc-header">
				<header class="aioseo-toc-header-area">
					<div class="aioseo-toc-header-title aioseo-toc-header-collapsible-closed ' . $class1 . '">
					<div class="aioseo-toc-header-collapsible">
						' . $this->getCollapsedIcon() . '
					</div>
					' . esc_html( $attributes['collapsedTitle'] ) . '
					</div>

					<div class="aioseo-toc-header-title aioseo-toc-header-collapsible-open ' . $class2 . '">
					<div class="aioseo-toc-header-collapsible">
						' . $this->getExpandedIcon() . '
					</div>
					' . esc_html( $attributes['expandedTitle'] ) . '
					</div>
				</header>
				<div class="aioseo-toc-contents ' . $class3 . '">
					' . $htmlString . '
				</div>
			</div>
		</div>';

		$fullHtmlString = 'off' === $attributes['collapsibleType'] ? $htmlString : $fullHtmlString;

		return $fullHtmlString;
	}

	/**
	 * Render the block.
	 *
	 * @since 4.9.0
	 *
	 * @param array $attributes The attributes for the block.
	 *
	 * @return string
	 */
	public function render( $attributes ) {
		if ( empty( $attributes['headings'] ) ) {
			return null;
		}

		return $this->getHtml( array_merge( $this->getDefaultAttributes(), $attributes ) );
	}
}