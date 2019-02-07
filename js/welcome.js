(function($) {

	$( document ).on(
		'click', '.nav-tab-wrapper a', function() {
			$( 'section' ).hide();
			$( 'section' ).eq( $( this ).index() ).show();

			// alert($('section'));
			if ($( this ).attr( 'className' ) === 'nav-tab-active') {
				// $(this).removeClass('nav-tab-active');
				// $(this).addClass('nav-tab-active');
			} else {
				// $(this).addClass('nav-tab-active');
				// $(this).removeClass('nav-tab-active');
			}
			// $(this).addClass('nav-tab-active');
			// $(this).eq($(this).index()).removeClass('nav-tab-active');
			// $(this).removeClass('nav-tab-active');
			return false;
		}
	);

	$( "a.nav-tab" ).click(
		function() {

			$( "a.nav-tab" ).removeClass( 'nav-tab-active' );
			$( this ).addClass( 'nav-tab-active' );

		}
	);

}( jQuery ));
