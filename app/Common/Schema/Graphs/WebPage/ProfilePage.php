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
	 * Returns the graph data.
	 *
	 * @since 4.5.4
	 *
	 * @return array The graph data.
	 */
	public function get() {
		$data = parent::get();

		$data['@type'] = 'ProfilePage';

		// Check if our addons need to add more data.
		$addonsPersonAuthorData = array_filter( aioseo()->addons->doAddonFunction( 'profilePage', 'get' ) );
		foreach ( $addonsPersonAuthorData as $addonPersonAuthorData ) {
			$data = array_merge( $data, $addonPersonAuthorData );
		}

		return $data;
	}
}