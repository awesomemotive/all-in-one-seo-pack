<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\WebPage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs;

/**
 * WebPage graph class.
 *
 * @since 4.0.0
 */
class WebPage extends Graphs\Graph {
	/**
	 * The graph type.
	 *
	 * This value can be overridden by WebPage child graphs that are more specific.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $type = 'WebPage';

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {
		$homeUrl = trailingslashit( home_url() );
		$data    = [
			'@type'       => $this->type,
			'@id'         => aioseo()->schema->context['url'] . '#' . strtolower( $this->type ),
			'url'         => aioseo()->schema->context['url'],
			'name'        => aioseo()->meta->title->getTitle(),
			'description' => aioseo()->schema->context['description'],
			'inLanguage'  => aioseo()->helpers->currentLanguageCodeBCP47(),
			'isPartOf'    => [ '@id' => $homeUrl . '#website' ]
		];

		$breadcrumbs = aioseo()->breadcrumbs->frontend->getBreadcrumbs() ?? '';
		if ( ! empty( $breadcrumbs ) ) {
			$data['breadcrumb'] = [ '@id' => aioseo()->schema->context['url'] . '#breadcrumblist' ];
		}

		if ( is_singular() && 'page' !== get_post_type() ) {
			$post = aioseo()->helpers->getPost();
			if ( is_a( $post, 'WP_Post' ) && post_type_supports( $post->post_type, 'author' ) ) {
				$author = get_author_posts_url( $post->post_author );
				if ( ! empty( $author ) ) {
					if ( ! in_array( 'PersonAuthor', aioseo()->schema->graphs, true ) ) {
						aioseo()->schema->graphs[] = 'PersonAuthor';
					}

					$data['author']  = [ '@id' => $author . '#author' ];
					$data['creator'] = [ '@id' => $author . '#author' ];
				}
			}
		}

		if ( isset( aioseo()->schema->context['description'] ) && aioseo()->schema->context['description'] ) {
			$data['description'] = aioseo()->schema->context['description'];
		}

		if ( is_singular() ) {
			if ( ! isset( aioseo()->schema->context['object'] ) || ! aioseo()->schema->context['object'] ) {
				return $this->getAddonData( $data, 'webPage' );
			}

			$post = aioseo()->schema->context['object'];
			if ( has_post_thumbnail( $post ) ) {
				$image = $this->image( get_post_thumbnail_id(), 'mainImage' );
				if ( $image ) {
					$data['image']              = $image;
					$data['primaryImageOfPage'] = [
						'@id' => aioseo()->schema->context['url'] . '#mainImage'
					];
				}
			}

			$data['datePublished'] = mysql2date( DATE_W3C, $post->post_date, false );
			$data['dateModified']  = mysql2date( DATE_W3C, $post->post_modified, false );

			return $this->getAddonData( $data, 'webPage' );
		}

		if ( is_front_page() ) {
			$data['about'] = [ '@id' => trailingslashit( home_url() ) . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ];
		}

		return $this->getAddonData( $data, 'webPage' );
	}
}