<?php
namespace AIOSEO\Plugin\Common\Integrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to integrate with the BuddyPress plugin.
 *
 * @since 4.7.6
 */
class BuddyPress {
	/**
	 * Call the callback given by the first parameter.
	 *
	 * @since 4.7.6
	 *
	 * @param  callable   $callback The function to be called.
	 * @param  mixed      ...$args  Zero or more parameters to be passed to the function
	 * @return mixed|null           The function result or null if the function is not callable.
	 */
	public static function callFunc( $callback, ...$args ) {
		if ( is_callable( $callback ) ) {
			return call_user_func( $callback, ...$args );
		}

		return null;
	}

	/**
	 * Returns the BuddyPress email custom post type slug.
	 *
	 * @since 4.7.6
	 *
	 * @return string The BuddyPress email custom post type slug if found or an empty string.
	 */
	public static function getEmailCptSlug() {
		$slug = '';
		if ( aioseo()->helpers->isPluginActive( 'buddypress' ) ) {
			$slug = self::callFunc( 'bp_get_email_post_type' );
		}

		return is_scalar( $slug ) ? strval( $slug ) : '';
	}

	/**
	 * Retrieves the BuddyPress component archive page permalink.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $component The BuddyPress component.
	 * @return string            The component archive page permalink.
	 */
	public static function getComponentArchiveUrl( $component ) {
		switch ( $component ) {
			case 'activity':
				$output = self::callFunc( 'bp_get_activity_directory_permalink' );
				break;
			case 'member':
				$output = self::callFunc( 'bp_get_members_directory_permalink' );
				break;
			case 'group':
				$output = self::callFunc( 'bp_get_groups_directory_url' );
				break;
			default:
				$output = '';
		}

		return is_scalar( $output ) ? strval( $output ) : '';
	}

	/**
	 * Returns the BuddyPress component single page permalink.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $component The BuddyPress component.
	 * @param  mixed  $id        The component ID.
	 * @return string            The component single page permalink.
	 */
	public static function getComponentSingleUrl( $component, $id ) {
		switch ( $component ) {
			case 'activity':
				$output = self::callFunc( 'bp_activity_get_permalink', $id );
				break;
			case 'group':
				$output = self::callFunc( 'bp_get_group_url', $id );
				break;
			case 'member':
				$output = self::callFunc( 'bp_core_get_userlink', $id, false, true );
				break;
			default:
				$output = '';
		}

		return is_scalar( $output ) ? strval( $output ) : '';
	}

	/**
	 * Returns the BuddyPress component edit link.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $component The BuddyPress component.
	 * @param  mixed  $id        The component ID.
	 * @return string            The component edit link.
	 */
	public static function getComponentEditUrl( $component, $id ) {
		switch ( $component ) {
			case 'activity':
				$output = add_query_arg( [
					'page'   => 'bp-activity',
					'aid'    => $id,
					'action' => 'edit'
				], self::callFunc( 'bp_get_admin_url', 'admin.php' ) );
				break;
			case 'group':
				$output = add_query_arg( [
					'page'   => 'bp-groups',
					'gid'    => $id,
					'action' => 'edit'
				], self::callFunc( 'bp_get_admin_url', 'admin.php' ) );
				break;
			case 'member':
				$output = get_edit_user_link( $id );
				break;
			default:
				$output = '';
		}

		return is_scalar( $output ) ? strval( $output ) : '';
	}

	/**
	 * Returns whether the BuddyPress component is active or not.
	 *
	 * @since 4.7.6
	 *
	 * @param  string $component The BuddyPress component.
	 * @return bool              Whether the BuddyPress component is active.
	 */
	public static function isComponentActive( $component ) {
		static $active = [];
		if ( isset( $active[ $component ] ) ) {
			return $active[ $component ];
		}

		switch ( $component ) {
			case 'activity':
				$active[ $component ] = self::callFunc( 'bp_is_active', 'activity' );
				break;
			case 'group':
				$active[ $component ] = self::callFunc( 'bp_is_active', 'groups' );
				break;
			case 'member':
				$active[ $component ] = self::callFunc( 'bp_is_active', 'members' );
				break;
			default:
				$active[ $component ] = false;
		}

		return $active[ $component ];
	}

	/**
	 * Returns whether the current page is a BuddyPress component page.
	 *
	 * @since 4.7.6
	 *
	 * @return bool Whether the current page is a BuddyPress component page.
	 */
	public static function isComponentPage() {
		return ! empty( aioseo()->standalone->buddyPress->component->templateType );
	}
}