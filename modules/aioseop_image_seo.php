<?php

/**
 * Image Sitemap class.
 *
 * Optimize your image for SEO.
 *
 * @package All-in-One-SEO-Pack
 * @since 1.0.0
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Image_Seo' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Image_Seo
	 */
	class All_in_One_SEO_Pack_Image_Seo extends All_in_One_SEO_Pack_Module {

		/**
		 * All_in_One_SEO_Pack_Image_Seo constructor.
		 */
		function __construct() {
			if ( 'All_in_One_SEO_Pack_Image_Seo' === get_class( $this ) ) {

				$this->name = __( 'Image SEO', 'all-in-one-seo-pack' );
				$this->prefix = 'aiosp_image_seo_';
				$this->file = __FILE__;
				add_filter( 'wp_get_attachment_image_attributes', array( $this, 'edit_image_attributes' ), 10, 2 );
				add_filter( 'get_image_tag', array( $this, 'edit_image_tag' ), 10, 4 );
				add_filter( 'the_content', array( $this, 'aioseo_the_content' ) );
			}

			/**
			 * Default options.
			 *
			 * @since 1.0.0
			 * @access public
			 * @var array $default_options
			 */
			$this->help_values();
			$this->create_default_options();
			$this->layout_locations();
			// Load initial options / set defaults.
			$this->update_options();
			$other_options = array();
			foreach ( $this->layout as $k => $v ) {
				$other_options = array_merge( $other_options, $v['options'] );
			}
			$this->layout['default']['options'] = array_diff( array_keys( $this->default_options ), $other_options );
			$this->add_help_text_links();
			parent::__construct();
		}
		/**
		 * Set default values.
		 *
		 *
		 * @since 1.0.0
		 *
		 */
		public function create_default_options() {
		$this->default_options = array(
				'use_aiseo_image_tags' =>
					array(
						'name' => __( 'Use AIOSEO Image Title and Alt tag', 'all-in-one-seo-pack' ),
						'type' => 'checkbox',
					),
				'title_format'     => array(
					'name'     => __( 'Image Title Format', 'all-in-one-seo-pack' ),
					'default'  => '%image_title%',
					'type'     => 'text',
					'sanitize' => 'text',
				),
				'alt_format'       => array(
					'name'     => __( 'Alt Tag Format', 'all-in-one-seo-pack' ),
					'default'  => '%alt%',
					'type'     => 'text',
					'sanitize' => 'text',
				),
				'alt_strip_punc'   => array(
					'name' => __( 'Strip punctuation from alt tag', 'all-in-one-seo-pack' ),
					'type' => 'checkbox',
				),
				'title_strip_punc' => array(
					'name' => __( 'Strip punctuation from title tag', 'all-in-one-seo-pack' ),
					'type' => 'checkbox',
				),
			);
		}

		/**
		 * Set location layout values.
		 *
		 *
		 * @since 1.0.0
		 *
		 */
		public function layout_locations() {
			$this->locations = array(
				'image_seo' => array(
					'name'    => $this->name,
					'prefix'  => 'aiosp_',
					'type'    => 'settings',
					'options' => array(
						'use_aiseo_image_tags',
						'title_format',
						'title_strip_punc',
						'alt_format',
						'alt_strip_punc',
					),
				),
			);
			$this->layout    = array(
				'default' => array(
					'name'      => __( 'General Settings', 'all-in-one-seo-pack' ),
					'help_link' => 'http://semperplugins.com/documentation/general-settings/',
					'options'   => array(),
					// This is set below, to the remaining options -- pdb.
				),
			);
		}

		/**
		 * Set help text and anchors
		 *
		 *
		 * @since 1.0.0
		 *
		 */
		public function help_values() {
			$this->help_text    = array(
				'use_aiseo_image_tags' => __( "Use AISEOP's customized titles", 'all-in-one-seo-pack' ),
				'title_format'     => __( 'Title format of images', 'all-in-one-seo-pack' ),
				'alt_format'       => __( 'Alt tag format', 'all-in-one-seo-pack' ),
				'alt_strip_punc'   => __( 'Strip puncuation from alt tags', 'all-in-one-seo-pack' ),
				'title_strip_punc' => __( 'Strip puncuation from title tags', 'all-in-one-seo-pack' ),
			);
			$this->help_anchors = array(
				'alt_format'       => '#alt_format',
				'title_format'     => '#title_format',
				'title_strip_punc' => '#title_strip_punc',
				'alt_strip_punc'   => '#alt_strip_punc',
			);
		}

		/**
		 * @param $post
		 *
		 * @return array
		 */
		public function find_replacements( $post, $image_id ) {
			$categories           = wp_get_post_categories( $post->ID );
			$category_title       = get_cat_name( $categories[0] );
			$post_type            = get_post_type( $post->ID );
			$post_seo_title       = get_post_meta( $post->ID, '_aioseop_title', true );
			$image_post = get_post( $image_id );
			$image_post_title =  $image_post->post_title;
			$post_seo_description = get_post_meta( $post->ID, '_aioseop_description', true );
			$image_seo_title = get_post_meta( $image_id, '_aioseop_title', true );
			$image_seo_description = get_post_meta( $image_id, '_aioseop_description', true );

			$replacements         = array(
				'%image_seo_description%' => $image_seo_description,
				'%image_seo_title%'      => $image_seo_title,
				'%blog_title%'           => get_bloginfo( 'name' ),
				'%post_title%'           => $post->post_title,
				'%category_title%'       => $category_title,
				'%post_type%'            => $post_type,
				'%post_seo_title%'       => $post_seo_title,
				'%post_seo_description%' => $post_seo_description,
				'%image_title%'          => $image_post_title,
				'%image_name%'           => $image_post_title,
			);
	
			return $replacements;
		}

		/**
		 * @param $str
		 *
		 * @return mixed
		 */
		public function strip_puncuation( $str ) {
			$puncuation = array(
				'&#039;',
				"'",
				'&quot;',
				'"',
				'-',
				':',
				';',
				'...',
				'. . .',
				'!',
				'[',
				']',
				'}',
				'{',
			);
			foreach ( $puncuation as $mark ) {
				$str = str_replace( $mark, '', $str );
			}

			return $str;
		}

		/**
		 * Helper function to apply image title format where appropriate.
		 *
		 * Use the format options to display the image title
		 *
		 * @since 1.0.0
		 *
		 * @param string $title Title being passed.
		 *
		 * @return mixed|string
		 */
		public function apply_title_format( $title, $image_id ) {
			global $post;
			$title = str_replace( '"', '', get_the_title( $image_id ) );
			if ( 'on' === $this->options['aiosp_image_seo_alt_strip_punc'] ) {
				$title = $this->strip_puncuation($title);
			}
			$title = str_replace( '%image_title%', $title, $this->options['aiosp_image_seo_title_format'] );
			foreach ( $this->find_replacements( $post, $image_id ) as $key => $value ) {
				if ( false !== strrpos( $title, $key ) ) {
					if ( 'on' === $this->options['aiosp_image_seo_alt_strip_punc'] ) {
						$value = $this->strip_puncuation($value);
					}
					$title = str_replace( $key, $value, $title );
				}
			}

			return $title;
		}

		/**
		 * Helper function to apply image alt format where appropriate.
		 *
		 * Use the format options to display the image title
		 *
		 * @since 1.0.0
		 *
		 * @param string $alt Alt tag being passed.
		 *
		 * @return mixed|string
		 */
		public function apply_alt_format( $alt, $image_id ) {
			global $post;
			$alt = str_replace( '"', '', $alt );
			if ( 'on' === $this->options['aiosp_image_seo_alt_strip_punc'] ) {
				$alt = $this->strip_puncuation( $alt );
			}
			$alt = str_replace( '%alt%', $alt, $this->options['aiosp_image_seo_alt_format'] );
			foreach ( $this->find_replacements( $post, $image_id ) as $key => $value ) {
				if ( false !== strrpos( $alt, $key ) ) {
					if ( 'on' === $this->options['aiosp_image_seo_alt_strip_punc'] ) {
						$value = $this->strip_puncuation($value);
					}
					$alt = str_replace( $key, $value, $alt );
				}
			}

			return $alt;
		}


		/**
		 * Edit image attributes.
		 *
		 * Insert AIOSEOP title into injected post image.
		 *
		 * @since 1.0.0
		 *
		 * @param array $attr Attributes for image contained in meta values.
		 * @param object $attachment Attachment object.
		 *
		 * @return array
		 */
		public function edit_image_attributes( $attr, $attachment ) {
			if ( 'on' === $this->options['aiosp_image_seo_use_aiseo_image_tags'] ) {
				$attr['alt']   = $this->apply_alt_format( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) , $attachment->ID );
				$attr['title'] = $this->apply_title_format( $attachment->post_title, $attachment->ID  );
			}

			return $attr;
		}

		/**
		 * Add image tags.
		 *
		 * Insert title tags into embedded images as they are not there by default.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html Markup returned for html tag.
		 * @param int $id Attachment id.
		 * @param $alt
		 * @param $title
		 *
		 * @return mixed|string
		 * @return mixed|string
		 */
		public function edit_image_tag( $html, $id, $alt, $title ) {
			$post  = get_post( $id );
			$title = $post->post_title;
			$html  = str_replace( ' />', ' title="' . esc_attr( $title ) . '" />', $html );

			return $html;
		}

		/**
		 * Add image tags to images in post content.
		 *
		 * Insert AIOSEOP values into images if they are set for embedded images.
		 *
		 * @since 1.0.0
		 *
		 * @param string $content Content of post.
		 *
		 * @return mixed
		 */
		public function aioseo_the_content( $content ) {
			if ( 'on' === $this->options['aiosp_image_seo_use_aiseo_image_tags'] ) {
			$content = preg_replace_callback( '/<img[^>]+/', array( $this, 'replace_tags' ), $content, 20 );
			}
			return $content;
		}

		/**
		 * Apply customized alt/image tags to embedded images.
		 *
		 * @since 1.0.0
		 *
		 * @param array $matches Image tags returned for post using regex call.
		 *
		 * @return string
		 */
		public function replace_tags( $matches ) {
			// Blow up image tags into components/attributes.
			$pieces = preg_split( '/(\w+=)/', $matches[0], - 1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
			if ( in_array( 'class=', $pieces, true ) ) {
				$index = array_search( 'class=', $pieces );
				$image_class = substr($pieces[ $index + 1 ], 0 );
				$image_id_position = strrpos( $image_class, 'wp-image-' );
				$image_id = substr( substr($image_class, $image_id_position ), 9 );
			}

			if ( in_array( 'alt=', $pieces, true ) ) {
				$index                = array_search( 'alt=', $pieces );
				$pieces[ $index + 1 ] = '"' . $this->apply_alt_format( $pieces[ $index + 1 ] , intval($image_id )) . '" ';
			}
			if ( in_array( 'title=', $pieces, true ) ) {
				$index = array_search( 'title=', $pieces );
				$pieces[ $index + 1 ] = '"' . $this->apply_title_format( $pieces[ $index + 1 ] , intval($image_id )) . '" ';
			}

			return implode( '', $pieces ) . ' /';
		}
	}
}



