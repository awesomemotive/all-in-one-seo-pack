<?php
/**
 * XSL Breadcrumb partial for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if ( empty( $data['items'] ) ) {
	return;
}

$sitemapIndex = aioseo()->sitemap->helpers->filename( 'general' );
$sitemapIndex = $sitemapIndex ? $sitemapIndex : 'sitemap';
?>
<div class="breadcrumb">
	<svg class="back" width="6" height="9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.274 7.56L2.22 4.5l3.054-3.06-.94-.94-4 4 4 4 .94-.94z" fill="#141B38"/></svg>

	<a href="<?php echo esc_attr( home_url() ); ?>"><span><?php esc_attr_e( 'Home', 'all-in-one-seo-pack' ); ?></span></a>

	<?php
	foreach ( $data['items'] as $key => $item ) {
		if ( empty( $item ) ) {
			continue;
		}
		?>
		<svg width="6" height="8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M.727 7.06L3.78 4 .727.94l.94-.94 4 4-4 4-.94-.94z" fill="#141B38"/></svg>

		<?php if ( count( $data['items'] ) === $key + 1 ) : ?>
			<span><?php echo esc_html( $item['title'] ); ?></span>
		<?php else : ?>
			<a href="<?php echo esc_attr( $item['url'] ) ?>"><span><?php echo esc_html( $item['title'] ); ?></span></a>
		<?php endif; ?>
		<?php
	}
	?>
</div>