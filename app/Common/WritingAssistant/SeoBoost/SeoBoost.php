<?php
namespace AIOSEO\Plugin\Common\WritingAssistant\SeoBoost;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the connection with SEOBoost.
 *
 * @since 4.7.4
 */
class SeoBoost {
	/**
	 * URL of the login page.
	 *
	 * @since 4.7.4
	 */
	private $loginUrl = 'https://app.seoboost.com/login/';

	/**
	 * URL of the Create Account page.
	 *
	 * @since 4.7.4
	 */
	private $createAccountUrl = 'https://seoboost.com/checkout/';

	/**
	 * The service.
	 *
	 * @since 4.7.4
	 *
	 * @var Service
	 */
	public $service;

	/**
	 * Class constructor.
	 *
	 * @since 4.7.4
	 */
	public function __construct() {
		$this->service = new Service();

		$returnParam = isset( $_GET['aioseo-writing-assistant'] ) // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['aioseo-writing-assistant'] ) ) // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			: null;

		if ( 'auth_return' === $returnParam ) {
			add_action( 'init', [ $this, 'checkToken' ], 50 );
		}

		if ( 'ms_logged_in' === $returnParam ) {
			add_action( 'init', [ $this, 'marketingSiteCallback' ], 50 );
		}

		add_action( 'init', [ $this, 'migrateUserData' ], 10 );
		add_action( 'init', [ $this, 'refreshUserOptionsAfterError' ] );
	}

	/**
	 * Returns if the user has an access key.
	 *
	 * @since 4.7.4
	 *
	 * @return bool
	 */
	public function isLoggedIn() {
		return $this->getAccessToken() !== '';
	}

	/**
	 * Gets the login URL.
	 *
	 * @since 4.7.4
	 *
	 * @return string The login URL.
	 */
	public function getLoginUrl() {
		$url = $this->loginUrl;
		if ( defined( 'AIOSEO_WRITING_ASSISTANT_LOGIN_URL' ) ) {
			$url = AIOSEO_WRITING_ASSISTANT_LOGIN_URL;
		}

		$params = [
			'oauth'    => true,
			'redirect' => get_site_url() . '?' . build_query( [ 'aioseo-writing-assistant' => 'auth_return' ] ),
			'domain'   => aioseo()->helpers->getMultiSiteDomain()
		];

		return trailingslashit( $url ) . '?' . build_query( $params );
	}

	/**
	 * Gets the login URL.
	 *
	 * @since 4.7.4
	 *
	 * @return string The login URL.
	 */
	public function getCreateAccountUrl() {
		$url = $this->createAccountUrl;
		if ( defined( 'AIOSEO_WRITING_ASSISTANT_CREATE_ACCOUNT_URL' ) ) {
			$url = AIOSEO_WRITING_ASSISTANT_CREATE_ACCOUNT_URL;
		}

		$params = [
			'url'                        => base64_encode( get_site_url() . '?' . build_query( [ 'aioseo-writing-assistant' => 'ms_logged_in' ] ) ),
			'writing-assistant-checkout' => true
		];

		return trailingslashit( $url ) . '?' . build_query( $params );
	}

	/**
	 * Gets the user's access token.
	 *
	 * @since 4.7.4
	 *
	 * @return string The access token.
	 */
	public function getAccessToken() {
		$metaKey = 'seoboost_access_token_' . get_current_blog_id();

		return get_user_meta( get_current_user_id(), $metaKey, true );
	}

	/**
	 * Sets the user's access token.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function setAccessToken( $accessToken ) {
		$metaKey = 'seoboost_access_token_' . get_current_blog_id();
		update_user_meta( get_current_user_id(), $metaKey, $accessToken );

		$this->refreshUserOptions();
	}

	/**
	 * Refreshes user options from SEOBoost.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function refreshUserOptions() {
		$userOptions = $this->service->getUserOptions();
		if ( is_wp_error( $userOptions ) || ! empty( $userOptions['error'] ) ) {
			$userOptions = $this->getDefaultUserOptions();

			aioseo()->cache->update( 'seoboost_get_user_options_error', time() + DAY_IN_SECONDS, MONTH_IN_SECONDS );
		}

		$this->setUserOptions( $userOptions );
	}

	/**
	 * Gets the user options.
	 *
	 * @since 4.7.4
	 *
	 * @param  bool  $refresh Whether to refresh the user options.
	 * @return array          The user options.
	 */
	public function getUserOptions( $refresh = false ) {
		if ( ! $refresh ) {
			$metaKey     = 'seoboost_user_options_' . get_current_blog_id();
			$userOptions = get_user_meta( get_current_user_id(), $metaKey, true );

			if ( ! empty( $userOptions ) ) {
				return json_decode( (string) $userOptions, true ) ?? [];
			}
		}

		// If there are no options or we need to refresh them, get them from SEOBoost.
		$this->refreshUserOptions();

		$userOptions = $this->getUserOptions();
		if ( empty( $userOptions ) ) {
			return $this->getDefaultUserOptions();
		}

		return $userOptions;
	}

	/**
	 * Gets the user options.
	 *
	 * @since 4.7.4
	 *
	 * @param  array $options The user options.
	 * @return void
	 */
	public function setUserOptions( $options ) {
		if ( ! is_array( $options ) ) {
			return;
		}

		$userOptions = array_merge( $this->getDefaultUserOptions(), $options );
		$metaKey     = 'seoboost_user_options_' . get_current_blog_id();

		update_user_meta( get_current_user_id(), $metaKey, wp_json_encode( $userOptions ) );
	}

	/**
	 * Gets the user info from SEOBoost.
	 *
	 * @since 4.7.4
	 *
	 * @return array|\WP_Error The user info or a WP_Error.
	 */
	public function getUserInfo() {
		return $this->service->getUserInfo();
	}

	/**
	 * Checks the token.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function checkToken() {
		$authToken = isset( $_GET['token'] ) // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			? sanitize_key( wp_unslash( $_GET['token'] ) ) // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			: null;

		if ( $authToken ) {
			$accessToken = $this->service->getAccessToken( $authToken );

			if ( ! is_wp_error( $accessToken ) && ! empty( $accessToken['token'] ) ) {
				$this->setAccessToken( $accessToken['token'] );
				?>
				<script>
					// Send message to parent window.
					window.opener.postMessage('seoboost-authenticated', '*');
				</script>
				<?php
			}
		}
		?>
		<script>
			// Close window.
			window.close();
		</script>
		<?php
		die;
	}

	/**
	 * Handles the callback from the marketing site after completing authentication.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function marketingSiteCallback() {
		?>
		<script>
			// Send message to parent window.
			window.opener.postMessage('seoboost-ms-logged-in', '*');
			window.close();
		</script>
		<?php
	}

	/**
	 * Resets the logins.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function resetLogins() {
		// Delete access token and user options from the database.
		aioseo()->core->db->delete( 'usermeta' )->whereRaw( 'meta_key LIKE \'seoboost_access_token%\'' )->run();
		aioseo()->core->db->delete( 'usermeta' )->where( 'meta_key', 'seoboost_user_options' )->run();
	}

	/**
	 * Gets the report history.
	 *
	 * @since 4.7.4
	 *
	 * @return array|\WP_Error The report history.
	 */
	public function getReportHistory() {
		return $this->service->getReportHistory();
	}

	/**
	 * Migrate Writing Assistant access tokens.
	 * This handles the fix for multisites where subsites all used the same workspace/account.
	 *
	 * @since 4.7.7
	 *
	 * @return void
	 */
	public function migrateUserData() {
		$userToken = get_user_meta( get_current_user_id(), 'seoboost_access_token', true );
		if ( ! empty( $userToken ) ) {
			$this->setAccessToken( $userToken );
			delete_user_meta( get_current_user_id(), 'seoboost_access_token' );
		}

		$userOptions = get_user_meta( get_current_user_id(), 'seoboost_user_options', true );
		if ( ! empty( $userOptions ) ) {
			$this->setUserOptions( $userOptions );
			delete_user_meta( get_current_user_id(), 'seoboost_user_options' );
		}
	}

	/**
	 * Refreshes user options after an error.
	 * This needs to run on init since service class is not available in the constructor.
	 *
	 * @since 4.7.7.2
	 *
	 * @return void
	 */
	public function refreshUserOptionsAfterError() {
		$userOptionsFetchError = aioseo()->cache->get( 'seoboost_get_user_options_error' );
		if ( $userOptionsFetchError && time() > $userOptionsFetchError ) {
			aioseo()->cache->delete( 'seoboost_get_user_options_error' );

			$this->refreshUserOptions();
		}
	}

	/**
	 * Returns the default user options.
	 *
	 * @since 4.7.7.1
	 *
	 * @return array The default user options.
	 */
	private function getDefaultUserOptions() {
		return [
			'language'      => 'en',
			'country'       => 'US',
			'countries'     => $this->getCountries(),
			'languages'     => $this->getLanguages(),
			'searchEngines' => $this->getSearchEngines()
		];
	}

	/**
	 * Returns the list of countries.
	 *
	 * @since 4.7.7.1
	 *
	 * @return array The list of countries.
	 */
	private function getCountries() {
		$countries = [
			'AF' => __( 'Afghanistan', 'all-in-one-seo-pack' ),
			'AL' => __( 'Albania', 'all-in-one-seo-pack' ),
			'DZ' => __( 'Algeria', 'all-in-one-seo-pack' ),
			'AS' => __( 'American Samoa', 'all-in-one-seo-pack' ),
			'AD' => __( 'Andorra', 'all-in-one-seo-pack' ),
			'AO' => __( 'Angola', 'all-in-one-seo-pack' ),
			'AI' => __( 'Anguilla', 'all-in-one-seo-pack' ),
			'AG' => __( 'Antigua & Barbuda', 'all-in-one-seo-pack' ),
			'AR' => __( 'Argentina', 'all-in-one-seo-pack' ),
			'AM' => __( 'Armenia', 'all-in-one-seo-pack' ),
			'AU' => __( 'Australia', 'all-in-one-seo-pack' ),
			'AT' => __( 'Austria', 'all-in-one-seo-pack' ),
			'AZ' => __( 'Azerbaijan', 'all-in-one-seo-pack' ),
			'BS' => __( 'Bahamas', 'all-in-one-seo-pack' ),
			'BH' => __( 'Bahrain', 'all-in-one-seo-pack' ),
			'BD' => __( 'Bangladesh', 'all-in-one-seo-pack' ),
			'BY' => __( 'Belarus', 'all-in-one-seo-pack' ),
			'BE' => __( 'Belgium', 'all-in-one-seo-pack' ),
			'BZ' => __( 'Belize', 'all-in-one-seo-pack' ),
			'BJ' => __( 'Benin', 'all-in-one-seo-pack' ),
			'BT' => __( 'Bhutan', 'all-in-one-seo-pack' ),
			'BO' => __( 'Bolivia', 'all-in-one-seo-pack' ),
			'BA' => __( 'Bosnia & Herzegovina', 'all-in-one-seo-pack' ),
			'BW' => __( 'Botswana', 'all-in-one-seo-pack' ),
			'BR' => __( 'Brazil', 'all-in-one-seo-pack' ),
			'VG' => __( 'British Virgin Islands', 'all-in-one-seo-pack' ),
			'BN' => __( 'Brunei', 'all-in-one-seo-pack' ),
			'BG' => __( 'Bulgaria', 'all-in-one-seo-pack' ),
			'BF' => __( 'Burkina Faso', 'all-in-one-seo-pack' ),
			'BI' => __( 'Burundi', 'all-in-one-seo-pack' ),
			'KH' => __( 'Cambodia', 'all-in-one-seo-pack' ),
			'CM' => __( 'Cameroon', 'all-in-one-seo-pack' ),
			'CA' => __( 'Canada', 'all-in-one-seo-pack' ),
			'CV' => __( 'Cape Verde', 'all-in-one-seo-pack' ),
			'CF' => __( 'Central African Republic', 'all-in-one-seo-pack' ),
			'TD' => __( 'Chad', 'all-in-one-seo-pack' ),
			'CL' => __( 'Chile', 'all-in-one-seo-pack' ),
			'CO' => __( 'Colombia', 'all-in-one-seo-pack' ),
			'CG' => __( 'Congo - Brazzaville', 'all-in-one-seo-pack' ),
			'CD' => __( 'Congo - Kinshasa', 'all-in-one-seo-pack' ),
			'CK' => __( 'Cook Islands', 'all-in-one-seo-pack' ),
			'CR' => __( 'Costa Rica', 'all-in-one-seo-pack' ),
			'CI' => __( 'Côte d’Ivoire', 'all-in-one-seo-pack' ),
			'HR' => __( 'Croatia', 'all-in-one-seo-pack' ),
			'CU' => __( 'Cuba', 'all-in-one-seo-pack' ),
			'CY' => __( 'Cyprus', 'all-in-one-seo-pack' ),
			'CZ' => __( 'Czechia', 'all-in-one-seo-pack' ),
			'DK' => __( 'Denmark', 'all-in-one-seo-pack' ),
			'DJ' => __( 'Djibouti', 'all-in-one-seo-pack' ),
			'DM' => __( 'Dominica', 'all-in-one-seo-pack' ),
			'DO' => __( 'Dominican Republic', 'all-in-one-seo-pack' ),
			'EC' => __( 'Ecuador', 'all-in-one-seo-pack' ),
			'EG' => __( 'Egypt', 'all-in-one-seo-pack' ),
			'SV' => __( 'El Salvador', 'all-in-one-seo-pack' ),
			'EE' => __( 'Estonia', 'all-in-one-seo-pack' ),
			'ET' => __( 'Ethiopia', 'all-in-one-seo-pack' ),
			'FJ' => __( 'Fiji', 'all-in-one-seo-pack' ),
			'FI' => __( 'Finland', 'all-in-one-seo-pack' ),
			'FR' => __( 'France', 'all-in-one-seo-pack' ),
			'GA' => __( 'Gabon', 'all-in-one-seo-pack' ),
			'GM' => __( 'Gambia', 'all-in-one-seo-pack' ),
			'GE' => __( 'Georgia', 'all-in-one-seo-pack' ),
			'DE' => __( 'Germany', 'all-in-one-seo-pack' ),
			'GH' => __( 'Ghana', 'all-in-one-seo-pack' ),
			'GI' => __( 'Gibraltar', 'all-in-one-seo-pack' ),
			'GR' => __( 'Greece', 'all-in-one-seo-pack' ),
			'GL' => __( 'Greenland', 'all-in-one-seo-pack' ),
			'GT' => __( 'Guatemala', 'all-in-one-seo-pack' ),
			'GG' => __( 'Guernsey', 'all-in-one-seo-pack' ),
			'GY' => __( 'Guyana', 'all-in-one-seo-pack' ),
			'HT' => __( 'Haiti', 'all-in-one-seo-pack' ),
			'HN' => __( 'Honduras', 'all-in-one-seo-pack' ),
			'HK' => __( 'Hong Kong', 'all-in-one-seo-pack' ),
			'HU' => __( 'Hungary', 'all-in-one-seo-pack' ),
			'IS' => __( 'Iceland', 'all-in-one-seo-pack' ),
			'IN' => __( 'India', 'all-in-one-seo-pack' ),
			'ID' => __( 'Indonesia', 'all-in-one-seo-pack' ),
			'IQ' => __( 'Iraq', 'all-in-one-seo-pack' ),
			'IE' => __( 'Ireland', 'all-in-one-seo-pack' ),
			'IM' => __( 'Isle of Man', 'all-in-one-seo-pack' ),
			'IL' => __( 'Israel', 'all-in-one-seo-pack' ),
			'IT' => __( 'Italy', 'all-in-one-seo-pack' ),
			'JM' => __( 'Jamaica', 'all-in-one-seo-pack' ),
			'JP' => __( 'Japan', 'all-in-one-seo-pack' ),
			'JE' => __( 'Jersey', 'all-in-one-seo-pack' ),
			'JO' => __( 'Jordan', 'all-in-one-seo-pack' ),
			'KZ' => __( 'Kazakhstan', 'all-in-one-seo-pack' ),
			'KE' => __( 'Kenya', 'all-in-one-seo-pack' ),
			'KI' => __( 'Kiribati', 'all-in-one-seo-pack' ),
			'KW' => __( 'Kuwait', 'all-in-one-seo-pack' ),
			'KG' => __( 'Kyrgyzstan', 'all-in-one-seo-pack' ),
			'LA' => __( 'Laos', 'all-in-one-seo-pack' ),
			'LV' => __( 'Latvia', 'all-in-one-seo-pack' ),
			'LB' => __( 'Lebanon', 'all-in-one-seo-pack' ),
			'LS' => __( 'Lesotho', 'all-in-one-seo-pack' ),
			'LY' => __( 'Libya', 'all-in-one-seo-pack' ),
			'LI' => __( 'Liechtenstein', 'all-in-one-seo-pack' ),
			'LT' => __( 'Lithuania', 'all-in-one-seo-pack' ),
			'LU' => __( 'Luxembourg', 'all-in-one-seo-pack' ),
			'MG' => __( 'Madagascar', 'all-in-one-seo-pack' ),
			'MW' => __( 'Malawi', 'all-in-one-seo-pack' ),
			'MY' => __( 'Malaysia', 'all-in-one-seo-pack' ),
			'MV' => __( 'Maldives', 'all-in-one-seo-pack' ),
			'ML' => __( 'Mali', 'all-in-one-seo-pack' ),
			'MT' => __( 'Malta', 'all-in-one-seo-pack' ),
			'MU' => __( 'Mauritius', 'all-in-one-seo-pack' ),
			'MX' => __( 'Mexico', 'all-in-one-seo-pack' ),
			'FM' => __( 'Micronesia', 'all-in-one-seo-pack' ),
			'MD' => __( 'Moldova', 'all-in-one-seo-pack' ),
			'MN' => __( 'Mongolia', 'all-in-one-seo-pack' ),
			'ME' => __( 'Montenegro', 'all-in-one-seo-pack' ),
			'MS' => __( 'Montserrat', 'all-in-one-seo-pack' ),
			'MA' => __( 'Morocco', 'all-in-one-seo-pack' ),
			'MZ' => __( 'Mozambique', 'all-in-one-seo-pack' ),
			'MM' => __( 'Myanmar (Burma)', 'all-in-one-seo-pack' ),
			'NA' => __( 'Namibia', 'all-in-one-seo-pack' ),
			'NR' => __( 'Nauru', 'all-in-one-seo-pack' ),
			'NP' => __( 'Nepal', 'all-in-one-seo-pack' ),
			'NL' => __( 'Netherlands', 'all-in-one-seo-pack' ),
			'NZ' => __( 'New Zealand', 'all-in-one-seo-pack' ),
			'NI' => __( 'Nicaragua', 'all-in-one-seo-pack' ),
			'NE' => __( 'Niger', 'all-in-one-seo-pack' ),
			'NG' => __( 'Nigeria', 'all-in-one-seo-pack' ),
			'NU' => __( 'Niue', 'all-in-one-seo-pack' ),
			'MK' => __( 'North Macedonia', 'all-in-one-seo-pack' ),
			'NO' => __( 'Norway', 'all-in-one-seo-pack' ),
			'OM' => __( 'Oman', 'all-in-one-seo-pack' ),
			'PK' => __( 'Pakistan', 'all-in-one-seo-pack' ),
			'PS' => __( 'Palestine', 'all-in-one-seo-pack' ),
			'PA' => __( 'Panama', 'all-in-one-seo-pack' ),
			'PG' => __( 'Papua New Guinea', 'all-in-one-seo-pack' ),
			'PY' => __( 'Paraguay', 'all-in-one-seo-pack' ),
			'PE' => __( 'Peru', 'all-in-one-seo-pack' ),
			'PH' => __( 'Philippines', 'all-in-one-seo-pack' ),
			'PN' => __( 'Pitcairn Islands', 'all-in-one-seo-pack' ),
			'PL' => __( 'Poland', 'all-in-one-seo-pack' ),
			'PT' => __( 'Portugal', 'all-in-one-seo-pack' ),
			'PR' => __( 'Puerto Rico', 'all-in-one-seo-pack' ),
			'QA' => __( 'Qatar', 'all-in-one-seo-pack' ),
			'RO' => __( 'Romania', 'all-in-one-seo-pack' ),
			'RU' => __( 'Russia', 'all-in-one-seo-pack' ),
			'RW' => __( 'Rwanda', 'all-in-one-seo-pack' ),
			'WS' => __( 'Samoa', 'all-in-one-seo-pack' ),
			'SM' => __( 'San Marino', 'all-in-one-seo-pack' ),
			'ST' => __( 'São Tomé & Príncipe', 'all-in-one-seo-pack' ),
			'SA' => __( 'Saudi Arabia', 'all-in-one-seo-pack' ),
			'SN' => __( 'Senegal', 'all-in-one-seo-pack' ),
			'RS' => __( 'Serbia', 'all-in-one-seo-pack' ),
			'SC' => __( 'Seychelles', 'all-in-one-seo-pack' ),
			'SL' => __( 'Sierra Leone', 'all-in-one-seo-pack' ),
			'SG' => __( 'Singapore', 'all-in-one-seo-pack' ),
			'SK' => __( 'Slovakia', 'all-in-one-seo-pack' ),
			'SI' => __( 'Slovenia', 'all-in-one-seo-pack' ),
			'SB' => __( 'Solomon Islands', 'all-in-one-seo-pack' ),
			'SO' => __( 'Somalia', 'all-in-one-seo-pack' ),
			'ZA' => __( 'South Africa', 'all-in-one-seo-pack' ),
			'KR' => __( 'South Korea', 'all-in-one-seo-pack' ),
			'ES' => __( 'Spain', 'all-in-one-seo-pack' ),
			'LK' => __( 'Sri Lanka', 'all-in-one-seo-pack' ),
			'SH' => __( 'St. Helena', 'all-in-one-seo-pack' ),
			'VC' => __( 'St. Vincent & Grenadines', 'all-in-one-seo-pack' ),
			'SR' => __( 'Suriname', 'all-in-one-seo-pack' ),
			'SE' => __( 'Sweden', 'all-in-one-seo-pack' ),
			'CH' => __( 'Switzerland', 'all-in-one-seo-pack' ),
			'TW' => __( 'Taiwan', 'all-in-one-seo-pack' ),
			'TJ' => __( 'Tajikistan', 'all-in-one-seo-pack' ),
			'TZ' => __( 'Tanzania', 'all-in-one-seo-pack' ),
			'TH' => __( 'Thailand', 'all-in-one-seo-pack' ),
			'TL' => __( 'Timor-Leste', 'all-in-one-seo-pack' ),
			'TG' => __( 'Togo', 'all-in-one-seo-pack' ),
			'TO' => __( 'Tonga', 'all-in-one-seo-pack' ),
			'TT' => __( 'Trinidad & Tobago', 'all-in-one-seo-pack' ),
			'TN' => __( 'Tunisia', 'all-in-one-seo-pack' ),
			'TR' => __( 'Turkey', 'all-in-one-seo-pack' ),
			'TM' => __( 'Turkmenistan', 'all-in-one-seo-pack' ),
			'VI' => __( 'U.S. Virgin Islands', 'all-in-one-seo-pack' ),
			'UG' => __( 'Uganda', 'all-in-one-seo-pack' ),
			'UA' => __( 'Ukraine', 'all-in-one-seo-pack' ),
			'AE' => __( 'United Arab Emirates', 'all-in-one-seo-pack' ),
			'GB' => __( 'United Kingdom', 'all-in-one-seo-pack' ),
			'US' => __( 'United States', 'all-in-one-seo-pack' ),
			'UY' => __( 'Uruguay', 'all-in-one-seo-pack' ),
			'UZ' => __( 'Uzbekistan', 'all-in-one-seo-pack' ),
			'VU' => __( 'Vanuatu', 'all-in-one-seo-pack' ),
			'VE' => __( 'Venezuela', 'all-in-one-seo-pack' ),
			'VN' => __( 'Vietnam', 'all-in-one-seo-pack' ),
			'ZM' => __( 'Zambia', 'all-in-one-seo-pack' ),
			'ZW' => __( 'Zimbabwe', 'all-in-one-seo-pack' )
		];

		return $countries;
	}

	/**
	 * Returns the list of languages.
	 *
	 * @since 4.7.7.1
	 *
	 * @return array The list of languages.
	 */
	private function getLanguages() {
		$languages = [
			'ca' => __( 'Catalan', 'all-in-one-seo-pack' ),
			'da' => __( 'Danish', 'all-in-one-seo-pack' ),
			'nl' => __( 'Dutch', 'all-in-one-seo-pack' ),
			'en' => __( 'English', 'all-in-one-seo-pack' ),
			'fr' => __( 'French', 'all-in-one-seo-pack' ),
			'de' => __( 'German', 'all-in-one-seo-pack' ),
			'id' => __( 'Indonesian', 'all-in-one-seo-pack' ),
			'it' => __( 'Italian', 'all-in-one-seo-pack' ),
			'no' => __( 'Norwegian', 'all-in-one-seo-pack' ),
			'pt' => __( 'Portuguese', 'all-in-one-seo-pack' ),
			'ro' => __( 'Romanian', 'all-in-one-seo-pack' ),
			'es' => __( 'Spanish', 'all-in-one-seo-pack' ),
			'sv' => __( 'Swedish', 'all-in-one-seo-pack' ),
			'tr' => __( 'Turkish', 'all-in-one-seo-pack' )
		];

		return $languages;
	}

	/**
	 * Returns the list of search engines.
	 *
	 * @since 4.7.7.1
	 *
	 * @return array The list of search engines.
	 */
	private function getSearchEngines() {
		$searchEngines = [
			'AF' => 'google.com.af',
			'AL' => 'google.al',
			'DZ' => 'google.dz',
			'AS' => 'google.as',
			'AD' => 'google.ad',
			'AO' => 'google.it.ao',
			'AI' => 'google.com.ai',
			'AG' => 'google.com.ag',
			'AR' => 'google.com.ar',
			'AM' => 'google.am',
			'AU' => 'google.com.au',
			'AT' => 'google.at',
			'AZ' => 'google.az',
			'BS' => 'google.bs',
			'BH' => 'google.com.bh',
			'BD' => 'google.com.bd',
			'BY' => 'google.com.by',
			'BE' => 'google.be',
			'BZ' => 'google.com.bz',
			'BJ' => 'google.bj',
			'BT' => 'google.bt',
			'BO' => 'google.com.bo',
			'BA' => 'google.ba',
			'BW' => 'google.co.bw',
			'BR' => 'google.com.br',
			'VG' => 'google.vg',
			'BN' => 'google.com.bn',
			'BG' => 'google.bg',
			'BF' => 'google.bf',
			'BI' => 'google.bi',
			'KH' => 'google.com.kh',
			'CM' => 'google.cm',
			'CA' => 'google.ca',
			'CV' => 'google.cv',
			'CF' => 'google.cf',
			'TD' => 'google.td',
			'CL' => 'google.cl',
			'CO' => 'google.com.co',
			'CG' => 'google.cg',
			'CD' => 'google.cd',
			'CK' => 'google.co.ck',
			'CR' => 'google.co.cr',
			'CI' => 'google.ci',
			'HR' => 'google.hr',
			'CU' => 'google.com.cu',
			'CY' => 'google.com.cy',
			'CZ' => 'google.cz',
			'DK' => 'google.dk',
			'DJ' => 'google.dj',
			'DM' => 'google.dm',
			'DO' => 'google.com.do',
			'EC' => 'google.com.ec',
			'EG' => 'google.com.eg',
			'SV' => 'google.com.sv',
			'EE' => 'google.ee',
			'ET' => 'google.com.et',
			'FJ' => 'google.com.fj',
			'FI' => 'google.fi',
			'FR' => 'google.fr',
			'GA' => 'google.ga',
			'GM' => 'google.gm',
			'GE' => 'google.ge',
			'DE' => 'google.de',
			'GH' => 'google.com.gh',
			'GI' => 'google.com.gi',
			'GR' => 'google.gr',
			'GL' => 'google.gl',
			'GT' => 'google.com.gt',
			'GG' => 'google.gg',
			'GY' => 'google.gy',
			'HT' => 'google.ht',
			'HN' => 'google.hn',
			'HK' => 'google.com.hk',
			'HU' => 'google.hu',
			'IS' => 'google.is',
			'IN' => 'google.co.in',
			'ID' => 'google.co.id',
			'IQ' => 'google.iq',
			'IE' => 'google.ie',
			'IM' => 'google.co.im',
			'IL' => 'google.co.il',
			'IT' => 'google.it',
			'JM' => 'google.com.jm',
			'JP' => 'google.co.jp',
			'JE' => 'google.co.je',
			'JO' => 'google.jo',
			'KZ' => 'google.kz',
			'KE' => 'google.co.ke',
			'KI' => 'google.ki',
			'KW' => 'google.com.kw',
			'KG' => 'google.com.kg',
			'LA' => 'google.la',
			'LV' => 'google.lv',
			'LB' => 'google.com.lb',
			'LS' => 'google.co.ls',
			'LY' => 'google.com.ly',
			'LI' => 'google.li',
			'LT' => 'google.lt',
			'LU' => 'google.lu',
			'MG' => 'google.mg',
			'MW' => 'google.mw',
			'MY' => 'google.com.my',
			'MV' => 'google.mv',
			'ML' => 'google.ml',
			'MT' => 'google.com.mt',
			'MU' => 'google.mu',
			'MX' => 'google.com.mx',
			'FM' => 'google.fm',
			'MD' => 'google.md',
			'MN' => 'google.mn',
			'ME' => 'google.me',
			'MS' => 'google.ms',
			'MA' => 'google.co.ma',
			'MZ' => 'google.co.mz',
			'MM' => 'google.com.mm',
			'NA' => 'google.com.na',
			'NR' => 'google.nr',
			'NP' => 'google.com.np',
			'NL' => 'google.nl',
			'NZ' => 'google.co.nz',
			'NI' => 'google.com.ni',
			'NE' => 'google.ne',
			'NG' => 'google.com.ng',
			'NU' => 'google.nu',
			'MK' => 'google.mk',
			'NO' => 'google.no',
			'OM' => 'google.com.om',
			'PK' => 'google.com.pk',
			'PS' => 'google.ps',
			'PA' => 'google.com.pa',
			'PG' => 'google.com.pg',
			'PY' => 'google.com.py',
			'PE' => 'google.com.pe',
			'PH' => 'google.com.ph',
			'PN' => 'google.pn',
			'PL' => 'google.pl',
			'PT' => 'google.pt',
			'PR' => 'google.com.pr',
			'QA' => 'google.com.qa',
			'RO' => 'google.ro',
			'RU' => 'google.ru',
			'RW' => 'google.rw',
			'WS' => 'google.as',
			'SM' => 'google.sm',
			'ST' => 'google.st',
			'SA' => 'google.com.sa',
			'SN' => 'google.sn',
			'RS' => 'google.rs',
			'SC' => 'google.sc',
			'SL' => 'google.com.sl',
			'SG' => 'google.com.sg',
			'SK' => 'google.sk',
			'SI' => 'google.si',
			'SB' => 'google.com.sb',
			'SO' => 'google.so',
			'ZA' => 'google.co.za',
			'KR' => 'google.co.kr',
			'ES' => 'google.es',
			'LK' => 'google.lk',
			'SH' => 'google.sh',
			'VC' => 'google.com.vc',
			'SR' => 'google.sr',
			'SE' => 'google.se',
			'CH' => 'google.ch',
			'TW' => 'google.com.tw',
			'TJ' => 'google.com.tj',
			'TZ' => 'google.co.tz',
			'TH' => 'google.co.th',
			'TL' => 'google.tl',
			'TG' => 'google.tg',
			'TO' => 'google.to',
			'TT' => 'google.tt',
			'TN' => 'google.tn',
			'TR' => 'google.com.tr',
			'TM' => 'google.tm',
			'VI' => 'google.co.vi',
			'UG' => 'google.co.ug',
			'UA' => 'google.com.ua',
			'AE' => 'google.ae',
			'GB' => 'google.co.uk',
			'US' => 'google.com',
			'UY' => 'google.com.uy',
			'UZ' => 'google.co.uz',
			'VU' => 'google.vu',
			'VE' => 'google.co.ve',
			'VN' => 'google.com.vn',
			'ZM' => 'google.co.zm',
			'ZW' => 'google.co.zw'
		];

		return $searchEngines;
	}
}