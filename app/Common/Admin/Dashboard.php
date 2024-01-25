<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that holds our dashboard widget.
 *
 * @since 4.0.0
 */
class Dashboard {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'addDashboardWidgets' ] );
	}

	/**
	 * Registers our dashboard widgets.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function addDashboardWidgets() {
		// Add the SEO Setup widget.
		if (
			$this->canShowWidget( 'seoSetup' ) &&
			apply_filters( 'aioseo_show_seo_setup', true ) &&
			( aioseo()->access->isAdmin() || aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) ) &&
			! aioseo()->standalone->setupWizard->isCompleted()
		) {
			wp_add_dashboard_widget(
				'aioseo-seo-setup',
				// Translators: 1 - The plugin short name ("AIOSEO").
				sprintf( esc_html__( '%s Setup', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME ),
				[
					$this,
					'outputSeoSetup',
				],
				null,
				null,
				'normal',
				'high'
			);
		}

		// Add the Overview widget.
		if (
			$this->canShowWidget( 'seoOverview' ) &&
			apply_filters( 'aioseo_show_seo_overview', true ) &&
			( aioseo()->access->isAdmin() || aioseo()->access->hasCapability( 'aioseo_page_analysis' ) )
		) {
			wp_add_dashboard_widget(
				'aioseo-overview',
				// Translators: 1 - The plugin short name ("AIOSEO").
				sprintf( esc_html__( '%s Overview', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME ),
				[
					$this,
					'outputSeoOverview',
				]
			);
		}

		// Add the News widget.
		if (
			$this->canShowWidget( 'seoNews' ) &&
			apply_filters( 'aioseo_show_seo_news', true ) &&
			aioseo()->access->isAdmin()
		) {
			wp_add_dashboard_widget(
				'aioseo-rss-feed',
				esc_html__( 'SEO News', 'all-in-one-seo-pack' ),
				[
					$this,
					'displayRssDashboardWidget',
				]
			);
		}
	}

	/**
	 * Whether or not to show the widget.
	 *
	 * @since   4.0.0
	 * @version 4.2.8
	 *
	 * @param  string  $widget The widget to check if can show.
	 * @return boolean True if yes, false otherwise.
	 */
	protected function canShowWidget( $widget ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return true;
	}

	/**
	 * Output the SEO Setup widget.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function outputSeoSetup() {
		$this->output( 'aioseo-seo-setup-app' );
	}

	/**
	 * Output the SEO Overview widget.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function outputSeoOverview() {
		$this->output( 'aioseo-overview-app' );
	}

	/**
	 * Output the widget wrapper for the Vue App.
	 *
	 * @since 4.2.0
	 *
	 * @param  string $appId The App ID to print out.
	 * @return void
	 */
	private function output( $appId ) {
		// Enqueue the scripts for the widget.
		$this->enqueue();

		// Opening tag.
		echo '<div id="' . esc_attr( $appId ) . '">';

		// Loader element.
		require AIOSEO_DIR . '/app/Common/Views/parts/loader.php';

		// Closing tag.
		echo '</div>';
	}

	/**
	 * Enqueue the scripts and styles.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	private function enqueue() {
		aioseo()->core->assets->load( 'src/vue/standalone/dashboard-widgets/main.js', [], aioseo()->helpers->getVueData( 'dashboard' ) );
	}

	/**
	 * Display RSS Dashboard Widget
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function displayRssDashboardWidget() {
		// Check if the user has chosen not to display this widget through screen options.
		$currentScreen = aioseo()->helpers->getCurrentScreen();
		if ( empty( $currentScreen->id ) ) {
			return;
		}

		$hiddenWidgets = get_user_meta( get_current_user_id(), 'metaboxhidden_' . $currentScreen->id );
		if ( $hiddenWidgets && count( $hiddenWidgets ) > 0 && is_array( $hiddenWidgets[0] ) && in_array( 'aioseo-rss-feed', $hiddenWidgets[0], true ) ) {
			return;
		}

		include_once ABSPATH . WPINC . '/feed.php';

		$rssItems = aioseo()->core->networkCache->get( 'rss_feed' );
		if ( null === $rssItems ) {
			$rss = fetch_feed( 'https://aioseo.com/feed/' );
			if ( is_wp_error( $rss ) ) {
				esc_html_e( 'Temporarily unable to load feed.', 'all-in-one-seo-pack' );

				return;
			}
			$rssItems = $rss->get_items( 0, 4 ); // Show four items.
			$cached   = [];
			foreach ( $rssItems as $item ) {
				$cached[] = [
					'url'     => $item->get_permalink(),
					'title'   => aioseo()->helpers->decodeHtmlEntities( $item->get_title() ),
					'date'    => $item->get_date( get_option( 'date_format' ) ),
					'content' => substr( wp_strip_all_tags( $item->get_content() ), 0, 128 ) . '...',
				];
			}
			$rssItems = $cached;

			aioseo()->core->networkCache->update( 'rss_feed', $cached, 12 * HOUR_IN_SECONDS );
		}
		?>
		<ul>
			<?php
			if ( false === $rssItems ) {
				echo '<li>' . esc_html( __( 'No articles were found.', 'all-in-one-seo-pack' ) ) . '</li>';

				return;
			}

			foreach ( $rssItems as $item ) {
				?>
				<li>
					<a target="_blank" href="<?php echo esc_url( $item['url'] ); ?>" rel="noopener noreferrer">
						<?php echo esc_html( $item['title'] ); ?>
					</a>
					<span><?php echo esc_html( $item['date'] ); ?></span>
					<div>
						<?php echo esc_html( wp_strip_all_tags( $item['content'] ) ) . '...'; ?>
					</div>
				</li>
				<?php
			}

			?>
		</ul>
		<?php
	}
}