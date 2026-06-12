<?php
namespace AIOSEO\Plugin\Common\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Service for post-level SEO operations.
 *
 * Single source of truth for the read/update/list operations on a post's SEO data.
 * Used by the Abilities API, the AIOSEO REST controllers (opportunistically), and
 * any internal code that needs the same logic.
 *
 * @internal Not a public extension surface. Reserved for AIOSEO internals.
 *
 * @since 4.9.8
 */
class PostSeoService {
	/**
	 * Returns the SEO snapshot for a post.
	 *
	 * @since 4.9.8
	 *
	 * @param  int   $postId  The post ID.
	 * @param  array $include Optional list of additional sections (currently: "analysis").
	 * @return array|\WP_Error
	 */
	public function getSeoData( $postId, $include = [] ) {
		$postId = (int) $postId;
		if ( ! aioseo()->access->hasAccess( 'aioseo_page_general_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to view this post.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		// Resolve the WordPress post first: a missing id should report not_found rather than a
		// misleading permission error ( current_user_can( 'edit_post', <missing id> ) is always false ).
		// The per-post edit_post gate below still protects existing posts the caller cannot edit (IDOR).
		if ( ! get_post( $postId ) ) {
			return new \WP_Error( 'not_found', __( 'Post not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to view this post.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$post = Models\Post::getPost( $postId );
		if ( ! $post->exists() ) {
			return new \WP_Error( 'not_found', __( 'Post not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		$response = $this->serialize( $post );

		$include = is_array( $include ) ? $include : [];
		if ( in_array( 'analysis', $include, true ) ) {
			$response['analysis'] = $post->page_analysis ?: new \stdClass();
		}

		return $response;
	}

	/**
	 * Updates the SEO data for a post. Only fields present in $fields are changed.
	 *
	 * @since 4.9.8
	 *
	 * @param  int   $postId The post ID.
	 * @param  array $fields The fields to update. See PostSeoService docs for the accepted shape.
	 * @return array|\WP_Error
	 */
	public function updateSeoData( $postId, $fields ) {
		$postId = (int) $postId;
		if ( ! aioseo()->access->hasAccess( 'aioseo_page_general_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to edit this post.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		// Resolve the WordPress post first: a missing id should report not_found rather than a
		// misleading permission error ( current_user_can( 'edit_post', <missing id> ) is always false ).
		// The per-post edit_post gate below still protects existing posts the caller cannot edit (IDOR).
		if ( ! get_post( $postId ) ) {
			return new \WP_Error( 'not_found', __( 'Post not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to edit this post.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$post = Models\Post::getPost( $postId );
		if ( ! $post->exists() ) {
			return new \WP_Error( 'not_found', __( 'Post not found.', 'all-in-one-seo-pack' ), [ 'status' => 404 ] );
		}

		$fields = is_array( $fields ) ? $fields : [];

		// Build only the keys we want to update. Post::savePost is patch-aware as of 4.9.8,
		// so we no longer need to pre-load the full save shape — missing keys preserve their values.
		$data = [];
		$this->applyBasicFields( $fields, $data );
		$this->applyKeyphraseFields( $fields, $data, $post );
		$this->applyRobotsFields( $fields, $data );
		$this->applySocialFields( $fields, $data );

		Models\Post::savePost( $postId, $data );

		$lastError = aioseo()->core->db->lastError();
		if ( ! empty( $lastError ) ) {
			return new \WP_Error(
				'save_failed',
				__( 'Failed to save post SEO data.', 'all-in-one-seo-pack' ),
				[ 'status' => 500 ]
			);
		}

		return [
			'updated' => true,
			'post'    => $this->serialize( Models\Post::getPost( $postId ) )
		];
	}

	/**
	 * Returns posts that are missing one or more SEO fields, projected to a stable agent shape.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $filters Accepted: missing_fields (array of "title"|"description"|"focus_keyphrase"),
	 *                        post_type (array), status (array), limit (int 1-100), offset (int >= 0).
	 * @return array|\WP_Error
	 */
	public function listMissingSeo( $filters = [] ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_page_general_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to list posts.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$filters       = is_array( $filters ) ? $filters : [];
		$missingFields = isset( $filters['missing_fields'] ) && is_array( $filters['missing_fields'] )
			? array_values( array_intersect( [ 'title', 'description', 'focus_keyphrase' ], $filters['missing_fields'] ) )
			: [ 'focus_keyphrase' ];
		if ( empty( $missingFields ) ) {
			$missingFields = [ 'focus_keyphrase' ];
		}

		$postTypes = $this->filterEditablePostTypes( isset( $filters['post_type'] ) ? $filters['post_type'] : null );
		if ( empty( $postTypes ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to list posts.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$statuses = isset( $filters['status'] ) && is_array( $filters['status'] ) && ! empty( $filters['status'] )
			? array_map( 'sanitize_key', $filters['status'] )
			: [ 'publish' ];
		$limit     = isset( $filters['limit'] ) ? min( max( 1, (int) $filters['limit'] ), 100 ) : 20;
		$offset    = isset( $filters['offset'] ) ? max( 0, (int) $filters['offset'] ) : 0;

		$missingClauses = [];
		if ( in_array( 'title', $missingFields, true ) ) {
			$missingClauses[] = "( ap.title IS NULL OR ap.title = '' )";
		}
		if ( in_array( 'description', $missingFields, true ) ) {
			$missingClauses[] = "( ap.description IS NULL OR ap.description = '' )";
		}
		if ( in_array( 'focus_keyphrase', $missingFields, true ) ) {
			$missingClauses[] = "( ap.keyphrases IS NULL OR ap.keyphrases = '' OR ap.keyphrases = '[]' OR ap.keyphrases NOT LIKE '%keyphrase%' )";
		}

		// Build the filtered query as a closure so it can run twice: once for the full-match count
		// (the pagination total) and once for the page of rows. The db builder is a shared instance
		// that fully resets on start(), and it does not survive being cloned once a join is applied,
		// so we rebuild rather than clone.
		$buildQuery = function() use ( $postTypes, $statuses, $missingClauses ) {
			$query = aioseo()->core->db
				->start( 'aioseo_posts as ap' )
				->select( 'ap.post_id, ap.title, ap.description, ap.keyphrases, ap.seo_score, p.post_title, p.post_type, p.post_status' )
				->join( 'posts as p', 'p.ID = ap.post_id' )
				->whereIn( 'p.post_type', $postTypes )
				->whereIn( 'p.post_status', $statuses );

			// Object-level scoping: a user who cannot edit others' posts must only see their own,
			// otherwise the list leaks other authors' (incl. unpublished) titles/permalinks/scores.
			// Mirrors the per-post `edit_post` gate enforced on the single-post abilities.
			if ( ! current_user_can( 'edit_others_posts' ) ) {
				$query->where( 'p.post_author', get_current_user_id() );
			}

			if ( ! empty( $missingClauses ) ) {
				$query->whereRaw( '(' . implode( ' OR ', $missingClauses ) . ')' );
			}

			return $query;
		};

		$total = (int) $buildQuery()->count();
		$rows  = $buildQuery()->orderBy( 'p.post_modified DESC' )->limit( $limit, $offset )->run()->result();

		$posts = [];
		foreach ( (array) $rows as $row ) {
			$missing = [];
			if ( in_array( 'title', $missingFields, true ) && empty( $row->title ) ) {
				$missing[] = 'title';
			}
			if ( in_array( 'description', $missingFields, true ) && empty( $row->description ) ) {
				$missing[] = 'description';
			}
			if ( in_array( 'focus_keyphrase', $missingFields, true ) ) {
				$kp = json_decode( (string) $row->keyphrases, true );
				if ( empty( $kp['focus']['keyphrase'] ) ) {
					$missing[] = 'focus_keyphrase';
				}
			}

			$posts[] = [
				'id'             => (int) $row->post_id,
				'post_title'     => (string) $row->post_title,
				'post_type'      => (string) $row->post_type,
				'status'         => (string) $row->post_status,
				'permalink'      => get_permalink( (int) $row->post_id ) ?: null,
				'seo_score'      => (int) $row->seo_score,
				'missing_fields' => $missing
			];
		}

		return [
			'posts' => $posts,
			'total' => $total
		];
	}

	/**
	 * Returns posts ordered by TruSEO score, optionally bounded by min/max thresholds.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $filters Accepted: min_score (int 0-100), max_score (int 0-100), order ("asc"|"desc"),
	 *                        post_type (array), status (array), limit (int 1-100), offset (int >= 0).
	 * @return array|\WP_Error
	 */
	public function listByTruseoScore( $filters = [] ) {
		if ( ! aioseo()->access->hasAccess( 'aioseo_page_general_settings' ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to list posts.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$filters  = is_array( $filters ) ? $filters : [];
		$minScore = isset( $filters['min_score'] ) ? max( 0, min( 100, (int) $filters['min_score'] ) ) : 0;
		$maxScore = isset( $filters['max_score'] ) ? max( 0, min( 100, (int) $filters['max_score'] ) ) : 100;
		$order    = isset( $filters['order'] ) && 'desc' === strtolower( (string) $filters['order'] ) ? 'DESC' : 'ASC';

		$postTypes = $this->filterEditablePostTypes( isset( $filters['post_type'] ) ? $filters['post_type'] : null );
		if ( empty( $postTypes ) ) {
			return new \WP_Error( 'forbidden', __( 'You do not have permission to list posts.', 'all-in-one-seo-pack' ), [ 'status' => 403 ] );
		}

		$statuses = isset( $filters['status'] ) && is_array( $filters['status'] ) && ! empty( $filters['status'] )
			? array_map( 'sanitize_key', $filters['status'] )
			: [ 'publish' ];
		$limit     = isset( $filters['limit'] ) ? min( max( 1, (int) $filters['limit'] ), 100 ) : 20;
		$offset    = isset( $filters['offset'] ) ? max( 0, (int) $filters['offset'] ) : 0;

		// Build the filtered query as a closure so it can run twice: once for the full-match count
		// (the pagination total) and once for the page of rows. The db builder is a shared instance
		// that fully resets on start(), and it does not survive being cloned once a join is applied,
		// so we rebuild rather than clone.
		$buildQuery = function() use ( $postTypes, $statuses, $minScore, $maxScore ) {
			$query = aioseo()->core->db
				->start( 'aioseo_posts as ap' )
				->select( 'ap.post_id, ap.seo_score, p.post_title, p.post_type, p.post_status' )
				->join( 'posts as p', 'p.ID = ap.post_id' )
				->whereIn( 'p.post_type', $postTypes )
				->whereIn( 'p.post_status', $statuses )
				->where( 'ap.seo_score >=', $minScore )
				->where( 'ap.seo_score <=', $maxScore );

			// Object-level scoping: restrict to the caller's own posts when they cannot edit others'.
			if ( ! current_user_can( 'edit_others_posts' ) ) {
				$query->where( 'p.post_author', get_current_user_id() );
			}

			return $query;
		};

		$total = (int) $buildQuery()->count();
		$rows  = $buildQuery()
			->orderBy( 'ap.seo_score ' . $order )
			->limit( $limit, $offset )
			->run()
			->result();

		$posts = [];
		foreach ( (array) $rows as $row ) {
			$posts[] = [
				'id'         => (int) $row->post_id,
				'post_title' => (string) $row->post_title,
				'post_type'  => (string) $row->post_type,
				'status'     => (string) $row->post_status,
				'permalink'  => get_permalink( (int) $row->post_id ) ?: null,
				'seo_score'  => (int) $row->seo_score
			];
		}

		return [
			'posts' => $posts,
			'total' => $total
		];
	}

	/**
	 * Returns the post types the current user is allowed to edit, intersected with the requested set
	 * (or all public AIOSEO-tracked post types when nothing is requested). Each type is checked against
	 * its own `edit_posts` cap via `get_post_type_object()->cap->edit_posts`, so custom post types
	 * that define their own caps (e.g. WooCommerce `edit_products`) are honoured.
	 *
	 * @since 4.9.8
	 *
	 * @param  array|null $requested The post types the caller asked for, or null for "all".
	 * @return string[]              The editable post types — empty array means no access.
	 */
	protected function filterEditablePostTypes( $requested ) {
		$publicTypes = array_values( aioseo()->helpers->getPublicPostTypes( true ) );
		$candidate   = is_array( $requested ) && ! empty( $requested )
			? array_values( array_intersect( array_map( 'sanitize_key', $requested ), $publicTypes ) )
			: $publicTypes;

		$editable = [];
		foreach ( $candidate as $postType ) {
			$pto = get_post_type_object( $postType );
			$cap = ( $pto && isset( $pto->cap->edit_posts ) ) ? $pto->cap->edit_posts : 'edit_posts';
			if ( current_user_can( $cap ) ) {
				$editable[] = $postType;
			}
		}

		return $editable;
	}

	/**
	 * Projects a Post model into the stable agent-facing snapshot shape.
	 *
	 * @since 4.9.8
	 *
	 * @param  Models\Post $post The Post model.
	 * @return array
	 */
	protected function serialize( $post ) {
		$keyphrases     = $this->jsonFieldToArray( $post->keyphrases );
		$focusKeyphrase = isset( $keyphrases['focus']['keyphrase'] ) ? (string) $keyphrases['focus']['keyphrase'] : null;
		$additional     = [];
		if ( isset( $keyphrases['additional'] ) && is_array( $keyphrases['additional'] ) ) {
			foreach ( $keyphrases['additional'] as $entry ) {
				if ( is_array( $entry ) && isset( $entry['keyphrase'] ) ) {
					$additional[] = (string) $entry['keyphrase'];
				}
			}
		}

		$schema     = $this->jsonFieldToArray( $post->schema );
		$schemaType = null;
		if ( ! empty( $schema['default']['data']['Article']['articleType'] ) ) {
			$schemaType = (string) $schema['default']['data']['Article']['articleType'];
		}

		return [
			'title'                 => $post->title,
			'description'           => $post->description,
			'canonical_url'         => $post->canonical_url,
			'focus_keyphrase'       => $focusKeyphrase,
			'additional_keyphrases' => $additional,
			'seo_score'             => (int) $post->seo_score,
			'pillar_content'        => (bool) $post->pillar_content,
			'robots'                => [
				'use_default'  => (bool) $post->robots_default,
				'noindex'      => (bool) $post->robots_noindex,
				'nofollow'     => (bool) $post->robots_nofollow,
				'noarchive'    => (bool) $post->robots_noarchive,
				'nosnippet'    => (bool) $post->robots_nosnippet,
				'noimageindex' => (bool) $post->robots_noimageindex,
				'notranslate'  => (bool) $post->robots_notranslate,
				'noodp'        => (bool) $post->robots_noodp
			],
			'social'                => [
				'og_title'            => $post->og_title,
				'og_description'      => $post->og_description,
				'twitter_title'       => $post->twitter_title,
				'twitter_description' => $post->twitter_description
			],
			'schema_type'           => $schemaType
		];
	}

	/**
	 * Overlays the basic post-level SEO fields onto the save-shape array.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $fields The input fields.
	 * @param  array $data   The save-shape array; mutated in place.
	 * @return void
	 */
	protected function applyBasicFields( $fields, &$data ) {
		if ( array_key_exists( 'title', $fields ) ) {
			$data['title'] = null === $fields['title'] ? null : sanitize_text_field( (string) $fields['title'] );
		}
		if ( array_key_exists( 'description', $fields ) ) {
			$data['description'] = null === $fields['description'] ? null : sanitize_text_field( (string) $fields['description'] );
		}
		if ( array_key_exists( 'canonical_url', $fields ) ) {
			$data['canonicalUrl'] = null === $fields['canonical_url'] ? null : esc_url_raw( (string) $fields['canonical_url'] );
		}
		if ( array_key_exists( 'pillar_content', $fields ) ) {
			$data['pillar_content'] = (bool) $fields['pillar_content'];
		}
	}

	/**
	 * Overlays focus keyphrase and additional keyphrases onto the save-shape array.
	 *
	 * The keyphrases field is a nested structure (focus + additional list) that the caller
	 * may patch one half of at a time. We have to merge with the existing model state so that
	 * passing only `focus_keyphrase` doesn't wipe `additional`, and vice versa.
	 *
	 * @since 4.9.8
	 *
	 * @param  array        $fields The input fields.
	 * @param  array        $data   The save payload; mutated in place.
	 * @param  Models\Post  $post   The current Post model — source of existing keyphrases.
	 * @return void
	 */
	protected function applyKeyphraseFields( $fields, &$data, $post ) {
		$hasFocus      = array_key_exists( 'focus_keyphrase', $fields );
		$hasAdditional = array_key_exists( 'additional_keyphrases', $fields );
		if ( ! $hasFocus && ! $hasAdditional ) {
			return;
		}

		$existing = $this->jsonFieldToArray( $post->keyphrases );

		if ( $hasFocus ) {
			$existing['focus'] = isset( $existing['focus'] ) && is_array( $existing['focus'] ) ? $existing['focus'] : [];
			$existing['focus']['keyphrase'] = null === $fields['focus_keyphrase']
				? ''
				: sanitize_text_field( (string) $fields['focus_keyphrase'] );
		}

		if ( $hasAdditional ) {
			$additional = $fields['additional_keyphrases'];

			// WP REST schema validation accepts a comma-separated string for `type: array`
			// but does not coerce it, so the callback can receive a plain string here. Apply
			// the coercion (comma-split, not wp_parse_list — keyphrases may contain spaces)
			// instead of silently dropping the value.
			if ( is_string( $additional ) ) {
				$additional = array_values( array_filter( array_map( 'trim', explode( ',', $additional ) ) ) );
			}

			if ( is_array( $additional ) ) {
				$existing['additional'] = array_map( function( $kp ) {
					return [ 'keyphrase' => sanitize_text_field( (string) $kp ) ];
				}, $additional );
			}
		}

		$data['keyphrases'] = $existing;
	}

	/**
	 * Overlays the robots flags from the input onto the save-shape array.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $fields The input fields.
	 * @param  array $data   The save-shape array; mutated in place.
	 * @return void
	 */
	protected function applyRobotsFields( $fields, &$data ) {
		if ( ! isset( $fields['robots'] ) || ! is_array( $fields['robots'] ) ) {
			return;
		}

		$robotsMap = [
			'use_default'  => 'default',
			'noindex'      => 'noindex',
			'nofollow'     => 'nofollow',
			'noarchive'    => 'noarchive',
			'nosnippet'    => 'nosnippet',
			'noimageindex' => 'noimageindex',
			'notranslate'  => 'notranslate',
			'noodp'        => 'noodp'
		];

		foreach ( $robotsMap as $inputKey => $saveKey ) {
			if ( array_key_exists( $inputKey, $fields['robots'] ) ) {
				$data[ $saveKey ] = (bool) $fields['robots'][ $inputKey ];
			}
		}
	}

	/**
	 * Overlays the social meta fields (OG title/description, Twitter title/description) onto the save-shape array.
	 *
	 * @since 4.9.8
	 *
	 * @param  array $fields The input fields.
	 * @param  array $data   The save-shape array; mutated in place.
	 * @return void
	 */
	protected function applySocialFields( $fields, &$data ) {
		if ( ! isset( $fields['social'] ) || ! is_array( $fields['social'] ) ) {
			return;
		}

		foreach ( [ 'og_title', 'og_description', 'twitter_title', 'twitter_description' ] as $socialField ) {
			if ( array_key_exists( $socialField, $fields['social'] ) ) {
				$value                  = $fields['social'][ $socialField ];
				$data[ $socialField ]   = null === $value ? null : sanitize_text_field( (string) $value );
			}
		}
	}

	/**
	 * Normalises a Model jsonField (which the base Model class decodes to stdClass) into an
	 * associative array suitable for array_key/array-bracket access throughout the service.
	 *
	 * @since 4.9.8
	 *
	 * @param  mixed $value The model field value (stdClass, array, or null).
	 * @return array
	 */
	protected function jsonFieldToArray( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}
		if ( is_object( $value ) ) {
			return json_decode( wp_json_encode( $value ), true ) ?: [];
		}
		if ( is_string( $value ) && '' !== $value ) {
			return json_decode( $value, true ) ?: [];
		}

		return [];
	}
}