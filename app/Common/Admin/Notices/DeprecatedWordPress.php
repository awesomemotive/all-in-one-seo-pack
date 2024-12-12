<?php
namespace AIOSEO\Plugin\Common\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Deprecated Notice.
 *
 * @since 4.1.2
 */
class DeprecatedWordPress {
	/**
	 * Class Constructor.
	 *
	 * @since 4.1.2
	 */
	public function __construct() {
		add_action( 'wp_ajax_aioseo-dismiss-deprecated-wordpress-notice', [ $this, 'dismissNotice' ] );
	}

	/**
	 * Go through all the checks to see if we should show the notice.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function maybeShowNotice() {
		global $wp_version; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$dismissed = get_option( '_aioseo_deprecated_wordpress_dismissed', true );
		if ( '1' === $dismissed ) {
			return;
		}

		// Only show to users that interact with our pluign.
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		// Only show if WordPress version is deprecated.
		if ( version_compare( $wp_version, '5.3', '>=' ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			return;
		}

		$this->showNotice();

		// Print the script to the footer.
		add_action( 'admin_footer', [ $this, 'printScript' ] );
	}

	/**
	 * Actually show the review plugin.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function showNotice() {
		$medium = false !== strpos( AIOSEO_PHP_VERSION_DIR, 'pro' ) ? 'proplugin' : 'liteplugin';
		?>
		<div class="notice notice-warning aioseo-deprecated-wordpress-notice is-dismissible">
			<p>
				<?php
				echo wp_kses(
					sprintf(
						// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
						__( 'Your site is running an %1$soutdated version%2$s of WordPress. We recommend using the latest version of WordPress in order to keep your site secure.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
						'<strong>',
						'</strong>'
					),
					[
						'strong' => [],
					]
				);
				?>
				<br><br>
				<?php
				echo wp_kses(
					sprintf(
						// phpcs:ignore Generic.Files.LineLength.MaxExceeded
						// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag, 3 - The short plugin name ("AIOSEO"), 4 - The current year, 5 - Opening HTML link tag, 6 - Closing HTML link tag.
						__( '%1$sNote:%2$s %3$s will be discontinuing support for WordPress versions older than version 5.3 by the end of %4$s. %5$sRead more for additional information.%6$s', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
						'<strong>',
						'</strong>',
						'AIOSEO',
						gmdate( 'Y' ),
						'<a href="https://aioseo.com/docs/update-wordpress/?utm_source=WordPress&utm_medium=' . $medium . '&utm_campaign=outdated-wordpress-notice" target="_blank" rel="noopener noreferrer">', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
						'</a>'
					),
					[
						'a'      => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
						'strong' => [],
					]
				);
				?>
			</p>
		</div>

		<?php
		// In case this is on plugin activation.
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
			unset( $_GET['activate'] );
		}
	}

	/**
	 * Print the script for dismissing the notice.
	 *
	 * @since 4.1.2
	 *
	 * @return void
	 */
	public function printScript() {
		// Create a nonce.
		$nonce = wp_create_nonce( 'aioseo-dismiss-deprecated-wordpress' );
		?>
		<script>
			window.addEventListener('load', function () {
				var dismissBtn

				// Add an event listener to the dismiss button.
				dismissBtn = document.querySelector('.aioseo-deprecated-wordpress-notice .notice-dismiss')
				dismissBtn.addEventListener('click', function (event) {
					var httpRequest = new XMLHttpRequest(),
						postData    = ''

					// Build the data to send in our request.
					postData += '&action=aioseo-dismiss-deprecated-wordpress-notice'
					postData += '&nonce=<?php echo esc_html( $nonce ); ?>'

					httpRequest.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>')
					httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
					httpRequest.send(postData)
				})
			});
		</script>
		<?php
	}

	/**
	 * Dismiss the deprecated WordPress notice.
	 *
	 * @since 4.1.2
	 *
	 * @return string The successful response.
	 */
	public function dismissNotice() {
		// Early exit if we're not on a aioseo-dismiss-deprecated-wordpress-notice action.
		if ( ! isset( $_POST['action'] ) || 'aioseo-dismiss-deprecated-wordpress-notice' !== $_POST['action'] ) {
			return;
		}

		check_ajax_referer( 'aioseo-dismiss-deprecated-wordpress', 'nonce' );

		update_option( '_aioseo_deprecated_wordpress_dismissed', true );

		return wp_send_json_success();
	}
}