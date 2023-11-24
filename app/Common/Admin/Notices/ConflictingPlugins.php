<?php
namespace AIOSEO\Plugin\Common\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Conflicting Plugins notice..
 *
 * @since 4.5.1
 */
class ConflictingPlugins {
	/**
	 * Class constructor.
	 *
	 * @since 4.5.1
	 */
	public function __construct() {
		add_action( 'wp_ajax_aioseo-dismiss-conflicting-plugins-notice', [ $this, 'dismissNotice' ] );
		add_action( 'wp_ajax_aioseo-deactivate-conflicting-plugins-notice', [ $this, 'deactivateConflictingPlugins' ] );
	}

	/**
	 * Go through all the checks to see if we should show the notice.
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function maybeShowNotice() {
		$dismissed = get_option( '_aioseo_conflicting_plugins_dismissed', true );
		if ( '1' === $dismissed ) {
			return;
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Only show if there are conflicting plugins.
		$conflictingPlugins = aioseo()->conflictingPlugins->getAllConflictingPlugins();
		if ( empty( $conflictingPlugins ) ) {
			return;
		}

		$this->showNotice();

		// Print the script to the footer.
		add_action( 'admin_footer', [ $this, 'printScript' ] );
	}

	/**
	 * Renders the notice.
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function showNotice() {
		$type = ! empty( aioseo()->conflictingPlugins->getConflictingPlugins( 'seo' ) ) ? 'SEO' : 'sitemap';
		?>
		<div class="notice notice-error aioseo-conflicting-plugins-notice is-dismissible">
			<p>
				<?php
				echo wp_kses(
					sprintf(
						// phpcs:ignore Generic.Files.LineLength.MaxExceeded
						// Translators: 1 - Type of conflicting plugin (i.e. SEO or Sitemap), 2 - Opening HTML link tag, 3 - Closing HTML link tag.
						__( 'Please keep only one %1$s plugin active, otherwise, you might lose your rankings and traffic. %2$sClick here to Deactivate.%3$s', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
						$type,
						'<a href="#" rel="noopener noreferrer" class="deactivate-conflicting-plugins">',
						'</a>'
					),
					[
						'a'      => [
							'href'  => [],
							'rel'   => [],
							'class' => []
						],
						'strong' => [],
					]
				);
				?>
			</p>
		</div>

		<style>
			#conflicting_seo_plugins.rank-math-notice {
				display: none;
			}
		</style>

		<?php
	}

	/**
	 * Print the script for dismissing the notice.
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function printScript() {
		// Create a nonce.
		$nonce1 = wp_create_nonce( 'aioseo-dismiss-conflicting-plugins' );
		$nonce2 = wp_create_nonce( 'aioseo-deactivate-conflicting-plugins' );
		?>
		<script>
			window.addEventListener('load', function () {
				var dismissBtn,
					deactivateBtn

				// Add an event listener to the dismiss button.
				dismissBtn = document.querySelector('.aioseo-conflicting-plugins-notice .notice-dismiss')
				dismissBtn.addEventListener('click', function (event) {
					var httpRequest = new XMLHttpRequest(),
						postData    = ''

					// Build the data to send in our request.
					postData += '&action=aioseo-dismiss-conflicting-plugins-notice'
					postData += '&nonce=<?php echo esc_html( $nonce1 ); ?>'

					httpRequest.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>')
					httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
					httpRequest.send(postData)
				})

				deactivateBtn = document.querySelector('.aioseo-conflicting-plugins-notice .deactivate-conflicting-plugins')
				deactivateBtn.addEventListener('click', function (event) {
					event.preventDefault()

					var httpRequest = new XMLHttpRequest(),
						postData    = ''

					// Build the data to send in our request.
					postData += '&action=aioseo-deactivate-conflicting-plugins-notice'
					postData += '&nonce=<?php echo esc_html( $nonce2 ); ?>'

					httpRequest.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>')
					httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
					httpRequest.onerror = function () {
						window.location.reload()
					}
					httpRequest.onload = function () {
						window.location.reload()
					}
					httpRequest.send(postData)
				})
			});
		</script>
		<?php
	}

	/**
	 * Dismiss the notice.
	 *
	 * @since 4.5.1
	 *
	 * @return string The successful response.
	 */
	public function dismissNotice() {
		// Early exit if we're not on a aioseo-dismiss-conflicting-plugins-notice action.
		if ( ! isset( $_POST['action'] ) || 'aioseo-dismiss-conflicting-plugins-notice' !== $_POST['action'] ) {
			return wp_send_json_error( 'invalid-action' );
		}

		check_ajax_referer( 'aioseo-dismiss-conflicting-plugins', 'nonce' );

		update_option( '_aioseo_conflicting_plugins_dismissed', true );

		return wp_send_json_success();
	}

	/**
	 * Deactivates the conflicting plugins.
	 *
	 * @since 4.5.1
	 *
	 * @return string The successful response.
	 */
	public function deactivateConflictingPlugins() {
		// Early exit if we're not on a aioseo-dismiss-conflicting-plugins-notice action.
		if ( ! isset( $_POST['action'] ) || 'aioseo-deactivate-conflicting-plugins-notice' !== $_POST['action'] ) {
			return wp_send_json_error( 'invalid-action' );
		}

		check_ajax_referer( 'aioseo-deactivate-conflicting-plugins', 'nonce' );

		aioseo()->conflictingPlugins->deactivateConflictingPlugins( [ 'seo', 'sitemap' ] );

		return wp_send_json_success();
	}
}