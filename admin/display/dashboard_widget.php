<?php
/**
 * Dashboard Widget
 *
 * @package All_in_One_SEO_Pack
 * @since 2.3.10
 */

if ( ! class_exists( 'aioseop_dashboard_widget' ) ) {

	/**
	 * Class aioseop_dashboard_widget
	 *
	 * @since 2.3.10
	 */
	// @codingStandardsIgnoreStart
	class aioseop_dashboard_widget {
	// @codingStandardsIgnoreEnd

		/**
		 * Constructor
		 *
		 * Add the action to the constructor.
		 *
		 * @since 2.3.10
		 */
		function __construct() {
			add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		}

		/**
		 * Add Dashboard Widget
		 *
		 * @since 2.3.10
		 */
		function add_dashboard_widget() {
			if ( current_user_can( 'install_plugins' ) && false !== $this->show_widget() ) {
				wp_add_dashboard_widget(
					'semperplugins-rss-feed',
					__( 'SEO News', 'all-in-one-seo-pack' ),
					array(
						$this,
						'display_rss_dashboard_widget',
					)
				);
			}

		}

		/**
		 * Show Widget
		 *
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
		 * Display RSS Dashboard Widget
		 *
		 * @since 2.3.10
		 */
		function display_rss_dashboard_widget() {
			// check if the user has chosen not to display this widget through screen options.
			$current_screen = get_current_screen();
			$hidden_widgets = get_user_meta( get_current_user_id(), 'metaboxhidden_' . $current_screen->id );
			if ( $hidden_widgets && count( $hidden_widgets ) > 0 && is_array( $hidden_widgets[0] ) && in_array( 'semperplugins-rss-feed', $hidden_widgets[0], true ) ) {
				return;
			}

			include_once( ABSPATH . WPINC . '/feed.php' );

			$rss_items = get_transient( 'aioseop_feed' );
			if ( false === $rss_items ) {

				$rss = fetch_feed( 'https://www.semperplugins.com/feed/' );
				if ( is_wp_error( $rss ) ) {
					echo __( '{Temporarily unable to load feed.}', 'all-in-one-seo-pack' );

					return;
				}
				$rss_items = $rss->get_items( 0, 4 ); // Show four items.

				$cached = array();
				foreach ( $rss_items as $item ) {
					$cached[] = array(
						'url'     => $item->get_permalink(),
						'title'   => $item->get_title(),
						'date'    => $item->get_date( 'M jS Y' ),
						'content' => substr( strip_tags( $item->get_content() ), 0, 128 ) . '...',
					);
				}
				$rss_items = $cached;

				set_transient( 'aioseop_feed', $cached, 12 * HOUR_IN_SECONDS );

			}

			?>

			<ul>
				<?php
				if ( false === $rss_items ) {
					echo '<li>No items</li>';

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
							<?php echo strip_tags( $item['content'] ) . '...'; ?>
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


