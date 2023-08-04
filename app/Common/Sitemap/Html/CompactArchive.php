<?php
namespace AIOSEO\Plugin\Common\Sitemap\Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Compact Archive's output.
 *
 * @since 4.1.3
 */
class CompactArchive {
	/**
	 * The shortcode attributes.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	private $attributes;

	/**
	 * Outputs the compact archives sitemap.
	 *
	 * @since 4.1.3
	 *
	 * @param  array   $attributes The shortcode attributes.
	 * @param  boolean $echo       Whether the HTML code should be printed or returned.
	 * @return string              The HTML for the compact archive.
	 */
	public function output( $attributes, $echo = true ) {
		$dateArchives     = ( new Query() )->archives();
		$this->attributes = $attributes;

		if ( 'asc' === strtolower( $this->attributes['order'] ) ) {
			$dateArchives = array_reverse( $dateArchives, true );
		}

		$data = [
			'dateArchives' => $dateArchives,
			'lines'        => ''
		];
		foreach ( $dateArchives as $year => $months ) {
			$data['lines'] .= $this->generateYearLine( $year, $months ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		ob_start();
		aioseo()->templates->getTemplate( 'sitemap/html/compact-archive.php', $data );
		$output = ob_get_clean();

		if ( $echo ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $output;
	}
	// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

	/**
	* Generates the HTML for a year line.
	*
	* @since 4.1.3
	*
	* @param  int    $year   The year archive.
	* @param  array  $months The month archives for the current year.
	* @return string         The HTML code for the year.
	*/
	protected function generateYearLine( $year, $months ) {
		$html = '<li><strong><a href="' . get_year_link( $year ) . '">' . esc_html( $year ) . '</a>: </strong> ';

		for ( $month = 1; $month <= 12; $month++ ) {
			$html .= $this->generateMonth( $year, $months, $month );
		}

		$html .= '</li>' . "\n";

		return wp_kses_post( $html );
	}

	/**
	 * Generates the HTML for a month.
	 *
	 * @since 4.1.3
	 *
	 * @param  int    $year   The year archive.
	 * @param  array  $months All month archives for the current year.
	 * @param  int    $month  The month archive.
	 * @return string         The HTML code for the month.
	 */
	public function generateMonth( $year, $months, $month ) {
		$hasPosts         = isset( $months[ $month ] );
		$dummyDate        = strtotime( "2009/{$month}/25" );
		$monthAbbrevation = date_i18n( 'M', $dummyDate );

		$html = '<span class="aioseo-empty-month">' . esc_html( $monthAbbrevation ) . '</span> ';
		if ( $hasPosts ) {
			$noFollow = filter_var( $this->attributes['nofollow_links'], FILTER_VALIDATE_BOOLEAN );
			$html     = sprintf(
				'<a href="%1$s" title="%2$s"%3$s>%4$s</a> ',
				get_month_link( $year, $month ),
				esc_attr( date_i18n( 'F Y', $dummyDate ) ),
				$noFollow ? ' rel="nofollow"' : '',
				esc_html( $monthAbbrevation )
			);
		}

		return $html;
	}
}