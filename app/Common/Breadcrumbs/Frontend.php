<?php
namespace AIOSEO\Plugin\Common\Breadcrumbs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Frontend.
 *
 * @since 4.1.1
 */
class Frontend {
	/**
	 * A local 'cached' crumb array.
	 *
	 * @since 4.1.1
	 *
	 * @var array
	 */
	public $breadcrumbs = [];

	/**
	 * Gets the current page's breadcrumbs.
	 *
	 * @since 4.1.1
	 *
	 * @return array
	 */
	public function getBreadcrumbs() {
		if ( ! empty( $this->breadcrumbs ) ) {
			return apply_filters( 'aioseo_breadcrumbs_trail', $this->breadcrumbs );
		}

		$type      = '';
		$reference = get_queried_object();
		// These types need the queried object for reference.
		if ( is_object( $reference ) ) {
			if ( is_single() ) {
				$type = 'single';
			}

			if ( is_singular( 'post' ) ) {
				$type = 'post';
			}

			if ( is_page() && ! is_front_page() ) {
				$type = 'page';
			}

			if ( is_category() || is_tag() ) {
				$type = 'category';
			}

			if ( is_tax() ) {
				$type = 'taxonomy';
			}

			if ( is_post_type_archive() ) {
				$type = 'postTypeArchive';
			}

			if ( is_author() ) {
				$type = 'author';
			}

			if ( is_home() ) {
				$type = 'blog';
			}

			// Support WC shop page.
			if ( aioseo()->helpers->isWooCommerceShopPage() ) {
				$type = 'wcShop';
			}

			// Support WC products.
			if ( aioseo()->helpers->isWooCommerceProductPage() ) {
				$type = 'wcProduct';
			}
		}

		if ( is_date() ) {
			$type      = 'date';
			$reference = [
				'year'  => get_query_var( 'year' ),
				'month' => get_query_var( 'monthnum' ),
				'day'   => get_query_var( 'day' )
			];
		}

		if ( is_search() ) {
			$type      = 'search';
			$reference = htmlspecialchars( sanitize_text_field( get_search_query() ) );
		}

		if ( is_404() ) {
			$type = 'notFound';
		}

		$paged = false;
		if ( is_paged() || ( is_singular() && 1 < get_query_var( 'page' ) ) ) {
			global $wp;
			$paged = [
				'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ),
				'link'  => home_url( $wp->request )
			];
		}

		return apply_filters( 'aioseo_breadcrumbs_trail', aioseo()->breadcrumbs->buildBreadcrumbs( $type, $reference, $paged ) );
	}

	/**
	 * Helper function to display breadcrumbs for a specific page.
	 *
	 * @since 4.1.1
	 *
	 * @param  bool        $echo      Print out the breadcrumb.
	 * @param  string      $type      The type for the breadcrumb.
	 * @param  string      $reference A reference to be used for rendering the breadcrumb.
	 * @return string|void            A html breadcrumb.
	 */
	public function sideDisplay( $echo = true, $type = '', $reference = '' ) {
		// Save previously built breadcrumbs.
		$previousCrumbs = $this->breadcrumbs;

		// Build and run the sideDisplay.
		$this->breadcrumbs = aioseo()->breadcrumbs->buildBreadcrumbs( $type, $reference );
		$sideDisplay       = $this->display( $echo );

		// Restore previously built breadcrumbs.
		$this->breadcrumbs = $previousCrumbs;

		return $sideDisplay;
	}

	/**
	 * Display a generic breadcrumb preview.
	 *
	 * @since 4.1.5
	 *
	 * @param  bool        $echo  Print out the breadcrumb.
	 * @param  string      $label The preview crumb label.
	 * @return string|void        A html breadcrumb.
	 */
	public function preview( $echo = true, $label = '' ) {
		// Translators: "Crumb" refers to a part of the breadcrumb trail.
		$label = empty( $label ) ? __( 'Sample Crumb', 'all-in-one-seo-pack' ) : $label;

		return $this->sideDisplay( $echo, 'preview', $label );
	}

	/**
	 * Display the breadcrumb in the frontend.
	 *
	 * @since 4.1.1
	 *
	 * @param  bool        $echo Print out the breadcrumb.
	 * @return string|void       A html breadcrumb.
	 */
	public function display( $echo = true ) {
		if ( ! aioseo()->options->breadcrumbs->enable || ! apply_filters( 'aioseo_breadcrumbs_output', true ) ) {
			return;
		}

		// We can only run after this action because we need all post types loaded.
		if ( ! did_action( 'init' ) ) {
			return;
		}

		$breadcrumbs = $this->getBreadcrumbs();
		if ( empty( $breadcrumbs ) ) {
			return;
		}

		$breadcrumbsCount = count( $breadcrumbs );

		$display = '<div class="aioseo-breadcrumbs">';
		foreach ( $breadcrumbs as $breadcrumb ) {
			--$breadcrumbsCount;

			$breadcrumbDisplay = $this->breadcrumbToDisplay( $breadcrumb );

			// Strip link from Last crumb.
			if (
				0 === $breadcrumbsCount &&
				aioseo()->breadcrumbs->showCurrentItem() &&
				! $this->linkCurrentItem() &&
				'default' === $breadcrumbDisplay['templateType']
			) {
				$breadcrumbDisplay['template'] = $this->stripLink( $breadcrumbDisplay['template'] );
			}

			$display .= $breadcrumbDisplay['template'];

			if ( 0 < $breadcrumbsCount ) {
				$display .= $this->getSeparator();
			}
		}
		$display .= '</div>';

		$display = wp_kses_post( $display );

		if ( $echo ) {
			echo $display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $display;
	}

	/**
	 * Turns a crumb array into a rendered html crumb.
	 *
	 * @since 4.1.1
	 *
	 * @param  array       $item The crumb array.
	 * @return string|void       The crumb html.
	 */
	protected function breadcrumbToDisplay( $item ) {
		$templateItem = $this->getCrumbTemplate( $item );
		if ( empty( $templateItem['template'] ) ) {
			return;
		}

		// Do tags.
		$templateItem['template'] = aioseo()->breadcrumbs->tags->replaceTags( $templateItem['template'], $item );

		// Restore html.
		$templateItem['template'] = aioseo()->helpers->decodeHtmlEntities( $templateItem['template'] );

		// Remove html link if it comes back from the template but we passed no links to it.
		if ( empty( $item['link'] ) ) {
			$templateItem['template'] = $this->stripLink( $templateItem['template'] );
		}

		// Allow shortcodes to run in the final html.
		$templateItem['template'] = do_shortcode( $templateItem['template'] );

		// Final security cleaning.
		$templateItem['template'] = wp_kses_post( $templateItem['template'] );

		return $templateItem;
	}

	/**
	 * Helper function to get a crumb's template.
	 *
	 * @since 4.1.1
	 *
	 * @param  array $crumb The crumb array.
	 * @return string       The html template.
	 */
	protected function getTemplate( $crumb ) {
		return $this->getDefaultTemplate( $crumb );
	}

	/**
	 * Helper function to get a crumb's template.
	 *
	 * @since 4.1.1
	 *
	 * @param  array $crumb The crumb array.
	 * @return array        The template type and html.
	 */
	protected function getCrumbTemplate( $crumb ) {
		return [
			'templateType' => 'default',
			'template'     => $this->getTemplate( $crumb )
		];
	}

	/**
	 * Default html template.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $type      The crumb's type.
	 * @param  mixed  $reference The crumb's reference.
	 * @return string            The default crumb template.
	 */
	public function getDefaultTemplate( $type = '', $reference = '' ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return <<<TEMPLATE
<span class="aioseo-breadcrumb">
	<a href="#breadcrumb_link" title="#breadcrumb_label">#breadcrumb_label</a>
</span>
TEMPLATE;
	}

	/**
	 * Helper function to strip a html link from the crumb.
	 *
	 * @since 4.1.1
	 *
	 * @param  string $html The crumb's html.
	 * @return string       A crumb html without links.
	 */
	public function stripLink( $html ) {
		return preg_replace( '/<a\s.*?>|<\/a>/is', '', $html );
	}

	/**
	 * Get the breadcrumb configured separator.
	 *
	 * @since 4.1.1
	 *
	 * @return string The separator html.
	 */
	public function getSeparator() {
		$separator = apply_filters( 'aioseo_breadcrumbs_separator_symbol', aioseo()->options->breadcrumbs->separator );

		return apply_filters( 'aioseo_breadcrumbs_separator', '<span class="aioseo-breadcrumb-separator">' . esc_html( $separator ) . '</span>' );
	}

	/**
	 * Function to filter the linkCurrentItem option.
	 *
	 * @since 4.1.3
	 *
	 * @return bool Link current item.
	 */
	public function linkCurrentItem() {
		return apply_filters( 'aioseo_breadcrumbs_link_current_item', aioseo()->options->breadcrumbs->linkCurrentItem );
	}
}