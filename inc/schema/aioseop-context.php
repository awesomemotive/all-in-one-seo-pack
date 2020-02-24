<?php

/**
 * Website Context Handler.
 *
 * Handles data from multiple WordPress classes which contain content, structure, and properties.
 *
 * Multiton (Multi-Singleton)
 *
 */
class AIOSEOP_Context {

	/**
	 * @var array $type {
	 *     @type array $key {
	 *         @type AIOSEOP_Context
	 *     }
	 * }
	 */
	protected static $instances = array();

	/**
	 * @since 3.4.0
	 *
	 * @var string
	 */
	public $context_type = '';

	/**
	 * Unique key for WP Objects.
	 *
	 * Could be a numberic ID or a string Slug.
	 *
	 * @since 3.4.0
	 *
	 * @var string
	 */
	public $context_key = '';

	/**
	 * WP Class Properties
	 *
	 * Stores essentual properties to query by or potentually reduce querying.
	 * 
	 * These properties can also be used with `$context` param to query by.
	 *
	 * @var array {
	 *     @type string       $taxonomy    (Optional) Terms limited to those matching `taxonomy`.
	 *                                     Used with: WP_Terms.
	 *     @type array|string $object_type Name(s) of the post type(s) the taxonomy object is registered for.
	 *                                     Used with: WP_Taxonomy.
	 *     @type string       $user_login  Username.
	 *                                     Used with: WP_User.
	 *     @type int          $site_id     Site ID.
	 *                                     Used with: WP_User.
	 * }
	 */
	public $wp_props = array();


	/**
	 * @since 3.4.0
	 *
	 * @param string|array|AIOSEOP_Context|WP_Site|WP_Post|WP_Post_Type|WP_Taxonomy|WP_Term|WP_User $context
	 * @return AIOSEOP_Context
	 */
	public static function get_instance( $context = '' ) {
		$type   = self::get_context_type( $context );
		$key    = self::get_context_key( $context, $type );
		$props  = self::get_wp_props( $context, $type, $key );

		if ( ! isset( self::$instances[ $type ] ) ) {
			self::$instances[ $type ] = array();
		}
		if ( ! isset( self::$instances[ $type ][ $key ] ) ) {
			if ( ! $context instanceof $type ) {
				$context = self::get_object( $type, $key, $props );
			}

			self::$instances[ $type ][ $key ] = new self( $context );
		}

		return self::$instances[ $type ][ $key ];
	}

	/**
	 * AIOSEOP_Context constructor.
	 *
	 * @since 3.4.0
	 *
	 * @param string $context
	 */
	protected function __construct( $context = '' ) {
		$type               = self::get_context_type( $context );
		$this->context_type = $type;
		$key                = self::get_context_key( $context, $type );
		$this->context_key  = $key;
		$props              = self::get_wp_props( $context, $type, $key );
		$this->wp_props     = $props;
	}

	/**
	 * Get current is_*() state.
	 *
	 * @since 3.4.0
	 *
	 * @param string|object $context
	 * @return string
	 */
	public static function get_is() {
		$state_is = '';
		if ( is_front_page() || is_home() ) {
			if ( is_front_page() ) {
				$state_is = 'front_page';
			} else {
				$state_is = 'home'; // Static front page.
			}
		} elseif ( is_archive() ) {
			if ( is_author() ) {
				$state_is = 'author_archive';
			} elseif ( is_post_type_archive() ) {
				$state_is = 'post_type_archive';
			} elseif ( is_tax() || is_category() || is_tag() ) {
				$state_is = 'taxonomy_term_archive';
			} elseif ( is_date() ) {
				$state_is = 'date_archive';
				if ( is_year() ) {
					$state_is = 'year_date_archive';
				} elseif ( is_month() ) {
					$state_is = 'month_date_archive';
				} elseif ( is_day() ) {
					$state_is = 'day_date_archive';
				}
			}
		} elseif ( is_singular() || is_single() ) {
			$post = get_post();

			$state_is = 'single_post';
			if ( is_post_type_hierarchical( $post->post_type ) ) {
				$state_is = 'single_page';
			} elseif ( is_attachment() ) {
				$state_is = 'single_attachment';
			}
		} elseif ( is_search() ) {
			$state_is = 'search';
		} elseif ( is_attachment() ) {
			$state_is = 'attachment';
		} elseif ( is_404() ) {
			$state_is = '404';
		}

		return $state_is;
	}

	/**
	 * Get Object Type of Context
	 *
	 * @since 3.4.0
	 *
	 * @param string $context
	 * @return string
	 */
	public static function get_context_type( $context = '' ) {
		if ( is_array( $context ) && isset( $context['context_type'] ) ) {
			if ( 'WP_Site' === $context['context_type'] && ! class_exists( 'WP_Site' ) ) {
				$context['context_type'] = 'var_site';
			}
			return $context['context_type'];
		} elseif ( $context instanceof AIOSEOP_Context || ! empty ( $context->context_type ) ) {
			if ( 'WP_Site' === $context->context_type && ! class_exists( 'WP_Site' ) ) {
				$context->context_type = 'var_site';
			}
			return $context->context_type;
		}

		$test01 = get_queried_object();
		$obj_type = '';
		if ( $context instanceof WP_Network ) {
			$obj_type = 'WP_Site';
		} elseif ( $context instanceof WP_Site ) {
			$obj_type = 'WP_Site';
		} elseif ( $context instanceof WP_Post_Type ) {
			$obj_type = 'WP_Post_Type';
		} elseif ( $context instanceof WP_Taxonomy ) {
			$obj_type = 'WP_Taxonomy';
		} elseif ( $context instanceof WP_Term ) {
			$obj_type = 'WP_Term';
		} elseif ( $context instanceof WP_Post ) {
			$obj_type = 'WP_Post';
		} elseif ( $context instanceof WP_User ) {
			$obj_type = 'WP_User';
		}

		// If context isn't a WP object, or is empty, then set by current is_*() condition.
		if ( empty( $obj_type ) ) {
			$current_is = self::get_is();

			switch ( $current_is ) {
				case 'front_page':
					if ( is_multisite() ) {
						$obj_type = 'WP_Site';
					} else {
						$obj_type = 'var_site';
					}
					break;

				case 'author_archive':
					$obj_type = 'WP_User';
					break;

				case 'post_type_archive':
					$obj_type = 'WP_Post_Type';
					break;

				case 'taxonomy_term_archive':
					$obj_type = 'WP_Term';
					break;

				case 'home':
				case 'single_page':
				case 'single_post':
				case 'single_attachment':
				case 'attachment':
					$obj_type = 'WP_Post';
					break;

				case 'date_archive':
				case 'year_date_archive':
				case 'month_date_archive':
				case 'day_date_archive':
					$obj_type = 'var_date';
					break;

				case 'search':
					$obj_type = 'var_search';
					break;

				case '404':
					// TODO Find current object.
					break;
			}
		}

		return $obj_type;
	}

	/**
	 * Get (WP) Object ID
	 *
	 * Searches for an object's ID, if there is not an ID then the current ID available is fetched.
	 * This would also contain majority of the query operations for (individual) objects since this
	 * is a Unique Key for a given class type; wp_props is also used to refine a query.
	 *
	 * @since 3.4.0
	 *
	 * @param        $context
	 * @param string $type
	 * @return int
	 */
	public static function get_context_key( $context, $type = '' ) {
		if ( is_array( $context ) && isset( $context['context_key'] ) ) {
			return $context['context_key'];
		} elseif ( $context instanceof AIOSEOP_Context || ! empty ( $context->context_key ) ) {
			return $context->context_key;
		}

		$key = 0;
		if ( empty( $type ) ) {
			$type = self::get_context_type( $context );
		}
		switch ( $type ) {
			case 'var_site':
				$key = 0;
				break;

			case 'WP_Site':
				if ( $context instanceof WP_Site ) {
					$key = $context->blog_id;
				} else {
					$key = get_current_blog_id();
				}
				break;

			case 'WP_Post':
				if ( ! $context instanceof WP_Post ) {
					global $post;
					$context = $post;
				}
				$key = $context->ID;
				break;

			case 'WP_Taxonomy':
				if ( ! $context instanceof WP_Taxonomy ) {
					get_queried_object();
				}
				$key = $context->name;
				break;

			case 'WP_Term':
				if ( ! $context instanceof WP_Term) {
					$context = get_queried_object();
				}
				$key = $context->term_id;
				break;

			case 'WP_User':
				if ( $context instanceof WP_User ) {
					$key = $context->ID;
					break;
				}

				if ( is_array( $context ) && is_array( $context['wp_props'] ) ) {
					if ( ! empty( $context['wp_props']['user_nicename'] ) ) {
						$context = get_user_by( 'slug', $context['wp_props']['user_nicename'] );
					} elseif ( ! empty( $context['wp_props']['user_email'] ) ) {
						$context = get_user_by( 'email', $context['wp_props']['user_email'] );
					} elseif ( ! empty( $context['wp_props']['user_login'] ) ) {
						$context = get_user_by( 'login', $context['wp_props']['user_login'] );
					}
				}

				if ( $context instanceof WP_User ) {
					$key = $context->ID;
				} else {
					// Current author/user page on frontend.
					$key = get_the_author_meta( 'ID' );
				}
				break;

			default:
				// Do stuff.
		}

		return $key;
	}

	/**
	 * Get (Required/Requested) WP Object Fields
	 * 
	 * @since 3.4.0
	 *
	 * @param mixed  $context
	 * @param string $type
	 * @param string $key
	 * @return array|mixed
	 */
	public static function get_wp_props( $context, $type = '', $key = '' ) {
		$wp_props = array();
		if ( empty( $type ) ) {
			$type = self::get_context_type( $context );
		}
		if ( empty( $key ) && 0 !== $key ) {
			$key = self::get_context_key( $context, $type );
		}

		if ( is_array( $context ) && isset( $context['wp_props'] ) ) {
			$wp_props = $context['wp_props'];
		} elseif ( $context instanceof AIOSEOP_Context || ! empty ( $context->wp_props ) ) {
			$wp_props = $context->wp_props;
		}

		$object = new stdClass();
		switch ( $type ) {
			case 'WP_Taxonomy':
				$object = self::get_object( $type, $key, $wp_props );
				$wp_props['object_type'] = $object->object_type;
				break;
			case 'WP_Term':
//				$object = self::get_object( $type, $key, $wp_props );
//				$wp_props['taxonomy'] = $object->taxonomy;
				break;
			case 'WP_User':
				$object = self::get_object( $type, $key, $wp_props );
//				$wp_props['user_login'] = $object->user_login;
				$wp_props['site_id']    = $object->site_id;
				break;
		}
		
		// Also get only the object properties that match in $context['wp_props'] | $context->wp_props.
		foreach ( $wp_props as $key => $value ) {
			if ( isset( $object->$key ) ) {
				$wp_props[ $key ] = $object->$key;
			}
		}

		return $wp_props;
	}

	/**
	 * @since 3.4.0
	 *
	 * @param string $type WP object type.
	 * @param string $key  Integer or slug.
	 * @param array  $args
	 * @return false|WP_Site|WP_Post|WP_Post_Type|WP_Taxonomy|WP_Term|WP_User
	 */
	public static function get_object( $type, $key, $args = array() ) {
		$object = false;
		switch ( $type ) {
			case 'var_site':
				$object = array(
					'context_type' => $type,
					'context_key'  => $key,
				);
				break;
			case 'WP_Site':
			case 'WP_Post':
				$object = $type::get_instance( $key );
				break;
			case 'WP_Taxonomy':
				$object_type = isset( $args['object_type'] ) ? $args['object_type'] : 'post';
				$object = new WP_Taxonomy( $key, $object_type );
				break;
			case 'WP_Term':
				$taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : null;
				$object   = WP_Term::get_instance( $key, $taxonomy );
				break;
			case 'WP_User':
				$name    = isset( $args['user_login'] ) ? $args['user_login'] : '';
				$site_id = isset( $args['site_id'] )    ? $args['site_id']    : '';
				$object  = new WP_User( $key, $name, $site_id );
				break;
		}

		return $object;
	}

	/**
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_slug() {
		$slug = '';
		$wp_obj = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
		switch ( $this->context_type ) {
			case 'var_site':
				break;
			case 'WP_Post':
				$slug = $wp_obj->post_name;
				break;
			case 'WP_Taxonomy':
				$slug = $wp_obj->name;
				break;
			case 'WP_Term':
				$slug = $wp_obj->slug;
				break;
			case 'WP_User':
				$slug = $wp_obj->user_login;
				break;
		}

		return $slug;
	}
	
	public function get_nicename() {}

	/**
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_display_name() {
		$display_name = '';
		switch ( $this->context_type ) {
			case 'var_site':
				$display_name = get_bloginfo( 'name' );
				break;

			case 'WP_Post':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				$display_name = $wp_obj->post_title;
				break;

			case 'WP_Term':
				$wp_obj = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
				$display_name = $wp_obj->name;
				break;

			case 'WP_User':
				$wp_obj = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
				$display_name = $wp_obj->display_name;
				break;

			case 'var_search':
				$display_name = sprintf( __( 'Search results for \'%s\'', 'all-in-one-seo-pack' ), esc_html( get_search_query() ) );
				break;

			case 'var_date_year':
				$display_name = sprintf( __( 'Year: %s', 'all-in-one-seo-pack' ), get_the_date( 'Y' ) );
				break;

			case 'var_date_month':
				$display_name = sprintf( __( 'Day: %s', 'all-in-one-seo-pack' ), get_the_date( 'F Y' ) );
				break;

			case 'var_date_day':
			case 'var_date':
				$display_name = sprintf( __( 'Day: %s', 'all-in-one-seo-pack' ), get_the_date( 'F j, Y' ) );
				break;
		}

		return $display_name;
	}

	/**
	 * Get URL (Page)
	 *
	 * Uses a static variable for performance faulty operations; only use with heavy operations.
	 *
	 *
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_url() {
		static $s_url;
		if ( is_null( $s_url ) ) {
			$s_url = array();
		}
		if ( ! isset( $s_url[ $this->context_type ] ) || ! is_array( $s_url[ $this->context_type ] ) ) {
			$s_url[ $this->context_type ] = array();
		}
		if ( ! empty( $s_url[ $this->context_type ][ $this->context_key ] ) ) {
			return $s_url[ $this->context_type ][ $this->context_key ];
		}

		$url = '';
		switch ( $this->context_type ) {
			case 'var_site':
				$url = home_url();
				break;

			case 'WP_Site':
				// Do stuff.
				break;

			case 'WP_Post':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );

				if ( 'attachment' === $wp_obj->post_type ) {
					// Source URL.
					// May need to check setting for attachment redirect.
					// Use $this->get_images() to get attachment link.
					//$url = wp_get_attachment_url( $wp_obj->ID );
					// (Attachment) Post URL.
					$url = get_permalink( $wp_obj );
				} else {
					$url = wp_get_canonical_url( $wp_obj );
				}

				if ( false === $url ) {
					$url = '';
				}

				$s_url[ $this->context_type ][ $this->context_key ] = $url;
				break;

			case 'WP_Taxonomy':
				// Does not exist.
				break;

			case 'WP_Term':
				$taxonomy = isset( $this->wp_props['taxonomy'] ) ? $this->wp_props['taxonomy'] : '';
				$url      = get_term_link( $this->context_key, $taxonomy );

				$s_url[ $this->context_type ][ $this->context_key ] = $url;
				break;
			case 'WP_User':
				$url = get_author_posts_url( $this->context_key );
				break;

			case 'var_search':
				$url = get_search_link();
				break;

			case 'var_date_year':
				$url = get_year_link( false );
				break;

			case 'var_date_month':
				$url = get_month_link( false, false );
				break;

			case 'var_date_day':
			case 'var_date':
				$url = get_day_link( false, false, false );
				break;
		}

		return $url;
	}

	/**
	 * @since 3.4.0
	 *
	 * @return string
	 */
	public function get_description() {
		$desc = '';

		switch ( $this->context_type ) {
			case 'var_site':
				$desc = get_bloginfo( 'description' );
				break;
			case 'WP_Term':
			case 'WP_User':
				$wp_obj = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
				$desc   = $wp_obj->description;
				break;
		}

		return $desc;
	}

	/**
	 * Get Image Context
	 *
	 * Returns Image ID (Context Key) if possible, and Image URL.
	 *
	 * This is used to get the Image WP_Post object via $context.
	 *
	 * attachment post parent.
	 * registered images to post.
	 * post content.
	 *
	 * @return array {
	 *     @type int|string $id
	 *     @type string     $url
	 * }
	 */
	public function get_images( $sources = 'all') {
		$image = array();
		switch ( $this->context_type ) {
			case 'WP_Post':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				if ( 'attachment' === $wp_obj->post_type ) {
					$images['attachments'][] = array(
						'id'  => $wp_obj->ID,
						'url' => wp_get_attachment_url( $wp_obj->ID ),
					);
				}

				$media_list = get_attached_media( 'image', $wp_obj );

				break;
		}
	}

	/**
	 * @since 3.4.0
	 *
	 * @return array {
	 *     @type array $index {
	 *         @type int    $position
	 *         @type string $title
	 *         @type string $url
	 *     }
	 * }
	 */
	public function get_breadcrumb() {
		$rtn_list = array();
		// WP_Post & WP_Terms could be merged once a parent_id() method is created.
		switch ( $this->context_type ) {
			case 'var_site':
			case 'WP_Site':
				// Site data added at last.
				break;

			case 'WP_Post':
				$context = $this;
				$object  = self::get_object( $this->context_type, $this->context_key );
				while ( ! empty( $object->post_parent ) ) {
					array_unshift(
						$rtn_list,
						array(
							'title' => $context->get_display_name(),
							'url'   => $context->get_url(),
						)
					);

					$context = array(
						'context_type' => $context->context_type,
						'context_key'  => $object->post_parent, // Create get_parent().
					);
					$context = self::get_instance( $context );
					$object         = self::get_object( $context->context_type, $context->context_key );
				}
				array_unshift(
					$rtn_list,
					array(
						'title' => $context->get_display_name(),
						'url'   => $context->get_url(),
					)
				);
				break;

			case 'WP_Taxonomy':
				// No URL destination exists to trigger this.
				break;

			case 'WP_Term':
				$context = $this;
				$object  = self::get_object( $context->context_type, $context->context_key, $context->wp_props );
				while ( ! empty( $object->parent ) ) {
					array_unshift(
						$rtn_list,
						array(
							'title' => $context->get_display_name(),
							'url'   => $context->get_url(),
						)
					);

					$context = array(
						'context_type' => $context->context_type,
						'context_key'  => $object->parent, // Create get_parent().
						'wp_props'    => $context->wp_props
					);
					$context = self::get_instance( $context );
					$object  = self::get_object( $context->context_type, $context->context_key, $context->wp_props );
				}
				array_unshift(
					$rtn_list,
					array(
						'title' => $context->get_display_name(),
						'url'   => $context->get_url(),
					)
				);
				break;

			case 'WP_User':
			case 'var_search':
				$context = $this;
				array_unshift(
					$rtn_list,
					array(
						'title' => $context->get_display_name(),
						'url'   => $context->get_url(),
					)
				);
				break;

		}

		// Add Homepage as root/base.
		$site_context = array();
		if ( is_multisite() ) {
			$site_context['context_type'] = 'WP_Site';
			$site_context['context_key']  = get_current_blog_id();
		} else {
			$site_context['context_type'] = 'var_site';
			$site_context['context_key']  = 0;
		}
		$site_context = self::get_instance( $site_context );

		array_unshift(
			$rtn_list,
			array(
				'title' => $site_context->get_display_name(),
				'url'   => $site_context->get_url() . '/',
			)
		);

		// Add position values.
		foreach ( $rtn_list as $index => &$item ) {
			$item['position'] = $index + 1;
		}

		return $rtn_list;
	}

}
