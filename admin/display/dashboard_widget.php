<?php

if ( ! class_exists( 'aioseop_dashboard_widget' ) ) {

	/**
	 * Class aioseop_dashboard_widget
	 *
	 * @since 2.3.10
	 */
	class aioseop_dashboard_widget {

		/**
		 * Add the action to the constructor.
		 */
		function __construct() {
			add_action( "wp_dashboard_setup", array( $this, 'aioseop_add_dashboard_widget' ) );
		}

		/**
		 * @since 2.3.10
		 */
		function aioseop_add_dashboard_widget() {

			if ( current_user_can( 'install_plugins' ) && false !== $this->show_widget() ) {
				wp_add_dashboard_widget( "semperplugins-rss-feed", __( 'SEO News', 'all-in-one-seo-pack' ), array(
					$this,
					'aioseop_display_rss_dashboard_widget',
				) );
			}

		}

		/**
		 * @since 2.3.10.2
		 */
		function show_widget() {

			$show = true;

			if ( apply_filters( 'aioseo_show_seo_news', true ) === false ) {
				// API filter hook to disable showing SEO News dashboard widget.
				return false;
			}

			global $aioseop_options;

			if ( AIOSEOPPRO && isset( $aioseop_options['aiosp_showseonews'] ) && ! $aioseop_options['aiosp_showseonews'] ) {
				return false;
			}

			return $show;
		}

		/**
		 * @since 2.3.10
		 */
		function aioseop_display_rss_dashboard_widget() {

			include_once( ABSPATH . WPINC . "/feed.php" );

			if ( false === ( $rss_items = get_transient( 'aioseop_feed' ) ) ) {

				$rss = fetch_feed( "https://www.semperplugins.com/feed/" );
				if ( is_wp_error( $rss ) ) {
					echo '{Temporarily unable to load feed.}';

					return;
				}
				$rss_items = $rss->get_items( 0, 4 ); // Show four items.

				$cached = array();
				foreach ( $rss_items as $item ) {
					$cached[] = array(
						'url'     => $item->get_permalink(),
						'title'   => $item->get_title(),
						'date'    => $item->get_date( "M jS Y" ),
						'content' => substr( strip_tags( $item->get_content() ), 0, 128 ) . "...",
					);
				}
				$rss_items = $cached;

				set_transient( 'aioseop_feed', $cached, 12 * HOUR_IN_SECONDS );

			}

			?>

			<ul>
				<?php
				if ( false === $rss_items ) {
					echo "<li>No items</li>";

					return;
				}

				foreach ( $rss_items as $item ) {
					?>
					<li>
						<a target="_blank" href="<?php echo esc_url( $item['url'] ); ?>">
							<?php echo esc_html( $item['title'] ); ?>
						</a>
						<span class="aioseop-rss-date"><?php echo $item['date']; ?></span>
						<div class="aioseop_news">
							<?php echo strip_tags( $item['content'] ) . "..."; ?>
						</div>
					</li>
					<?php
				}

				?>
			</ul>

			<?php

		}
	}

	new aioseop_dashboard_widget();
}


