<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class PostsTerms {
	/**
	 * MySQL DATETIME format used when stamping `updated` columns.
	 *
	 * @since 5.0.0
	 *
	 * @var string
	 */
	const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Searches for posts or terms by ID/name.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function searchForObjects( $request ) {
		$body = $request->get_json_params();

		if ( empty( $body['query'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No search term was provided.'
			], 400 );
		}
		if ( empty( $body['type'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No type was provided.'
			], 400 );
		}

		$searchQuery = esc_sql( aioseo()->core->db->db->esc_like( $body['query'] ) );

		$objects        = [];
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		if ( 'posts' === $body['type'] ) {

			$postTypes = aioseo()->helpers->getPublicPostTypes( true );
			foreach ( $postTypes as $postType ) {
				// Check if post type isn't noindexed.
				if ( $dynamicOptions->searchAppearance->postTypes->has( $postType ) && ! $dynamicOptions->searchAppearance->postTypes->$postType->show ) {
					$postTypes = aioseo()->helpers->unsetValue( $postTypes, $postType );
				}
			}

			$objects = aioseo()->core->db
				->start( 'posts' )
				->select( 'ID, post_type, post_title, post_name' )
				->whereRaw( "( post_title LIKE '%{$searchQuery}%' OR post_name LIKE '%{$searchQuery}%' OR ID = '{$searchQuery}' )" )
				->whereIn( 'post_type', $postTypes )
				->whereIn( 'post_status', [ 'publish', 'draft', 'future', 'pending' ] )
				->orderBy( 'post_title' )
				->limit( 10 )
				->run()
				->result();

		} elseif ( 'terms' === $body['type'] ) {

			$taxonomies = aioseo()->helpers->getPublicTaxonomies( true );
			foreach ( $taxonomies as $taxonomy ) {
				// Check if taxonomy isn't noindexed.
				if ( $dynamicOptions->searchAppearance->taxonomies->has( $taxonomy ) && ! $dynamicOptions->searchAppearance->taxonomies->$taxonomy->show ) {
					$taxonomies = aioseo()->helpers->unsetValue( $taxonomies, $taxonomy );
				}
			}

			$objects = aioseo()->core->db
				->start( 'terms as t' )
				->select( 't.term_id as term_id, t.slug as slug, t.name as name, tt.taxonomy as taxonomy' )
				->join( 'term_taxonomy as tt', 't.term_id = tt.term_id', 'INNER' )
				->whereRaw( "( t.name LIKE '%{$searchQuery}%' OR t.slug LIKE '%{$searchQuery}%' OR t.term_id = '{$searchQuery}' )" )
				->whereIn( 'tt.taxonomy', $taxonomies )
				->orderBy( 't.name' )
				->limit( 10 )
				->run()
				->result();
		}

		if ( empty( $objects ) ) {
			return new \WP_REST_Response( [
				'success' => true,
				'objects' => []
			], 200 );
		}

		$parsed = [];
		foreach ( $objects as $object ) {
			if ( 'posts' === $body['type'] ) {
				$parsed[] = [
					'type'  => $object->post_type,
					'value' => (int) $object->ID,
					'slug'  => $object->post_name,
					'label' => $object->post_title,
					'link'  => get_permalink( $object->ID )
				];
			} elseif ( 'terms' === $body['type'] ) {
				// Always pass the taxonomy so get_term_link() can resolve the term, and coerce
				// the result to a string — get_term_link() can return WP_Error and the
				// term_link filter may also return false/null. Without this, the non-string
				// gets JSON-serialized and the frontend coerces it to "[object Object]" in
				// the href. See issue #8094.
				$termLink = get_term_link( (int) $object->term_id, $object->taxonomy );
				$parsed[] = [
					'type'  => $object->taxonomy,
					'value' => (int) $object->term_id,
					'slug'  => $object->slug,
					'label' => $object->name,
					'link'  => is_wp_error( $termLink ) ? '' : (string) $termLink
				];
			}
		}

		return new \WP_REST_Response( [
			'success' => true,
			'objects' => $parsed
		], 200 );
	}

	/**
	 * Get post data for fetch requests
	 *
	 * @since   4.0.0
	 * @version 4.8.3 Changes the return value to include only the Vue data.
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getPostData( $request ) {
		$args   = $request->get_query_params();
		$postId = $args['postId'] ?? null;

		if ( empty( $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No post ID was provided.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You are not allowed to access the data for this post.'
			], 403 );
		}

		$data = aioseo()->helpers->getVueData( 'post', $postId, $args['integrationSlug'] ?? null );

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				// We just send the minimum data that is needed for the post settings. See #7461
				'currentPost'  => $data['currentPost'],
				'redirects'    => ! empty( $data['redirects'] ) ? $data['redirects'] : null,
				'seoRevisions' => ! empty( $data['seoRevisions'] ) ? $data['seoRevisions'] : null
			]
		], 200 );
	}

	/**
	 * Get the first attached image for a post.
	 *
	 * @since 4.1.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getFirstAttachedImage( $request ) {
		$args = $request->get_params();

		if ( empty( $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No post ID was provided.'
			], 400 );
		}

		if ( ! current_user_can( 'read_post', $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		// Disable the cache.
		aioseo()->social->image->useCache = false;

		$post = aioseo()->helpers->getPost( $args['postId'] );
		$url  = aioseo()->social->image->getImage( 'facebook', 'attach', $post );

		// Reset the cache property.
		aioseo()->social->image->useCache = true;

		return new \WP_REST_Response( [
			'success' => true,
			'url'     => is_array( $url ) ? $url[0] : $url,
		], 200 );
	}

	/**
	 * Returns the posts custom fields.
	 *
	 * @since 4.0.6
	 *
	 * @param  \WP_Post|int $post The post.
	 * @return string             The custom field content.
	 */
	private static function getAnalysisContent( $post = null ) {
		$analysisContent = apply_filters( 'aioseo_analysis_content', aioseo()->helpers->getPostContent( $post ) );

		return sanitize_post_field( 'post_content', $analysisContent, $post->ID, 'display' );
	}

	/**
	 * Update post settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePosts( $request ) {
		$body   = $request->get_json_params();
		$postId = ! empty( $body['id'] ) ? intval( $body['id'] ) : null;

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post ID is missing.'
			], 400 );
		}

		if ( ! aioseo()->access->hasCapability( 'aioseo_page_general_settings' ) || ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You are not allowed to update the post settings.'
			], 403 );
		}

		$body['id']                  = $postId;
		$body['title']               = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : null;
		$body['description']         = ! empty( $body['description'] ) ? sanitize_text_field( $body['description'] ) : null;
		$body['keywords']            = ! empty( $body['keywords'] ) ? aioseo()->helpers->sanitize( $body['keywords'] ) : null;
		$body['og_title']            = ! empty( $body['og_title'] ) ? sanitize_text_field( $body['og_title'] ) : null;
		$body['og_description']      = ! empty( $body['og_description'] ) ? sanitize_text_field( $body['og_description'] ) : null;
		$body['og_article_section']  = ! empty( $body['og_article_section'] ) ? sanitize_text_field( $body['og_article_section'] ) : null;
		$body['og_article_tags']     = ! empty( $body['og_article_tags'] ) ? aioseo()->helpers->sanitize( $body['og_article_tags'] ) : null;
		$body['twitter_title']       = ! empty( $body['twitter_title'] ) ? sanitize_text_field( $body['twitter_title'] ) : null;
		$body['twitter_description'] = ! empty( $body['twitter_description'] ) ? sanitize_text_field( $body['twitter_description'] ) : null;

		$error = Models\Post::savePost( $postId, $body );

		if ( ! empty( $error ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to update the post data.'
			], 500 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'posts'   => $postId
		], 200 );
	}

	/**
	 * Load post settings from Post screen.
	 *
	 * @since 4.5.5
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function loadPostDetailsColumn( $request ) {
		$body = $request->get_json_params();
		$ids  = ! empty( $body['ids'] ) ? (array) $body['ids'] : [];

		if ( ! $ids ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post IDs are missing.'
			], 400 );
		}

		$posts = [];
		foreach ( $ids as $postId ) {
			// read_post is true for any logged-in user on a published post — WordPress enforces the
			// password separately — so protected content needs an explicit edit_post check.
			if (
				! current_user_can( 'read_post', $postId ) ||
				( post_password_required( $postId ) && ! current_user_can( 'edit_post', $postId ) )
			) {
				$posts[] = [
					'id'                => $postId,
					'titleParsed'       => '',
					'descriptionParsed' => '',
					'headlineScore'     => null
				];

				continue;
			}

			$thePost       = Models\Post::getPost( $postId );
			$hasTruseoData = ! empty( $thePost->truseo );

			$data = [
				'id'                => $postId,
				'titleParsed'       => aioseo()->meta->title->getPostTitle( $postId ),
				'descriptionParsed' => aioseo()->meta->description->getPostDescription( $postId ),
				'headlineScore'     => aioseo()->standalone->headlineAnalyzer->getScoreForPost( $postId, $thePost ),
				'hasTruseoData'     => $hasTruseoData,
			];

			if ( ! $hasTruseoData ) {
				$wpPost = get_post( $postId );
				$data['truseoData'] = self::getPostDataForTruseo( $wpPost, $thePost );
			}

			$posts[] = $data;
		}

		return new \WP_REST_Response( [
			'success' => true,
			'posts'   => $posts
		], 200 );
	}

	/**
	 * Determines the custom analysis type based on post type and context.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $postType The post type.
	 * @param  int    $postId   The post ID.
	 * @return string           The custom analysis type.
	 */
	protected static function getCustomAnalysisType( $postType, $postId ) {
		// No custom type if WooCommerce is not active
		if ( ! aioseo()->helpers->isWooCommerceActive() ) {
			return '';
		}

		// Product pages
		if ( 'product' === $postType ) {
			return 'productPage';
		}

		// Store posts and pages
		if ( 'post' === $postType || 'page' === $postType ) {
			// Check if it's the WooCommerce shop page
			if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) === $postId ) {
				return 'storeBlog';
			}

			return 'storePostsAndPages';
		}

		return '';
	}

	/**
	 * Update post settings from Post screen.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePostDetailsColumn( $request ) {
		$body    = $request->get_json_params();
		$postId  = ! empty( $body['postId'] ) ? intval( $body['postId'] ) : null;
		$isMedia = isset( $body['isMedia'] ) ? true : false;

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post ID is missing.'
			], 400 );
		}

		if ( ! aioseo()->access->hasCapability( 'aioseo_page_general_settings' ) || ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You are not allowed to update the post settings.'
			], 403 );
		}

		$aioseoPost = Models\Post::getPost( $postId );

		if ( $isMedia ) {
			wp_update_post(
				[
					'ID'         => $postId,
					'post_title' => sanitize_text_field( $body['imageTitle'] ),
				]
			);
			update_post_meta( $postId, '_wp_attachment_image_alt', sanitize_text_field( $body['imageAltTag'] ) );
		}

		if ( array_key_exists( 'title', $body ) ) {
			$aioseoPost->title = ! empty( $body['title'] ) ? sanitize_text_field( $body['title'] ) : null;
		}

		if ( array_key_exists( 'description', $body ) ) {
			$aioseoPost->description = ! empty( $body['description'] ) ? sanitize_textarea_field( $body['description'] ) : null;
		}

		$aioseoPost->save();

		if ( ! empty( $aioseoPost->lastError ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to update the post data.'
			], 500 );
		}

		// Trigger the action hook so we can create a revision.
		do_action( 'aioseo_insert_post', $postId );

		return new \WP_REST_Response( [
			'success'     => true,
			'posts'       => $postId,
			'title'       => aioseo()->meta->title->getPostTitle( $postId ),
			'description' => aioseo()->meta->description->getPostDescription( $postId )
		], 200 );
	}

	/**
	 * Update post keyphrases.
	 *
	 * @since   4.0.0
	 * @version 5.0.0.1 Mirrors keywords into the focus_keyword/additional_keywords columns.
	 * @version 5.0.1 Derives the mirrored keyword columns from the sanitized keyphrases.
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePostKeyphrases( $request ) {
		$body   = $request->get_json_params();
		$postId = ! empty( $body['postId'] ) ? intval( $body['postId'] ) : null;

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post ID is missing.'
			], 400 );
		}

		if ( ! aioseo()->access->hasCapability( 'aioseo_page_general_settings' ) || ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You are not allowed to update the post settings.'
			], 403 );
		}

		$thePost = Models\Post::getPost( $postId );

		$thePost->post_id = $postId;
		if ( ! empty( $body['keyphrases'] ) ) {
			$thePost->keyphrases = Models\Post::sanitizeKeyphrases( $body['keyphrases'] );

			// The editor and every keyword feature read the dedicated columns, so a legacy-only
			// write here would be invisible to them. Derived from the sanitized structure, not the
			// request body, so all three columns hold the same keywords.
			$columns                      = Models\Post::getKeywordColumnsFromKeyphrases( $thePost->keyphrases );
			$thePost->focus_keyword       = $columns['focus_keyword'];
			$thePost->additional_keywords = $columns['additional_keywords'];
		}

		if ( ! empty( $body['page_analysis'] ) ) {
			$thePost->page_analysis = Models\Post::sanitizePageAnalysis( $body['page_analysis'] );
		}

		if ( ! empty( $body['seo_score'] ) ) {
			$thePost->seo_score = sanitize_text_field( $body['seo_score'] );
		}

		$thePost->updated = gmdate( self::MYSQL_DATETIME_FORMAT );
		$thePost->save();

		if ( ! empty( $thePost->lastError ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to update the post data.'
			], 500 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'post'    => $postId
		], 200 );
	}

	/**
	 * Updates post TruSEO data from batch scanning.
	 * Streamlined endpoint for batch scan operations.
	 *
	 * @since   5.0.0
	 * @version 5.0.0.1 Mirrors keywords into the legacy keyphrases column.
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function updatePostTruseo( $request ) {
		$body   = $request->get_json_params();
		$postId = ! empty( $request->get_param( 'postId' ) ) ? intval( $request->get_param( 'postId' ) ) : null;

		if ( ! $postId ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Post ID is missing.'
			], 400 );
		}

		if ( ! aioseo()->access->hasCapability( 'aioseo_page_analysis' ) || ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'You are not allowed to update the post settings.'
			], 403 );
		}

		$thePost = Models\Post::getPost( $postId );
		$thePost->post_id = $postId;

		// Update TruSEO fields
		if ( ! empty( $body['truseo'] ) ) {
			$thePost->truseo = wp_json_encode( Models\Post::sanitizeTruseo( $body['truseo'] ) );
		}

		if ( isset( $body['seo_score'] ) ) {
			$thePost->seo_score = sanitize_text_field( $body['seo_score'] );
		}

		if ( array_key_exists( 'focus_keyword', $body ) ) {
			$focusKeyword           = is_string( $body['focus_keyword'] ) ? sanitize_text_field( $body['focus_keyword'] ) : '';
			$thePost->focus_keyword = '' !== $focusKeyword ? $focusKeyword : null;
		}

		// Keyed on presence, not truthiness: the editor sends an empty array when the user removes
		// every additional keyword, and `! empty()` would leave the deleted set in place.
		if ( array_key_exists( 'additional_keywords', $body ) ) {
			$additionalKeywords           = is_array( $body['additional_keywords'] )
				? Models\Post::sanitizeAdditionalKeywords( $body['additional_keywords'] )
				: [];
			$thePost->additional_keywords = ! empty( $additionalKeywords ) ? wp_json_encode( $additionalKeywords ) : null;
		}

		if ( isset( $body['truseo_locale'] ) ) {
			$thePost->truseo_locale = ! empty( $body['truseo_locale'] ) ? sanitize_text_field( $body['truseo_locale'] ) : null;
		}

		// Mirror into the legacy column, which SEO Revisions, Keyword Rank Tracker and the SEO
		// Analysis checkers still read. Guarded so a score-only save can't wipe keywords it never
		// sent.
		if ( array_key_exists( 'focus_keyword', $body ) || array_key_exists( 'additional_keywords', $body ) ) {
			$thePost->keyphrases = Models\Post::getKeyphrasesFromKeywordColumns(
				$thePost->focus_keyword,
				$thePost->additional_keywords,
				$thePost->keyphrases,
				$thePost->truseo
			);
		}

		$thePost->updated = gmdate( self::MYSQL_DATETIME_FORMAT );
		$thePost->save();

		if ( ! empty( $thePost->lastError ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Failed to update the post data.'
			], 500 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'post'    => $thePost
		], 200 );
	}

	/**
	 * Disable the Primary Term education.
	 *
	 * @since 4.3.6
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function disablePrimaryTermEducation( $request ) {
		$args = $request->get_params();

		if ( empty( $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No post ID was provided.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		$thePost = Models\Post::getPost( $args['postId'] );
		$thePost->options->primaryTerm->productEducationDismissed = true;
		$thePost->save();

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Disable the link format education.
	 *
	 * @since 4.2.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function disableLinkFormatEducation( $request ) {
		$args = $request->get_params();

		if ( empty( $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No post ID was provided.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		$thePost = Models\Post::getPost( $args['postId'] );
		$thePost->options->linkFormat->linkAssistantDismissed = true;
		$thePost->save();

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Increment the internal link count.
	 *
	 * @since 4.2.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updateInternalLinkCount( $request ) {
		$args  = $request->get_params();
		$body  = $request->get_json_params();
		$count = ! empty( $body['count'] ) ? intval( $body['count'] ) : null;

		if ( empty( $args['postId'] ) || null === $count ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No post ID or count was provided.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		$thePost = Models\Post::getPost( $args['postId'] );
		$thePost->options->linkFormat->internalLinkCount = $count;
		$thePost->save();

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Persists the per-post TruSEO highlighter on/off preference.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function updateTruSeoHighlighting( $request ) {
		$args = $request->get_params();
		$body = $request->get_json_params();

		if ( empty( $args['postId'] ) || ! isset( $body['enabled'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No post ID or enabled state was provided.'
			], 400 );
		}

		if ( ! current_user_can( 'edit_post', $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		$thePost = Models\Post::getPost( $args['postId'] );
		$thePost->options->truSeo->highlightingEnabled = (bool) $body['enabled'];
		$thePost->save();

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Get the processed content by the given raw content.
	 *
	 * @since   4.5.2
	 * @version 5.0.2 Fixed a PHP warning when the request body has no content key.
	 *
	 * @param  \WP_REST_Request  $request The REST Request.
	 * @return \WP_REST_Response          The response.
	 */
	public static function processContent( $request ) {
		$args = $request->get_params();
		$body = $request->get_json_params();

		if ( empty( $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No post ID was provided.'
			], 400 );
		}

		if ( ! current_user_can( 'read_post', $args['postId'] ) || post_password_required( $args['postId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unauthorized.'
			], 403 );
		}

		// Check if we can process it using a page builder integration.
		$pageBuilder = $body['integration'] ?? aioseo()->helpers->getPostPageBuilderName( $args['postId'] );
		if ( ! empty( $pageBuilder ) ) {
			return new \WP_REST_Response( [
				'success' => true,
				'content' => aioseo()->standalone->pageBuilderIntegrations[ $pageBuilder ]->processContent( $args['postId'], $body['content'] ?? null ),
			], 200 );
		}

		// Check if the content was passed, otherwise get it from the post.
		$content = $body['content'] ?? aioseo()->helpers->getPostContent( $args['postId'] );

		return new \WP_REST_Response( [
			'success' => true,
			'content' => apply_filters( 'the_content', $content ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		], 200 );
	}

	/**
	 * Load post content and metadata for TruSEO batch scanning.
	 * Returns all fields required for TruSEO analysis including focus keywords, content, titles, etc.
	 *
	 * @since 5.0.0
	 *
	 * @param  \WP_Post     $wpPost     The WordPress post.
	 * @param  \AIOSEO\Plugin\Common\Models\Post $aioseoPost The AIOSEO post.
	 * @return array                       The post data for TruSEO analysis.
	 */
	private static function getPostDataForTruseo( $wpPost, $aioseoPost ) {
		$postId = $wpPost->ID;

		// Focus keyword is a plain string in the column; pull from keyphrases as a legacy fallback.
		$focusKeyword         = Models\Post::getFocusKeywordDefaults( $aioseoPost->focus_keyword ?? null );
		$focusKeywordSynonyms = '';

		$keyphrasesFocus = ! empty( $aioseoPost->keyphrases->focus ) ? $aioseoPost->keyphrases->focus : null;
		if ( $keyphrasesFocus && ! empty( $keyphrasesFocus->keyphrase ) ) {
			if ( '' === $focusKeyword ) {
				$focusKeyword = (string) $keyphrasesFocus->keyphrase;
			}
			$focusKeywordSynonyms = ! empty( $keyphrasesFocus->synonyms )
				? (string) $keyphrasesFocus->synonyms
				: '';
		}

		// Get additional keywords data
		$additionalKeywords = $aioseoPost->additional_keywords;
		if ( empty( $additionalKeywords ) ) {
			// Fallback to old keyphrases structure if new fields are empty
			if ( ! empty( $aioseoPost->keyphrases ) ) {
				$keyphrases = $aioseoPost->keyphrases;
				if ( ! empty( $keyphrases->additional ) && is_array( $keyphrases->additional ) ) {
					// Convert old format to new format
					$additionalKeywords = [];
					foreach ( $keyphrases->additional as $keyphrase ) {
						if ( ! empty( $keyphrase->keyphrase ) ) {
							$additionalKeywords[] = [
								'word'     => $keyphrase->keyphrase,
								'synonyms' => ''
							];
						}
					}
				}
			}
		}

		// Ensure we have a valid structure
		if ( empty( $additionalKeywords ) ) {
			$additionalKeywords = [];
		}

		// Determine custom analysis type based on context
		$customAnalysisType = self::getCustomAnalysisType( $wpPost->post_type, $postId );

		return [
			'postType'             => $wpPost->post_type,
			'content'              => self::getAnalysisContent( $wpPost ),
			'excerpt'              => $wpPost->post_excerpt,
			'wpTitle'              => get_the_title( $postId ),  // WordPress title (fallback)
			// Analyze the fully-rendered meta title/description (smart tags parsed, separator and
			// site title applied, content-based fallback for an empty description) — the same values
			// the post editor analyzes — so the list score matches the editor score. Sending the raw
			// title template or an empty description here would score differently from the editor.
			'aioseoTitle'          => aioseo()->meta->title->getPostTitle( $postId ),
			'description'          => aioseo()->meta->description->getPostDescription( $postId ),
			// Raw visible post title — SingleH1Assessment counts it as the page's H1.
			'postTitle'            => get_the_title( $postId ),
			'slug'                 => $wpPost->post_name,
			'permalink'            => get_permalink( $postId ),
			'focusKeyword'         => $focusKeyword,
			'focusKeywordSynonyms' => $focusKeywordSynonyms,
			'additionalKeywords'   => $additionalKeywords,
			'cornerstone'          => ! empty( $aioseoPost->pillar_content ) ? (bool) $aioseoPost->pillar_content : false,
			'customAnalysisType'   => $customAnalysisType,
			'locale'               => get_locale()
		];
	}
}