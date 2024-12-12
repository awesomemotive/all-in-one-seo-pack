<?php
/**
 * XSL Pagination partial for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't allow pagination for now.
return;

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

// Check if requires pagination.
if ( $data['showing'] === $data['total'] ) {
	return;
}

$currentPage   = (int) $data['currentPage'];
$totalLinks    = (int) $data['total'];
$showing       = (int) $data['showing'];
$linksPerIndex = (int) $data['linksPerIndex'];
$totalPages    = ceil( $totalLinks / $linksPerIndex );
$start         = ( ( $currentPage - 1 ) * $linksPerIndex ) + 1;
$end           = ( ( $currentPage - 1 ) * $linksPerIndex ) + $showing;

$hasNextPage = $totalPages > $currentPage;
$hasPrevPage = $currentPage > 1;
$nextPageUri = $hasNextPage ? preg_replace( '/sitemap([0-9]*)\.xml/', 'sitemap' . ( $currentPage + 1 ) . '.xml', (string) $data['sitemapUrl'] ) : '#';
$prevPageUri = $hasPrevPage ? preg_replace( '/sitemap([0-9]*)\.xml/', 'sitemap' . ( $currentPage - 1 ) . '.xml', (string) $data['sitemapUrl'] ) : '#';
?>
<div class="pagination">
	<div class="label">
		<?php
		echo esc_html(
			sprintf(
				// Translators: 1 - The "start-end" pagination results, 2 - Total items.
				__( 'Showing %1$s of %2$s', 'all-in-one-seo-pack' ),
				"$start-$end",
				$totalLinks
			)
		);
		?>
	</div>

	<a href="<?php echo esc_attr( $prevPageUri ); ?>" class="<?php echo $hasPrevPage ? '' : 'disabled'; ?>">
		<svg width="7" height="10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.842 8.825L3.025 5l3.817-3.825L5.667 0l-5 5 5 5 1.175-1.175z" fill="#141B38"/></svg>
	</a>

	<a href="<?php echo esc_attr( $nextPageUri ); ?>" class="<?php echo $hasNextPage ? '' : 'disabled'; ?>">
		<svg width="7" height="10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M.158 8.825L3.975 5 .158 1.175 1.333 0l5 5-5 5L.158 8.825z" fill="#141B38"/></svg>
	</a>
</div>