<?php
namespace AIOSEO\Plugin\Common\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the dynamic backup.
 *
 * @since 4.1.3
 */
class DynamicBackup {
	/**
	 * A the name of the option to save dynamic backups to.
	 *
	 * @since 4.1.3
	 *
	 * @var string
	 */
	protected $optionsName = 'aioseo_dynamic_settings_backup';

	/**
	 * The dynamic backup.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	protected $backup = [];

	/**
	 * Whether the backup should be updated.
	 *
	 * @since 4.1.3
	 *
	 * @var boolean
	 */
	protected $shouldBackup = false;

	/**
	 * The option defaults.
	 *
	 * @since 4.1.3
	 *
	 * @var array
	 */
	protected $defaultOptions = [];

	/**
	 * The public post types.
	 *
	 * @since 4.1.5
	 *
	 * @var array
	 */
	protected $postTypes = [];

	/**
	 * The public taxonomies.
	 *
	 * @since 4.1.5
	 *
	 * @var array
	 */
	protected $taxonomies = [];

	/**
	 * The public archives.
	 *
	 * @since 4.1.5
	 *
	 * @var array
	 */
	protected $archives = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.3
	 */
	public function __construct() {
		add_action( 'wp_loaded', [ $this, 'init' ], 5000 );
		add_action( 'shutdown', [ $this, 'updateBackup' ] );
	}

	/**
	 * Updates the backup after restoring options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function updateBackup() {
		if ( $this->shouldBackup ) {
			$this->shouldBackup = false;
			$backup = aioseo()->dynamicOptions->convertOptionsToValues( $this->backup, 'value' );
			update_option( $this->optionsName, wp_json_encode( $backup ), 'no' );
		}
	}

	/**
	 * Checks whether data from the backup has to be restored.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public function init() {
		$this->postTypes  = wp_list_pluck( aioseo()->helpers->getPublicPostTypes( false, false, true ), 'name' );
		$this->taxonomies = wp_list_pluck( aioseo()->helpers->getPublicTaxonomies( false, true ), 'name' );
		$this->archives   = wp_list_pluck( aioseo()->helpers->getPublicPostTypes( false, true, true ), 'name' );

		$backup = json_decode( get_option( $this->optionsName ), true );
		if ( empty( $backup ) ) {
			update_option( $this->optionsName, '{}', 'no' );

			return;
		}

		$this->backup         = $backup;
		$this->defaultOptions = aioseo()->dynamicOptions->getDefaults();

		$this->restorePostTypes();
		$this->restoreTaxonomies();
		$this->restoreArchives();
	}

	/**
	 * Restores the dynamic Post Types options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function restorePostTypes() {
		foreach ( $this->postTypes as $postType ) {
			// Restore the post types for Search Appearance.
			if ( ! empty( $this->backup['postTypes'][ $postType ]['searchAppearance'] ) ) {
				$this->restoreOptions( $this->backup['postTypes'][ $postType ]['searchAppearance'], [ 'searchAppearance', 'postTypes', $postType ] );
				unset( $this->backup['postTypes'][ $postType ]['searchAppearance'] );
				$this->shouldBackup = true;
			}

			// Restore the post types for Social Networks.
			if ( ! empty( $this->backup['postTypes'][ $postType ]['social']['facebook'] ) ) {
				$this->restoreOptions( $this->backup['postTypes'][ $postType ]['social']['facebook'], [ 'social', 'facebook', 'general', 'postTypes', $postType ] );
				unset( $this->backup['postTypes'][ $postType ]['social']['facebook'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the dynamic Taxonomies options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function restoreTaxonomies() {
		foreach ( $this->taxonomies as $taxonomy ) {
			// Restore the taxonomies for Search Appearance.
			if ( ! empty( $this->backup['taxonomies'][ $taxonomy ]['searchAppearance'] ) ) {
				$this->restoreOptions( $this->backup['taxonomies'][ $taxonomy ]['searchAppearance'], [ 'searchAppearance', 'taxonomies', $taxonomy ] );
				unset( $this->backup['taxonomies'][ $taxonomy ]['searchAppearance'] );
				$this->shouldBackup = true;
			}

			// Restore the taxonomies for Social Networks.
			if ( ! empty( $this->backup['taxonomies'][ $taxonomy ]['social']['facebook'] ) ) {
				$this->restoreOptions( $this->backup['taxonomies'][ $taxonomy ]['social']['facebook'], [ 'social', 'facebook', 'general', 'taxonomies', $taxonomy ] );
				unset( $this->backup['taxonomies'][ $taxonomy ]['social']['facebook'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the dynamic Archives options.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	protected function restoreArchives() {
		foreach ( $this->archives as $postType ) {
			// Restore the archives for Search Appearance.
			if ( ! empty( $this->backup['archives'][ $postType ]['searchAppearance'] ) ) {
				$this->restoreOptions( $this->backup['archives'][ $postType ]['searchAppearance'], [ 'searchAppearance', 'archives', $postType ] );
				unset( $this->backup['archives'][ $postType ]['searchAppearance'] );
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Restores the backuped options.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $backupOptions The options to be restored.
	 * @param  array $groups        The group that the option should be restored to.
	 * @return void
	 */
	protected function restoreOptions( $backupOptions, $groups ) {
		$defaultOptions = $this->defaultOptions;
		foreach ( $groups as $group ) {
			if ( ! isset( $defaultOptions[ $group ] ) ) {
				return;
			}

			$defaultOptions = $defaultOptions[ $group ];
		}

		$dynamicOptions = aioseo()->dynamicOptions->noConflict();
		foreach ( $backupOptions as $setting => $value ) {
			// Check if the option exists before proceeding. If not, it might be a group.
			$type = $defaultOptions[ $setting ]['type'] ?? '';
			if (
				! $type &&
				is_array( $value ) &&
				aioseo()->helpers->isArrayAssociative( $value )
			) {
				$nextGroups = array_merge( $groups, [ $setting ] );

				$this->restoreOptions( $backupOptions[ $setting ], $nextGroups );

				continue;
			}

			// If we still can't find the option, it might be a group.
			if ( ! $type ) {
				continue;
			}

			foreach ( $groups as $group ) {
				$dynamicOptions = $dynamicOptions->$group;
			}

			$dynamicOptions->$setting = $value;
		}
	}

	/**
	 * Maybe backup the options if it has disappeared.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	public function maybeBackup( $newOptions ) {
		$this->maybeBackupPostType( $newOptions );
		$this->maybeBackupTaxonomy( $newOptions );
		$this->maybeBackupArchives( $newOptions );
	}

	/**
	 * Maybe backup the Post Types.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	protected function maybeBackupPostType( $newOptions ) {
		// Maybe backup the post types for Search Appearance.
		foreach ( $newOptions['searchAppearance']['postTypes'] as $dynamicPostTypeName => $dynamicPostTypeSettings ) {
			$found = in_array( $dynamicPostTypeName, $this->postTypes, true );
			if ( ! $found ) {
				$this->backup['postTypes'][ $dynamicPostTypeName ]['searchAppearance'] = $dynamicPostTypeSettings;
				$this->shouldBackup = true;
			}
		}

		// Maybe backup the post types for Social Networks.
		foreach ( $newOptions['social']['facebook']['general']['postTypes'] as $dynamicPostTypeName => $dynamicPostTypeSettings ) {
			$found = in_array( $dynamicPostTypeName, $this->postTypes, true );
			if ( ! $found ) {
				$this->backup['postTypes'][ $dynamicPostTypeName ]['social']['facebook'] = $dynamicPostTypeSettings;
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Maybe backup the Taxonomies.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	protected function maybeBackupTaxonomy( $newOptions ) {
		// Maybe backup the taxonomies for Search Appearance.
		foreach ( $newOptions['searchAppearance']['taxonomies'] as $dynamicTaxonomyName => $dynamicTaxonomySettings ) {
			$found = in_array( $dynamicTaxonomyName, $this->taxonomies, true );
			if ( ! $found ) {
				$this->backup['taxonomies'][ $dynamicTaxonomyName ]['searchAppearance'] = $dynamicTaxonomySettings;
				$this->shouldBackup = true;
			}
		}
	}

	/**
	 * Maybe backup the Archives.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $newOptions An array of options to check.
	 * @return void
	 */
	protected function maybeBackupArchives( $newOptions ) {
		// Maybe backup the archives for Search Appearance.
		foreach ( $newOptions['searchAppearance']['archives'] as $archiveName => $archiveSettings ) {
			$found = in_array( $archiveName, $this->archives, true );
			if ( ! $found ) {
				$this->backup['archives'][ $archiveName ]['searchAppearance'] = $archiveSettings;
				$this->shouldBackup = true;
			}
		}
	}
}