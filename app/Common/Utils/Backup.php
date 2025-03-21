<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup for AIOSEO Settings.
 *
 * @since 4.0.0
 */
class Backup {
	/**
	 * A the name of the option to save backups with.
	 *
	 * @since 4.00
	 *
	 * @var string
	 */
	private $optionsName = 'aioseo_settings_backup';

	/**
	 * Get all backups.
	 *
	 * @return array An array of backups.
	 */
	public function all() {
		$backups = json_decode( get_option( $this->optionsName ), true );
		if ( empty( $backups ) ) {
			$backups = [];
		}

		return $backups;
	}

	/**
	 * Creates a backup of the settings state.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function create() {
		$backupTime = time();
		$options    = $this->getOptions();

		update_option( $this->optionsName . '_' . $backupTime, wp_json_encode( $options ), 'no' );

		$backups = $this->all();

		$backups[] = $backupTime;

		update_option( $this->optionsName, wp_json_encode( $backups ), 'no' );
	}

	/**
	 * Deletes a backup of the settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function delete( $backupTime ) {
		delete_option( $this->optionsName . '_' . $backupTime );

		$backups = $this->all();

		foreach ( $backups as $key => $backup ) {
			if ( $backup === $backupTime ) {
				unset( $backups[ $key ] );
			}
		}

		update_option( $this->optionsName, wp_json_encode( array_values( $backups ) ), 'no' );
	}

	/**
	 * Restores a backup of the settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function restore( $backupTime ) {
		$backup = json_decode( get_option( $this->optionsName . '_' . $backupTime ), true );
		if ( ! empty( $backup['options']['tools']['robots']['rules'] ) ) {
			$backup['options']['tools']['robots']['rules'] = array_merge(
				aioseo()->robotsTxt->extractSearchAppearanceRules(),
				$backup['options']['tools']['robots']['rules']
			);
		}

		aioseo()->options->sanitizeAndSave( $backup['options'] );
		aioseo()->internalOptions->sanitizeAndSave( $backup['internalOptions'] );
	}

	/**
	 * Get the options to save.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of options to save.
	 */
	private function getOptions() {
		return [
			'options'         => aioseo()->options->all(),
			'internalOptions' => aioseo()->internalOptions->all()
		];
	}
}