<?php
/**
 * This is the output for structured data/schema on the page.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
// phpcs:disable Generic.Files.EndFileNoNewline.Found

$schema = aioseo()->schema->get();
?>
<?php if ( ! empty( $schema ) ) : ?>
		<script type="application/ld+json" class="aioseo-schema">
			<?php echo $schema . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</script>
<?php
endif;
