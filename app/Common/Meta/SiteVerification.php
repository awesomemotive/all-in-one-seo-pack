<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the site verification meta tags.
 *
 * @since 4.0.0
 */
class SiteVerification {
	/**
	 * An array of webmaster tools and their meta names.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $webmasterTools = [
		'google'    => 'google-site-verification',
		'bing'      => 'msvalidate.01',
		'pinterest' => 'p:domain_verify',
		'yandex'    => 'yandex-verification',
		'baidu'     => 'baidu-site-verification'
	];

	/**
	 * Returns the robots meta tag value.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed The robots meta tag value or false.
	 */
	public function meta() {
		$metaArray = [];
		foreach ( $this->webmasterTools as $key => $metaName ) {
			$value = aioseo()->options->webmasterTools->$key;
			if ( ! empty( $value ) ) {
				$metaArray[ $metaName ] = $value;
			}
		}

		return $metaArray;
	}
}