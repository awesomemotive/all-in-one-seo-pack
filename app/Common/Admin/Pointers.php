<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the pointers for the admin.
 *
 * @since 4.8.3
 */
class Pointers {
	/**
	 * Class constructor.
	 *
	 * @since 4.8.3
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', [ $this, 'maybeDismissPointer' ] );
		add_action( 'in_admin_header', [ $this, 'init' ] );
	}

	/**
	 * Initializes the pointers.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	public function init() {
		$this->registerKwRankTracker();
	}

	/**
	 * Checks if a pointer should be dismissed.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	public function maybeDismissPointer() {
		if (
			! isset( $_GET['aioseo-dismiss-pointer'] ) ||
			! isset( $_GET['aioseo-dismiss-pointer-nonce'] ) ||
			! wp_verify_nonce( $_GET['aioseo-dismiss-pointer-nonce'], 'aioseo-dismiss-pointer' )
		) {
			return;
		}

		$pointer = sanitize_text_field( wp_unslash( $_GET['aioseo-dismiss-pointer'] ) );
		update_user_meta( get_current_user_id(), "_aioseo-$pointer-dismissed", true );
	}

	/**
	 * Registers a pointer.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	public function registerPointer( $id, $pageSlug, $args ) {
		if ( get_user_meta( get_current_user_id(), "_aioseo-$id-dismissed", true ) ) {
			return;
		}

		if ( "all-in-one-seo_page_aioseo-{$pageSlug}" === aioseo()->helpers->getCurrentScreen()->id ) {
			return;
		}

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		// phpcs:disable AIOSEO.Wp.I18n.NonSingularStringLiteralText, Squiz.PHP.EmbeddedPhp, Generic.WhiteSpace.ScopeIndent.IncorrectExact
		?>
		<script>
			jQuery( document ).ready( function( $ ) {
				var isClosed = false;
				var pointer  = $( '#toplevel_page_aioseo > a' ).pointer( {
					content :
						"<h3><?php esc_html_e( $args['title'], 'all-in-one-seo-pack' ); ?><\/h3>" +
						"<h4><?php esc_html_e( $args['subtitle'], 'all-in-one-seo-pack' ); ?><\/h4>" +
						"<p><?php esc_html_e( $args['content'], 'all-in-one-seo-pack' ); ?><\/p>" +
						"<?php
							echo sprintf(
								'<p><a class=\"button button-primary\" href=\"%s\">%s</a></p>',
								esc_attr( esc_url( $args['url'] ) ),
								esc_html__( $args['button'], 'all-in-one-seo-pack' )
							);
						?>",
					position : {
						edge  : <?php echo is_rtl() ? "'right'" : "'left'"; ?>,
						align : 'center'
					},
					pointerWidth : 420,
					show: function(event, el) {
						el.pointer.css({'position':'fixed'});
						el.pointer.addClass('aioseo-wp-pointer');
					},
					close : function() {
						isClosed = true;
						jQuery.get(
							window.location.href,
							{
								'aioseo-dismiss-pointer'       : '<?php echo esc_js( $id ); ?>',
								'aioseo-dismiss-pointer-nonce' : '<?php echo esc_js( wp_create_nonce( 'aioseo-dismiss-pointer' ) ); ?>'
							}
						);
					}
				} ).pointer('open');
			} );
		</script>
		<?php
		// phpcs:enable
	}

	/**
	 * Registers the KW Rank Tracker pointer.
	 *
	 * @since 4.8.3
	 *
	 * @return void
	 */
	public function registerKwRankTracker() {
		if (
			! current_user_can( 'aioseo_search_statistics_settings' ) ||
			(
				is_object( aioseo()->license ) &&
				aioseo()->license->hasCoreFeature( 'search-statistics', 'keyword-rank-tracker' ) &&
				aioseo()->searchStatistics->api->auth->isConnected()
			)
		) {
			return;
		}

		$nonce = wp_create_nonce( 'aioseo-dismiss-pointer' );

		$args = [
			'title'    => 'NEW! Keyword Rank Tracker',
			'subtitle' => 'Get insights into how your site is performing for your most important keywords',
			'content'  => 'Track keywords and combine them into groups to see how your site is performing for key topics in Google search results.',
			'url'      => admin_url( 'admin.php?aioseo-dismiss-pointer=kw-rank-tracker&aioseo-dismiss-pointer-nonce=' . $nonce . '&page=aioseo-search-statistics#/keyword-rank-tracker' ),
			'button'   => 'Unlock Keyword Rank Tracker'
		];

		$this->registerPointer( 'kw-rank-tracker', 'search-statistics', $args );
	}
}