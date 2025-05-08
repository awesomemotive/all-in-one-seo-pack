<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils;

/**
 * Contains all WP related helper methods.
 *
 * @since 4.1.4
 */
trait Wp {
	/**
	 * Whether or not we have a local connection.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	private static $connection = false;

	/**
	 * Returns user roles in the current WP install.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of user roles.
	 */
	public function getUserRoles() {
		global $wp_roles; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$wpRoles = $wp_roles; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		if ( ! is_object( $wpRoles ) ) {
			// Don't assign this to the global because otherwise WordPress won't override it.
			$wpRoles = new \WP_Roles();
		}

		$roleNames = $wpRoles->get_names();
		asort( $roleNames );

		return $roleNames;
	}

	/**
	 * Returns the custom roles in the current WP install.
	 *
	 * @since 4.1.3
	 *
	 * @return array An array of custom roles.
	 */
	public function getCustomRoles() {
		$allRoles = $this->getUserRoles();

		$toSkip = array_merge(
			// Default WordPress roles.
			[ 'superadmin', 'administrator', 'editor', 'author', 'contributor' ],
			// Default AIOSEO roles.
			[ 'aioseo_manager', 'aioseo_editor' ],
			// Filterable roles.
			apply_filters( 'aioseo_access_control_excluded_roles', array_merge( [
				'subscriber'
			], aioseo()->helpers->isWooCommerceActive() ? [ 'customer' ] : [] ) )
		);

		// Remove empty entries.
		$toSkip = array_filter( $toSkip );

		$customRoles = [];
		foreach ( $allRoles as $roleName => $role ) {
			// Skip specific roles.
			if ( in_array( $roleName, $toSkip, true ) ) {
				continue;
			}

			$customRoles[ $roleName ] = $role;
		}

		return $customRoles;
	}

	/**
	 * Returns an array of plugins with the active status.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of plugins with active status.
	 */
	public function getPluginData() {
		$pluginUpgrader   = new Utils\PluginUpgraderSilentAjax();
		$installedPlugins = array_keys( get_plugins() );

		$plugins = [];
		foreach ( $pluginUpgrader->pluginSlugs as $key => $slug ) {
			$adminUrl        = admin_url( $pluginUpgrader->pluginAdminUrls[ $key ] );
			$networkAdminUrl = null;
			if (
				is_multisite() &&
				is_network_admin() &&
				! empty( $pluginUpgrader->hasNetworkAdmin[ $key ] )
			) {
				$networkAdminUrl = network_admin_url( $pluginUpgrader->hasNetworkAdmin[ $key ] );
				if ( aioseo()->helpers->isPluginNetworkActivated( $pluginUpgrader->pluginSlugs[ $key ] ) ) {
					$adminUrl = $networkAdminUrl;
				}
			}

			$plugins[ $key ] = [
				'basename'        => $slug,
				'installed'       => in_array( $slug, $installedPlugins, true ),
				'activated'       => is_plugin_active( $slug ),
				'adminUrl'        => $adminUrl,
				'networkAdminUrl' => $networkAdminUrl,
				'canInstall'      => aioseo()->addons->canInstall(),
				'canActivate'     => aioseo()->addons->canActivate(),
				'canUpdate'       => aioseo()->addons->canUpdate(),
				'wpLink'          => ! empty( $pluginUpgrader->wpPluginLinks[ $key ] ) ? $pluginUpgrader->wpPluginLinks[ $key ] : null
			];
		}

		return $plugins;
	}

	/**
	 * Returns all registered Post Statuses.
	 *
	 * @since 4.1.6
	 *
	 * @param  boolean $statusesOnly Whether or not to only return statuses.
	 * @return array              An array of post statuses.
	 */
	public function getPublicPostStatuses( $statusesOnly = false ) {
		$allStatuses = get_post_stati( [ 'show_in_admin_all_list' => true ], 'objects' );

		$postStatuses = [];
		foreach ( $allStatuses as $status => $data ) {
			if (
				! $data->public &&
				! $data->protected &&
				! $data->private
			) {
				continue;
			}

			if ( $statusesOnly ) {
				$postStatuses[] = $status;
				continue;
			}

			$postStatuses[] = [
				'label'  => $data->label,
				'status' => $status
			];
		}

		return $postStatuses;
	}

	/**
	 * Returns a list of public post types objects or names.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool  $namesOnly       Whether only the names should be returned.
	 * @param  bool  $hasArchivesOnly Whether to only include post types which have archives.
	 * @param  bool  $rewriteType     Whether to rewrite the type slugs.
	 * @param  array $args            Additional arguments.
	 * @return array                  List of public post types.
	 */
	public function getPublicPostTypes( $namesOnly = false, $hasArchivesOnly = false, $rewriteType = false, $args = [] ) {
		$args = array_merge( [
			'include' => [] // Post types to include.
		], $args );

		$postTypes   = [];
		$postTypeObjects = get_post_types( [], 'objects' );
		foreach ( $postTypeObjects as $postTypeObject ) {
			if ( ! is_post_type_viewable( $postTypeObject ) ) {
				continue;
			}

			$postTypeArray = $this->getPostType( $postTypeObject, $namesOnly, $hasArchivesOnly, $rewriteType );
			if ( ! empty( $postTypeArray ) ) {
				$postTypes[] = $postTypeArray;
			}
		}

		if ( isset( aioseo()->standalone->buddyPress ) ) {
			aioseo()->standalone->buddyPress->maybeAddPostTypes( $postTypes, $namesOnly, $hasArchivesOnly, $args );
		}

		return apply_filters( 'aioseo_public_post_types', $postTypes, $namesOnly, $hasArchivesOnly, $args );
	}

	/**
	 * Returns the data for the given post type.
	 *
	 * @since 4.2.2
	 *
	 * @param  \WP_Post_Type $postTypeObject  The post type object.
	 * @param  bool          $namesOnly       Whether only the names should be returned.
	 * @param  bool          $hasArchivesOnly Whether to only include post types which have archives.
	 * @param  bool          $rewriteType     Whether to rewrite the type slugs.
	 * @return mixed                          Data for the post type.
	 */
	public function getPostType( $postTypeObject, $namesOnly = false, $hasArchivesOnly = false, $rewriteType = false ) {
		if ( empty( $postTypeObject->label ) ) {
			return $namesOnly ? null : [];
		}

		// We don't want to include archives for the WooCommerce shop page.
		if (
			$hasArchivesOnly &&
			(
				! $postTypeObject->has_archive ||
				( 'product' === $postTypeObject->name && $this->isWooCommerceActive() )
			)
		) {
			return $namesOnly ? null : [];
		}

		if ( $namesOnly ) {
			return $postTypeObject->name;
		}

		if ( 'attachment' === $postTypeObject->name ) {
			// We have to check if the 'init' action has been fired to avoid a PHP notice
			// in WP 6.7+ due to loading translations too early.
			if ( did_action( 'init' ) ) {
				$postTypeObject->label = __( 'Attachments', 'all-in-one-seo-pack' );
			}
		}

		if ( 'product' === $postTypeObject->name && $this->isWooCommerceActive() ) {
			$postTypeObject->menu_icon = 'dashicons-products';
		}

		$name = $postTypeObject->name;
		if ( 'type' === $postTypeObject->name && $rewriteType ) {
			$name = '_aioseo_type';
		}

		return [
			'name'         => $name,
			'label'        => ucwords( $postTypeObject->label ),
			'singular'     => ucwords( $postTypeObject->labels->singular_name ),
			'icon'         => $postTypeObject->menu_icon,
			'hasArchive'   => $postTypeObject->has_archive,
			'hierarchical' => $postTypeObject->hierarchical,
			'taxonomies'   => get_object_taxonomies( $name ),
			'slug'         => isset( $postTypeObject->rewrite['slug'] ) ? $postTypeObject->rewrite['slug'] : $name,
			'supports'     => get_all_post_type_supports( $name )
		];
	}

	/**
	 * Returns a list of public taxonomies objects or names.
	 *
	 * @since 4.0.0
	 *
	 * @param  bool  $namesOnly   Whether only the names should be returned.
	 * @param  bool  $rewriteType Whether to rewrite the type slugs.
	 * @return array              List of public taxonomies.
	 */
	public function getPublicTaxonomies( $namesOnly = false, $rewriteType = false ) {
		$taxonomies = [];
		if ( count( $taxonomies ) ) {
			return $taxonomies;
		}

		$taxObjects = get_taxonomies( [], 'objects' );
		foreach ( $taxObjects as $taxObject ) {
			if (
				empty( $taxObject->label ) ||
				! is_taxonomy_viewable( $taxObject ) ||
				aioseo()->helpers->isWooCommerceProductAttribute( $taxObject->name )
			) {
				continue;
			}

			if ( in_array( $taxObject->name, [
				'product_shipping_class',
				'post_format'
			], true ) ) {
				continue;
			}

			if ( $namesOnly ) {
				$taxonomies[] = $taxObject->name;
				continue;
			}

			$name = $taxObject->name;
			if ( 'type' === $taxObject->name && $rewriteType ) {
				$name = '_aioseo_type';
			}

			global $wp_taxonomies; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			$taxonomyPostTypes = ! empty( $wp_taxonomies[ $name ] ) // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				? $wp_taxonomies[ $name ]->object_type // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				: [];

			$taxonomies[] = [
				'name'               => $name,
				'label'              => ucwords( $taxObject->label ),
				'singular'           => ucwords( $taxObject->labels->singular_name ),
				'icon'               => strpos( $taxObject->label, 'categor' ) !== false ? 'dashicons-category' : 'dashicons-tag',
				'hierarchical'       => $taxObject->hierarchical,
				'slug'               => isset( $taxObject->rewrite['slug'] ) ? $taxObject->rewrite['slug'] : '',
				'primaryTermSupport' => (bool) $taxObject->hierarchical,
				'restBase'           => ( $taxObject->rest_base ) ? $taxObject->rest_base : $taxObject->name,
				'postTypes'          => $taxonomyPostTypes
			];
		}

		if ( $this->isWooCommerceActive() ) {
			// We inject a fake one for WooCommerce product attributes so that we can show a single set of settings
			// instead of having to duplicate them for each attribute.
			if ( $namesOnly ) {
				$taxonomies[] = 'product_attributes';
			} else {
				$taxonomies[] = [
					'name'               => 'product_attributes',
					'label'              => __( 'Product Attributes', 'all-in-one-seo-pack' ),
					'singular'           => __( 'Product Attribute', 'all-in-one-seo-pack' ),
					'icon'               => 'dashicons-products',
					'hierarchical'       => true,
					'slug'               => 'product_attributes',
					'primaryTermSupport' => true,
					'restBase'           => 'product_attributes_class',
					'postTypes'          => [ 'product' ]
				];
			}
		}

		return apply_filters( 'aioseo_public_taxonomies', $taxonomies, $namesOnly );
	}

	/**
	 * Retrieve a list of users that match passed in roles.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of user data.
	 */
	public function getSiteUsers( $roles ) {
		static $users = [];

		if ( ! empty( $users ) ) {
			return $users;
		}

		$rolesWhere = [];
		foreach ( $roles as $role ) {
			$rolesWhere[] = '(um.meta_key = \'' . aioseo()->core->db->db->prefix . 'capabilities\' AND um.meta_value LIKE \'%\"' . $role . '\"%\')';
		}
		// We get the table name from WPDB since multisites share the same table.
		$usersTableName    = aioseo()->core->db->db->users;
		$usermetaTableName = aioseo()->core->db->db->usermeta;
		$dbUsers           = aioseo()->core->db->start( "$usersTableName as u", true )
			->select( 'u.ID, u.display_name, u.user_nicename, u.user_email' )
			->join( "$usermetaTableName as um", 'u.ID = um.user_id', '', true )
			->whereRaw( '(' . implode( ' OR ', $rolesWhere ) . ')' )
			->orderBy( 'u.user_nicename' )
			->run()
			->result();

		foreach ( $dbUsers as $dbUser ) {
			$users[] = [
				'id'          => (int) $dbUser->ID,
				'displayName' => $dbUser->display_name,
				'niceName'    => $dbUser->user_nicename,
				'email'       => $dbUser->user_email,
				'gravatar'    => get_avatar_url( $dbUser->user_email )
			];
		}

		return $users;
	}

	/**
	 * Returns the ID of the site logo if it exists.
	 *
	 * @since 4.0.0
	 *
	 * @return int
	 */
	public function getSiteLogoId() {
		if ( ! get_theme_support( 'custom-logo' ) ) {
			return false;
		}

		return get_theme_mod( 'custom_logo' );
	}

	/**
	 * Returns the URL of the site logo if it exists.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function getSiteLogoUrl() {
		$id = $this->getSiteLogoId();
		if ( ! $id ) {
			return false;
		}

		$image = wp_get_attachment_image_src( $id, 'full' );
		if ( empty( $image ) ) {
			return false;
		}

		return $image[0];
	}

	/**
	 * Returns noindexed post types.
	 *
	 * @since 4.0.0
	 *
	 * @return array A list of noindexed post types.
	 */
	public function getNoindexedPostTypes() {
		return $this->getNoindexedObjects( 'postTypes' );
	}

	/**
	 * Checks whether a given post type is noindexed.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $postType The post type.
	 * @return bool              Whether the post type is noindexed.
	 */
	public function isPostTypeNoindexed( $postType ) {
		$noindexedPostTypes = $this->getNoindexedPostTypes();

		return in_array( $postType, $noindexedPostTypes, true );
	}

	/**
	 * Checks whether a given post type is public.
	 *
	 * @since 4.2.2
	 *
	 * @param  string  $postType The post type.
	 * @return bool              Whether the post type is public.
	 */
	public function isPostTypePublic( $postType ) {
		$publicPostTypes = $this->getPublicPostTypes( true );

		return in_array( $postType, $publicPostTypes, true );
	}

	/**
	 * Returns noindexed taxonomies.
	 *
	 * @since 4.0.0
	 *
	 * @return array A list of noindexed taxonomies.
	 */
	public function getNoindexedTaxonomies() {
		return $this->getNoindexedObjects( 'taxonomies' );
	}

	/**
	 * Checks whether a given post type is noindexed.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $taxonomy The taxonomy.
	 * @return bool              Whether the taxonomy is noindexed.
	 */
	public function isTaxonomyNoindexed( $taxonomy ) {
		$noindexedTaxonomies = $this->getNoindexedTaxonomies();

		return in_array( $taxonomy, $noindexedTaxonomies, true );
	}

	/**
	 * Checks whether a given taxonomy is public.
	 *
	 * @since 4.2.2
	 *
	 * @param  string  $taxonomy The taxonomy.
	 * @return bool              Whether the taxonomy is public.
	 */
	public function isTaxonomyPublic( $taxonomy ) {
		$publicTaxonomies = $this->getPublicTaxonomies( true );

		return in_array( $taxonomy, $publicTaxonomies, true );
	}

	/**
	 * Returns noindexed object types of a given parent type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $type The parent object type ("postTypes", "archives", "taxonomies").
	 * @return array        A list of noindexed objects types.
	 */
	public function getNoindexedObjects( $type ) {
		$noindexed = [];
		foreach ( aioseo()->dynamicOptions->searchAppearance->$type->all() as $name => $object ) {
			if (
				! $object['show'] ||
				( $object['advanced']['robotsMeta'] && ! $object['advanced']['robotsMeta']['default'] && $object['advanced']['robotsMeta']['noindex'] )
			) {
				$noindexed[] = $name;
			}
		}

		return $noindexed;
	}

	/**
	 * Returns all categories for a post.
	 *
	 * @since 4.1.4
	 *
	 * @param  int   $postId The post ID.
	 * @return array         The category names.
	 */
	public function getAllCategories( $postId = 0 ) {
		$names      = [];
		$categories = get_the_category( $postId );
		if ( $categories && count( $categories ) ) {
			foreach ( $categories as $category ) {
				$names[] = aioseo()->helpers->internationalize( $category->name );
			}
		}

		return $names;
	}

	/**
	 * Returns all tags for a post.
	 *
	 * @since 4.1.4
	 *
	 * @param  int   $postId The post ID.
	 * @return array $names  The tag names.
	 */
	public function getAllTags( $postId = 0 ) {
		$names = [];

		$tags = get_the_tags( $postId );
		if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				if ( ! empty( $tag->name ) ) {
					$names[] = aioseo()->helpers->internationalize( $tag->name );
				}
			}
		}

		return $names;
	}

	/**
	 * Loads the translations for a given domain.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function loadTextDomain( $domain ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Unload the domain in case WordPress has enqueued the translations for the site language instead of profile language.
		// Reloading the text domain will otherwise not override the existing loaded translations.
		unload_textdomain( $domain );

		$mofile = $domain . '-' . get_user_locale() . '.mo';
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );
	}

	/**
	 * Get the page builder the given Post ID was built with.
	 *
	 * @since 4.1.7
	 *
	 * @param  int         $postId The Post ID.
	 * @return bool|string         The page builder or false if not built with page builders.
	 */
	public function getPostPageBuilderName( $postId ) {
		foreach ( aioseo()->standalone->pageBuilderIntegrations as $integration => $pageBuilder ) {
			if ( $pageBuilder->isBuiltWith( $postId ) ) {
				return $integration;
			}
		}

		return false;
	}

	/**
	 * Get the edit link for the given Post ID.
	 *
	 * @since 4.3.1
	 *
	 * @param  int         $postId The Post ID.
	 * @return bool|string         The edit link or false if not built with page builders.
	 */
	public function getPostEditLink( $postId ) {
		$pageBuilder = $this->getPostPageBuilderName( $postId );
		if ( ! empty( $pageBuilder ) ) {
			return aioseo()->standalone->pageBuilderIntegrations[ $pageBuilder ]->getEditUrl( $postId );
		}

		return get_edit_post_link( $postId );
	}

	/**
	 * Checks if the current user can edit posts of the given post type.
	 *
	 * @since 4.1.9
	 *
	 * @param  string $postType The name of the post type.
	 * @return bool             Whether the user can edit posts of the given post type.
	 */
	public function canEditPostType( $postType ) {
		$capabilities = $this->getPostTypeCapabilities( $postType );

		return current_user_can( $capabilities['edit_posts'] );
	}

	/**
	 * Returns a list of capabilities for the given post type.
	 *
	 * @since 4.1.9
	 *
	 * @param  string $postType The name of the post type.
	 * @return array            The capabilities.
	 */
	public function getPostTypeCapabilities( $postType ) {
		static $capabilities = [];
		if ( isset( $capabilities[ $postType ] ) ) {
			return $capabilities[ $postType ];
		}

		$postTypeObject = get_post_type_object( $postType );
		if ( ! is_a( $postTypeObject, 'WP_Post_Type' ) ) {
			$capabilities[ $postType ] = [];

			return $capabilities[ $postType ];
		}

		$capabilityType = $postTypeObject->capability_type;
		if ( ! is_array( $capabilityType ) ) {
			$capabilityType = [
				$capabilityType,
				$capabilityType . 's'
			];
		}

		// Singular base for meta capabilities, plural base for primitive capabilities.
		list( $singularBase, $pluralBase ) = $capabilityType;

		$capabilities[ $postType ] = [
			'edit_post'          => 'edit_' . $singularBase,
			'read_post'          => 'read_' . $singularBase,
			'delete_post'        => 'delete_' . $singularBase,
			'edit_posts'         => 'edit_' . $pluralBase,
			'edit_others_posts'  => 'edit_others_' . $pluralBase,
			'delete_posts'       => 'delete_' . $pluralBase,
			'publish_posts'      => 'publish_' . $pluralBase,
			'read_private_posts' => 'read_private_' . $pluralBase,
		];

		return $capabilities[ $postType ];
	}

	/**
	 * Checks if the current user can edit terms of the given taxonomy.
	 *
	 * @since 4.1.9
	 *
	 * @param  string $taxonomy The name of the taxonomy.
	 * @return bool             Whether the user can edit posts of the given taxonomy.
	 */
	public function canEditTaxonomy( $taxonomy ) {
		$capabilities = $this->getTaxonomyCapabilities( $taxonomy );

		return current_user_can( $capabilities['edit_terms'] );
	}

	/**
	 * Returns a list of capabilities for the given taxonomy.
	 *
	 * @since 4.1.9
	 *
	 * @param  string $taxonomy The name of the taxonomy.
	 * @return array            The capabilities.
	 */
	public function getTaxonomyCapabilities( $taxonomy ) {
		static $capabilities = [];
		if ( isset( $capabilities[ $taxonomy ] ) ) {
			return $capabilities[ $taxonomy ];
		}

		$taxonomyObject = get_taxonomy( $taxonomy );
		if ( ! is_a( $taxonomyObject, 'WP_Taxonomy' ) ) {
			$capabilities[ $taxonomy ] = [];

			return $capabilities[ $taxonomy ];
		}

		$capabilities[ $taxonomy ] = (array) $taxonomyObject->cap;

		return $capabilities[ $taxonomy ];
	}

	/**
	 * Returns the charset for the site.
	 *
	 * @since 4.2.3
	 *
	 * @return string The name of the charset.
	 */
	public function getCharset() {
		static $charset = null;
		if ( null !== $charset ) {
			return $charset;
		}

		$charset = get_option( 'blog_charset' );
		$charset = $charset ? $charset : 'UTF-8';

		return $charset;
	}

	/**
	 * Returns the given data as JSON.
	 * We temporarily change the floating point precision in order to prevent rounding errors.
	 * Otherwise e.g. 4.9 could be output as 4.90000004.
	 *
	 * @since 4.2.7
	 *
	 * @param  mixed  $data  The data.
	 * @param  int    $flags The flags.
	 * @return string        The JSON output.
	 */
	public function wpJsonEncode( $data, $flags = 0 ) {
		$originalPrecision          = false;
		$originalSerializePrecision = false;
		if ( version_compare( PHP_VERSION, '7.1', '>=' ) ) {
			$originalPrecision          = ini_get( 'precision' );
			$originalSerializePrecision = ini_get( 'serialize_precision' );
			ini_set( 'precision', 17 );
			ini_set( 'serialize_precision', -1 );
		}

		$json = wp_json_encode( $data, $flags );

		if ( version_compare( PHP_VERSION, '7.1', '>=' ) ) {
			ini_set( 'precision', $originalPrecision );
			ini_set( 'serialize_precision', $originalSerializePrecision );
		}

		return $json;
	}

	/**
	 * Returns the post title or a placeholder if there isn't one.
	 *
	 * @since 4.3.0
	 *
	 * @param  int    $postId The post ID.
	 * @return string         The post title.
	 */
	public function getPostTitle( $postId ) {
		static $titles = [];
		if ( isset( $titles[ $postId ] ) ) {
			return $titles[ $postId ];
		}

		$post = aioseo()->helpers->getPost( $postId );
		if ( ! is_a( $post, 'WP_Post' ) ) {
			$titles[ $postId ] = __( '(no title)', 'default' ); // phpcs:ignore AIOSEO.Wp.I18n.TextDomainMismatch, WordPress.WP.I18n.TextDomainMismatch

			return $titles[ $postId ];
		}

		$title = $post->post_title;
		$title = $title ? $title : __( '(no title)', 'default' ); // phpcs:ignore AIOSEO.Wp.I18n.TextDomainMismatch, WordPress.WP.I18n.TextDomainMismatch

		$titles[ $postId ] = aioseo()->helpers->decodeHtmlEntities( $title );

		return $titles[ $postId ];
	}

	/**
	 * Checks whether the post status should be considered viewable.
	 * This function is a copy of the WordPress core function is_post_status_viewable() which was introduced in WP 5.7.
	 *
	 * @since 4.5.0
	 *
	 * @param  string|\stdClass $postStatus The post status name or object.
	 * @return bool                         Whether the post status is viewable.
	 */
	public function isPostStatusViewable( $postStatus ) {
		if ( is_scalar( $postStatus ) ) {
			$postStatus = get_post_status_object( $postStatus );

			if ( ! $postStatus ) {
				return false;
			}
		}

		if (
			! is_object( $postStatus ) ||
			$postStatus->internal ||
			$postStatus->protected
		) {
			return false;
		}

		return $postStatus->publicly_queryable || ( $postStatus->_builtin && $postStatus->public );
	}

	/**
	 * Checks whether the given post is publicly viewable.
	 * This function is a copy of the WordPress core function is_post_publicly_viewable() which was introduced in WP 5.7.
	 *
	 * @since 4.5.0
	 *
	 * @param  int|\WP_Post  $post Optional. Post ID or post object. Defaults to global $post.
	 * @return boolean                      Whether the post is publicly viewable or not.
	 */
	public function isPostPubliclyViewable( $post = null ) {
		$post = get_post( $post );
		if ( empty( $post ) ) {
			return false;
		}

		$postType   = get_post_type( $post );
		$postStatus = get_post_status( $post );

		return is_post_type_viewable( $postType ) && $this->isPostStatusViewable( $postStatus );
	}

	/**
	 * Only register a legacy widget if the WP version is lower than 5.8 or the widget is being used.
	 * The "Block-based Widgets Editor" was released in WP 5.8, so for WP versions below 5.8 it's okay to register them.
	 * The main purpose here is to avoid blocks and widgets with the same name to be displayed on the Customizer,
	 * like e.g. the "Breadcrumbs" Block and Widget.
	 *
	 * @since 4.3.9
	 *
	 * @param string $idBase The base ID of a widget created by extending WP_Widget.
	 * @return bool          Whether the legacy widget can be registered.
	 */
	public function canRegisterLegacyWidget( $idBase ) {
		global $wp_version; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		if (
			version_compare( $wp_version, '5.8', '<' ) || // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			is_active_widget( false, false, $idBase ) ||
			aioseo()->standalone->pageBuilderIntegrations['elementor']->isPluginActive()
		) {
			return true;
		}

		return false;
	}

	/**
	 * Parses blocks for a given post.
	 *
	 * @since 4.6.8
	 *
	 * @param  \WP_Post|int $post          The post or post ID.
	 * @param  bool         $flattenBlocks Whether to flatten the blocks.
	 * @return array                       The parsed blocks.
	 */
	public function parseBlocks( $post, $flattenBlocks = true ) {
		if ( ! is_a( $post, 'WP_Post' ) ) {
			$post = aioseo()->helpers->getPost( $post );
		}

		static $parsedBlocks = [];
		if ( isset( $parsedBlocks[ $post->ID ] ) ) {
			return $parsedBlocks[ $post->ID ];
		}

		$parsedBlocks = parse_blocks( $post->post_content );

		if ( $flattenBlocks ) {
			$parsedBlocks = $this->flattenBlocks( $parsedBlocks );
		}

		$parsedBlocks[ $post->ID ] = $parsedBlocks;

		return $parsedBlocks[ $post->ID ];
	}

	/**
	 * Flattens the given blocks.
	 *
	 * @since 4.6.8
	 *
	 * @param  array $blocks The blocks.
	 * @return array         The flattened blocks.
	 */
	public function flattenBlocks( $blocks ) {
		$flattenedBlocks = [];

		foreach ( $blocks as $block ) {
			if ( ! empty( $block['innerBlocks'] ) ) {
				// Flatten inner blocks first.
				$innerBlocks = $this->flattenBlocks( $block['innerBlocks'] );
				unset( $block['innerBlocks'] );

				// Add the current block to the result.
				$flattenedBlocks[] = $block;

				// Add the flattened inner blocks to the result.
				$flattenedBlocks = array_merge( $flattenedBlocks, $innerBlocks );
			} else {
				// If no inner blocks, just add the block to the result.
				$flattenedBlocks[] = $block;
			}
		}

		return $flattenedBlocks;
	}

	/**
	 * Checks if the Classic eEditor is active and if the Block Editor is disabled in its settings.
	 *
	 * @since 4.7.3
	 *
	 * @return bool Whether the Classic Editor is active.
	 */
	public function isClassicEditorActive() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return false;
		}

		return 'classic' === get_option( 'classic-editor-replace' );
	}

	/**
	 * Redirects to a 404 Not Found page if the sitemap is disabled.
	 *
	 * @since 4.0.0
	 * @version 4.8.0 Moved from the Sitemap class.
	 *
	 * @return void
	 */
	public function notFoundPage() {
		global $wp_query; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$wp_query->set_404(); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		status_header( 404 );
		include_once get_404_template();
		exit;
	}

	/**
	 * Retrieves the post type labels for the given post type.
	 *
	 * @since 4.8.2
	 *
	 * @param  string $postType The name of a registered post type.
	 * @return object           Object with all the labels as member variables.
	 */
	public function getPostTypeLabels( $postType ) {
		static $postTypeLabels = [];
		if ( ! isset( $postTypeLabels[ $postType ] ) ) {
			$postTypeObject = get_post_type_object( $postType );
			if ( ! is_a( $postTypeObject, 'WP_Post_Type' ) ) {
				return null;
			}

			$postTypeLabels[ $postType ] = get_post_type_labels( $postTypeObject );
		}

		return $postTypeLabels[ $postType ];
	}
}