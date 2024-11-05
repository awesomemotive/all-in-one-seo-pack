<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Access {
	/**
	 * Capabilities for our users.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $capabilities = [
		'aioseo_dashboard',
		'aioseo_general_settings',
		'aioseo_search_appearance_settings',
		'aioseo_social_networks_settings',
		'aioseo_sitemap_settings',
		'aioseo_link_assistant_settings',
		'aioseo_redirects_manage',
		'aioseo_page_redirects_manage',
		'aioseo_redirects_settings',
		'aioseo_seo_analysis_settings',
		'aioseo_search_statistics_settings',
		'aioseo_tools_settings',
		'aioseo_feature_manager_settings',
		'aioseo_page_analysis',
		'aioseo_page_general_settings',
		'aioseo_page_advanced_settings',
		'aioseo_page_schema_settings',
		'aioseo_page_social_settings',
		'aioseo_page_link_assistant_settings',
		'aioseo_page_redirects_settings',
		'aioseo_local_seo_settings',
		'aioseo_page_local_seo_settings',
		'aioseo_page_writing_assistant_settings',
		'aioseo_about_us_page',
		'aioseo_setup_wizard',
		'aioseo_page_seo_revisions_settings'
	];

	/**
	 * Whether we're already updating the roles during this request.
	 *
	 * @since 4.2.7
	 *
	 * @var bool
	 */
	protected $isUpdatingRoles = false;

	/**
	 * Roles we check capabilities against.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $roles = [
		'superadmin'    => 'superadmin',
		'administrator' => 'administrator',
		'editor'        => 'editor',
		'author'        => 'author',
		'contributor'   => 'contributor'
	];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		// First load the roles so that we can pull the roles from the other plugins.
		add_action( 'plugins_loaded', [ $this, 'setRoles' ], 999 );

		// Load later again so that we can pull the roles lately registered.
		// This needs to run before 1000 so that our update migrations and other hook callbacks can pull the roles.
		add_action( 'init', [ $this, 'setRoles' ], 999 );
	}

	/**
	 * Sets the roles on the instance.
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function setRoles() {
		$adminRoles = [];
		$allRoles   = aioseo()->helpers->getUserRoles();
		foreach ( $allRoles as $roleName => $wpRole ) {
			$role = get_role( $roleName );
			if ( $this->isAdmin( $roleName ) || $role->has_cap( 'publish_posts' ) ) {
				$adminRoles[ $roleName ] = $roleName;
			}
		}

		$this->roles = array_merge( $this->roles, $adminRoles );
	}

	/**
	 * Adds capabilities into WordPress for the current user.
	 * Only on activation or settings saved.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addCapabilities() {
		$this->isUpdatingRoles = true;

		foreach ( $this->roles as $wpRole => $role ) {
			$roleObject = get_role( $wpRole );
			if ( ! is_object( $roleObject ) ) {
				continue;
			}

			if ( $this->isAdmin( $role ) ) {
				$roleObject->add_cap( 'aioseo_manage_seo' );
			}

			if ( function_exists( 'wp_get_current_user' ) && current_user_can( 'edit_posts' ) ) {
				$postCapabilities = [
					'aioseo_page_analysis',
					'aioseo_page_general_settings',
					'aioseo_page_advanced_settings',
					'aioseo_page_schema_settings',
					'aioseo_page_social_settings',
				];

				foreach ( $postCapabilities as $capability ) {
					$roleObject->add_cap( $capability );
				}
			}
		}
	}

	/**
	 * Removes capabilities for any unknown role.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function removeCapabilities() {
		$this->isUpdatingRoles = true;

		// Clear out capabilities for unknown roles.
		$wpRoles  = wp_roles();
		$allRoles = $wpRoles->roles;
		foreach ( $allRoles as $key => $wpRole ) {
			$checkRole = is_multisite() ? 'superadmin' : 'administrator';
			if ( $checkRole === $key ) {
				continue;
			}

			if ( array_key_exists( $key, $this->roles ) ) {
				continue;
			}

			$role = get_role( $key );
			if ( ! is_a( $role, 'WP_Role' ) || ! is_array( $role->capabilities ) ) {
				continue;
			}

			// We don't need to remove the capabilities for administrators.
			if ( $this->isAdmin( $key ) ) {
				continue;
			}

			foreach ( $this->capabilities as $capability ) {
				if ( $role->has_cap( $capability ) ) {
					$role->remove_cap( $capability );
				}
			}

			$role->remove_cap( 'aioseo_manage_seo' );
		}
	}

	/**
	 * Checks if the current user has the capability.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|array $capability The capability to check against.
	 * @param  string|null  $checkRole  A role to check against.
	 * @return bool                     Whether or not the user has this capability.
	 */
	public function hasCapability( $capability, $checkRole = null ) {
		if ( $this->isAdmin( $checkRole ) ) {
			return true;
		}

		$canPublishOrEdit = $this->can( 'publish_posts', $checkRole ) || $this->can( 'edit_posts', $checkRole );
		if ( ! $canPublishOrEdit ) {
			return false;
		}

		if ( is_array( $capability ) ) {
			foreach ( $capability as $cap ) {
				if ( false !== strpos( $cap, 'aioseo_page_' ) ) {
					return true;
				}
			}

			return false;
		}

		return false !== strpos( $capability, 'aioseo_page_' );
	}

	/**
	 * Gets all the capabilities for the current user.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|null $role A role to check against.
	 * @return array             An array of capabilities.
	 */
	public function getAllCapabilities( $role = null ) {
		$capabilities = [];
		foreach ( $this->getCapabilityList() as $capability ) {
			$capabilities[ $capability ] = $this->hasCapability( $capability, $role );
		}

		$capabilities['aioseo_admin']         = $this->isAdmin( $role );
		$capabilities['aioseo_manage_seo']    = $this->isAdmin( $role );
		$capabilities['aioseo_about_us_page'] = $this->canManage( $role );

		return $capabilities;
	}

	/**
	 * Returns the capability list.
	 *
	 * @return 4.1.3
	 *
	 * @return array An array of capabilities.
	 */
	public function getCapabilityList() {
		return $this->capabilities;
	}

	/**
	 * If the current user is an admin, or superadmin, they have access to all caps regardless.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|null $role The role to check admin privileges if we have one.
	 * @return bool              Whether not the user/role is an admin.
	 */
	public function isAdmin( $role = null ) {
		if ( $role ) {
			if ( ( is_multisite() && 'superadmin' === $role ) || 'administrator' === $role ) {
				return true;
			}

			return false;
		}

		if ( ! function_exists( 'wp_get_current_user' ) ) {
			return false;
		}

		if ( ( is_multisite() && current_user_can( 'superadmin' ) ) || current_user_can( 'administrator' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the passed in role can publish posts.
	 *
	 * @since 4.0.9
	 *
	 * @param  string  $capability The capability to check against.
	 * @param  string  $role       The role to check.
	 * @return boolean             True if the role can publish.
	 */
	protected function can( $capability, $role ) {
		if ( empty( $role ) ) {
			return current_user_can( $capability );
		}

		$wpRoles  = wp_roles();
		$allRoles = $wpRoles->roles;
		foreach ( $allRoles as $key => $wpRole ) {
			if ( $key === $role ) {
				$r = get_role( $key );
				if ( $r->has_cap( $capability ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if the current user can manage AIOSEO.
	 *
	 * @since 4.0.0
	 *
	 * @param  string|null $checkRole A role to check against.
	 * @return bool                   Whether or not the user can manage AIOSEO.
	 */
	public function canManage( $checkRole = null ) {
		return $this->isAdmin( $checkRole );
	}

	/**
	 * Gets all options that the user does not have access to manage.
	 *
	 * @since 4.1.3
	 *
	 * @return array An array with the option names.
	 */
	public function getNotAllowedOptions() {
		return [];
	}

	/**
	 * Gets all page fields that the user does not have access to manage.
	 *
	 * @since 4.1.3
	 *
	 * @return array An array with the field names.
	 */
	public function getNotAllowedPageFields() {
		return [];
	}

	/**
	 * Returns Roles.
	 *
	 * @since 4.0.17
	 *
	 * @return array An array of role names.
	 */
	public function getRoles() {
		return $this->roles;
	}
}