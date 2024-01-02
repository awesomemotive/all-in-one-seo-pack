<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\WebPage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RealEstateListing graph class.
 *
 * @since 4.0.0
 */
class RealEstateListing extends WebPage {
	/**
	 * The graph type.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $type = 'RealEstateListing';

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {
		$data = parent::get();
		$post = aioseo()->helpers->getPost();
		if ( ! $post ) {
			return $data;
		}

		$data['datePosted'] = mysql2date( DATE_W3C, $post->post_date, false );

		return $data;
	}
}