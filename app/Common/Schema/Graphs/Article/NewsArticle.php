<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\Article;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * News Article graph class.
 *
 * @since 4.0.0
 */
class NewsArticle extends Article {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		$data = parent::get( $graphData );
		if ( ! $data ) {
			return [];
		}

		$data['@type']    = 'NewsArticle';
		$data['@id']      = ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#newsarticle';
		// Translators: 1 - The date the article was published on.
		$data['dateline'] = ! empty( $graphData->properties->datePublished )
			// Translators: 1 - A data (e.g. September 2, 2022).
			? sprintf( __( 'Published on %1$s.', 'all-in-one-seo-pack' ), mysql2date( 'F j, Y', $graphData->properties->datePublished, false ) )
			// Translators: 1 - A data (e.g. September 2, 2022).
			: sprintf( __( 'Published on %1$s.', 'all-in-one-seo-pack' ), get_the_date( 'F j, Y' ) );

		return $data;
	}
}