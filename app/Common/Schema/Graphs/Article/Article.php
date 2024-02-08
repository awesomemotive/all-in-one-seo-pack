<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\Article;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs;

/**
 * Article graph class.
 *
 * @since 4.0.0
 */
class Article extends Graphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.2.5
	 *
	 * @param  Object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		$post = aioseo()->helpers->getPost();
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return [];
		}

		$data = [
			'@type'            => 'Article',
			'@id'              => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#article',
			'name'             => ! empty( $graphData->properties->name ) ? $graphData->properties->name : aioseo()->schema->context['name'],
			'headline'         => ! empty( $graphData->properties->headline ) ? $graphData->properties->headline : get_the_title(),
			'description'      => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'author'           => [
				'@type' => 'Person',
				'name'  => ! empty( $graphData->properties->author->name ) ? $graphData->properties->author->name : get_the_author_meta( 'display_name' ),
				'url'   => ! empty( $graphData->properties->author->url ) ? $graphData->properties->author->url : '',
			],
			'publisher'        => [ '@id' => trailingslashit( home_url() ) . '#' . aioseo()->options->searchAppearance->global->schema->siteRepresents ],
			'image'            => ! empty( $graphData->properties->image ) ? $this->image( $graphData->properties->image ) : $this->postImage( $post ),
			'datePublished'    => ! empty( $graphData->properties->dates->datePublished )
				? mysql2date( DATE_W3C, $graphData->properties->dates->datePublished, false )
				: mysql2date( DATE_W3C, $post->post_date, false ),
			'dateModified'     => ! empty( $graphData->properties->dates->dateModified )
				? mysql2date( DATE_W3C, $graphData->properties->dates->dateModified, false )
				: mysql2date( DATE_W3C, $post->post_modified, false ),
			'inLanguage'       => aioseo()->helpers->currentLanguageCodeBCP47(),
			'commentCount'     => get_comment_count( $post->ID )['approved'],
			'mainEntityOfPage' => empty( $graphData ) ? [ '@id' => aioseo()->schema->context['url'] . '#webpage' ] : '',
			'isPartOf'         => empty( $graphData ) ? [ '@id' => aioseo()->schema->context['url'] . '#webpage' ] : ''
		];

		if ( empty( $graphData->properties->author->name ) ) {
			if ( ! in_array( 'PersonAuthor', aioseo()->schema->graphs, true ) ) {
				aioseo()->schema->graphs[] = 'PersonAuthor';
			}

			$data['author'] = [
				'@id' => get_author_posts_url( $post->post_author ) . '#author'
			];
		}

		if ( ! empty( $graphData->properties->keywords ) ) {
			$keywords = json_decode( $graphData->properties->keywords, true );
			$keywords = array_map( function ( $keywordObject ) {
				return $keywordObject['value'];
			}, $keywords );
			$data['keywords'] = implode( ', ', $keywords );
		}

		if ( isset( $graphData->properties->dates->include ) && ! $graphData->properties->dates->include ) {
			unset( $data['datePublished'] );
			unset( $data['dateModified'] );
		}

		$postTaxonomies = get_post_taxonomies( $post );
		$postTerms      = [];
		foreach ( $postTaxonomies as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy );
			if ( $terms ) {
				$postTerms = array_merge( $postTerms, wp_list_pluck( $terms, 'name' ) );
			}
		}

		if ( ! empty( $postTerms ) ) {
			$data['articleSection'] = implode( ', ', $postTerms );
		}

		$pageNumber = aioseo()->helpers->getPageNumber();
		if ( 1 < $pageNumber ) {
			$data['pagination'] = $pageNumber;
		}

		return $data;
	}

	/**
	 * Returns the graph data for the post image.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post object.
	 * @return array          The image graph data.
	 */
	private function postImage( $post ) {
		$featuredImage = $this->getFeaturedImage();
		if ( $featuredImage ) {
			return $featuredImage;
		}

		preg_match_all( '#<img[^>]+src="([^">]+)"#', $post->post_content, $matches );
		if ( isset( $matches[1] ) && isset( $matches[1][0] ) ) {
			$url     = aioseo()->helpers->removeImageDimensions( $matches[1][0] );
			$imageId = aioseo()->helpers->attachmentUrlToPostId( $url );
			if ( $imageId ) {
				return $this->image( $imageId, 'articleImage' );
			} else {
				return $this->image( $url, 'articleImage' );
			}
		}

		if ( 'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents ) {
			$logo = ( new Graphs\KnowledgeGraph\KgOrganization() )->logo();
			if ( ! empty( $logo ) ) {
				$logo['@id'] = trailingslashit( home_url() ) . '#articleImage';

				return $logo;
			}
		} else {
			$avatar = $this->avatar( $post->post_author, 'articleImage' );
			if ( $avatar ) {
				return $avatar;
			}
		}

		$imageId = aioseo()->helpers->getSiteLogoId();
		if ( $imageId ) {
			return $this->image( $imageId, 'articleImage' );
		}

		return [];
	}
}