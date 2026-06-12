<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Post DB Model.
 *
 * @since 4.0.0
 */
class Post extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table = 'aioseo_posts';

	/**
	 * Fields that should be json encoded on save and decoded on get.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $jsonFields = [
		'keywords',
		'keyphrases',
		'page_analysis',
		'schema',
		'images',
		'videos',
		'ai',
		'options',
		'local_seo',
		'primary_term',
		'breadcrumb_settings',
		'og_article_tags',
		'ai'
	];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * Fields that should be boolean values.
	 *
	 * @since 4.0.13
	 *
	 * @var array
	 */
	protected $booleanFields = [
		'twitter_use_og',
		'pillar_content',
		'robots_default',
		'robots_noindex',
		'robots_noarchive',
		'robots_nosnippet',
		'robots_nofollow',
		'robots_noimageindex',
		'robots_noodp',
		'robots_notranslate',
		'limit_modified_date',
	];

	/**
	 * Fields that can be null when saved.
	 *
	 * @since 4.5.7
	 *
	 * @var array
	 */
	protected $nullFields = [
		'priority'
	];

	/**
	 * Fields that should be float values.
	 *
	 * @since 4.7.3
	 *
	 * @var array
	 */
	protected $floatFields = [
		'priority'
	];

	/**
	 * Returns a Post with a given ID.
	 *
	 * @since 4.0.0
	 *
	 * @param  int  $postId The post ID.
	 * @return Post         The Post object.
	 */
	public static function getPost( $postId ) {
		// This is needed to prevent an error when upgrading from 4.1.8 to 4.1.9.
		// WordPress deletes the attachment .zip file for the new plugin version after installing it, which triggers the "delete_post" hook.
		// In-between the 4.1.8 to 4.1.9 update, the new Core class does not exist yet, causing the PHP error.
		// TODO: Delete this in a future release.
		$post = new self();
		if ( ! property_exists( aioseo(), 'core' ) ) {
			return $post;
		}

		$post = aioseo()->core->db->start( 'aioseo_posts' )
			->where( 'post_id', $postId )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Post' );

		if ( ! $post->exists() ) {
			$post->post_id = $postId;
			$post          = self::setDynamicDefaults( $post, $postId );
		} else {
			$post = self::runDynamicMigrations( $post );
		}

		// Set options object.
		$post = self::setOptionsDefaults( $post );

		return apply_filters( 'aioseo_get_post', $post );
	}

	/**
	 * Sets the dynamic defaults on the post object if it doesn't exist in the DB yet.
	 *
	 * @since 4.1.4
	 *
	 * @param  Post $post   The Post object.
	 * @param  int  $postId The post ID.
	 * @return Post         The modified Post object.
	 */
	private static function setDynamicDefaults( $post, $postId ) {
		if ( 'page' === get_post_type( $postId ) ) { // This check cannot be deleted and is required to prevent errors after WordPress cleans up the attachment it creates when a plugin is updated.
			$isWooCommerceCheckoutPage = aioseo()->helpers->isWooCommerceCheckoutPage( $postId );
			if (
				$isWooCommerceCheckoutPage ||
				aioseo()->helpers->isWooCommerceCartPage( $postId ) ||
				aioseo()->helpers->isWooCommerceAccountPage( $postId )
			) {
				$post->robots_default = false;
				$post->robots_noindex = true;
			}
		}

		if ( aioseo()->helpers->isStaticHomePage( $postId ) ) {
			$post->og_object_type = 'website';
		}

		$post->twitter_use_og = aioseo()->options->social->twitter->general->useOgData;

		if ( property_exists( $post, 'schema' ) && null === $post->schema ) {
			$post->schema = self::getDefaultSchemaOptions();
		}

		return $post;
	}

	/**
	 * Migrates removed QAPage schema on-the-fly when the post is loaded.
	 *
	 * @since 4.1.8
	 *
	 * @param  Post $aioseoPost The post object.
	 * @return Post             The modified post object.
	 */
	private static function migrateRemovedQaSchema( $aioseoPost ) {
		if ( ! $aioseoPost->schema_type || 'webpage' !== strtolower( $aioseoPost->schema_type ) ) {
			return $aioseoPost;
		}

		$schemaTypeOptions = json_decode( $aioseoPost->schema_type_options );
		if ( 'qapage' !== strtolower( $schemaTypeOptions->webPage->webPageType ) ) {
			return $aioseoPost;
		}

		$schemaTypeOptions->webPage->webPageType = 'WebPage';
		$aioseoPost->schema_type_options         = wp_json_encode( $schemaTypeOptions );
		$aioseoPost->save();

		return $aioseoPost;
	}

	/**
	 * Runs dynamic migrations whenever the post object is loaded.
	 *
	 * @since 4.1.7
	 *
	 * @param  Post $post The Post object.
	 * @return Post       The modified Post object.
	 */
	private static function runDynamicMigrations( $post ) {
		$post = self::migrateRemovedQaSchema( $post );
		$post = self::migrateImageTypes( $post );
		$post = self::runDynamicSchemaMigration( $post );
		$post = self::migrateKoreaCountryCodeSchemas( $post );

		return $post;
	}


	/**
	 * Migrates the post's schema data when it is loaded.
	 *
	 * @since 4.2.5
	 *
	 * @param  Post $post The Post object.
	 * @return Post       The modified Post object.
	 */
	private static function runDynamicSchemaMigration( $post ) {
		if ( ! property_exists( $post, 'schema' ) ) {
			return $post;
		}

		if ( null === $post->schema ) {
			$post = aioseo()->updates->migratePostSchemaHelper( $post );
		}

		// If the schema prop isn't set yet, we want to set it here.
		// We also want to run this regardless of whether it is already set to make sure the default schema graph
		// is correctly propagated on the frontend after changing it.
		$post->schema = self::getDefaultSchemaOptions( $post->schema );

		// Filter out null or empty graphs.
		$post->schema->graphs = array_filter( $post->schema->graphs, function( $graph ) {
			return ! empty( $graph );
		} );

		foreach ( $post->schema->graphs as $graph ) {
			// If the first character of the graph ID isn't a pound, add one.
			// We have to do this because the schema migration in 4.2.5 didn't add the pound for custom graphs.
			if ( property_exists( $graph, 'id' ) && '#' !== substr( $graph->id, 0, 1 ) ) {
				$graph->id = '#' . $graph->id;
			}

			// If the graph has an old rating value, we need to migrate it to the review.
			if (
				property_exists( $graph, 'id' ) &&
				preg_match( '/(movie|software-application)/', (string) $graph->id ) &&
				property_exists( $graph->properties, 'rating' ) &&
				property_exists( $graph->properties->rating, 'value' )
			) {
				$graph->properties->review->rating = $graph->properties->rating->value;
				unset( $graph->properties->rating->value );
			}

			// If the graph has audience data, we need to migrate it to the correct one.
			if (
				property_exists( $graph, 'id' ) &&
				preg_match( '/(product|product-review)/', $graph->id ) &&
				property_exists( $graph->properties, 'audience' )
			) {
				$graph->properties->audience = self::migratePostAudienceAgeSchema( $graph->properties->audience );
			}
		}

		return $post;
	}

	/**
	 * Migrates the post's image types when it is loaded.
	 *
	 * @since 4.2.5
	 *
	 * @param  Post $post The Post object.
	 * @return Post       The modified Post object.
	 */
	private static function migrateImageTypes( $post ) {
		$pageBuilder = aioseo()->helpers->getPostPageBuilderName( $post->post_id );
		if ( ! $pageBuilder ) {
			return $post;
		}

		$deprecatedImageSources = 'seedprod' === strtolower( $pageBuilder )
			? [ 'auto', 'custom', 'featured' ]
			: [ 'auto' ];

		if ( ! empty( $post->og_image_type ) && in_array( $post->og_image_type, $deprecatedImageSources, true ) ) {
			$post->og_image_type = 'default';
		}

		if ( ! empty( $post->twitter_image_type ) && in_array( $post->twitter_image_type, $deprecatedImageSources, true ) ) {
			$post->twitter_image_type = 'default';
		}

		return $post;
	}

	/**
	 * Saves the Post object.
	 *
	 * @since 4.0.3
	 *
	 * @param  int              $postId The Post ID.
	 * @param  array            $data   The post data to save.
	 * @return bool|void|string         Whether the post data was saved or a DB error message.
	 */
	public static function savePost( $postId, $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		$thePost = self::getPost( $postId );
		$data    = apply_filters( 'aioseo_save_post', $data, $thePost );

		// Before setting the data, we check if the title/description are the same as the defaults and clear them if so.
		$data    = self::checkForDefaultFormat( $postId, $thePost, $data );
		$thePost = self::sanitizeAndSetDefaults( $postId, $thePost, $data );

		// Update traditional post meta so that it can be used by multilingual plugins.
		self::updatePostMeta( $postId, $data );

		$thePost->save();
		$thePost->reset();

		$lastError = aioseo()->core->db->lastError();
		if ( ! empty( $lastError ) ) {
			return $lastError;
		}

		// Fires once an AIOSEO post has been saved.
		do_action( 'aioseo_insert_post', $postId );
	}

	/**
	 * Checks if the title/description is the same as their default format in Search Appearance and nulls it if this is the case.
	 * Doing this ensures that updates to the default title/description format also propogate to the post.
	 *
	 * @since 4.1.5
	 *
	 * @param  int   $postId  The post ID.
	 * @param  Post  $thePost The Post object.
	 * @param  array $data    The data.
	 * @return array          The data.
	 */
	private static function checkForDefaultFormat( $postId, $thePost, $data ) {
		// Patch-friendly: only normalise fields the caller actually sent. Missing keys are left alone
		// so the patch-style sanitizeAndSetDefaults below doesn't overwrite the existing model value.
		$hasTitle       = array_key_exists( 'title', $data );
		$hasDescription = array_key_exists( 'description', $data );

		if ( ! $hasTitle && ! $hasDescription ) {
			return $data;
		}

		if ( $hasTitle ) {
			$data['title'] = trim( (string) $data['title'] );
		}
		if ( $hasDescription ) {
			$data['description'] = trim( (string) $data['description'] );
		}

		$post                     = aioseo()->helpers->getPost( $postId );
		$defaultTitleFormat       = trim( aioseo()->meta->title->getPostTypeTitle( $post->post_type ) );
		$defaultDescriptionFormat = trim( aioseo()->meta->description->getPostTypeDescription( $post->post_type ) );

		if ( $hasTitle && ! empty( $data['title'] ) && $data['title'] === $defaultTitleFormat ) {
			$data['title'] = null;
		}
		if ( $hasDescription && ! empty( $data['description'] ) && $data['description'] === $defaultDescriptionFormat ) {
			$data['description'] = null;
		}

		return $data;
	}

	/**
	 * Sanitize the keyphrases posted data.
	 *
	 * @since 4.2.8
	 *
	 * @param  array $data An array containing the keyphrases field data.
	 * @return array       The sanitized data.
	 */
	private static function sanitizeKeyphrases( $data ) {
		if (
			! empty( $data['focus']['analysis'] ) &&
			is_array( $data['focus']['analysis'] )
		) {
			foreach ( $data['focus']['analysis'] as &$analysis ) {
				// Remove unnecessary 'title' and 'description'.
				unset( $analysis['title'] );
				unset( $analysis['description'] );
			}
		}

		if (
			! empty( $data['additional'] ) &&
			is_array( $data['additional'] )
		) {
			foreach ( $data['additional'] as &$additional ) {
				if (
					! empty( $additional['analysis'] ) &&
					is_array( $additional['analysis'] )
				) {
					foreach ( $additional['analysis'] as &$additionalAnalysis ) {
						// Remove unnecessary 'title' and 'description'.
						unset( $additionalAnalysis['title'] );
						unset( $additionalAnalysis['description'] );
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Sanitize the page_analysis posted data.
	 *
	 * @since 4.2.7
	 *
	 * @param  array $data An array containing the page_analysis field data.
	 * @return array       The sanitized data.
	 */
	private static function sanitizePageAnalysis( $data ) {
		if (
			empty( $data['analysis'] ) ||
			! is_array( $data['analysis'] )
		) {
			return $data;
		}

		foreach ( $data['analysis'] as &$analysis ) {
			foreach ( $analysis as $key => $result ) {
				// Remove unnecessary data.
				foreach ( [ 'title', 'description', 'highlightSentences' ] as $keyToRemove ) {
					if ( isset( $analysis[ $key ][ $keyToRemove ] ) ) {
						unset( $analysis[ $key ][ $keyToRemove ] );
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Returns the patch-friendly field map for {@see Post::sanitizeAndSetDefaults()}.
	 *
	 * Each entry maps an input data key to:
	 *   - `column`    The Post model column to write.
	 *   - `sanitize`  One of `text`, `url`, `bool`, `helper`, `int_neg1`, `raw`. Picks the
	 *                 sanitiser applied to the value when the key is present in input data.
	 *   - `default`   Optional. Value to write when the input value is empty (for `text`/`url`/`helper`/`raw`).
	 *                 Booleans + int_neg1 ignore this — they always have a deterministic mapping.
	 *
	 * Fields that need bespoke logic (keyphrases, schema, ai, priority, breadcrumb_settings) live
	 * outside this map and are handled directly in sanitizeAndSetDefaults().
	 *
	 * @since 4.9.8
	 *
	 * @return array
	 */
	protected static function getSanitizeFieldMap() {
		return [
			// General.
			'title'                       => [
				'column'   => 'title',
				'sanitize' => 'text'
			],
			'description'                 => [
				'column'   => 'description',
				'sanitize' => 'text'
			],
			'canonicalUrl'                => [
				'column'   => 'canonical_url',
				'sanitize' => 'text'
			],
			'keywords'                    => [
				'column'   => 'keywords',
				'sanitize' => 'helper'
			],
			'pillar_content'              => [
				'column'   => 'pillar_content',
				'sanitize' => 'bool'
			],
			// TruSEO score (numeric stored as text by AIOSEO).
			'seo_score'                   => [
				'column'   => 'seo_score',
				'sanitize' => 'text',
				'default'  => 0
			],
			// Sitemap.
			'frequency'                   => [
				'column'   => 'frequency',
				'sanitize' => 'text',
				'default'  => 'default'
			],
			// Robots Meta.
			'default'                     => [
				'column'   => 'robots_default',
				'sanitize' => 'bool'
			],
			'noindex'                     => [
				'column'   => 'robots_noindex',
				'sanitize' => 'bool'
			],
			'nofollow'                    => [
				'column'   => 'robots_nofollow',
				'sanitize' => 'bool'
			],
			'noarchive'                   => [
				'column'   => 'robots_noarchive',
				'sanitize' => 'bool'
			],
			'notranslate'                 => [
				'column'   => 'robots_notranslate',
				'sanitize' => 'bool'
			],
			'noimageindex'                => [
				'column'   => 'robots_noimageindex',
				'sanitize' => 'bool'
			],
			'nosnippet'                   => [
				'column'   => 'robots_nosnippet',
				'sanitize' => 'bool'
			],
			'noodp'                       => [
				'column'   => 'robots_noodp',
				'sanitize' => 'bool'
			],
			'maxSnippet'                  => [
				'column'   => 'robots_max_snippet',
				'sanitize' => 'int_neg1'
			],
			'maxVideoPreview'             => [
				'column'   => 'robots_max_videopreview',
				'sanitize' => 'int_neg1'
			],
			'maxImagePreview'             => [
				'column'   => 'robots_max_imagepreview',
				'sanitize' => 'text',
				'default'  => 'large'
			],
			// Open Graph Meta.
			'og_title'                    => [
				'column'   => 'og_title',
				'sanitize' => 'text'
			],
			'og_description'              => [
				'column'   => 'og_description',
				'sanitize' => 'text'
			],
			'og_object_type'              => [
				'column'   => 'og_object_type',
				'sanitize' => 'text',
				'default'  => 'default'
			],
			'og_image_type'               => [
				'column'   => 'og_image_type',
				'sanitize' => 'text',
				'default'  => 'default'
			],
			'og_image_custom_url'         => [
				'column'   => 'og_image_custom_url',
				'sanitize' => 'url'
			],
			'og_image_custom_fields'      => [
				'column'   => 'og_image_custom_fields',
				'sanitize' => 'text'
			],
			'og_video'                    => [
				'column'   => 'og_video',
				'sanitize' => 'text',
				'default'  => ''
			],
			'og_article_section'          => [
				'column'   => 'og_article_section',
				'sanitize' => 'text'
			],
			'og_article_tags'             => [
				'column'   => 'og_article_tags',
				'sanitize' => 'helper'
			],
			// Twitter Meta.
			'twitter_title'               => [
				'column'   => 'twitter_title',
				'sanitize' => 'text'
			],
			'twitter_description'         => [
				'column'   => 'twitter_description',
				'sanitize' => 'text'
			],
			'twitter_use_og'              => [
				'column'   => 'twitter_use_og',
				'sanitize' => 'bool'
			],
			'twitter_card'                => [
				'column'   => 'twitter_card',
				'sanitize' => 'text',
				'default'  => 'default'
			],
			'twitter_image_type'          => [
				'column'   => 'twitter_image_type',
				'sanitize' => 'text',
				'default'  => 'default'
			],
			'twitter_image_custom_url'    => [
				'column'   => 'twitter_image_custom_url',
				'sanitize' => 'url'
			],
			'twitter_image_custom_fields' => [
				'column'   => 'twitter_image_custom_fields',
				'sanitize' => 'text'
			],
			// Misc.
			'local_seo'                   => [
				'column'   => 'local_seo',
				'sanitize' => 'raw'
			],
			'limit_modified_date'         => [
				'column'   => 'limit_modified_date',
				'sanitize' => 'bool'
			],
			'primary_term'                => [
				'column'   => 'primary_term',
				'sanitize' => 'raw'
			]
		];
	}

	/**
	 * Applies a patch-style field map to a model: only writes keys present in $data; leaves others alone.
	 *
	 * Generic helper used by both Post and Term sanitizeAndSetDefaults(). The sanitize switch encodes
	 * the small set of patterns the AIOSEO models share. Anything outside this set should be handled
	 * inline in the model's sanitizeAndSetDefaults() rather than added to the switch.
	 *
	 * @since 4.9.8
	 *
	 * @param  object $model The model instance being mutated.
	 * @param  array  $data  The input data array.
	 * @param  array  $map   The field map (see getSanitizeFieldMap()).
	 * @return void
	 */
	public static function applyPatchFields( $model, $data, $map ) {
		foreach ( $map as $key => $spec ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			$value  = $data[ $key ];
			$column = $spec['column'];

			switch ( $spec['sanitize'] ) {
				case 'text':
					$model->$column = ! empty( $value ) ? sanitize_text_field( $value ) : ( $spec['default'] ?? null );
					break;
				case 'url':
					$model->$column = ! empty( $value ) ? esc_url_raw( $value ) : ( $spec['default'] ?? null );
					break;
				case 'bool':
					$model->$column = rest_sanitize_boolean( $value );
					break;
				case 'helper':
					$model->$column = ! empty( $value ) ? aioseo()->helpers->sanitize( $value ) : ( $spec['default'] ?? null );
					break;
				case 'int_neg1':
					$model->$column = is_numeric( $value ) ? (int) sanitize_text_field( $value ) : -1;
					break;
				case 'raw':
					$model->$column = ! empty( $value ) ? $value : ( $spec['default'] ?? null );
					break;
			}
		}
	}

	/**
	 * Sanitizes the post data and sets it (or the default value) to the Post object.
	 *
	 * @since 4.1.5
	 *
	 * @param  int   $postId  The post ID.
	 * @param  Post  $thePost The Post object.
	 * @param  array $data    The data.
	 * @return Post           The Post object with data set.
	 */
	protected static function sanitizeAndSetDefaults( $postId, $thePost, $data ) {
		// Patch semantics: only assign fields that appear in $data. Missing keys preserve the
		// current model value. Callers that want to clear a field must pass an explicit null/empty.

		$thePost->post_id = $postId;

		self::applyPatchFields( $thePost, $data, self::getSanitizeFieldMap() );

		// Custom fields — those that need a per-field callable or model-specific logic don't
		// fit the generic map. Each guards on array_key_exists so partial updates stay safe.
		if ( array_key_exists( 'keyphrases', $data ) ) {
			$thePost->keyphrases = ! empty( $data['keyphrases'] ) ? self::sanitizeKeyphrases( $data['keyphrases'] ) : null;
		}
		if ( array_key_exists( 'page_analysis', $data ) ) {
			$thePost->page_analysis = ! empty( $data['page_analysis'] ) ? self::sanitizePageAnalysis( $data['page_analysis'] ) : null;
		}
		if ( array_key_exists( 'priority', $data ) ) {
			$thePost->priority = 'default' === sanitize_text_field( (string) $data['priority'] ) ? null : (float) $data['priority'];
		}
		if ( array_key_exists( 'schema', $data ) ) {
			$thePost->schema = ! empty( $data['schema'] ) ? self::getDefaultSchemaOptions( $data['schema'] ) : null;
		}
		if ( array_key_exists( 'ai', $data ) ) {
			$thePost->ai = ! empty( $data['ai'] ) ? self::getDefaultAiOptions( $data['ai'] ) : null;
		}
		if ( array_key_exists( 'breadcrumb_settings', $data ) ) {
			$thePost->breadcrumb_settings = isset( $data['breadcrumb_settings']['default'] ) && false === $data['breadcrumb_settings']['default'] ? $data['breadcrumb_settings'] : null;
		}

		// Always reset — recomputed by setOgTwitterImageData() below.
		$thePost->og_image_url      = null;
		$thePost->og_image_width    = null;
		$thePost->og_image_height   = null;
		$thePost->twitter_image_url = null;

		// Always-stamped — every save bumps the timestamp.
		$thePost->updated = gmdate( 'Y-m-d H:i:s' );

		// Before we determine the OG/Twitter image, we need to set the meta data cache manually because the changes haven't been saved yet.
		aioseo()->meta->metaData->bustPostCache( $thePost->post_id, $thePost );

		// Set the OG/Twitter image data.
		$thePost = self::setOgTwitterImageData( $thePost );

		if ( ! $thePost->exists() ) {
			$thePost->created = gmdate( 'Y-m-d H:i:s' );
		}

		// Update defaults from addons.
		foreach ( aioseo()->addons->getLoadedAddons() as $addon ) {
			if ( isset( $addon->postModel ) && method_exists( $addon->postModel, 'sanitizeAndSetDefaults' ) ) {
				$thePost = $addon->postModel->sanitizeAndSetDefaults( $postId, $thePost, $data );
			}
		}

		return $thePost;
	}

	/**
	 * Set the OG/Twitter image data on the post object.
	 *
	 * @since 4.1.6
	 *
	 * @param  Post $thePost The Post object to modify.
	 * @return Post          The modified Post object.
	 */
	public static function setOgTwitterImageData( $thePost ) {
		// Set the OG image.
		if (
			in_array( $thePost->og_image_type, [
				'featured',
				'content',
				'attach',
				'custom',
				'custom_image'
			], true )
		) {
			// Disable the cache.
			aioseo()->social->image->useCache = false;

			// Set the image details.
			$ogImage                  = aioseo()->social->facebook->getImage( $thePost->post_id );
			$thePost->og_image_url    = is_array( $ogImage ) ? $ogImage[0] : $ogImage;
			$thePost->og_image_width  = aioseo()->social->facebook->getImageWidth();
			$thePost->og_image_height = aioseo()->social->facebook->getImageHeight();

			// Reset the cache property.
			aioseo()->social->image->useCache = true;
		}

		// Set the Twitter image.
		if (
			! $thePost->twitter_use_og &&
			in_array( $thePost->twitter_image_type, [
				'featured',
				'content',
				'attach',
				'custom',
				'custom_image'
			], true )
		) {
			// Disable the cache.
			aioseo()->social->image->useCache = false;

			// Set the image details.
			$ogImage                    = aioseo()->social->twitter->getImage( $thePost->post_id );
			$thePost->twitter_image_url = is_array( $ogImage ) ? $ogImage[0] : $ogImage;

			// Reset the cache property.
			aioseo()->social->image->useCache = true;
		}

		return $thePost;
	}

	/**
	 * Saves some of the data as post meta so that it can be used for localization.
	 *
	 * @since   4.1.5
	 * @version 4.9.8 Patch-aware: only syncs meta for keys present in $data, so partial saves
	 *                 (e.g. the Abilities API) don't warn on, or wipe, fields they didn't touch.
	 *
	 * @param  int   $postId The post ID.
	 * @param  array $data   The data.
	 * @return void
	 */
	public static function updatePostMeta( $postId, $data ) {
		// Update the post meta as well for localization. Only the keys actually present in $data are
		// synced — missing keys keep their existing meta, mirroring sanitizeAndSetDefaults()'s patch semantics.
		$metaMap = [
			'title'               => '_aioseo_title',
			'description'         => '_aioseo_description',
			'og_title'            => '_aioseo_og_title',
			'og_description'      => '_aioseo_og_description',
			'og_article_section'  => '_aioseo_og_article_section',
			'twitter_title'       => '_aioseo_twitter_title',
			'twitter_description' => '_aioseo_twitter_description'
		];
		foreach ( $metaMap as $key => $metaKey ) {
			if ( array_key_exists( $key, $data ) ) {
				update_post_meta( $postId, $metaKey, $data[ $key ] );
			}
		}

		if ( array_key_exists( 'keywords', $data ) ) {
			$keywords = ! empty( $data['keywords'] ) ? aioseo()->helpers->jsonTagsToCommaSeparatedList( $data['keywords'] ) : [];
			update_post_meta( $postId, '_aioseo_keywords', $keywords );
		}
		if ( array_key_exists( 'og_article_tags', $data ) ) {
			$ogArticleTags = ! empty( $data['og_article_tags'] ) ? aioseo()->helpers->jsonTagsToCommaSeparatedList( $data['og_article_tags'] ) : [];
			update_post_meta( $postId, '_aioseo_og_article_tags', $ogArticleTags );
		}
	}

	/**
	 * Returns the default values for the TruSEO page analysis.
	 *
	 * @since 4.0.0
	 *
	 * @param  object|null $pageAnalysis The page analysis object.
	 * @return object                    The default values.
	 */
	public static function getPageAnalysisDefaults( $pageAnalysis = null ) {
		$defaults = [
			'analysis' => [
				'basic'       => [
					'lengthContent' => [
						'error'    => 1,
						'maxScore' => 9,
						'score'    => 6,
					],
				],
				'title'       => [
					'titleLength' => [
						'error'    => 1,
						'maxScore' => 9,
						'score'    => 1,
					],
				],
				'readability' => [
					'contentHasAssets' => [
						'error'    => 1,
						'maxScore' => 5,
						'score'    => 0,
					],
				]
			]
		];

		if ( empty( $pageAnalysis ) ) {
			return json_decode( wp_json_encode( $defaults ) );
		}

		return $pageAnalysis;
	}

	/**
	 * Returns a JSON object with default schema options.
	 *
	 * @since 4.2.5
	 *
	 * @param  string        $existingOptions The existing options in JSON.
	 * @param  null|\WP_Post $post            The post object.
	 * @return object                         The existing options with defaults added in JSON.
	 */
	public static function getDefaultSchemaOptions( $existingOptions = '', $post = null ) {
		$defaultGraphName = aioseo()->schema->getDefaultPostTypeGraph( $post );

		$defaults = [
			'blockGraphs'  => [],
			'customGraphs' => [],
			'default'      => [
				'data'      => [
					'Article'             => [],
					'Course'              => [],
					'Dataset'             => [],
					'FAQPage'             => [],
					'Movie'               => [],
					'Person'              => [],
					'Product'             => [],
					'ProductReview'       => [],
					'Car'                 => [],
					'Recipe'              => [],
					'Service'             => [],
					'SoftwareApplication' => [],
					'WebPage'             => []
				],
				'graphName' => $defaultGraphName,
				'isEnabled' => true
			],
			'graphs'       => []
		];

		if ( empty( $existingOptions ) ) {
			return json_decode( wp_json_encode( $defaults ) );
		}

		$existingOptions = json_decode( wp_json_encode( $existingOptions ), true );
		$existingOptions = array_replace_recursive( $defaults, $existingOptions );

		if ( isset( $existingOptions['defaultGraph'] ) && ! empty( $existingOptions['defaultPostTypeGraph'] ) ) {
			$existingOptions['default']['isEnabled'] = ! empty( $existingOptions['defaultGraph'] );

			unset( $existingOptions['defaultGraph'] );
			unset( $existingOptions['defaultPostTypeGraph'] );
		}

		// Reset the default graph type to make sure it's accurate.
		if ( $defaultGraphName ) {
			$existingOptions['default']['graphName'] = $defaultGraphName;
		}

		return json_decode( wp_json_encode( $existingOptions ) );
	}

	/**
	 * Returns the defaults for the keyphrases column.
	 *
	 * @since 4.1.7
	 *
	 * @param  null|object $keyphrases The database keyphrases.
	 * @return object                  The defaults.
	 */
	public static function getKeyphrasesDefaults( $keyphrases = null ) {
		$defaults = [
			'focus'      => [
				'keyphrase' => '',
				'score'     => 0,
				'analysis'  => [
					'keyphraseInTitle' => [
						'score'    => 0,
						'maxScore' => 9,
						'error'    => 1
					]
				]
			],
			'additional' => []
		];

		if ( empty( $keyphrases ) ) {
			return json_decode( wp_json_encode( $defaults ) );
		}

		$defaults = json_decode( wp_json_encode( $defaults ) );

		if ( empty( $keyphrases->focus ) ) {
			$keyphrases->focus = $defaults->focus;
		}

		if ( empty( $keyphrases->additional ) ) {
			$keyphrases->additional = $defaults->additional;
		}

		return $keyphrases;
	}

	/**
	 * Returns the defaults for the options column.
	 *
	 * @since   4.2.2
	 * @version 4.2.9
	 *
	 * @param  Post $post   The Post object.
	 * @return Post         The modified Post object.
	 */
	public static function setOptionsDefaults( $post ) {
		$defaults = [
			'linkFormat'  => [
				'internalLinkCount'      => 0,
				'linkAssistantDismissed' => false
			],
			'primaryTerm' => [
				'productEducationDismissed' => false
			]
		];

		if ( empty( $post->options ) ) {
			$post->options = json_decode( wp_json_encode( $defaults ) );

			return $post;
		}

		$post->options = json_decode( wp_json_encode( $post->options ), true );
		$post->options = array_replace_recursive( $defaults, $post->options );
		$post->options = json_decode( wp_json_encode( $post->options ) );

		return $post;
	}

	/**
	 * Returns the default breadcrumb settings options.
	 *
	 * @since 4.8.3
	 *
	 * @param  array  $postType        The post type.
	 * @param  array  $existingOptions The existing options.
	 * @return object                  The default options.
	 */
	public static function getDefaultBreadcrumbSettingsOptions( $postType, $existingOptions = [] ) {
		$default       = aioseo()->dynamicOptions->breadcrumbs->postTypes->$postType->useDefaultTemplate ?? true;
		$showHomeCrumb = $default ? aioseo()->options->breadcrumbs->homepageLink : aioseo()->dynamicOptions->breadcrumbs->postTypes->$postType->showHomeCrumb ?? true;
		$allTaxonomies = get_object_taxonomies( $postType, 'objects' );
		$taxonomy      = aioseo()->dynamicOptions->breadcrumbs->postTypes->$postType->taxonomy ?? array_values( $allTaxonomies )[0]->name ?? '';

		$defaults = [
			'default'            => true,
			'separator'          => aioseo()->options->breadcrumbs->separator,
			'showHomeCrumb'      => $showHomeCrumb ?? true,
			'showTaxonomyCrumbs' => aioseo()->dynamicOptions->breadcrumbs->postTypes->$postType->showTaxonomyCrumbs ?? true,
			'showParentCrumbs'   => aioseo()->dynamicOptions->breadcrumbs->postTypes->$postType->showParentCrumbs ?? true,
			'template'           => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'single' ) ),
			'parentTemplate'     => aioseo()->helpers->encodeOutputHtml( aioseo()->breadcrumbs->frontend->getDefaultTemplate( 'single' ) ),
			'taxonomy'           => $taxonomy,
			'primaryTerm'        => null
		];

		if ( empty( $existingOptions ) ) {
			return json_decode( wp_json_encode( $defaults ) );
		}

		$existingOptions = json_decode( wp_json_encode( $existingOptions ), true );
		if ( ! is_array( $existingOptions ) ) {
			return json_decode( wp_json_encode( $defaults ) );
		}

		$existingOptions = array_replace_recursive( $defaults, $existingOptions );

		return json_decode( wp_json_encode( $existingOptions ) );
	}

	/**
	 * Migrates the post's audience age schema data when it is loaded.
	 * Min age: [0 => newborns, 0.25 => infants, 1 => toddlers, 5 => kids, 13 => adults]
	 * Max age: [0.25 => newborns, 1 => infants, 5 => toddlers, 13 => kids]
	 *
	 * @since 4.7.9
	 *
	 * @param  object $audience The audience data.
	 * @return object
	 */
	public static function migratePostAudienceAgeSchema( $audience ) {
		$ages = [ 0, 0.25, 1, 5, 13 ];

		// converts variable to integer if it's a number otherwise is null.
		$parsedMinAge = filter_var( $audience->minimumAge, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE );
		$parsedMaxAge = filter_var( $audience->maximumAge, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE );

		if ( null === $parsedMinAge && null === $parsedMaxAge ) {
			return $audience;
		}

		$minAge = is_numeric( $parsedMinAge ) ? $parsedMinAge : 0;
		$maxAge = is_numeric( $parsedMaxAge ) ? $parsedMaxAge : null;

		// get the minimumAge if available or the nearest bigger one.
		foreach ( $ages as $age ) {
			if ( $age >= $minAge ) {
				$audience->minimumAge = $age;
				break;
			}
		}

		// get the maximumAge if available or the nearest bigger one.
		foreach ( $ages as $age ) {
			if ( $age >= $maxAge ) {
				$maxAge = $age;
				break;
			}
		}

		// makes sure the maximumAge is 13 below
		if ( null !== $maxAge ) {
			$audience->maximumAge = 13 < $maxAge ? 13 : $maxAge;
		}

		// Minimum age 13 is for adults.
		// If minimumAge is still higher or equal 13 then it's for adults and maximumAge should be empty.
		if ( 13 <= $audience->minimumAge ) {
			$audience->minimumAge = 13;
			$audience->maximumAge = null;
		}

		return $audience;
	}

	/**
	 * Migrates update Korea country code for Person, Product, Event, and JobsPosting schemas.
	 *
	 * @since 4.7.1
	 *
	 * @param  Post $aioseoPost The post object.
	 * @return Post             The modified post object.
	 */
	private static function migrateKoreaCountryCodeSchemas( $aioseoPost ) {
		if ( empty( $aioseoPost->schema ) || empty( $aioseoPost->schema->graphs ) ) {
			return $aioseoPost;
		}

		foreach ( $aioseoPost->schema->graphs as $key => $graph ) {
			if ( isset( $aioseoPost->schema->graphs[ $key ]->properties->location->country ) ) {
				$aioseoPost->schema->graphs[ $key ]->properties->location->country = self::invertKoreaCode( $graph->properties->location->country );
			}

			if ( isset( $aioseoPost->schema->graphs[ $key ]->properties->shippingDestinations ) ) {
				$aioseoPost->schema->graphs[ $key ]->properties->shippingDestinations = array_map( function( $item ) {
					$item->country = self::invertKoreaCode( $item->country );

					return $item;
				}, $graph->properties->shippingDestinations );
			}
		}

		$aioseoPost->save();

		return $aioseoPost;
	}

	/**
	 * Utility function to invert the country code for Korea.
	 *
	 * @since 4.7.1
	 *
	 * @param  string $code country code.
	 * @return string       country code.
	 */
	public static function invertKoreaCode( $code ) {
		return 'KP' === $code ? 'KR' : $code;
	}

	/**
	 * Returns the default AI options.
	 *
	 * @since 4.8.4
	 *
	 * @param  array $existingOptions The existing options.
	 * @return object                 The default options.
	 */
	public static function getDefaultAiOptions( $existingOptions = [] ) {
		$defaults = [
			'faqs'         => [],
			'keyPoints'    => [],
			'schemas'      => [],
			'titles'       => [],
			'descriptions' => [],
			'socialPosts'  => [
				'email'     => [],
				'linkedin'  => [],
				'twitter'   => [],
				'facebook'  => [],
				'instagram' => []
			]
		];

		if ( empty( $existingOptions ) ) {
			return json_decode( wp_json_encode( $defaults ) );
		}

		$existingOptions = array_replace_recursive( $defaults, (array) $existingOptions );

		return json_decode( wp_json_encode( $existingOptions ) );
	}
}