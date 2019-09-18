<?php
/**
 * The compat-gutenberg.php file.
 *
 * Contains compatibility fixes for the Gutenberg editor.
 *
 * @package All_in_One_SEO_Pack
 *
 * @since 3.3.0
 */

/**
 * The gutenberg_fix_metabox() function.
 *
 * Change height of a specific CSS class to fix an issue in Chrome with Gutenberg.
 *
 * @see https://github.com/WordPress/gutenberg/issues/17406
 *
 * @since 3.3.0
 */
function gutenberg_fix_metabox() {
	if ( false !== stripos( $_SERVER['HTTP_USER_AGENT'], 'Chrome/77.' ) ) {
		add_action(
			'admin_head',
			static function () {
				global $wp_version;

				$class = 'editor-writing-flow';

				if ( version_compare( $wp_version, '5.2', '>=' ) ) {
					$class = 'block-' . $class;
				}

				echo '<style>.' . $class . ' { height: auto; }</style>';
			}
		);
	}
}

gutenberg_fix_metabox();
