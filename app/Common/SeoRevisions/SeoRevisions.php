<?php
namespace AIOSEO\Plugin\Common\SeoRevisions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Revisions container class.
 *
 * @since 4.4.0
 */
class SeoRevisions {
	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.4.0
	 *
	 * @return array The data.
	 */
	public function getVueDataCompare() {
		return [
			'currentUser' => $this->getVueDataCurrentUserMeta()
		];
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.4.0
	 *
	 * @return array The data.
	 */
	public function getVueDataEdit() {
		return $this->getVueDataCompare();
	}

	/**
	 * Retrieve the current user info for usage on Vue UI.
	 *
	 * @since 4.4.0
	 *
	 * @return array Current logged-in user info.
	 */
	protected function getVueDataCurrentUserMeta() {
		$currentUserId = get_current_user_id();
		$avatarData    = get_avatar_data( $currentUserId, [
			'size'    => 32,
			'default' => 'mystery'
		] );

		return [
			'avatar'       => [
				'size' => absint( $avatarData['size'] ),
				'url'  => $avatarData['found_avatar'] ? esc_url( $avatarData['url'] ) : strval( get_avatar_url( 0, $avatarData ) )
			],
			'display_name' => get_the_author_meta( 'display_name', $currentUserId )
		];
	}
}