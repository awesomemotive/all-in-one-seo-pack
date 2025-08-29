<?php
namespace AIOSEO\Plugin\Lite\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all Vue related helper methods for Lite.
 *
 * @since 4.8.6.1
 */
trait Vue {
	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.8.6.1
	 *
	 * @param  string $page         The current page.
	 * @param  int    $staticPostId Data for a specific post.
	 * @param  string $integration  Data for integration (builder).
	 * @return array                The data.
	 */
	public function getVueData( $page = null, $staticPostId = null, $integration = null ) {
		$this->args = compact( 'page', 'staticPostId', 'integration' );
		$hash       = md5( implode( '', array_map( 'strval', $this->args ) ) );
		if ( isset( $this->cache[ $hash ] ) ) {
			return $this->cache[ $hash ];
		}

		$this->data = parent::getVueData( $page, $staticPostId, $integration );

		$this->setInitialData();

		$this->cache[ $hash ] = $this->data;

		return $this->cache[ $hash ];
	}

	/**
	 * Set Vue initial data for Lite.
	 *
	 * @since 4.8.6.1
	 *
	 * @return void
	 */
	private function setInitialData() {
		// Override the upgrade URL for Lite users
		$this->data['urls']['upgradeUrl'] = apply_filters( 'aioseo_upgrade_link', AIOSEO_MARKETING_URL . 'lite-upgrade/' );
	}
}