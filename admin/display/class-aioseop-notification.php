<?php
/**
 * The class responsible for showing notification.
 *
 * @package All-in-One-SEO-Pack
 */

if ( ! class_exists( 'AIOSEOP_Notification' ) ) {

	/**
	 * Class AIOSEOP_Notification
	 *
	 * @since 2.3.11.5
	 */
	class AIOSEOP_Notification {

		/**
		 * The heading of the notification.
		 *
		 * @var string $heading The heading of the notification.
		 */
		private static $heading = 'Hey, it\'s great to see you have {name} active for a few days now. How is everything going? If you can spare a few moments to rate it on WordPress.org it would help me lot (and boost my motivation). Cheers! <br/> <br/>~ {developer}, developer of {name}';

		/**
		 * Yes button.
		 *
		 * @var string $button_do Yes button.
		 */
		private static $button_do = 'Ok, I will gladly help.';

		/**
		 * No button.
		 *
		 * @var string $button_do No button.
		 */
		private static $button_no = 'No, thanks.';

		/**
		 * Already done button.
		 *
		 * @var string $button_done Already done button.
		 */
		private static $button_done = 'Already done!';

		/**
		 * Show after how many days of activation.
		 */
		const SHOW_AFTER_ACTIVATION_DAYS = 7;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->add_hooks();
		}

		/**
		 * Add the hooks.
		 */
		private function add_hooks() {
			if ( current_user_can( 'manage_options' ) || current_user_can( 'aiosp_manage_seo' ) ) {
				add_action( 'admin_head', array( $this, 'show_notification' ) );
				add_action( 'wp_ajax_' . __CLASS__, array( $this, 'dismiss' ) );
			}
		}

		/**
		 * Either we can notify or not.
		 *
		 * @return bool Notification available or not.
		 */
		public function can_notify() {
			$show = get_option( 'aioseop_feedback_review', 'yes' );
			if ( 'no' === $show ) {
				return false;
			}

			$days = ( time() - get_option( 'aioseop_activation_date', 0 ) ) / DAY_IN_SECONDS;
			return $days >= self::SHOW_AFTER_ACTIVATION_DAYS;
		}

		/**
		 * Shows the notification
		 */
		public function show_notification() {
			if ( $this->can_notify() ) {
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			}
		}

		/**
		 * Called when the either button is clicked
		 */
		public function dismiss() {
			check_ajax_referer( esc_attr( (string) __CLASS__ ), 'nonce' );

			$this->disable();
		}

		/**
		 * Disables the notification
		 */
		private function disable() {
			update_option( 'aioseop_feedback_review', 'no' );
		}

		/**
		 * Shows the admin notice
		 */
		public function admin_notices() {
			?>
				<style type="text/css" id="aioseop-review-css">
					#aioseop-review-notification {
						padding-bottom: 5px;
					}

					#aioseop-review-notification .review-dismiss {
						margin-left: 5px;
					}
				</style>
				<script type="text/javascript" id="aioseop-review-js">
					(function ($) {
						$(document).ready(function () {
							$('#aioseop-feedback-review').on('click', '.notice-dismiss, .review-dismiss', function (e) {
								$.ajax({
									url: ajaxurl,
									method: "post",
									data: {
										'nonce': '<?php echo esc_attr( wp_create_nonce( (string) __CLASS__ ) ); ?>',
										'action': '<?php echo esc_attr( (string) __CLASS__ ); ?>'
									},
									success: function () {
										$('#aioseop-feedback-review').hide();
									}
								});
							});
						});
					})(jQuery);
				</script>
				<div class="notice notice-success is-dismissible" id="aioseop-feedback-review" >
					<div class="aioseop-review-box">
						<?php echo $this->get_html(); ?>
					</div>
				</div>
			<?php
		}

		/**
		 * Create the HTML.
		 */
		private function get_html() {
			global $aioseop_plugin_name;

			$heading = str_replace( array( '{name}', '{developer}' ), array( $aioseop_plugin_name, 'Michael Torbert' ), self::$heading );
			$link = 'https://wordpress.org/plugins/all-in-one-seo-pack/#reviews';

			return '<div id="aioseop-review-notification">'
				   . '<p>' . $heading . '</p>'
				   . '<div class="actions">'
				   . '<a href="' . $link . '" target="_blank" class="button button-primary review-dismiss"> ' . self::$button_do . '</a>'
				   . get_submit_button( self::$button_no, 'review-dismiss aioseop-review', 'aioseop-review-no', false )
				   . get_submit_button( self::$button_done, 'review-dismiss aioseop-review', 'aioseop-review-done', false )
				   . '</div></div>';
		}

	}
}

new AIOSEOP_Notification();
