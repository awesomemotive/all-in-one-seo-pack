<?php
namespace AIOSEO\Plugin\Common\WritingAssistant\SeoBoost;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for SeoBoost.
 *
 * @since 4.7.4
 */
class SeoBoost {
	/**
	 * Login url.
	 *
	 * @since 4.7.4
	 */
	private $loginUrl = 'https://app.seoboost.com/login/';

	/**
	 * Create Account url.
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
	 * Constructor.
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
			add_action( 'init', [ $this, 'marketingSiteReturn' ], 50 );
		}

		// Migrate user access token and options.
		add_action( 'init', [ $this, 'migrateUserData' ], 10 );
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
	 * Refreshes user options from SeoBoost.
	 *
	 * @since 4.7.4
	 *
	 * @return bool|\WP_Error|array The options array if refreshed.
	 */
	public function refreshUserOptions() {
		$userOptions = $this->service->getUserOptions();
		if ( is_wp_error( $userOptions ) || ! empty( $userOptions['error'] ) ) {
			return false;
		}

		return $this->setUserOptions( $userOptions );
	}

	/**
	 * Gets the user options.
	 *
	 * @since 4.7.4
	 *
	 * @return array The user options.
	 */
	public function getUserOptions( $refresh = true ) {
		$metaKey     = 'seoboost_user_options_' . get_current_blog_id();
		$userOptions = get_user_meta( get_current_user_id(), $metaKey, true );

		if ( empty( $userOptions ) && $refresh ) {
			if ( $this->refreshUserOptions() ) {
				return $this->getUserOptions( false );
			}
		}

		return json_decode( $userOptions, true );
	}

	/**
	 * Gets the user options.
	 *
	 * @since 4.7.4
	 *
	 * @param  array $options The user options.
	 * @return array          The user options.
	 */
	public function setUserOptions( $options ) {
		if ( ! is_array( $options ) ) {
			return [];
		}

		$userOptions = $this->getUserOptions( false ) ?? [];
		$userOptions = array_merge( $userOptions, $options );
		$metaKey     = 'seoboost_user_options_' . get_current_blog_id();

		update_user_meta( get_current_user_id(), $metaKey, wp_json_encode( $userOptions ) );

		return $userOptions;
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
	 * Handles the marketing site return.
	 *
	 * @since 4.7.4
	 *
	 * @return void
	 */
	public function marketingSiteReturn() {
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
	 * Migrate writing assistant access tokens.
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
}