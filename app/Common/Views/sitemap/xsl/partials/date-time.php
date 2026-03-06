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
if ( empty( $data['datetime'] ) || empty( $data['node'] ) ) {
	return;
}

?>
<div class="date">
	<xsl:choose>
		<?php foreach ( $data['datetime'] as $slug => $datetime ) : ?>
			<xsl:when test="<?php echo esc_attr( $data['node'] ); ?> = '<?php echo esc_attr( $slug ); ?>'"><?php echo esc_html( $datetime['date'] ); ?></xsl:when>
		<?php endforeach; ?>
	</xsl:choose>
</div>
<div class="time">
	<xsl:choose>
		<?php foreach ( $data['datetime'] as $slug => $datetime ) : ?>
			<xsl:when test="<?php echo esc_attr( $data['node'] ); ?> = '<?php echo esc_attr( $slug ); ?>'"><?php echo esc_html( $datetime['time'] ); ?></xsl:when>
		<?php endforeach; ?>
	</xsl:choose>
</div>