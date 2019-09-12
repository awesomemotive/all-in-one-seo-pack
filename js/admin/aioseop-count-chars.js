/**
 * Counts characters of the title and description fields.
 *
 * @since 2.9.2
 * @since 3.2.0 Moved to its own file.
 */


var aiosp_title_extra = parseInt( aioseop_count_chars.extraTitleLength, 10 );

jQuery( document ).ready( function() {
	aioseopInitCounting();
});

/**
 * AIOSEOP Init Counting
 *
 * @since ?
 */
function aioseopInitCounting(){
	/* count them characters */
	jQuery( '.aioseop_count_chars' ).on('keyup keydown', function(){
		aioseopCountChars( jQuery(this).eq(0), jQuery(this).parent().find('[name="' + jQuery(this).attr('data-length-field') + '"]').eq(0));
	});
	jQuery( '.aioseop_count_chars' ).each(function(){
		aioseopCountChars( jQuery(this).eq(0), jQuery(this).parent().find('[name="' + jQuery(this).attr('data-length-field') + '"]').eq(0));
	});
}

/**
 * @summary Counts characters.
 *
 * @since 1.0.0
 * @since 2.9.1 Fix JS conflict with LearnDash and function name.
 *
 * @param Object $field.
 * @param Object $cntfield.
 * @return Mixed.
 */
function aioseopCountChars( field, cntfield ) {
	var extra = 0;
	var field_size;
	if ( ( field.attr('name') === 'aiosp_title' ) && ( typeof aiosp_title_extra !== 'undefined' ) ) {
		extra = aiosp_title_extra;
	}
	cntfield.val( field.val().length + extra );
	if ( typeof field.attr('size') !== 'undefined' ) {
		field_size = field.attr('size');
	} else {
		field_size = field.attr('rows') * field.attr('cols');
	}
	field_size = parseInt(field_size, 10);
	if ( field_size < 10 ) {
		return;
	}
	if ( cntfield.val() > field_size ) {
		cntfield.removeClass().addClass('aioseop_count_ugly');
	} else if ( ( 'aiosp_title' === field.attr('name' ) ) || ( 'aiosp_home_title' === field.attr('name') ) ) {
		if ( cntfield.val() > ( field_size - 6 ) ) {
			cntfield.removeClass().addClass('aioseop_count_bad');
		} else {
			cntfield.removeClass().addClass('aioseop_count_good');
		}
	} else {
		if ( cntfield.val() > ( field_size - 10 ) ) {
			cntfield.removeClass().addClass('aioseop_count_bad');
		} else {
			cntfield.removeClass().addClass('aioseop_count_good');
		}
	}
}
