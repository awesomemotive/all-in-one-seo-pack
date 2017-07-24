<?php
/**
 * Admin Footer.
 *
 * @package All-in-One-SEO-Pack
 * @since 2.3.15
 */

if ( ! function_exists( 'aioseop_display_admin_footer' ) ) {
	/**
	 * Admin Footer.
	 *
	 * @since 2.3.15
	 *
	 * @uses 'admin_footer_text' hook.
	 * @link https://developer.wordpress.org/reference/hooks/admin_footer_text/
	 *
	 * @param string $text Current WP footer text.
	 * @return string
	 */
	function aioseop_display_admin_footer( $text ) {
		$rtn_footer = $text;

		$current_screen = get_current_screen();
		$aioseop_screens = array(
			'toplevel_page_all-in-one-seo-pack/aioseop_class',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_performance',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_sitemap',
			'all-in-one-seo_page_aiosp_opengraph',
			'all-in-one-seo_page_aiosp_robots_generator',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_file_editor',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_importer_exporter',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_bad_robots',
			'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_feature_manager',
		);
		if ( isset( $current_screen->id ) && in_array( $current_screen->id, $aioseop_screens, true ) ) {
			/* translators: %1$s is replaced with "string" */
			$rtn_footer = sprintf( __( 'Thank you for using All in One SEO Plugin for WordPress. If you enjoy your experience, feel free to give us a %1$s rating.', 'all-in-one-seo-pack' ), '<a href="https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post" target="_blank" class="aioseop-rating-link" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
		}

		return $rtn_footer;
	}
}
add_action( 'admin_footer_text', 'aioseop_display_admin_footer' );
