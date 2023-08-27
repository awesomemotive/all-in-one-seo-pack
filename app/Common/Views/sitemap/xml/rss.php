<?php
/**
 * XML template for the RSS Sitemap.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel><?php
	// Yandex doesn't support some tags so we need to check the user agent.
	if ( ! aioseo()->helpers->isYandexUserAgent() ) {
		?>

		<title><?php aioseo()->sitemap->output->escapeAndEcho( $title, false ); ?></title>
		<link><?php aioseo()->sitemap->output->escapeAndEcho( $link ); ?></link>
		<?php if ( $description ) {
		?><description><?php aioseo()->sitemap->output->escapeAndEcho( $description ); ?></description>
		<?php }
		?><?php if ( ! empty( $entries[0]['pubDate'] ) ) {
		?><lastBuildDate><?php aioseo()->sitemap->output->escapeAndEcho( $entries[0]['pubDate'] ); ?></lastBuildDate>
		<?php }
		?><docs>https://validator.w3.org/feed/docs/rss2.html</docs>
		<atom:link href="<?php echo aioseo()->sitemap->helpers->getUrl( 'rss' ); ?>" rel="self" type="application/rss+xml" />
		<ttl><?php aioseo()->sitemap->output->escapeAndEcho( $ttl ); ?></ttl>

<?php }
foreach ( $entries as $entry ) {
		if ( empty( $entry['guid'] ) ) {
			continue;
			}?>
		<item>
			<guid><?php aioseo()->sitemap->output->escapeAndEcho( $entry['guid'] ); ?></guid>
			<link><?php aioseo()->sitemap->output->escapeAndEcho( $entry['guid'] ); ?></link><?php
			if ( ! empty( $entry['title'] ) ) {
				?>

			<title><?php aioseo()->sitemap->output->escapeAndEcho( $entry['title'], false ); ?></title><?php
			}
			if ( ! empty( $entry['pubDate'] ) ) {
				?>

			<pubDate><?php aioseo()->sitemap->output->escapeAndEcho( $entry['pubDate'] ); ?></pubDate><?php
			}
			?>

		</item>
			<?php } ?>
	</channel>
</rss>
