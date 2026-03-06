<?php
/**
 * This is the output for Microsoft Clarity on the page.
 *
 * @since 4.1.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$projectId = aioseo()->options->webmasterTools->microsoftClarityProjectId;

if ( empty( $projectId ) || aioseo()->helpers->isAmpPage() ) {
	return;
}
?>
		<script type="text/javascript">
			(function(c,l,a,r,i,t,y){
			c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;
			t.src="https://www.clarity.ms/tag/"+i+"?ref=aioseo";y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
		})(window, document, "clarity", "script", "<?php echo esc_js( $projectId ); ?>");
		</script>
<?php
// Leave this comment to allow for a line break after the closing script tag.