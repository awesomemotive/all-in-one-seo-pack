<?php
/**
 * XSL emptySitemap template for the sitemap.
 *
 * @since 4.1.5
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$canManageSitemap = is_user_logged_in() && aioseo()->access->hasCapability( 'aioseo_sitemap_settings' );
$adminUrl         = admin_url( 'admin.php?page=aioseo-sitemaps' );

// phpcs:disable
if ( 'xml-sitemap' !== $data['utmMedium'] ) {
	$adminUrl .= '#/' . str_replace( 'aioseo-', '', $data['utmMedium'] );
}
?>
<xsl:template name="emptySitemap">
	<?php
	if ( ! empty( $data['items'] ) ) {
		aioseo()->templates->getTemplate(
			'sitemap/xsl/partials/breadcrumb.php',
			[ 'items' => $data['items'] ]
		);
	}
	?>
	<div class="empty-sitemap">
		<h2 class="empty-sitemap__title">
			<?php _e( 'Whoops!', 'all-in-one-seo-pack' ); ?>
			<br />
			<?php _e( 'There are no posts here', 'all-in-one-seo-pack' ); ?>
		</h2>
		<div class="empty-sitemap__buttons">
			<a href="<?php echo esc_attr( home_url() ); ?>" class="button"><?php _e( 'Back to Homepage', 'all-in-one-seo-pack' ); ?></a>
			<?php if ( $canManageSitemap ) : ?>
				<a href="<?php echo esc_attr( esc_url( $adminUrl ) ); ?>" class="button"><?php _e( 'Configure Sitemap', 'all-in-one-seo-pack' ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( $canManageSitemap ) : ?>
			<div class="aioseo-alert yellow">
				<?php
					echo sprintf(
						// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
						__( 'Didn\'t expect to see this? Make sure your sitemap is enabled and your content is set to be indexed. %1$sLearn More →%2$s', 'all-in-one-seo-pack' ),
						'<a target="_blank" href="' . aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'docs/how-to-fix-a-404-error-when-viewing-your-sitemap/', $data['utmMedium'], 'learn-more' ) . '">',
						'</a>'
					);
				?>
			</div>
		<?php endif; ?>
	</div>
	<style>
		.hand-magnifier{
			animation: hand-magnifier .8s infinite ease-in-out;
			transform-origin: center 90%;
			transform-box: fill-box;
		}
		@keyframes hand-magnifier {
			0% {
				transform: rotate(0deg);
			}
			50% {
				transform: rotate(-12deg);
			}
			100% {
				transform: rotate(0deg);
			}
		}
	</style>
</xsl:template>
