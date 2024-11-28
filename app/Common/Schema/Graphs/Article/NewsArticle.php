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
	 * @param  object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		if ( ! empty( self::$overwriteGraphData[ __CLASS__ ] ) ) {
			$graphData = json_decode( wp_json_encode( wp_parse_args( self::$overwriteGraphData[ __CLASS__ ], $graphData ) ) );
		}

		$data = parent::get( $graphData );
		if ( ! $data ) {
			return [];
		}

		$data['@type'] = 'NewsArticle';
		$data['@id']   = ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#newsarticle';

		$date = ! empty( $graphData->properties->datePublished )
			? mysql2date( 'F j, Y', $graphData->properties->datePublished, false )
			: get_the_date( 'F j, Y' );
		if ( $date ) {
			// Translators: 1 - A date (e.g. September 2, 2022).
			$data['dateline'] = sprintf( __( 'Published on %1$s.', 'all-in-one-seo-pack' ), $date );
		}

		return $data;
	}
}