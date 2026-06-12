<?php
// phpcs:disable Generic.Files.LineLength.MaxExceeded
namespace AIOSEO\Plugin\Lite\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class PostSettings extends CommonAdmin\PostSettings {
	/**
	 * Holds a list of page builder integration class instances.
	 * This prop exists for backwards compatibility with pre-4.2.0 versions (see backwardsCompatibilityLoad() in AIOSEO.php).
	 *
	 * @since 4.4.2
	 *
	 * @var object[]
	 */
	public $integrations = null;

	/**
	 * Initialize the admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add upsell to terms.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		if ( is_admin() ) {
			// We don't call getPublicTaxonomies() here because we want to show the CTA for Product Attributes as well.
			$taxonomies = get_taxonomies( [], 'objects' );
			foreach ( $taxonomies as $taxObject ) {
				if (
					empty( $taxObject->label ) ||
					! is_taxonomy_viewable( $taxObject )
				) {
					unset( $taxonomies[ $taxObject->name ] );
				}
			}

			foreach ( $taxonomies as $taxonomy ) {
				add_action( $taxonomy->name . '_edit_form', [ $this, 'addTaxonomyUpsell' ] );
				add_action( 'after-' . $taxonomy->name . '-table', [ $this, 'addTaxonomyUpsell' ] );
			}
		}
	}

	/**
	 * Add Taxonomy Upsell.
	 *
	 * Renders the real post-settings metabox mount blurred out, with the floating CTA overlay on top.
	 * The Vue app mounts on #aioseo-term-settings-metabox using the stub currentPost data populated
	 * by {@see \AIOSEO\Plugin\Lite\Traits\Helpers\Vue::setTermData()}.
	 *
	 * @since   4.0.0
	 * @version 4.9.8 Replaced the stale static HTML mock with the real Vue post-settings metabox mount.
	 *
	 * @return void
	 */
	public function addTaxonomyUpsell() {
		$screen = aioseo()->helpers->getCurrentScreen();
		if (
			! isset( $screen->parent_base ) ||
			'edit' !== $screen->parent_base ||
			empty( $screen->taxonomy )
		) {
			return;
		}

		$utmUrl = aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'lite-upgrade/', 'taxonomies-upsell', 'features=[]=taxonomies', false );
		?>
		<style>
			#poststuff.aioseo-taxonomy-upsell {
				min-width: auto;
				overflow: hidden;
			}
			.aioseo-taxonomy-upsell-loading { visibility: hidden; }
		</style>
		<div id="poststuff" class="aioseo-taxonomy-upsell aioseo-taxonomy-upsell-loading" style="margin-top:30px;max-width: 800px;">
			<div id="advanced-sortables" class="meta-box-sortables">
				<div id="aioseo-tabbed" class="postbox ">
					<h2 class="hndle">
						<span><?php esc_html_e( 'AIOSEO Settings', 'all-in-one-seo-pack' ); ?></span>
					</h2>
					<div>
						<div class="aioseo-app aioseo-post-settings">
							<div class="aioseo-blur">
								<div id="aioseo-term-settings-field">
									<input type="hidden" name="aioseo-term-settings" id="aioseo-term-settings" value="" />
									<?php wp_nonce_field( 'aioseoTermSettingsNonce', 'TermSettingsNonce' ); ?>
								</div>
								<div id="aioseo-term-settings-metabox" class="inside">
									<?php aioseo()->templates->getTemplate( 'parts/loader.php' ); ?>
								</div>
							</div>

							<div class="aioseo-cta floating" style="max-width: 630px;">
								<div class="aioseo-cta-background">
									<div class="type-1">
										<div class="header-text"><?php esc_html_e( 'Custom Taxonomies are a PRO Feature', 'all-in-one-seo-pack' ); ?></div>
										<div class="description"><?php esc_html_e( 'Set custom SEO meta, social meta and more for individual terms.', 'all-in-one-seo-pack' ); ?></div>
										<div class="feature-list aioseo-row ">
											<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
												<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
													<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
												</svg> <?php esc_html_e( 'SEO Title/Description', 'all-in-one-seo-pack' ); ?>
											</div>
											<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
												<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
													<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
												</svg> <?php esc_html_e( 'Social Meta', 'all-in-one-seo-pack' ); ?>
											</div>
											<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
												<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
													<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
												</svg> <?php esc_html_e( 'SEO Revisions', 'all-in-one-seo-pack' ); ?>
											</div>
											<div class="aioseo-col col-xs-12 col-md-6 text-xs-left">
												<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-circle-check">
													<path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM10 14.17L16.59 7.58L18 9L10 17L6 13L7.41 11.59L10 14.17Z" fill="currentColor"></path>
												</svg> <?php esc_html_e( 'Import/Export', 'all-in-one-seo-pack' ); ?>
											</div>
										</div>
										<div class="actions">
											<a class="aioseo-button green" href="<?php echo esc_url( $utmUrl ); ?>" target="_blank"><?php esc_html_e( 'Unlock Custom Taxonomies', 'all-in-one-seo-pack' ); ?></a>
											<a href="https://aioseo.com/?utm_source=WordPress&amp;utm_campaign=liteplugin&amp;utm_medium=taxonomies-upsell&amp;features[]=taxonomies" target="_blank" class="learn-more"><?php esc_html_e( 'Learn more about all features', 'all-in-one-seo-pack' ); ?></a>
										</div>

										<div class="aioseo-alert yellow medium bonus-alert"> 🎁 <span>
											<strong><?php esc_html_e( 'Bonus:', 'all-in-one-seo-pack' ); ?></strong>
											<?php esc_html_e( 'You can upgrade to the Pro plan today and ', 'all-in-one-seo-pack' ); ?>
											<strong><?php esc_html_e( 'save 50% off', 'all-in-one-seo-pack' ); ?></strong>
											<?php esc_html_e( '(discount auto-applied)', 'all-in-one-seo-pack' ); ?>.</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
			window.addEventListener( 'load', function () {
				document.querySelectorAll( '.aioseo-taxonomy-upsell-loading' ).forEach( function ( el ) {
					el.classList.remove( 'aioseo-taxonomy-upsell-loading' );
				} );
			} );
		</script>
		<?php
	}
}