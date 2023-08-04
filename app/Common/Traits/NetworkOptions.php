<?php
namespace AIOSEO\Plugin\Common\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options trait.
 *
 * @since 4.2.5
 */
trait NetworkOptions {
	/**
	 * Initializes the options.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	protected function init() {
		if ( ! is_multisite() ) {
			return;
		}

		aioseo()->helpers->switchToBlog( $this->helpers->getNetworkId() );

		$dbOptions = json_decode( get_option( $this->optionsName ), true );
		if ( empty( $dbOptions ) ) {
			$dbOptions = [];
		}

		$this->defaultsMerged = aioseo()->helpers->arrayReplaceRecursive( $this->defaults, $this->defaultsMerged );

		$options = aioseo()->helpers->arrayReplaceRecursive(
			$this->defaultsMerged,
			$this->addValueToValuesArray( $this->defaultsMerged, $dbOptions )
		);

		aioseo()->core->optionsCache->setOptions( $this->optionsName, $options );

		aioseo()->helpers->restoreCurrentBlog();
	}

	/**
	 * Sanitizes, then saves the options to the database.
	 *
	 * @since 4.2.5
	 *
	 * @param  array $newOptions The new options to sanitize, then save.
	 * @return void
	 */
	public function sanitizeAndSave( $newOptions ) {
		if ( ! is_multisite() ) {
			return;
		}

		if ( ! is_array( $newOptions ) ) {
			return;
		}

		$this->init();

		aioseo()->helpers->switchToBlog( $this->helpers->getNetworkId() );

		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$dbOptions     = aioseo()->helpers->arrayReplaceRecursive(
			$cachedOptions,
			$this->addValueToValuesArray( $cachedOptions, $newOptions, [], true )
		);

		// Tools.
		if ( ! empty( $newOptions['tools'] ) ) {
			if ( isset( $newOptions['tools']['robots']['rules'] ) ) {
				$dbOptions['tools']['robots']['rules']['value'] = $this->sanitizeField( $newOptions['tools']['robots']['rules'], 'array' );
			}
		}

		aioseo()->core->optionsCache->setOptions( $this->optionsName, $dbOptions );
		$this->save( true );

		aioseo()->helpers->restoreCurrentBlog();
	}
}