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

		$metaKey     = 'seoboost_user_options_' . get_current_blog_id();
		$userOptions = array_intersect_key( $options, $this->getDefaultUserOptions() );

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
			'language' => 'en',
			'country'  => 'US'
		];
	}
}