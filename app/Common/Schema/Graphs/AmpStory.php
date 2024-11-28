<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AmpStory graph class.
 *
 * @since 4.7.6
 */
class AmpStory extends Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.7.6
	 *
	 * @return array The parsed graph data.
	 */
	public function get() {
		$post = aioseo()->helpers->getPost();
		if ( ! is_a( $post, 'WP_Post' ) || 'web-story' !== $post->post_type ) {
			return [];
		}

		$data = [
			'@type'         => 'AmpStory',
			'@id'           => aioseo()->schema->context['url'] . '#amp-story',
			'name'          => aioseo()->schema->context['name'],
			'headline'      => get_the_title(),
			'author'        => [
				'@id' => get_author_posts_url( $post->post_author ) . '#author'
			],
			'publisher'     => [ '@id' => trailingslashit( home_url() ) . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ],
			'image'         => $this->getFeaturedImage(),
			'datePublished' => mysql2date( DATE_W3C, $post->post_date, false ),
			'dateModified'  => mysql2date( DATE_W3C, $post->post_modified, false ),
			'inLanguage'    => aioseo()->helpers->currentLanguageCodeBCP47()
		];

		if ( ! in_array( 'PersonAuthor', aioseo()->schema->graphs, true ) ) {
			aioseo()->schema->graphs[] = 'PersonAuthor';
		}

		return $data;
	}
}