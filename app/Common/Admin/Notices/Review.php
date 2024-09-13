<?php
namespace AIOSEO\Plugin\Common\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Review Plugin Notice.
 *
 * @since 4.0.0
 */
class Review {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_aioseo-dismiss-review-plugin-cta', [ $this, 'dismissNotice' ] );
	}

	/**
	 * Go through all the checks to see if we should show the notice.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybeShowNotice() {
		$dismissed = get_user_meta( get_current_user_id(), '_aioseo_plugin_review_dismissed', true );
		if ( '3' === $dismissed || '4' === $dismissed ) {
			return;
		}

		if ( ! empty( $dismissed ) && $dismissed > time() ) {
			return;
		}

		// Only show to users that interact with our pluign.
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		// Only show if plugin has been active for over 10 days.
		if ( ! aioseo()->internalOptions->internal->firstActivated ) {
			aioseo()->internalOptions->internal->firstActivated = time();
		}

		$activated = aioseo()->internalOptions->internal->firstActivated( time() );
		if ( $activated > strtotime( '-10 days' ) ) {
			return;
		}

		if ( get_option( 'aioseop_options' ) || get_option( 'aioseo_options_v3' ) ) {
			$this->showNotice();
		} else {
			$this->showNotice2();
		}

		// Print the script to the footer.
		add_action( 'admin_footer', [ $this, 'printScript' ] );
	}

	/**
	 * Actually show the review plugin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function showNotice() {
		$feedbackUrl = add_query_arg(
			[
				'wpf7528_24'   => untrailingslashit( home_url() ),
				'wpf7528_26'   => aioseo()->options->has( 'general' ) && aioseo()->options->general->has( 'licenseKey' )
					? aioseo()->options->general->licenseKey
					: '',
				'wpf7528_27'   => aioseo()->pro ? 'pro' : 'lite',
				'wpf7528_28'   => AIOSEO_VERSION,
				'utm_source'   => aioseo()->pro ? 'proplugin' : 'liteplugin',
				'utm_medium'   => 'review-notice',
				'utm_campaign' => 'feedback',
				'utm_content'  => AIOSEO_VERSION,
			],
			'https://aioseo.com/plugin-feedback/'
		);

		$string1 = sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			__( 'Are you enjoying %1$s?', 'all-in-one-seo-pack' ),
			AIOSEO_PLUGIN_NAME
		);
		$string2  = __( 'Yes I love it', 'all-in-one-seo-pack' );
		$string3  = __( 'Not Really...', 'all-in-one-seo-pack' );
		$string4  = sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
			__( 'We\'re sorry to hear you aren\'t enjoying %1$s. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'all-in-one-seo-pack' ),
			AIOSEO_PLUGIN_NAME
		); // phpcs:ignore Generic.Files.LineLength.MaxExceeded
		$string5  = __( 'Give feedback', 'all-in-one-seo-pack' );
		$string6  = __( 'No thanks', 'all-in-one-seo-pack' );
		$string7  = __( 'That\'s awesome! Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'all-in-one-seo-pack' );
		// Translators: 1 - The plugin name ("All in One SEO").
		$string9  = __( 'Ok, you deserve it', 'all-in-one-seo-pack' );
		$string10 = __( 'Nope, maybe later', 'all-in-one-seo-pack' );
		$string11 = __( 'I already did', 'all-in-one-seo-pack' );

		?>
		<div class="notice notice-info aioseo-review-plugin-cta is-dismissible">
			<div class="step-1">
				<p><?php echo esc_html( $string1 ); ?></p>
				<p>
					<a href="#" class="aioseo-review-switch-step-3" data-step="3"><?php echo esc_html( $string2 ); ?></a> 🙂 |
					<a href="#" class="aioseo-review-switch-step-2" data-step="2"><?php echo esc_html( $string3 ); ?></a>
				</p>
			</div>
			<div class="step-2" style="display:none;">
				<p><?php echo esc_html( $string4 ); ?></p>
				<p>
					<a href="<?php echo esc_url( $feedbackUrl ); ?>" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $string5 ); ?></a>&nbsp;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $string6 ); ?></a>
				</p>
			</div>
			<div class="step-3" style="display:none;">
				<p><?php echo esc_html( $string7 ); ?></p>
				<p>
					<a href="https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews/?filter=5#new-post" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string9 ); ?>
					</a>&nbsp;&bull;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice-delay" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string10 ); ?>
					</a>&nbsp;&bull;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string11 ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Actually show the review plugin 2.0.
	 *
	 * @since 4.2.2
	 *
	 * @return void
	 */
	public function showNotice2() {
		$string1 = sprintf(
			// Translators: 1 - The plugin name ("All in One SEO").
			__( 'Hey, we noticed you have been using %1$s for some time - that’s awesome! Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'<strong>' . esc_html( AIOSEO_PLUGIN_NAME ) . '</strong>'
		);

		// Translators: 1 - The plugin name ("All in One SEO").
		$string9  = __( 'Ok, you deserve it', 'all-in-one-seo-pack' );
		$string10 = __( 'Nope, maybe later', 'all-in-one-seo-pack' );
		$string11 = __( 'I already did', 'all-in-one-seo-pack' );

		?>
		<div class="notice notice-info aioseo-review-plugin-cta is-dismissible">
			<div class="step-3">
				<p><?php echo $string1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<p>
					<a href="https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews/?filter=5#new-post" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string9 ); ?>
					</a>&nbsp;&bull;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice-delay" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string10 ); ?>
					</a>&nbsp;&bull;&nbsp;
					<a href="#" class="aioseo-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $string11 ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Print the script for dismissing the notice.
	 *
	 * @since 4.0.13
	 *
	 * @return void
	 */
	public function printScript() {
		// Create a nonce.
		$nonce = wp_create_nonce( 'aioseo-dismiss-review' );
		?>
		<style>
			.aioseop-notice-review_plugin_cta .aioseo-action-buttons {
				display: none;
			}
			@keyframes dismissBtnVisible {
				from { opacity: 0.99; }
				to { opacity: 1; }
			}
			.aioseo-review-plugin-cta button.notice-dismiss {
				animation-duration: 0.001s;
				animation-name: dismissBtnVisible;
			}
		</style>
		<script>
			window.addEventListener('load', function () {
				var aioseoSetupButton,
					dismissBtn

				aioseoSetupButton = function (dismissBtn) {
					var notice      = document.querySelector('.notice.aioseo-review-plugin-cta'),
						delay       = false,
						relay       = true,
						stepOne     = notice.querySelector('.step-1'),
						stepTwo     = notice.querySelector('.step-2'),
						stepThree   = notice.querySelector('.step-3')

					// Add an event listener to the dismiss button.
					dismissBtn.addEventListener('click', function (event) {
						var httpRequest = new XMLHttpRequest(),
							postData    = ''

						// Build the data to send in our request.
						postData += '&delay=' + delay
						postData += '&relay=' + relay
						postData += '&action=aioseo-dismiss-review-plugin-cta'
						postData += '&nonce=<?php echo esc_html( $nonce ); ?>'

						httpRequest.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>')
						httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
						httpRequest.send(postData)
					})

					notice.addEventListener('click', function (event) {
						if (event.target.matches('.aioseo-review-switch-step-3')) {
							event.preventDefault()
							stepOne.style.display   = 'none'
							stepTwo.style.display   = 'none'
							stepThree.style.display = 'block'
						}
						if (event.target.matches('.aioseo-review-switch-step-2')) {
							event.preventDefault()
							stepOne.style.display   = 'none'
							stepThree.style.display = 'none'
							stepTwo.style.display   = 'block'
						}
						if (event.target.matches('.aioseo-dismiss-review-notice-delay')) {
							event.preventDefault()
							delay = true
							relay = false
							dismissBtn.click()
						}
						if (event.target.matches('.aioseo-dismiss-review-notice')) {
							if ('#' === event.target.getAttribute('href')) {
								event.preventDefault()
							}
							relay = false
							dismissBtn.click()
						}
					})
				}

				dismissBtn = document.querySelector('.aioseo-review-plugin-cta .notice-dismiss')
				if (!dismissBtn) {
					document.addEventListener('animationstart', function (event) {
						if (event.animationName == 'dismissBtnVisible') {
							dismissBtn = document.querySelector('.aioseo-review-plugin-cta .notice-dismiss')
							if (dismissBtn) {
								aioseoSetupButton(dismissBtn)
							}
						}
					}, false)

				} else {
					aioseoSetupButton(dismissBtn)
				}
			});
		</script>
		<?php
	}

	/**
	 * Dismiss the review plugin CTA.
	 *
	 * @since 4.0.0
	 *
	 * @return string The successful response.
	 */
	public function dismissNotice() {
		// Early exit if we're not on a aioseo-dismiss-review-plugin-cta action.
		if ( ! isset( $_POST['action'] ) || 'aioseo-dismiss-review-plugin-cta' !== $_POST['action'] ) {
			return;
		}

		check_ajax_referer( 'aioseo-dismiss-review', 'nonce' );
		$delay = isset( $_POST['delay'] ) ? 'true' === sanitize_text_field( wp_unslash( $_POST['delay'] ) ) : false;
		$relay = isset( $_POST['relay'] ) ? 'true' === sanitize_text_field( wp_unslash( $_POST['relay'] ) ) : false;

		if ( ! $delay ) {
			update_user_meta( get_current_user_id(), '_aioseo_plugin_review_dismissed', $relay ? '4' : '3' );

			return wp_send_json_success();
		}

		update_user_meta( get_current_user_id(), '_aioseo_plugin_review_dismissed', strtotime( '+1 week' ) );

		return wp_send_json_success();
	}
}