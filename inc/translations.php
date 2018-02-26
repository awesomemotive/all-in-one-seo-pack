<?php

if ( ! class_exists( 'AIOSEOP_Translations' ) ) :

	/**
	 * Class AIOSEOP_Translations
	 *
	 * @since 2.3.5
	 */
	class AIOSEOP_Translations {

		public $current_locale = '';

		public $url = 'https://translate.wordpress.org/api/projects/wp-plugins/all-in-one-seo-pack/dev';

		public $name = '';

		/**
		 * Loop through the locale info.
		 *
		 * @since 2.3.5
		 * @access public
		 * @var string $wplocale Information for a particular locale (in loop)
		 */
		public $wplocale = '';

		public $translated_count = 0;

		public $translation_url = 'https://translate.wordpress.org/projects/wp-plugins/all-in-one-seo-pack';

		public $slug = '';

		public $percent_translated = '';

		public $native_name = '';

		/**
		 * AIOSEOP_Translations constructor.
		 *
		 * @since 2.3.5
		 *
		 */
		public function __construct() {

			$this->current_locale = get_locale();

			if( $this->current_locale === 'en_US'){
				return;
			}

			$this->init();

		}

		/**
		 * Fetch locale data from WP.
		 *
		 * @since 2.3.5
		 * @since 2.3.6 Return FALSE on WP_Error object.
		 *
		 * @return mixed
		 */
		private function get_locale_data() {
			$response = wp_remote_get( $this->url );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			return $response['body'];
		}


		/**
		 *
		 * @since 2.3.5
		 *
		 * @param $locales
		 */
		private function set_current_locale_data( $locales ) {

			// Some locales are missing the locale code (wp_locale) so we need to check for that.

			foreach ( $locales as $locale ) {

				$wplocale = '';

				if ( isset( $locale->wp_locale ) ) {
					$wplocale = $locale->wp_locale;
				}

				if ( $wplocale === $this->current_locale ) {

					$name               = '';
					$percent_translated = '';

					if ( isset( $locale->name ) ) {
						$name = $locale->name;
					}

					if ( isset( $locale->percent_translated ) ) {
						$percent_translated = $locale->percent_translated;
					}

					$this->name               = $name;
					$this->wplocale           = $wplocale;
					$this->percent_translated = $percent_translated;
					$this->slug               = $locale->locale;

				}
			}

		}

		/**
		 *
		 * @since 2.3.5
		 *
		 * @param $locales
		 *
		 * @return int
		 */
		private function count_translated_languages( $locales ) {

			$count = 0;

			foreach ( $locales as $locale ) {

				if ( $locale->percent_translated > 0 ) {
					++ $count;
				}
			}

			return $count;
		}

		/**
		 *
		 *
		 * @since 2.3.5
		 */
		private function set_translation_url() {

			if ( null !== $this->wplocale ) {

				$url = "https://translate.wordpress.org/projects/wp-plugins/all-in-one-seo-pack/dev/$this->slug/default";

				$this->translation_url = $url;
			}

		}

		/**
		 * Gets and sets the native language.
		 *
		 * @since 2.3.12.1
		 */
		function set_native_language() {
			if ( file_exists( ABSPATH . 'wp-admin/includes/translation-install.php' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			} else {
				return;
			}
			if ( function_exists( 'wp_get_available_translations' ) ) {
				$translations      = wp_get_available_translations();
				$this->native_name = $translations[ $this->current_locale ]['native_name'];
			}
		}

		/**
		 *
		 * @since 2.3.5
		 * @since 2.3.6 Return FALSE on WP_Error object in get_locale_data().
		 * @since 2.3.12.1 set_native_language()
		 *
		 */
		private function init() {

			$json = $this->get_locale_data();

			if ( $json === false ) {
				return false;
			}

			$translation_data = json_decode( $json );

			$locales = $translation_data->translation_sets;

			$this->set_current_locale_data( $locales );

			$this->translated_count = $this->count_translated_languages( $locales );

			$this->set_translation_url();

			$this->set_native_language();
		}
	}

endif; // End class_exists check.
