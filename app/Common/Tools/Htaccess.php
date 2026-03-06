<?php
namespace AIOSEO\Plugin\Common\Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Htaccess {
	/**
	 * The path to the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->path = ABSPATH . '.htaccess';
	}

	/**
	 * Get the contents of the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @return string The contents of the file.
	 */
	public function getContents() {
		$fs = aioseo()->core->fs;
		if ( ! $fs->exists( $this->path ) ) {
			return false;
		}

		$contents = $fs->getContents( $this->path );

		return aioseo()->helpers->encodeOutputHtml( $contents );
	}

	/**
	 * Saves the contents of the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $contents The contents to write.
	 * @return boolean           True if the file was updated.
	 */
	public function saveContents( $contents ) {
		$fs = aioseo()->core->fs;
		if ( ! $fs->isWritable( $this->path ) ) {
			return [
				'success' => false,
				'reason'  => 'file-not-writable',
				'message' => __( 'We were unable to save the .htaccess file because the file was not writable. Please check the file permissions and try again.', 'all-in-one-seo-pack' )
			];
		}

		$fileExists       = $fs->exists( $this->path );
		$originalContents = $fileExists ? $fs->getContents( $this->path ) : null;
		$fileSaved        = $fs->putContents( $this->path, $contents );
		if ( false === $fileSaved ) {
			return [
				'success' => false,
				'reason'  => 'file-not-saved'
			];
		}

		$response       = wp_safe_remote_get( home_url( '?' . time() ) );
		$isValidRequest = wp_remote_retrieve_response_code( $response );

		if (
			// Add an exception for Windows devs since the request fails in Local.
			! defined( 'AIOSEO_DEV_WINDOWS' ) &&
			( is_wp_error( $response ) || 200 !== $isValidRequest )
		) {
			$fs->putContents( $this->path, $originalContents );

			return [
				'success' => false,
				'reason'  => 'syntax-errors',
				'message' => __( 'We were unable to save the .htaccess file due to syntax errors. Please check the code below and try again.', 'all-in-one-seo-pack' )
			];
		}

		return [
			'success' => true
		];
	}
}