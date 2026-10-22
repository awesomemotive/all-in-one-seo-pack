<?php
/**
 * XSL Breadcrumb partial for the sitemap.
 *
 * @since   4.1.5
 * @version 5.0.4 First crumb now links to the sitemap index instead of the site homepage; added $data['index'].
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$crumbs = isset( $data['items'] ) ? $data['items'] : [];

// Only sitemaps the general index lists start there; others (video, news, RSS) pass their own root as the first item.
// With the general sitemap off, /sitemap.xml redirects to the WordPress core sitemap.
if ( ! empty( $data['index'] ) && aioseo()->options->sitemap->general->enable ) {
	$sitemapIndex = aioseo()->sitemap->helpers->filename( 'general' );
	$sitemapIndex = $sitemapIndex ? $sitemapIndex : 'sitemap';
	array_unshift( $crumbs, [
		'title' => __( 'Sitemap Index', 'all-in-one-seo-pack' ),
		'url'   => home_url( "/$sitemapIndex.xml" )
	] );
}

$crumbs = array_values( array_filter( $crumbs ) );
if ( empty( $crumbs ) ) {
	return;
}
?>
<div class="breadcrumb">
	<?php if ( 1 < count( $crumbs ) ) : ?>
		<svg class="back" width="6" height="9" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.274 7.56L2.22 4.5l3.054-3.06-.94-.94-4 4 4 4 .94-.94z" fill="#141B38"/></svg>
	<?php endif; ?>

	<?php
	foreach ( $crumbs as $key => $item ) {
		if ( 0 < $key ) {
			?>
			<svg width="6" height="8" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M.727 7.06L3.78 4 .727.94l.94-.94 4 4-4 4-.94-.94z" fill="#141B38"/></svg>
			<?php
		}

		if ( count( $crumbs ) === $key + 1 ) :
			?>
			<span><?php echo esc_html( $item['title'] ); ?></span>
		<?php else : ?>
			<a href="<?php echo esc_url( $item['url'] ); ?>"><span><?php echo esc_html( $item['title'] ); ?></span></a>
			<?php
		endif;
	}
	?>
</div>