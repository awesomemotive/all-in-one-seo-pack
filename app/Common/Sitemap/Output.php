<?php
namespace AIOSEO\Plugin\Common\Sitemap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles outputting the sitemap.
 *
 * @since 4.0.0
 */
class Output {
	/**
	 * Outputs the sitemap.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $entries The sitemap entries.
	 * @return void
	 */
	public function output( $entries ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! in_array( aioseo()->sitemap->type, [ 'general', 'rss' ], true ) ) {
			return;
		}

		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$entries       = aioseo()->sitemap->helpers->decodeSitemapEntries( $entries );
		$charset       = aioseo()->helpers->getCharset();
		$excludeImages = aioseo()->sitemap->helpers->excludeImages();
		$generation    = ! isset( aioseo()->sitemap->isStatic ) || aioseo()->sitemap->isStatic ? __( 'statically', 'all-in-one-seo-pack' ) : __( 'dynamically', 'all-in-one-seo-pack' );
		$version       = aioseo()->helpers->getAioseoVersion();

		if ( ! empty( $version ) ) {
			$version = 'v' . $version;
		}

		echo '<?xml version="1.0" encoding="' . esc_attr( $charset ) . "\"?>\r\n";
		echo '<!-- ' . sprintf(
			// Translators: 1 - "statically" or "dynamically", 2 - The date, 3 - The time, 4 - The plugin name ("All in One SEO"), 5 - Currently installed version.
			esc_html__( 'This sitemap was %1$s generated on %2$s at %3$s by %4$s %5$s - the original SEO plugin for WordPress.', 'all-in-one-seo-pack' ),
			esc_html( $generation ),
			esc_html( date_i18n( get_option( 'date_format' ) ) ),
			esc_html( date_i18n( get_option( 'time_format' ) ) ),
			esc_html( AIOSEO_PLUGIN_NAME ),
			esc_html( $version )
		) . ' -->';

		if ( 'rss' === aioseo()->sitemap->type ) {
			$xslUrl = home_url() . '/default-sitemap.xsl';

			if ( ! is_multisite() ) {
				$title       = get_bloginfo( 'name' );
				$description = get_bloginfo( 'blogdescription' );
				$link        = home_url();
			} else {
				$title       = get_blog_option( get_current_blog_id(), 'blogname' );
				$description = get_blog_option( get_current_blog_id(), 'blogdescription' );
				$link        = get_blog_option( get_current_blog_id(), 'siteurl' );
			}

			$ttl = apply_filters( 'aioseo_sitemap_rss_ttl', 60 );

			echo "\r\n\r\n<?xml-stylesheet type=\"text/xsl\" href=\"" . esc_url( $xslUrl ) . "\"?>\r\n";
			include_once AIOSEO_DIR . '/app/Common/Views/sitemap/xml/rss.php';

			return;
		}

		if ( 'root' === aioseo()->sitemap->indexName && aioseo()->sitemap->indexes ) {
			$xslUrl = add_query_arg( 'sitemap', aioseo()->sitemap->indexName, home_url() . '/default-sitemap.xsl' );

			echo "\r\n\r\n<?xml-stylesheet type=\"text/xsl\" href=\"" . esc_url( $xslUrl ) . "\"?>\r\n";
			include AIOSEO_DIR . '/app/Common/Views/sitemap/xml/root.php';

			return;
		}

		$xslUrl = add_query_arg( 'sitemap', aioseo()->sitemap->indexName, home_url() . '/default-sitemap.xsl' );

		echo "\r\n\r\n<?xml-stylesheet type=\"text/xsl\" href=\"" . esc_url( $xslUrl ) . "\"?>\r\n";
		include AIOSEO_DIR . '/app/Common/Views/sitemap/xml/default.php';
	}

	/**
	 * Escapes and echoes the given XML tag value.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $value The tag value.
	 * @param  bool   $wrap  Whether the value should we wrapped in a CDATA section.
	 * @return void
	 */
	public function escapeAndEcho( $value, $wrap = true ) {
		$safeText = is_string( $value ) ? wp_check_invalid_utf8( $value, true ) : $value;
		$isZero   = is_numeric( $value ) ? 0 === (int) $value : false;
		if ( ! $safeText && ! $isZero ) {
			return;
		}

		$cdataRegex = '\<\!\[CDATA\[.*?\]\]\>';
		$regex      = "/(?=.*?{$cdataRegex})(?<non_cdata_followed_by_cdata>(.*?))(?<cdata>({$cdataRegex}))|(?<non_cdata>(.*))/sx";

		$safeText = (string) preg_replace_callback(
			$regex,
			static function( $matches ) {
				if ( ! $matches[0] ) {
					return '';
				}

				if ( ! empty( $matches['non_cdata'] ) ) {
					// Escape HTML entities in the non-CDATA section.
					return _wp_specialchars( $matches['non_cdata'], ENT_XML1 );
				}

				// Return the CDATA Section unchanged, escape HTML entities in the rest.
				return _wp_specialchars( $matches['non_cdata_followed_by_cdata'], ENT_XML1 ) . $matches['cdata'];
			},
			$safeText
		);

		$safeText = $safeText ? $safeText : ( $isZero ? $value : '' );

		if ( ! $wrap ) {
			return print( $safeText ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		printf( '<![CDATA[%1$s]]>', $safeText ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Returns the URL for the sitemap stylesheet.
	 *
	 * This is needed for compatibility with multilingual plugins such as WPML.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL to the sitemap stylesheet.
	 */
	private function xslUrl() {
		return esc_url( apply_filters( 'aioseo_sitemap_xsl_url', aioseo()->helpers->localizedUrl( '/sitemap.xsl' ) ) );
	}
}