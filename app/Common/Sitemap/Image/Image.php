<?php
namespace AIOSEO\Plugin\Common\Sitemap\Image;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines which images are included in a post/term.
 *
 * @since 4.0.0
 */
class Image {
	/**
	 * The image scan action name.
	 *
	 * @since 4.0.13
	 *
	 * @var string
	 */
	private $imageScanAction = 'aioseo_image_sitemap_scan';

	/**
	 * The supported image extensions.
	 *
	 * @since 4.2.2
	 *
	 * @var array[string]
	 */
	public $supportedExtensions = [
		'gif',
		'heic',
		'jpeg',
		'jpg',
		'png',
		'svg',
		'webp',
		'ico'
	];

	/**
	 * The post object.
	 *
	 * @since 4.2.7
	 *
	 * @var \WP_Post
	 */
	private $post = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.0.5
	 */
	public function __construct() {
		// Column may not have been created yet.
		if ( ! aioseo()->core->db->columnExists( 'aioseo_posts', 'image_scan_date' ) ) {
			return;
		}

		// NOTE: This needs to go above the is_admin check in order for it to run at all.
		add_action( $this->imageScanAction, [ $this, 'scanPosts' ] );

		// Don't schedule a scan if we are not in the admin.
		if ( ! is_admin() ) {
			return;
		}

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Don't schedule a scan if an importer or the V3 migration is running.
		// We'll do our scans there.
		if (
			aioseo()->importExport->isImportRunning() ||
			aioseo()->migration->isMigrationRunning()
		) {
			return;
		}
		// Action Scheduler hooks.
		add_action( 'init', [ $this, 'scheduleScan' ], 3001 );
	}

	/**
	 * Schedules the image sitemap scan.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function scheduleScan() {
		if (
			! aioseo()->options->sitemap->general->enable ||
			aioseo()->sitemap->helpers->excludeImages()
		) {
			return;
		}

		aioseo()->actionScheduler->scheduleSingle( $this->imageScanAction, 10 );
	}

	/**
	 * Scans posts for images.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function scanPosts() {
		if (
			! aioseo()->options->sitemap->general->enable ||
			aioseo()->sitemap->helpers->excludeImages()
		) {
			return;
		}

		$postsPerScan = apply_filters( 'aioseo_image_sitemap_posts_per_scan', 10 );
		$postTypes    = implode( "', '", aioseo()->helpers->getPublicPostTypes( true ) );

		$posts = aioseo()->core->db
			->start( aioseo()->core->db->db->posts . ' as p', true )
			->select( '`p`.`ID`, `p`.`post_type`, `p`.`post_content`, `p`.`post_excerpt`, `p`.`post_modified_gmt`' )
			->leftJoin( 'aioseo_posts as ap', '`ap`.`post_id` = `p`.`ID`' )
			->whereRaw( '( `ap`.`id` IS NULL OR `p`.`post_modified_gmt` > `ap`.`image_scan_date` OR `ap`.`image_scan_date` IS NULL )' )
			->whereRaw( "`p`.`post_status` IN ( 'publish', 'inherit' )" )
			->whereRaw( "`p`.`post_type` IN ( '$postTypes' )" )
			->limit( $postsPerScan )
			->run()
			->result();

		if ( ! $posts ) {
			aioseo()->actionScheduler->scheduleSingle( $this->imageScanAction, 15 * MINUTE_IN_SECONDS, [], true );

			return;
		}

		foreach ( $posts as $post ) {
			$this->scanPost( $post );
		}

		aioseo()->actionScheduler->scheduleSingle( $this->imageScanAction, 30, [], true );
	}

	/**
	 * Returns the image entries for a given post.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|int $post The post object or ID.
	 * @return void
	 */
	public function scanPost( $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		$this->post = $post;

		if ( ! empty( $post->post_password ) ) {
			$this->updatePost( $post->ID );

			return;
		}

		if ( 'attachment' === $post->post_type ) {
			if ( ! wp_attachment_is( 'image', $post->ID ) ) {
				$this->updatePost( $post->ID );

				return;
			}

			$image = $this->buildEntries( [ $post->ID ] );
			$this->updatePost( $post->ID, $image );

			return;
		}

		$images = $this->extract();
		$images = $this->removeImageDimensions( $images );

		$images = apply_filters( 'aioseo_sitemap_images', $images, $post );

		// Limit to a 1,000 URLs, in accordance to Google's specifications.
		$images = array_slice( $images, 0, 1000 );
		$this->updatePost( $post->ID, $this->buildEntries( $images ) );
	}

	/**
	 * Returns the image entries for a given term.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Term $term The term object.
	 * @return array          The image entries.
	 */
	public function term( $term ) {
		if ( aioseo()->sitemap->helpers->excludeImages() ) {
			return [];
		}

		$id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( ! $id ) {
			return [];
		}

		return $this->buildEntries( [ $id ] );
	}

	/**
	 * Builds the image entries.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $images The images, consisting of attachment IDs or external URLs.
	 * @return array         The image entries.
	 */
	private function buildEntries( $images ) {
		$entries = [];
		foreach ( $images as $image ) {
			$idOrUrl  = $this->getImageIdOrUrl( $image );
			$imageUrl = is_numeric( $idOrUrl ) ? wp_get_attachment_url( $idOrUrl ) : $idOrUrl;
			$imageUrl = aioseo()->sitemap->helpers->formatUrl( $imageUrl );
			if ( ! $imageUrl || ! preg_match( $this->getImageExtensionRegexPattern(), (string) $imageUrl ) ) {
				continue;
			}

			$entries[ $idOrUrl ] = [ 'image:loc' => $imageUrl ];
		}

		return array_values( $entries );
	}

	/**
	 * Returns the ID of the image if it's hosted on the site. Otherwise it returns the external URL.
	 *
	 * @since 4.1.3
	 *
	 * @param  int|string $image The attachment ID or URL.
	 * @return int|string        The attachment ID or URL.
	 */
	private function getImageIdOrUrl( $image ) {
		if ( is_numeric( $image ) ) {
			return $image;
		}

		$attachmentId = false;
		if ( aioseo()->helpers->isValidAttachment( $image ) ) {
			$attachmentId = aioseo()->helpers->attachmentUrlToPostId( $image );
		}

		return $attachmentId ? $attachmentId : $image;
	}

	/**
	 * Extracts all image URls and IDs from the post.
	 *
	 * @since 4.0.0
	 *
	 * @return array The image URLs and IDs.
	 */
	private function extract() {
		$images = [];

		if ( has_post_thumbnail( $this->post ) ) {
			$images[] = get_the_post_thumbnail_url( $this->post );
		}

		// Get the galleries here before doShortcodes() runs below to prevent buggy behaviour.
		// WordPress is supposed to only return the attached images but returns a different result if the shortcode has no valid attributes, so we need to grab them manually.
		$images = array_merge( $images, $this->getPostGalleryImages() );

		// Now, get the remaining images from image tags in the post content.
		$parsedPostContent = do_blocks( $this->post->post_content );
		$parsedPostContent = aioseo()->helpers->doShortcodes( $parsedPostContent, true, $this->post->ID );
		$parsedPostContent = preg_replace( '/\s\s+/u', ' ', (string) trim( $parsedPostContent ) ); // Trim both internal and external whitespace.

		// Get the images from any third-party plugins/themes that are active.
		$thirdParty = new ThirdParty( $this->post, $parsedPostContent );
		$images     = array_merge( $images, $thirdParty->extract() );

		preg_match_all( '#<(amp-)?img[^>]+src="([^">]+)"#', (string) $parsedPostContent, $matches );
		foreach ( $matches[2] as $url ) {
			$images[] = aioseo()->helpers->makeUrlAbsolute( $url );
		}

		return array_unique( $images );
	}

	/**
	 * Returns all images from WP Core post galleries.
	 *
	 * @since 4.2.2
	 *
	 * @return array[string] The image URLs.
	 */
	private function getPostGalleryImages() {
		$images    = [];
		$galleries = get_post_galleries( $this->post, false );
		foreach ( $galleries as $gallery ) {
			foreach ( $gallery['src'] as $imageUrl ) {
				$images[] = $imageUrl;
			}
		}

		// Now, get rid of them so that we don't process the shortcodes again.
		$regex                    = get_shortcode_regex( [ 'gallery' ] );
		$this->post->post_content = preg_replace( "/$regex/i", '', (string) $this->post->post_content );

		return $images;
	}

	/**
	 * Removes image dimensions from the slug.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $urls         The image URLs.
	 * @return array $preparedUrls The formatted image URLs.
	 */
	private function removeImageDimensions( $urls ) {
		$preparedUrls = [];
		foreach ( $urls as $url ) {
			$preparedUrls[] = aioseo()->helpers->removeImageDimensions( $url );
		}

		return array_unique( array_filter( $preparedUrls ) );
	}

	/**
	 * Stores the image data for a given post in our DB table.
	 *
	 * @since 4.0.5
	 *
	 * @param  int   $postId The post ID.
	 * @param  array $images The images.
	 * @return void
	 */
	private function updatePost( $postId, $images = [] ) {
		$post                    = \AIOSEO\Plugin\Common\Models\Post::getPost( $postId );
		$meta                    = $post->exists() ? [] : aioseo()->migration->meta->getMigratedPostMeta( $postId );
		$meta['post_id']         = $postId;
		$meta['images']          = ! empty( $images ) ? $images : null;
		$meta['image_scan_date'] = gmdate( 'Y-m-d H:i:s' );

		$post->set( $meta );
		$post->save();
	}

	/**
	 * Returns the image extension regex pattern.
	 *
	 * @since 4.2.2
	 *
	 * @return string
	 */
	public function getImageExtensionRegexPattern() {
		static $pattern;
		if ( null !== $pattern ) {
			return $pattern;
		}

		$pattern = '/http.*\.(' . implode( '|', $this->supportedExtensions ) . ')$/i';

		return $pattern;
	}
}