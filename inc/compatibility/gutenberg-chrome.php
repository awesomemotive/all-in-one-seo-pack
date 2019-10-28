<?php
/**
 * Contains compatibility fixes for the Gutenberg editor.
 *
 * @package All_in_One_SEO_Pack
 *
 * @since 3.2.8
 */

aioseop_chrome_fix_overlapping_metabox();

/**
 * Fixes a CSS compatibility issue between Gutenberg and Chrome v77 that affects meta boxes.
 *
 * @see https://github.com/WordPress/gutenberg/issues/17406
 * @link https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/2914
 *
 * @since 3.2.8
 *
 * @return void
 */
function aioseop_chrome_fix_overlapping_metabox() {
	if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		return;
	}

	if ( false !== stripos( $_SERVER['HTTP_USER_AGENT'], 'Chrome/77.' ) ) {
		add_action(
			'admin_head',
			'aioseop_override_gutenberg_css_class'
		);
	}
}

/**
 * Change height of a specific Gutenberg CSS class.
 *
 * @see https://github.com/WordPress/gutenberg/issues/17406
 * @link https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/2914
 *
 * @since 3.2.8
 *
 * @return void
 */
function aioseop_override_gutenberg_css_class() {
	global $wp_version;

	if ( version_compare( $wp_version, '5.0', '<' ) ) {
		return;
	}

	// CSS class renamed from 'editor' to 'block-editor' in WP v5.2.
	if ( version_compare( $wp_version, '5.2', '<' ) ) {
		aioseop_override_gutenberg_css_class_helper( 'editor-writing-flow' );
	} else {
		aioseop_override_gutenberg_css_class_helper( 'block-editor-writing-flow' );
	}
}

/**
 * Overrides a Gutenberg CSS class using inline CSS. Helper method of gutenberg_fix_metabox().
 *
 * @since 3.2.8
 *
 * @param string $class_name
 * @return void
 */
function aioseop_override_gutenberg_css_class_helper( $class_name ) {
	echo '<style>.' . $class_name . ' { height: auto; }</style>';
}
