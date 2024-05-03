<?php
namespace AIOSEO\Plugin\Common\SearchStatistics\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the trust token.
 *
 * @since   4.3.0
 * @version 4.6.2 Moved from Pro to Common.
 */
class TrustToken {
	/**
	 * Returns the trust token from the database or creates a new one & stores it.
	 *
	 * @since 4.3.0
	 *
	 * @return string The trust token.
	 */
	public function get() {
		$trustToken = aioseo()->internalOptions->internal->searchStatistics->trustToken;
		if ( empty( $trustToken ) ) {
			$trustToken = $this->generate();
			aioseo()->internalOptions->internal->searchStatistics->trustToken = $trustToken;
		}

		return $trustToken;
	}

	/**
	 * Rotates the trust token.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function rotate() {
		$trustToken = $this->generate();
		aioseo()->internalOptions->internal->searchStatistics->trustToken = $trustToken;
	}

	/**
	 * Generates a new trust token.
	 *
	 * @since 4.3.0
	 *
	 * @return string The trust token.
	 */
	public function generate() {
		return hash( 'sha512', wp_generate_password( 128, true, true ) . uniqid( '', true ) );
	}

	/**
	 * Verifies whether the passed trust token is valid or not.
	 *
	 * @since 4.3.0
	 *
	 * @param  string $passedTrustToken The trust token to validate.
	 * @return bool                     Whether the trust token is valid or not.
	 */
	public function validate( $passedTrustToken = '' ) {
		$trustToken = $this->get();

		return hash_equals( $trustToken, $passedTrustToken );
	}
}