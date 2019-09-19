<?php
/**
 * The compat-gutenberg.php file.
 *
 * Contains compatibility fixes for the Gutenberg editor.
 *
 * @package All_in_One_SEO_Pack
 *
 * @since 3.2.8
 */

/**
 * The gutenberg_fix_metabox() function.
 *
 * Change height of a specific CSS class to fix an issue in Chrome 77 with Gutenberg.
 *
 * @see https://github.com/WordPress/gutenberg/issues/17406
 * @link https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/2914
 *
 * @since 3.2.8
 *
 * @return void
 */
function gutenberg_fix_metabox() {
	if ( false !== stripos( $_SERVER['HTTP_USER_AGENT'], 'Chrome/77.' ) ) {
		add_action(
			'admin_head',
			static function () {
				global $wp_version;

				// Fix should be included in WP v5.3.
				if ( ! version_compare( $wp_version, '5.0', '>=' ) && version_compare( $wp_version, '5.3', '<' ) ) {
					return;
				}

				// CSS class renamed from 'editor' to 'block-editor' in WP v5.2.
				if ( version_compare( $wp_version, '5.2', '<' ) ) {
					gutenberg_fix_metabox_helper( 'editor-writing-flow' );
				} elseif ( version_compare( $wp_version, '5.2', '>=' ) ) {
					gutenberg_fix_metabox_helper( 'block-editor-writing-flow' );
				}
			}
		);
	}
}

/**
 * The gutenberg_fix_metabox_helper() function.
 *
 * Overrides a Gutenberg CSS class using inline CSS.
 * Helper method of gutenberg_fix_metabox().
 *
 * @since 3.2.8
 *
 * @param string $class_name
 * @return void
 */
function gutenberg_fix_metabox_helper( $class_name ) {
	echo '<style>.' . $class_name . ' { height: auto; }</style>';
}

gutenberg_fix_metabox();
