<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the General Settings.
 *
 * @since 4.1.4
 */
class GeneralSettings {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * List of our access control roles.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $roles = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_advanced_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->roles = aioseo()->access->getRoles();

		$this->migrateBlockMetaboxRoles();
		$this->migrateBlockContentAnalysisRoles();
		$this->migrateAttachmentRedirects();

		$settings = [
			'seopress_advanced_advanced_google'    => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'google' ] ],
			'seopress_advanced_advanced_bing'      => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'bing' ] ],
			'seopress_advanced_advanced_pinterest' => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'pinterest' ] ],
			'seopress_advanced_advanced_yandex'    => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'yandex' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates Block AIOSEO metabox setting.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateBlockMetaboxRoles() {
		$seoPressRoles = ! empty( $this->options['seopress_advanced_security_metaboxe_role'] ) ? $this->options['seopress_advanced_security_metaboxe_role'] : '';
		if ( empty( $seoPressRoles ) ) {
			return;
		}

		$roleSettings = [ 'useDefault', 'pageAnalysis', 'pageGeneralSettings', 'pageSocialSettings', 'pageSchemaSettings', 'pageAdvancedSettings' ];

		foreach ( $seoPressRoles as $wpRole => $value ) {
			$role = $this->roles[ $wpRole ];
			if ( empty( $role ) || aioseo()->access->isAdmin( $role ) ) {
				continue;
			}

			if ( aioseo()->options->accessControl->has( $role ) ) {
				foreach ( $roleSettings as $setting ) {
					aioseo()->options->accessControl->$role->$setting = false;
				}
			} elseif ( aioseo()->dynamicOptions->accessControl->has( $role ) ) {
				foreach ( $roleSettings as $setting ) {
					aioseo()->dynamicOptions->accessControl->$role->$setting = false;
				}
			}
		}
	}

	/**
	 * Migrates Block Content analysis metabox setting.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateBlockContentAnalysisRoles() {
		$seoPressRoles = ! empty( $this->options['seopress_advanced_security_metaboxe_ca_role'] ) ? $this->options['seopress_advanced_security_metaboxe_ca_role'] : '';
		if ( empty( $seoPressRoles ) ) {
			return;
		}

		$roleSettings = [ 'useDefault', 'pageAnalysis' ];

		foreach ( $seoPressRoles as $wpRole => $value ) {
			$role = $this->roles[ $wpRole ];
			if ( empty( $role ) || aioseo()->access->isAdmin( $role ) ) {
				continue;
			}

			if ( aioseo()->options->accessControl->has( $role ) ) {
				foreach ( $roleSettings as $setting ) {
					aioseo()->options->accessControl->$role->$setting = false;
				}
			} elseif ( aioseo()->dynamicOptions->accessControl->has( $role ) ) {
				foreach ( $roleSettings as $setting ) {
					aioseo()->dynamicOptions->accessControl->$role->$setting = false;
				}
			}
		}
	}

	/**
	 * Migrates redirect attachment pages settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrateAttachmentRedirects() {
		if ( ! empty( $this->options['seopress_advanced_advanced_attachments'] ) ) {
			aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = 'attachment_parent';
		}

		if ( ! empty( $this->options['seopress_advanced_advanced_attachments_file'] ) ) {
			aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = 'attachment';
		}

		if ( empty( $this->options['seopress_advanced_advanced_attachments'] ) && empty( $this->options['seopress_advanced_advanced_attachments_file'] ) ) {
			aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls = 'disabled';
		}
	}
}