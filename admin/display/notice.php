<?php

/**
 * Class Admin Notifications.
 *
 * @package All-in-One-SEO-Pack
 * @since 2.3.15
 */

if ( ! class_exists( 'AIOSEOP_Notice' ) ) {

	/**
	 * AIOSEOP Notice.
	 *
	 * Admin notices for AIOSEOP.
	 *
	 * @since 2.3.15
	 */
	class AIOSEOP_Notice {

		/**
		 * __constructor.
		 *
		 * @since 2.3.15
		 */
		public function __construct() {
			// Initial options.
			$this->set_init_timestamp();

			// Admin Notices.
			if ( is_admin() ) {
				if ( ! AIOSEOPPRO ) {
					add_action( 'all_admin_notices', array( $this, 'review' ) );
					add_action( 'wp_ajax_aioseop_review_dismiss', array( $this, 'review_dismiss' ) );
					add_action( 'wp_ajax_aioseop_review_later', array( $this, 'review_later' ) );
				}
			}
		}

		/**
		 * Get Notice Options
		 *
		 * @since 2.3.15
		 * @access private
		 *
		 * @return array
		 */
		private function get_notice_options() {
			$defaults = array(
				'review_dismiss'         => false,
				'review_sched'           => 0,
				'time_init'              => 0,
				'time_activated'         => 0,
				//'time_deactivated'    => 0,// Possible to accurately calculate 'used time' with a deactivation timestamp.
				'time_review_dismissed'  => 0,
			);
			$notice_options = get_option( 'aioseop_notice' );
			$notice_options = wp_parse_args( $notice_options, $defaults );

			return $notice_options;
		}

		/**
		 * Update Notice Options
		 *
		 * @since 2.3.15
		 * @access private
		 *
		 * @param array $notice_options New Notice Options.
		 * @return boolean False if failed saving.
		 */
		private function update_notice_options( $notice_options ) {
			$old_notice_options = $this->get_notice_options();
			$notice_options = wp_parse_args( $notice_options, $old_notice_options );

			return update_option( 'aioseop_notice', $notice_options );
		}

		/**
		 * Set Initial Timestamp.
		 *
		 * Used to record the initial timestamp, and to determine if a review notice
		 * is scheduled upon activation. Primarily for websites that already have
		 * aioseop active.
		 *
		 * @since 2.3.15
		 *
		 * @return void
		 */
		public function set_init_timestamp() {
			$notice_options = $this->get_notice_options();

			if ( empty( $notice_options['time_init'] ) ) {
				$notice_options['time_init'] = time();

				$this->update_notice_options( $notice_options );
			}
		}

		/**
		 * Set Activation Timestamp(s)
		 *
		 * Upon activation, a timestamp is set, and if it is a fresh activation
		 * a review notice is scheduled. NOTE: 10 days = 864000 seconds.
		 *
		 * @since 2.3.15
		 *
		 * @return void
		 */
		public function set_activation_timestamp() {
			$notice_options = $this->get_notice_options();

			// If within initial activation. 30 seconds til times out.
			if ( 30 > ( time() - $notice_options['time_init'] ) ) {
				$notice_options['review_sched'] = time() + 864000;
			}

			// Initially used to calculate if a notice appears. Possible future use.
			$notice_options['time_activated'] = time();

			$this->update_notice_options( $notice_options );
		}

		/**
		 * Rate Plugin Notice
		 *
		 * Admin Notice for rating the plugin after X amount of days has pass.
		 * The notice is also scheduled beforehand within the activation hook.
		 *
		 * @since 2.3.15
		 *
		 * @return void
		 */
		public function review() {
			if ( AIOSEOPPRO ) {
				return;
			}

			$current_screen = get_current_screen();
			$aioseop_screens = array(
				'toplevel_page_all-in-one-seo-pack/aioseop_class',
				'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_performance',
				'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_sitemap',
				'all-in-one-seo_page_aiosp_opengraph',
				'all-in-one-seo_page_aiosp_robots_generator',
				'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_file_editor',
				'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_importer_exporter',
				'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_bad_robots',
				'all-in-one-seo_page_all-in-one-seo-pack/modules/aioseop_feature_manager',
			);

			if ( isset( $current_screen->id ) && in_array( $current_screen->id, $aioseop_screens, true ) ) {
				$notice_options = $this->get_notice_options();

				// Display review notice if schedule (10 days) is after current time.
				// Scheduled time is set during initial activation & AJAX reschedule.
				if ( ! empty( $notice_options['review_sched'] ) && false === $notice_options['review_dismiss'] ) {
					if ( time() > $notice_options['review_sched'] ) {
						$this->display_review();
					}
				}
			}
		}

		/**
		 * Display Review Notice.
		 *
		 * HTML content for Review Admin Notice.
		 *
		 * @since 2.3.15
		 * @access private
		 *
		 * @return void
		 */
		private function display_review() {
			?>
			<div class="notice notice-info is-dismissible aioseop-review-notice">
				<p><?php esc_html_e( 'Looks like you\'ve been using All in One SEO Plugin for awhile now, and that\'s awesome! We are an open source community built from other contributors within WordPress. By helping us with a 5-star review, it also helps us to reach out to more people.', 'all-in-one-seo-pack' ); ?></p>
				<p>
					<a href="https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post" class="aioseop-review-dismiss aioseop-review-yes" target="_blank" rel="noopener"><?php esc_html_e( 'Yes, absolutely!', 'all-in-one-seo-pack' ); ?></a><br />
					<a href="#" class="aioseop-review-dismiss aioseop-review-later" target="_blank" rel="noopener"><?php esc_html_e( 'Not now, maybe later.', 'all-in-one-seo-pack' )?></a><br />
					<a href="#" class="aioseop-review-dismiss" target="_blank" rel="noopener"><?php esc_html_e( 'Already rated.', 'all-in-one-seo-pack' )?></a><br />
				</p>
			</div>
			<script type="text/javascript">
				jQuery(document).ready( function($) {
					$( document ).on( 'click', '.aioseop-review-dismiss, .aioseop-review-notice button', function( event ) {
						if ( ! $( this ).hasClass( 'aioseop-review-yes' ) ) {
							event.preventDefault();
						}

						if ( $( this ).hasClass( 'aioseop-review-later' ) ) {
							$.post( ajaxurl, {
								action: 'aioseop_review_later',
								_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'aioseop_review_later' ) ); ?>',
							});
						} else {
							$.post( ajaxurl, {
								action: 'aioseop_review_dismiss',
								_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'aioseop_review_dismiss' ) ); ?>',
							});
						}

						$('.aioseop-review-notice').remove();
					});
				});
			</script>
			<?php
		}

		/**
		 * Dismiss Review.
		 *
		 * AJAX function for dismissing the review notice.
		 *
		 * @since 2.3.15
		 *
		 * @return void
		 */
		public function review_dismiss() {
			check_ajax_referer( 'aioseop_review_dismiss' );

			$notice_options = $this->get_notice_options();

			$notice_options['review_dismiss']         = true;
			$notice_options['time_review_dismissed']  = time();

			$this->update_notice_options( $notice_options );

			wp_die();
		}

		/**
		 * Review Notice Later.
		 *
		 * AJAX for setting the admin notice for later (5 days).
		 *
		 * @since 2.3.15
		 */
		public function review_later() {
			check_ajax_referer( 'aioseop_review_later' );

			$notice_options = $this->get_notice_options();

			$notice_options['time_review_sched'] = time() + 432000;

			$this->update_notice_options( $notice_options );

			wp_die();
		}
	}

	new AIOSEOP_Notice();
}
