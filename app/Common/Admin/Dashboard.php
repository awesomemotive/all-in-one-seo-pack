<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Newsroom\Newsroom;

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

		// Add the SEO Checklist widget.
		if (
			$this->canShowWidget( 'seoChecklist' ) &&
			apply_filters( 'aioseo_show_seo_checklist', true ) &&
			( aioseo()->access->isAdmin() || aioseo()->access->hasCapability( 'aioseo_setup_wizard' ) || aioseo()->access->hasCapability( 'aioseo_general_settings' ) ) &&
			aioseo()->standalone->setupWizard->isCompleted()
		) {
			wp_add_dashboard_widget(
				'aioseo-seo-checklist',
				// Translators: 1 - The plugin short name ("AIOSEO").
				sprintf( esc_html__( '%s Checklist', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME ),
				[
					$this,
					'outputSeoChecklist',
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
			( aioseo()->access->isAdmin() || aioseo()->access->hasCapability( 'aioseo_page_analysis' ) ) &&
			aioseo()->options->advanced->truSeo
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
				esc_html__( "What's New in AIOSEO", 'all-in-one-seo-pack' ),
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
	 * Output the SEO Checklist widget.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function outputSeoChecklist() {
		$this->output( 'aioseo-seo-checklist-app' );
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
	 * Returns the items the newsroom widget shows.
	 *
	 * NOTE: One slot is held for the newest item of every other product that has any. AIOSEO posts far
	 * more often than the addons do, so on dates alone their news never reaches the four rows on show —
	 * which is the whole reason it was invisible here.
	 *
	 * @since 5.0.3
	 *
	 * @param  int   $limit How many items to return.
	 * @return array        The items, newest first.
	 */
	private function getNewsroomWidgetItems( $limit = 4 ) {
		// Other products add their own items here; each carries the badge and label the rows render.
		$items = apply_filters( 'aioseo_newsroom_dashboard_items', aioseo()->newsroom->getItems(), Newsroom::PRODUCT );
		if ( ! is_array( $items ) || ! $items ) {
			return [];
		}

		$byId = [];
		foreach ( $items as $item ) {
			if ( empty( $item['url'] ) || empty( $item['title'] ) ) {
				continue;
			}

			// An item cross-posted into more than one feed is still one item.
			$key = ! empty( $item['id'] ) ? (string) $item['id'] : (string) $item['url'];
			if ( ! isset( $byId[ $key ] ) ) {
				$byId[ $key ] = $item;
			}
		}

		$items = array_values( $byId );
		usort( $items, function ( $a, $b ) {
			return strtotime( $b['date'] ?? '' ) <=> strtotime( $a['date'] ?? '' );
		} );

		$reserved = [];
		foreach ( $items as $item ) {
			$product = (string) ( $item['product'] ?? '' );
			if ( '' === $product || Newsroom::PRODUCT === $product || isset( $reserved[ $product ] ) ) {
				continue;
			}

			$reserved[ $product ] = $item;
		}

		// Leave room for the reserved items, then fill what is left with the newest of everything.
		$reserved  = array_slice( array_values( $reserved ), 0, max( 0, $limit - 1 ) );
		$reservedKeys = [];
		foreach ( $reserved as $item ) {
			$reservedKeys[] = ! empty( $item['id'] ) ? (string) $item['id'] : (string) $item['url'];
		}

		$filled = [];
		foreach ( $items as $item ) {
			$key = ! empty( $item['id'] ) ? (string) $item['id'] : (string) $item['url'];
			if ( in_array( $key, $reservedKeys, true ) ) {
				continue;
			}

			$filled[] = $item;
			if ( count( $filled ) >= $limit - count( $reserved ) ) {
				break;
			}
		}

		$shown = array_merge( $filled, $reserved );
		usort( $shown, function ( $a, $b ) {
			return strtotime( $b['date'] ?? '' ) <=> strtotime( $a['date'] ?? '' );
		} );

		return array_slice( $shown, 0, $limit );
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

		$items = $this->getNewsroomWidgetItems();
		if ( ! $items ) {
			esc_html_e( 'Temporarily unable to load feed.', 'all-in-one-seo-pack' );

			return;
		}

		$archiveUrl = aioseo()->newsroom->getArchiveUrl( 'newsroom-dashboard-widget' );

		// This widget is plain PHP with no stylesheet in its path — the other dashboard
		// widgets are Vue apps. Four rows don't justify pulling that pipeline in, so the
		// handful of rules it needs are printed with it.
		?>
		<style>
			.aioseo-newsroom-widget { margin: -12px 0 -12px; }
			.aioseo-newsroom-widget__item { position: relative; padding: 10px 8px; margin: 0 -8px;
				border-bottom: 1px solid #f0f0f1; transition: background-color .15s ease; }
			.aioseo-newsroom-widget__item:hover { background-color: #f6f7f7; }
			.aioseo-newsroom-widget__item:last-of-type { border-bottom: 0; margin-bottom: 4px; }
			.aioseo-newsroom-widget__meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 4px; }
			.aioseo-newsroom-widget__badge { padding: 2px 6px; border-radius: 3px; background: #eceff5; color: #434960;
				font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; line-height: 1.4; }
			.aioseo-newsroom-widget__badge--aioseo { background: #e5efff; color: #0046b0; }
			.aioseo-newsroom-widget__badge--blc { background: #efe9fd; color: #5326bd; }
			.aioseo-newsroom-widget__version { padding: 1px 5px; border: 1px solid #e8e8eb; border-radius: 3px;
				color: #434960; font-size: 10px; font-variant-numeric: tabular-nums; }
			/* Pushed to the far edge so the dates form a column down the list, matching the
			drawer. */
			.aioseo-newsroom-widget__date { margin-left: auto; color: #8c8f9a; font-size: 11px;
				white-space: nowrap; }
			/* Dark at rest, blue on hover — matching the drawer and update modal rather than
			taking WP's default link blue, which reads as a different palette. */
			.aioseo-newsroom-widget__title { display: block; color: #141b38; font-weight: 600; line-height: 1.35;
				text-decoration: none; }
			/* Stretched over the row, so anywhere on it is clickable while the link's
			accessible name stays the article title. */
			.aioseo-newsroom-widget__title::after { content: ""; position: absolute; inset: 0; }
			.aioseo-newsroom-widget__item:hover .aioseo-newsroom-widget__title { color: #005ae0;
				text-decoration: underline; }
			/* Clamped rather than truncated server-side, so translations aren't cut mid-word. */
			.aioseo-newsroom-widget__excerpt { margin: 3px 0 0; color: #50575e; font-size: 12px; line-height: 1.45;
				display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
			.aioseo-newsroom-widget__all { display: block; margin: 0 -12px -12px; padding: 12px;
				border-top: 1px solid #dcdcde; background: #f6f7f7; color: #005ae0; font-weight: 600;
				text-decoration: none; }
			.aioseo-newsroom-widget__all:hover { background: #f0f0f1; }
		</style>
		<div class="aioseo-newsroom-widget">
			<?php foreach ( $items as $item ) : ?>
				<div class="aioseo-newsroom-widget__item">
					<div class="aioseo-newsroom-widget__meta">
						<?php if ( ! empty( $item['label'] ) ) : ?>
							<span class="aioseo-newsroom-widget__badge aioseo-newsroom-widget__badge--<?php echo esc_attr( $item['badge'] ); ?>">
								<?php echo esc_html( $item['label'] ); ?>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $item['version'] ) ) : ?>
							<span class="aioseo-newsroom-widget__version">v<?php echo esc_html( $item['version'] ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $item['date'] ) ) : ?>
							<span class="aioseo-newsroom-widget__date">
								<?php echo esc_html( aioseo()->newsroom->formatDate( $item['date'] ) ); ?>
							</span>
						<?php endif; ?>
					</div>

					<a
						class="aioseo-newsroom-widget__title"
						target="_blank"
						rel="noopener noreferrer"
						href="<?php echo esc_url( aioseo()->helpers->utmUrl( $item['url'], 'newsroom-dashboard-widget', null, false ) ); ?>"
					><?php echo esc_html( $item['title'] ); ?></a>

					<?php if ( ! empty( $item['excerpt'] ) ) : ?>
						<p class="aioseo-newsroom-widget__excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>

			<a class="aioseo-newsroom-widget__all" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $archiveUrl ); ?>">
				<?php esc_html_e( 'View all updates', 'all-in-one-seo-pack' ); ?> &rarr;
			</a>
		</div>
		<?php
	}
}