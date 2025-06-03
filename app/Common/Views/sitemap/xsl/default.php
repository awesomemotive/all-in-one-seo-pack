<?php
/**
 * XSL stylesheet for the sitemap.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
$utmMedium = 'xml-sitemap';
if ( '/sitemap.rss' === $sitemapPath ) {
	$utmMedium = 'rss-sitemap';
}
?>
<xsl:stylesheet
	version="2.0"
	xmlns:html="http://www.w3.org/TR/html40"
	xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="/">
		<xsl:variable name="fileType">
			<xsl:choose>
				<xsl:when test="//channel">RSS</xsl:when>
				<xsl:when test="//sitemap:url">Sitemap</xsl:when>
				<xsl:otherwise>SitemapIndex</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>
					<xsl:choose>
						<xsl:when test="$fileType='Sitemap' or $fileType='RSS'"><?php echo $title; ?></xsl:when>
						<xsl:otherwise><?php _e( 'Sitemap Index', 'all-in-one-seo-pack' ); ?></xsl:otherwise>
					</xsl:choose>
				</title>
				<meta name="viewport" content="width=device-width, initial-scale=1" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<?php aioseo()->templates->getTemplate( 'sitemap/xsl/styles.php' ); ?>
			</head>
			<body>
				<xsl:variable name="amountOfURLs">
					<xsl:choose>
						<xsl:when test="$fileType='RSS'">
							<xsl:value-of select="count(//channel/item)"></xsl:value-of>
						</xsl:when>
						<xsl:when test="$fileType='Sitemap'">
							<xsl:value-of select="count(sitemap:urlset/sitemap:url)"></xsl:value-of>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"></xsl:value-of>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<xsl:call-template name="Header">
					<xsl:with-param name="title"><?php echo $title; ?></xsl:with-param>
					<xsl:with-param name="amountOfURLs" select="$amountOfURLs"/>
					<xsl:with-param name="fileType" select="$fileType"/>
				</xsl:call-template>

				<div class="content">
					<div class="container">
						<xsl:choose>
							<xsl:when test="$amountOfURLs = 0"><xsl:call-template name="emptySitemap"/></xsl:when>
							<xsl:when test="$fileType='Sitemap'"><xsl:call-template name="sitemapTable"/></xsl:when>
							<xsl:when test="$fileType='RSS'"><xsl:call-template name="sitemapRSS"/></xsl:when>
							<xsl:otherwise><xsl:call-template name="siteindexTable"/></xsl:otherwise>
						</xsl:choose>
					</div>
				</div>
			</body>
		</html>
	</xsl:template>

	<xsl:template name="siteindexTable">
		<?php
		$sitemapIndex = aioseo()->sitemap->helpers->filename( 'general' );
		$sitemapIndex = $sitemapIndex ? $sitemapIndex : 'sitemap';
		aioseo()->templates->getTemplate(
			'sitemap/xsl/partials/breadcrumb.php',
			[
				'items' => [
					[ 'title' => __( 'Sitemap Index', 'all-in-one-seo-pack' ), 'url' => $sitemapUrl ],
				]
			]
		);
		?>
		<div class="table-wrapper">
			<table cellpadding="3">
				<thead>
				<tr>
					<th class="left">
						<?php _e( 'URL', 'all-in-one-seo-pack' ); ?>
					</th>
					<th><?php _e( 'URL Count', 'all-in-one-seo-pack' ); ?></th>
					<th>
						<?php
						aioseo()->templates->getTemplate(
							'sitemap/xsl/partials/sortable-column.php',
							[
								'parameters' => $sitemapParams,
								'sitemapUrl' => $sitemapUrl,
								'column'     => 'date',
								'title'      => __( 'Last Updated', 'all-in-one-seo-pack' )
							]
						);
						?>
					</th>
				</tr>
				</thead>
				<tbody>
				<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
				<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
				<xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
					<?php
					aioseo()->templates->getTemplate(
						'sitemap/xsl/partials/xsl-sort.php',
						[
							'parameters' => $sitemapParams,
							'node'       => 'sitemap:lastmod',
						]
					);
					?>
					<tr>
						<xsl:if test="position() mod 2 != 0">
							<xsl:attribute name="class">stripe</xsl:attribute>
						</xsl:if>
						<td class="left">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="sitemap:loc" />
								</xsl:attribute>
								<xsl:value-of select="sitemap:loc"/>
							</a>
						</td>
						<td>
							<?php if ( ! empty( $xslParams['counts'] ) ) : ?>
							<div class="item-count">
							<xsl:choose>
								<?php foreach ( $xslParams['counts'] as $slug => $count ) : ?>
									<xsl:when test="contains(sitemap:loc, '<?php echo $slug; ?>')"><?php echo $count; ?></xsl:when>
								<?php endforeach; ?>
								<xsl:otherwise><?php echo $linksPerIndex; ?></xsl:otherwise>
							</xsl:choose>
							</div>
							<?php endif; ?>
						</td>
						<td class="datetime">
							<?php
							if ( ! empty( $xslParams['datetime'] ) ) {
								aioseo()->templates->getTemplate(
									'sitemap/xsl/partials/date-time.php',
									[
										'datetime' => $xslParams['datetime'],
										'node'     => 'sitemap:loc'
									]
								);
							}
							?>
						</td>
					</tr>
				</xsl:for-each>
				</tbody>
			</table>
		</div>
	</xsl:template>

	<xsl:template name="sitemapRSS">
		<?php
		aioseo()->templates->getTemplate(
			'sitemap/xsl/partials/breadcrumb.php',
			[
				'items' => [
					[ 'title' => $title, 'url' => $sitemapUrl ],
				]
			]
		);
		?>
		<div class="table-wrapper">
			<table cellpadding="3">
				<thead>
					<tr>
						<th class="left"><?php _e( 'URL', 'all-in-one-seo-pack' ); ?></th>
						<th>
							<?php
							aioseo()->templates->getTemplate(
								'sitemap/xsl/partials/sortable-column.php',
								[
									'parameters' => $sitemapParams,
									'sitemapUrl' => $sitemapUrl,
									'column'     => 'date',
									'title'      => __( 'Publication Date', 'all-in-one-seo-pack' )
								]
							);
							?>
						</th>
					</tr>
				</thead>
				<tbody>
				<xsl:for-each select="//channel/item">
					<?php
					if ( ! empty( $sitemapParams['sitemap-order'] ) ) {
						aioseo()->templates->getTemplate(
							'sitemap/xsl/partials/xsl-sort.php',
							[
								'parameters' => $sitemapParams,
								'node'       => 'pubDate',
							]
						);
					}
					?>
					<tr>
						<xsl:if test="position() mod 2 != 0">
							<xsl:attribute name="class">stripe</xsl:attribute>
						</xsl:if>
						<td class="left">
							<a>
								<xsl:attribute name="href">
									<xsl:value-of select="link" />
								</xsl:attribute>
								<xsl:value-of select="link"/>
							</a>
						</td>
						<td class="datetime">
							<?php
							if ( ! empty( $xslParams['datetime'] ) ) {
								aioseo()->templates->getTemplate(
									'sitemap/xsl/partials/date-time.php',
									[
										'datetime' => $xslParams['datetime'],
										'node'     => 'link'
									]
								);
							}
							?>
						</td>
					</tr>
				</xsl:for-each>
				</tbody>
			</table>
		</div>
	</xsl:template>

	<xsl:template name="sitemapTable">
		<?php
		$sitemapIndex  = aioseo()->sitemap->helpers->filename( 'general' );
		$sitemapIndex  = $sitemapIndex ?: 'sitemap';
		$excludeImages = isset( $excludeImages ) ? $excludeImages : aioseo()->sitemap->helpers->excludeImages();
		aioseo()->templates->getTemplate(
			'sitemap/xsl/partials/breadcrumb.php',
			[
				'items' => [
					[ 'title' => __( 'Sitemap Index', 'all-in-one-seo-pack' ), 'url' => home_url( "/$sitemapIndex.xml" ) ],
					[ 'title' => $title, 'url' => $sitemapUrl ],
				]
			]
		);
		?>
		<div class="table-wrapper">
			<table cellpadding="3">
				<thead>
					<tr>
						<th class="left">
							<?php _e( 'URL', 'all-in-one-seo-pack' ); ?>
						</th>
						<?php if ( ! $excludeImages ) : ?>
							<th>
								<?php
								aioseo()->templates->getTemplate(
									'sitemap/xsl/partials/sortable-column.php',
									[
										'parameters' => $sitemapParams,
										'sitemapUrl' => $sitemapUrl,
										'column'     => 'image',
										'title'      => __( 'Images', 'all-in-one-seo-pack' )
									]
								);
								?>
							</th>
						<?php endif; ?>
						<th>
							<?php
							aioseo()->templates->getTemplate(
								'sitemap/xsl/partials/sortable-column.php',
								[
									'parameters' => $sitemapParams,
									'sitemapUrl' => $sitemapUrl,
									'column'     => 'changefreq',
									'title'      => __( 'Change Frequency', 'all-in-one-seo-pack' )
								]
							);
							?>
						</th>
						<th>
							<?php
							aioseo()->templates->getTemplate(
								'sitemap/xsl/partials/sortable-column.php',
								[
									'parameters' => $sitemapParams,
									'sitemapUrl' => $sitemapUrl,
									'column'     => 'priority',
									'title'      => __( 'Priority', 'all-in-one-seo-pack' )
								]
							);
							?>
						</th>
						<th>
							<?php
							aioseo()->templates->getTemplate(
								'sitemap/xsl/partials/sortable-column.php',
								[
									'parameters' => $sitemapParams,
									'sitemapUrl' => $sitemapUrl,
									'column'     => 'date',
									'title'      => __( 'Last Updated', 'all-in-one-seo-pack' )
								]
							);
							?>
						</th>
					</tr>
				</thead>
				<tbody>
				<xsl:variable name="lower" select="'abcdefghijklmnopqrstuvwxyz'"/>
				<xsl:variable name="upper" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'"/>
				<xsl:for-each select="sitemap:urlset/sitemap:url">
					<?php
					if ( ! empty( $sitemapParams['sitemap-order'] ) ) {
						switch ( $sitemapParams['sitemap-order'] ) {
							case 'image':
								$node = 'count(image:image)';
								break;
							case 'date':
								$node = 'sitemap:lastmod';
								break;
							default:
								$node = 'sitemap:' . $sitemapParams['sitemap-order'];
								break;
						}
						aioseo()->templates->getTemplate(
							'sitemap/xsl/partials/xsl-sort.php',
							[
								'parameters' => $sitemapParams,
								'node'       => $node,
							]
						);
					}
					?>
					<tr>
						<xsl:if test="position() mod 2 != 0">
							<xsl:attribute name="class">stripe</xsl:attribute>
						</xsl:if>

						<td class="left">
							<xsl:variable name="itemURL">
								<xsl:value-of select="sitemap:loc"/>
							</xsl:variable>

							<xsl:choose>
								<xsl:when test="count(./*[@rel='alternate']) > 0">
									<xsl:for-each select="./*[@rel='alternate']">
										<xsl:if test="position() = last()">
											<a href="{current()/@href}" class="localized">
												<xsl:value-of select="@href"/>
											</a> &#160;&#8594; <xsl:value-of select="@hreflang"/>
										</xsl:if>
									</xsl:for-each>
								</xsl:when>
								<xsl:otherwise>
									<a href="{$itemURL}">
										<xsl:value-of select="sitemap:loc"/>
									</a>
								</xsl:otherwise>
							</xsl:choose>

							<xsl:for-each select="./*[@rel='alternate']">
								<br />
								<xsl:if test="position() != last()">
									<a href="{current()/@href}" class="localized">
										<xsl:value-of select="@href"/>
									</a> &#160;&#8594; <xsl:value-of select="@hreflang"/>
								</xsl:if>
							</xsl:for-each>
						</td>
						<?php if ( ! $excludeImages ) : ?>
						<td>
							<div class="item-count">
								<xsl:value-of select="count(image:image)"/>
							</div>
						</td>
						<?php endif; ?>
						<td>
							<xsl:value-of select="concat(translate(substring(sitemap:changefreq, 1, 1),concat($lower, $upper),concat($upper, $lower)),substring(sitemap:changefreq, 2))"/>
						</td>
						<td>
							<xsl:if test="string(number(sitemap:priority))!='NaN'">
								<xsl:call-template name="formatPriority">
									<xsl:with-param name="priority" select="sitemap:priority"/>
								</xsl:call-template>
							</xsl:if>
						</td>
						<td class="datetime">
							<?php
							if ( ! empty( $xslParams['datetime'] ) ) {
								aioseo()->templates->getTemplate(
									'sitemap/xsl/partials/date-time.php',
									[
										'datetime' => $xslParams['datetime'],
										'node'     => 'sitemap:loc'
									]
								);
							}
							?>
						</td>
					</tr>
				</xsl:for-each>
				</tbody>
			</table>
		</div>
		<?php
		if ( ! empty( $xslParams['pagination'] ) ) {
			aioseo()->templates->getTemplate(
				'sitemap/xsl/partials/pagination.php',
				[
					'sitemapUrl'    => $sitemapUrl,
					'currentPage'   => $currentPage,
					'linksPerIndex' => $linksPerIndex,
					'total'         => $xslParams['pagination']['total'],
					'showing'       => $xslParams['pagination']['showing']
				]
			);
		}
		?>
	</xsl:template>

	<?php aioseo()->templates->getTemplate( 'sitemap/xsl/templates/header.php', [ 'utmMedium' => $utmMedium ] ); ?>
	<?php aioseo()->templates->getTemplate( 'sitemap/xsl/templates/format-priority.php' ); ?>
	<?php
	aioseo()->templates->getTemplate( 'sitemap/xsl/templates/empty-sitemap.php', [
		'utmMedium' => $utmMedium,
		'items'     => [
			[ 'title' => __( 'Sitemap Index', 'all-in-one-seo-pack' ), 'url' => $sitemapUrl ]
		]
	] );
	?>
</xsl:stylesheet>
