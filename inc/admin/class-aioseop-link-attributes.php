<?php
/**
 * Extends the Gutenberg Editor and Classic Editor with extra features.
 *
 * @since 3.4.0
 * @package All-in-One-SEO-Pack
 */

/**
 * Enqueues a script that allows users to add nofollow, sponsored and title attributes to links in the Gutenberg Editor and Classic Editor.
 *
 * @since 3.4.0
 */
class AIOSEOP_Link_Attributes {

	/**
	 * Enqueues the aioseop-link-attributes.js file for the Classic Editor.
	 *
	 * Acts as a callback for the wp_enqueue_editor action hook.
	 *
	 * @since 3.4.0
	 */
	public static function add_attributes_modal_classic_editor() {

		wp_deregister_script( 'aioseop-link-attributes' );
		wp_register_script( 'aioseop-link-attributes', AIOSEOP_PLUGIN_URL . 'js/admin/aioseop-link-attributes.js', array( 'jquery' ), AIOSEOP_VERSION, true );
		wp_enqueue_script( 'aioseop-link-attributes' );

		wp_localize_script(
			'aioseop-link-attributes',
			'aioseopL10n',
			array(
				'title'          => __( 'Insert/edit link', 'all-in-one-seo-pack' ),
				'update'         => __( 'Update', 'all-in-one-seo-pack' ),
				'save'           => __( 'Add Link', 'all-in-one-seo-pack' ),
				'noTitle'        => __( '(no title)', 'all-in-one-seo-pack' ),
				'labelTitle'     => __( 'Title', 'all-in-one-seo-pack' ),
				'noMatchesFound' => __( 'No results found.', 'all-in-one-seo-pack' ),
				'linkInserted'   => __( 'Link has been inserted.', 'all-in-one-seo-pack' ),
				'noFollow'       => __( '&nbsp;Add <code>rel="nofollow"</code> to link', 'all-in-one-seo-pack' ),
			)
		);
	}
}
