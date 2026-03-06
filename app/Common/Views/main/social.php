<?php
/**
 * This is the output for social meta on the page.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable Generic.WhiteSpace.ScopeIndent, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Set context for meta class to social meta.
$facebookMeta = aioseo()->social->output->getFacebookMeta();
foreach ( $facebookMeta as $key => $meta ) :
	// Each article tag needs to be output in a separate meta tag so we cast and loop over each key.
	if ( ! is_array( $meta ) ) {
		$meta = [ $meta ];
	}
	foreach ( $meta as $m ) :
	?>
		<meta property="<?php echo esc_attr( $key ); ?>" content="<?php echo esc_attr( $m ); ?>" />
<?php
	endforeach;
endforeach;

$twitterMeta = aioseo()->social->output->getTwitterMeta();
foreach ( $twitterMeta as $key => $meta ) :
?>
		<meta name="<?php echo esc_attr( $key ); ?>" content="<?php echo esc_attr( $meta ); ?>" />
<?php
endforeach;