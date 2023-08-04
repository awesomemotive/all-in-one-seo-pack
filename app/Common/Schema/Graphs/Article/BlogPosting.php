<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\Article;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blog Posting graph class.
 *
 * @since 4.0.0
 */
class BlogPosting extends Article {
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

		$data['@type'] = 'BlogPosting';
		$data['@id']   = ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#blogposting';

		return $data;
	}
}