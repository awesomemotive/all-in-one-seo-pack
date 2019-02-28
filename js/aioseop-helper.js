/**
 * Use for JavaScript compatability by using anonymous functions; which
 * are loaded into the footer.
 *
 * @summary   AIOSEOP Footer JS
 *
 * @since     2.4.2
 */
// phpcs:disable PEAR.Functions.FunctionCallSignature.ContentAfterOpenBracket
// phpcs:disable PEAR.Functions.FunctionCallSignature.MultipleArguments
// phpcs:disable PEAR.Functions.FunctionCallSignature.CloseBracketLine
(function($) {
	/**
	 * jQuery UI Tooltips
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
			open: function( event, ui ) {
				ui.tooltip.css( "min-width", "170px" );
				ui.tooltip.css( "max-width", "396px" );

				if ( 'undefined' === typeof( event.originalEvent ) ) {
					return false;
				}

				var $id = $( ui.tooltip ).attr( 'id' );

				// Close any lingering tooltips.
				$( 'div.ui-tooltip' ).not( '#' + $id ).remove();

				// AJAX function to pull in data and add it to the tooltip goes here.
			},
			close: function( event, ui ) {
				ui.tooltip.hover(
					function() {
						$( this ).stop( true ).fadeTo( 400, 1 );
					},
					function() {
						$( this ).fadeOut( '400', function() {
							$( this ).remove();
						});
					}
				);
			},
			content: function( callback ) {
				callback( $( this ).prop( "title" ) );
			}
		});
	}

	aioseopTooltips();
}(jQuery));
//phpcs:enable
