<?php
/**
 * XML template for our sitemap index pages.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>
<urlset
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xhtml="http://www.w3.org/1999/xhtml"
<?php if ( ! aioseo()->sitemap->helpers->excludeImages() ): ?>
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
<?php endif; ?>
>
<?php foreach ( $entries as $entry ) {
	if ( empty( $entry['loc'] ) ) {
		continue;
	}
	?>
	<url>
		<loc><?php aioseo()->sitemap->output->escapeAndEcho( $entry['loc'] ); ?></loc><?php
	if ( ! empty( $entry['lastmod'] ) ) {
			?>

		<lastmod><?php aioseo()->sitemap->output->escapeAndEcho( $entry['lastmod'] ); ?></lastmod><?php
	}
	if ( ! empty( $entry['changefreq'] ) ) {
			?>

		<changefreq><?php aioseo()->sitemap->output->escapeAndEcho( $entry['changefreq'] ); ?></changefreq><?php
	}
	if ( isset( $entry['priority'] ) ) {
			?>

		<priority><?php aioseo()->sitemap->output->escapeAndEcho( $entry['priority'] ); ?></priority><?php
	}
	if ( ! empty( $entry['languages'] ) ) {
		foreach ( $entry['languages'] as $subentry ) {
			if ( empty( $subentry['language'] ) || empty( $subentry['location'] ) ) {
				continue;
			}
		?>

		<xhtml:link rel="alternate" hreflang="<?php echo esc_attr( $subentry['language'] ); ?>" href="<?php echo esc_url( $subentry['location'] ); ?>" /><?php
		}
	}
	if ( ! aioseo()->sitemap->helpers->excludeImages() && ! empty( $entry['images'] ) ) {
			foreach ( $entry['images'] as $image ) {
				$image = (array) $image;
			?>

		<image:image>
			<image:loc><?php aioseo()->sitemap->output->escapeAndEcho( $image['image:loc'] ); ?></image:loc>
		</image:image><?php
		}
	}
	?>

	</url>
<?php } ?>
</urlset>
