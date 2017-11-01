/**
 * Use for JavaScript compatability by using anonymous functions; which
 * are loaded into the footer.
 *
 * @summary   AIOSEOP Footer JS
 *
 * @since     2.4.2
 */

(function($) {
	/**
	 * jQuery UI Tooltips.
	 *
	 * Initiates the jQuery UI Tooltips on the admin pages with the class and
	 * title attributes set properly.
	 *
	 * @since 2.4.1.1
	 *
	 * @link http://api.jqueryui.com/tooltip/
	 */
	function aioseopTooltips() {
		$( ".aioseop_help_text_link" ).tooltip({
			open( event, ui) {
				ui.tooltip.css("min-width", "170px");
				ui.tooltip.css("max-width", "256px");
			},
			content( callback ) {
				callback( $( this ).prop( "title" ) );
			}
		});
	}

	aioseopTooltips();
})(jQuery);


