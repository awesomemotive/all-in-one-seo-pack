<?php
namespace AIOSEO\Plugin\Common\Sitemap\Image;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Holds all code to extract images from third-party content.
 *
 * @since 4.2.2
 */
class ThirdParty {
	/**
	 * The post object.
	 *
	 * @since 4.2.2
	 *
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * The parsed post content.
	 * The post object holds the unparsed content as we need that for Divi.
	 *
	 * @since 4.2.5
	 *
	 * @var string
	 */
	private $parsedPostContent;

	/**
	 * The image URLs and IDs.
	 *
	 * @since 4.2.2
	 *
	 * @var array[mixed]
	 */
	private $images = [];

	/**
	 * Divi shortcodes.
	 *
	 * @since 4.2.3
	 *
	 * @var string[]
	 */
	private $shortcodes = [
		'et_pb_section',
		'et_pb_column',
		'et_pb_row',
		'et_pb_image',
		'et_pb_gallery',
		'et_pb_accordion',
		'et_pb_accordion_item',
		'et_pb_counters',
		'et_pb_blurb',
		'et_pb_cta',
		'et_pb_code',
		'et_pb_contact_form',
		'et_pb_divider',
		'et_pb_filterable_portfolio',
		'et_pb_map',
		'et_pb_number_counter',
		'et_pb_post_slider',
		'et_pb_pricing_tables',
		'et_pb_pricing_table',
		'et_pb_shop',
		'et_pb_slider',
		'et_pb_slide',
		'et_pb_tabs',
		'et_pb_tab',
		'et_pb_text',
		'et_pb_video',
		'et_pb_audio',
		'et_pb_blog',
		'et_pb_circle_counter',
		'et_pb_comments',
		'et_pb_countdown_timer',
		'et_pb_signup',
		'et_pb_login',
		'et_pb_menu',
		'et_pb_team_member',
		'et_pb_post_nav',
		'et_pb_post_title',
		'et_pb_search',
		'et_pb_sidebar',
		'et_pb_social_media_follow',
		'et_pb_social_media_follow_network',
		'et_pb_testimonial',
		'et_pb_toggle',
		'et_pb_video_slider',
		'et_pb_video_slider_item',
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.2.2
	 *
	 * @param \WP_Post $post              The post object.
	 * @param string   $parsedPostContent The parsed post content.
	 */
	public function __construct( $post, $parsedPostContent ) {
		$this->post              = $post;
		$this->parsedPostContent = $parsedPostContent;
	}

	/**
	 * Extracts the images from third-party content.
	 *
	 * @since 4.2.2
	 *
	 * @return array[mixed] The image URLs and IDs.
	 */
	public function extract() {
		$integrations = [
			'acf',
			'divi',
			'nextGen',
			'wooCommerce',
			'kadenceBlocks'
		];

		foreach ( $integrations as $integration ) {
			$this->{$integration}();
		}

		return $this->images;
	}

	/**
	 * Extracts image URLs from ACF fields.
	 *
	 * @since 4.2.2
	 *
	 * @return void
	 */
	private function acf() {
		if ( ! class_exists( 'ACF' ) || ! function_exists( 'get_fields' ) ) {
			return;
		}

		$fields = get_fields( $this->post->ID );
		if ( ! $fields ) {
			return;
		}

		$images       = $this->acfHelper( $fields );
		$this->images = array_merge( $this->images, $images );
	}

	/**
	 * Helper function for acf().
	 *
	 * @since 4.2.2
	 *
	 * @param  array         $fields The ACF fields.
	 * @return array[string]         The image URLs or IDs.
	 */
	private function acfHelper( $fields ) {
		$images = [];
		foreach ( $fields as $value ) {
			if ( is_array( $value ) ) {
				// Recursively loop over grouped fields.
				// We continue on since arrays aren't necessarily groups and might also simply aLready contain the value we're looking for.
				$images = array_merge( $images, $this->acfHelper( $value ) );

				if ( isset( $value['type'] ) && 'image' !== strtolower( $value['type'] ) ) {
					$images[] = $value['url'];
				}

				continue;
			}

			// Capture the value if it's an image URL, but not the default thumbnail from ACF.
			if ( is_string( $value ) && preg_match( aioseo()->sitemap->image->getImageExtensionRegexPattern(), $value ) && ! preg_match( '/media\/default\.png$/i', $value ) ) {
				$images[] = $value;
				continue;
			}

			// Capture the value if it's a numeric image ID, but make sure it's not an array of random field object properties.
			if (
				is_numeric( $value ) &&
				! isset( $fields['ID'] ) &&
				! isset( $fields['thumbnail'] )
			) {
				$images[] = $value;
			}
		}

		return $images;
	}

	/**
	 * Extracts images from Divi shortcodes.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	private function divi() {
		if ( ! defined( 'ET_BUILDER_VERSION' ) ) {
			return;
		}

		$urls  = [];
		$regex = implode( '|', array_map( 'preg_quote', $this->shortcodes ) );

		preg_match_all(
			"/\[($regex)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)/i",
			$this->post->post_content,
			$matches,
			PREG_SET_ORDER
		);

		foreach ( $matches as $shortcode ) {
			$attributes = shortcode_parse_atts( $shortcode[0] );
			if ( ! empty( $attributes['src'] ) ) {
				$urls[] = $attributes['src'];
			}

			if ( ! empty( $attributes['image_src'] ) ) {
				$urls[] = $attributes['image_src'];
			}

			if ( ! empty( $attributes['image_url'] ) ) {
				$urls[] = $attributes['image_url'];
			}

			if ( ! empty( $attributes['portrait_url'] ) ) {
				$urls[] = $attributes['portrait_url'];
			}

			if ( ! empty( $attributes['image'] ) ) {
				$urls[] = $attributes['image'];
			}

			if ( ! empty( $attributes['background_image'] ) ) {
				$urls[] = $attributes['background_image'];
			}

			if ( ! empty( $attributes['logo'] ) ) {
				$urls[] = $attributes['logo'];
			}

			if ( ! empty( $attributes['gallery_ids'] ) ) {
				$attachmentIds = explode( ',', $attributes['gallery_ids'] );
				foreach ( $attachmentIds as $attachmentId ) {
					$urls[] = wp_get_attachment_url( $attachmentId );
				}
			}
		}

		$this->images = array_merge( $this->images, $urls );
	}

	/**
	 * Extracts the image IDs of more advanced NextGen Pro gallerlies like the Mosaic and Thumbnail Grid.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	private function nextGen() {
		if ( ! defined( 'NGG_PLUGIN_BASENAME' ) && ! defined( 'NGG_PRO_PLUGIN_BASENAME' ) ) {
			return;
		}

		preg_match_all( '/data-image-id=\"([0-9]*)\"/i', $this->parsedPostContent, $imageIds );
		if ( ! empty( $imageIds[1] ) ) {
			$this->images = array_merge( $this->images, $imageIds[1] );
		}

		// For this specific check, we only want to parse blocks and do not want to run shortcodes because some NextGen blocks (e.g. Mosaic) are parsed into shortcodes.
		// And after parsing the shortcodes, the attributes we're looking for are gone.
		$contentWithBlocksParsed = do_blocks( $this->post->post_content );

		$imageIds = [];
		preg_match_all( '/\[ngg.*src="galleries" ids="(.*?)".*\]/i', $contentWithBlocksParsed, $shortcodes );
		if ( empty( $shortcodes[1] ) ) {
			return;
		}

		foreach ( $shortcodes[1] as $shortcode ) {
			$galleryIds = explode( ',', $shortcode[0] );
			foreach ( $galleryIds as $galleryId ) {
				global $nggdb;
				$galleryImageIds = $nggdb->get_ids_from_gallery( $galleryId );
				if ( empty( $galleryImageIds ) ) {
					continue;
				}

				foreach ( $galleryImageIds as $galleryImageId ) {
					$image = $nggdb->find_image( $galleryImageId );
					if ( ! empty( $image ) ) {
						$imageIds[] = $image->get_permalink();
					}
				}
			}
		}

		$this->images = array_merge( $this->images, $imageIds );
	}

	/**
	 * Extracts the image IDs of WooCommerce product galleries.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	private function wooCommerce() {
		if ( ! aioseo()->helpers->isWooCommerceActive() || 'product' !== $this->post->post_type ) {
			return;
		}

		$productImageIds = get_post_meta( $this->post->ID, '_product_image_gallery', true );
		if ( ! $productImageIds ) {
			return;
		}

		$productImageIds = explode( ',', $productImageIds );
		$this->images    = array_merge( $this->images, $productImageIds );
	}

	/**
	 * Extracts the image IDs of Kadence Block galleries.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	private function kadenceBlocks() {
		if ( ! defined( 'KADENCE_BLOCKS_VERSION' ) ) {
			return [];
		}

		$blocks = aioseo()->helpers->parseBlocks( $this->post );

		foreach ( $blocks as $block ) {
			if ( 'kadence/advancedgallery' === $block['blockName'] && ! empty( $block['attrs']['ids'] ) ) {
				$this->images = array_merge( $this->images, $block['attrs']['ids'] );
			}
		}
	}
}