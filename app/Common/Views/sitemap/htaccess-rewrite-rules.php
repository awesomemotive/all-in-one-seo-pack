<?php
/**
 * Htaccess rewrite rules for sites using plain permalinks.
 *
 * @since 4.2.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>


# START: All in One SEO Sitemap Rewrite Rules
# Do not make edits to these rules!
<IfModule mod_rewrite.c>
	RewriteEngine On

	RewriteRule sitemap(|[0-9]+)\.xml$ /index.php [L]
	RewriteRule (default|video)-sitemap\.xsl /index.php [L]
</IfModule>
# END: All in One SEO Sitemap Rewrite Rules