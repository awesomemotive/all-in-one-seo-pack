/**
 * Script for AIOSEOP OpenGraph
 *
 * @summary For AIOSEOP OpenGraph settings on AIOSEOP screens & edit post screen (possibly more others).
 *
 * @author Michael Torbert.
 * @author Semper Fi Web Design.
 * @copyright https://semperplugins.com
 * @version 2.9.2
 */

jQuery(document).ready(function () {
	var snippet = jQuery("#aioseop_snippet_link");
	if (snippet.length === 0) {
		jQuery("#aioseop_opengraph_settings_facebook_debug_wrapper").hide();
	} else {
		snippet = snippet.html();
		jQuery("#aioseop_opengraph_settings_facebook_debug")
			.attr("href", "https://developers.facebook.com/tools/debug/sharing/?q=" + snippet);
	}
});
