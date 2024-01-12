<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\WebPage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs;

/**
 * Person Author graph class.
 * This a secondary Person graph for post authors and BuddyPress profile pages.
 *
 * @since 4.0.0
 */
class PersonAuthor extends Graphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @param  int   $userId The user ID.
	 * @return array $data   The graph data.
	 */
	public function get( $userId = null ) {
		$post         = aioseo()->helpers->getPost();
		$user         = get_queried_object();
		$isAuthorPage = is_author() && is_a( $user, 'WP_User' );
		if (
			(
				( ! is_singular() && ! $isAuthorPage ) ||
				( is_singular() && ! is_a( $post, 'WP_Post' ) )
			) &&
			! $userId
		) {
			return [];
		}

		// Dynamically determine the User ID.
		if ( ! $userId ) {
			$userId = $isAuthorPage ? $user->ID : $post->post_author;
			if ( function_exists( 'bp_is_user' ) && bp_is_user() ) {
				$userId = intval( wp_get_current_user()->ID );
			}
		}

		if ( ! $userId ) {
			return [];
		}

		$authorUrl = get_author_posts_url( $userId );

		$data = [
			'@type' => 'Person',
			'@id'   => $authorUrl . '#author',
			'url'   => $authorUrl,
			'name'  => get_the_author_meta( 'display_name', $userId )
		];

		$avatar = $this->avatar( $userId, 'authorImage' );
		if ( $avatar ) {
			$data['image'] = $avatar;
		}

		$socialUrls = array_values( $this->getUserProfiles( $userId ) );
		if ( $socialUrls ) {
			$data['sameAs'] = $socialUrls;
		}

		if ( is_author() ) {
			$data['mainEntityOfPage'] = [
				'@id' => aioseo()->schema->context['url'] . '#profilepage'
			];
		}

		// Check if our addons need to modify this graph.
		$addonsPersonAuthorData = array_filter( aioseo()->addons->doAddonFunction( 'personAuthor', 'get', [
			'userId' => $userId,
			'data'   => $data
		] ) );

		foreach ( $addonsPersonAuthorData as $addonPersonAuthorData ) {
			$data = array_merge( $data, $addonPersonAuthorData );
		}

		return $data;
	}
}