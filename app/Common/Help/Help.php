<?php
namespace AIOSEO\Plugin\Common\Help;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Help {
	/**
	 * Source of the documentation content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $url = 'https://cdn.aioseo.com/wp-content/docs.json';

	/**
	 * Settings.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $settings = [
		'docsUrl'          => 'https://aioseo.com/docs/',
		'supportTicketUrl' => 'https://aioseo.com/account/support/',
		'upgradeUrl'       => 'https://aioseo.com/pricing/'
	];

	/**
	 * Gets the URL for the notifications api.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL to use for the api requests.
	 */
	private function getUrl() {
		if ( defined( 'AIOSEO_DOCS_FEED_URL' ) ) {
			return AIOSEO_DOCS_FEED_URL;
		}

		return $this->url;
	}

	/**
	 * Returns the help docs for our menus.
	 *
	 * @since 4.0.0
	 *
	 * @return array The help docs.
	 */
	public function getDocs() {
		$helpDocs = aioseo()->core->networkCache->get( 'admin_help_docs' );
		if ( null !== $helpDocs ) {
			if ( is_array( $helpDocs ) ) {
				return $helpDocs;
			}

			return json_decode( $helpDocs, true );
		}

		$request = aioseo()->helpers->wpRemoteGet( $this->getUrl() );
		if ( is_wp_error( $request ) ) {
			aioseo()->core->networkCache->update( 'admin_help_docs', [], DAY_IN_SECONDS );
		}

		$helpDocs = wp_remote_retrieve_body( $request );

		aioseo()->core->networkCache->update( 'admin_help_docs', $helpDocs, WEEK_IN_SECONDS );

		return json_decode( $helpDocs, true );
	}
}