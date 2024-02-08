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

		$author = get_queried_object();
		if ( ! is_a( $author, 'WP_User' ) ) {
			return [];
		}

		global $wp_query;
		$articles = [];
		$authorId = $author->ID;
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

		return $data;
	}
}