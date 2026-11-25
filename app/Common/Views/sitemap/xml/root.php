<?php
/**
 * XML template for our root index page.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

 // phpcs:disable
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php aioseo()->sitemap->xsl->printScript(); ?>
<?php foreach ( $entries as $entry ) {
	if ( empty( $entry['loc'] ) ) {
		continue;
	}
	?>
	<sitemap>
		<loc><?php aioseo()->sitemap->output->escapeAndEcho( $entry['loc'] ); ?></loc><?php
	if ( ! empty( $entry['lastmod'] ) ) {
			?>

		<lastmod><?php aioseo()->sitemap->output->escapeAndEcho( $entry['lastmod'] ); ?></lastmod><?php
		}
	?>

	</sitemap>
<?php } ?>
</sitemapindex>
