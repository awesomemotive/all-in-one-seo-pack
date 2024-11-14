<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\WebPage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ProfilePage graph class.
 *
 * @since 4.0.0
 */
class ProfilePage extends WebPage {
	/**
	 * The graph type.
	 *
	 * @since 4.5.6
	 *
	 * @var string
	 */
	protected $type = 'ProfilePage';

	/**
	 * Returns the graph data.
	 *
	 * @since 4.5.4
	 *
	 * @return array The graph data.
	 */
	public function get() {
		$data = parent::get();

		$post   = aioseo()->helpers->getPost();
		$author = get_queried_object();
		if (
			! is_a( $author, 'WP_User' ) &&
			( is_singular() && ! is_a( $post, 'WP_Post' ) )
		) {
			return [];
		}

		global $wp_query;
		$articles = [];
		$authorId = $author->ID ?? $post->post_author ?? 0;
		foreach ( $wp_query->posts as $post ) {
			if ( $post->post_author !== $authorId ) {
				continue;
			}

			$articles[] = [
				'@type'         => 'Article',
				'url'           => get_permalink( $post->ID ),
				'headline'      => $post->post_title,
				'datePublished' => mysql2date( DATE_W3C, $post->post_date, false ),
				'dateModified'  => mysql2date( DATE_W3C, $post->post_modified, false ),
				'author'        => [
					'@id' => get_author_posts_url( $authorId ) . '#author'
				]
			];
		}

		$data = array_merge( $data, [
			'dateCreated' => mysql2date( DATE_W3C, $author->user_registered, false ),
			'mainEntity'  => [
				'@id' => get_author_posts_url( $authorId ) . '#author'
			],
			'hasPart'     => $articles

		] );

		// Add BuddyPress specific data
		if ( aioseo()->schema->helpers->checkBuddyPressPage() ) {
			// Ensure 'mainEntity' exists and populate it with the correct details.
			if ( ! isset( $data['mainEntity'] ) ) {
				$data['mainEntity'] = [];
			}
			$data['mainEntity']['@type'] = 'Person';
			$data['mainEntity']['name']  = get_the_title();
			if ( function_exists( 'bp_get_displayed_user_link' ) && bp_get_displayed_user_link() ) {
				$data['mainEntity']['url'] = bp_get_displayed_user_link();
			}
		}

		return $data;
	}
}