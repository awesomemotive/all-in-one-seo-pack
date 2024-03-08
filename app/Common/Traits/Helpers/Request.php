<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse the current request.
 *
 * @since 4.2.1
 */
trait Request {
	/**
	 * Get the server port.
	 *
	 * @since 4.2.1
	 *
	 * @return string The server port.
	 */
	private function getServerPort() {
		if (
			empty( $_SERVER['SERVER_PORT'] ) ||
			80 === (int) $_SERVER['SERVER_PORT'] ||
			443 === (int) $_SERVER['SERVER_PORT']
		) {
			return '';
		}

		return ':' . (int) $_SERVER['SERVER_PORT'];
	}

	/**
	 * Get the protocol.
	 *
	 * @since 4.2.1
	 *
	 * @return string The protocol.
	 */
	private function getProtocol() {
		return is_ssl() ? 'https' : 'http';
	}

	/**
	 * Get the server name (from $_SERVER['SERVER_NAME]), or use the request name ($_SERVER['HTTP_HOST']) if not present.
	 *
	 * @since 4.2.1
	 *
	 * @return string The server name.
	 */
	private function getServerName() {
		$host = $this->getRequestServerName();

		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$host = wp_unslash( $_SERVER['SERVER_NAME'] ); // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		return $host;
	}

	/**
	 * Get the request server name (from $_SERVER['HTTP_HOST]).
	 *
	 * @since 4.2.1
	 *
	 * @return string The request server name.
	 */
	private function getRequestServerName() {
		$host = '';

		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$host = $_SERVER['HTTP_HOST'];
		}

		return $host;
	}

	/**
	 * Retrieve the request URL.
	 *
	 * @since 4.2.1
	 *
	 * @return string The request URL.
	 */
	public function getRequestUrl() {
		$url = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$url = $_SERVER['REQUEST_URI'];
		}

		return rawurldecode( stripslashes( $url ) );
	}
}