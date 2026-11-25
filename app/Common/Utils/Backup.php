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
	 * The database lock that serializes writes to the backup list.
	 *
	 * @since 5.0.6
	 *
	 * @var string
	 */
	private $lockName = 'aioseo_settings_backup_lock';

	/**
	 * Get all backups.
	 *
	 * @return array An array of backups.
	 */
	public function all() {
		$rawBackups = get_option( $this->optionsName );
		$backups    = is_string( $rawBackups ) ? json_decode( $rawBackups, true ) : $rawBackups;
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
		// Concurrent requests would otherwise read the same list and pick the same free timestamp.
		$locked = aioseo()->core->db->acquireLock( $this->lockName, 10 );

		try {
			$backups    = $this->all();
			$backupTime = time();
			$options    = $this->getOptions();

			// Backups are keyed on their timestamp, so a same-second backup would overwrite the previous one.
			while ( in_array( $backupTime, $backups, true ) ) {
				$backupTime++;
			}

			update_option( $this->optionsName . '_' . $backupTime, wp_json_encode( $options ), 'no' );

			$backups[] = $backupTime;

			update_option( $this->optionsName, wp_json_encode( $backups ), 'no' );
		} finally {
			if ( $locked ) {
				aioseo()->core->db->releaseLock( $this->lockName );
			}
		}
	}

	/**
	 * Deletes a backup of the settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function delete( $backupTime ) {
		$locked = aioseo()->core->db->acquireLock( $this->lockName, 10 );

		try {
			delete_option( $this->optionsName . '_' . $backupTime );

			$backups = $this->all();

			foreach ( $backups as $key => $backup ) {
				if ( (int) $backup === (int) $backupTime ) {
					unset( $backups[ $key ] );
				}
			}

			update_option( $this->optionsName, wp_json_encode( array_values( $backups ) ), 'no' );
		} finally {
			if ( $locked ) {
				aioseo()->core->db->releaseLock( $this->lockName );
			}
		}
	}

	/**
	 * Checks whether a backup is in the list of backups.
	 *
	 * @since 5.0.6
	 *
	 * @param  int  $backupTime The backup timestamp.
	 * @return bool             Whether the backup exists.
	 */
	public function exists( $backupTime ) {
		return in_array( (int) $backupTime, array_map( 'intval', $this->all() ), true );
	}

	/**
	 * Restores a backup of the settings.
	 *
	 * @since   4.0.0
	 * @version 5.0.6 Returns whether the backup was restored.
	 *
	 * @return bool Whether the backup was restored.
	 */
	public function restore( $backupTime ) {
		$rawBackup = get_option( $this->optionsName . '_' . $backupTime );
		$backup    = is_string( $rawBackup ) ? json_decode( $rawBackup, true ) : $rawBackup;
		if ( ! is_array( $backup ) || ! is_array( $backup['options'] ?? null ) || ! is_array( $backup['internalOptions'] ?? null ) ) {
			return false;
		}

		if ( ! empty( $backup['options']['tools']['robots']['rules'] ) ) {
			$backup['options']['tools']['robots']['rules'] = array_merge(
				aioseo()->robotsTxt->extractSearchAppearanceRules(),
				$backup['options']['tools']['robots']['rules']
			);
		}

		aioseo()->options->sanitizeAndSave( $backup['options'] );
		aioseo()->internalOptions->sanitizeAndSave( $backup['internalOptions'] );

		return true;
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