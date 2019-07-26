/**
 * Counts characters of the title and description fields.
 *
 * @since 2.9.2
 * @since 3.2.0 Refactored and moved Preview Snippet code to dedicated file.
 */

$(document).ready( function() {
	$('.aioseop_count_chars').on('keypress', function(){
		var inputField = $(this).eq(0);
		var counter =  $(this).parent().find('[name="' + $(this).attr('data-length-field') + '"]').eq(0);
		aioseopCountChars( inputField, counter );
	});
});

/**
 * The aisoeopCountChars() function.
 * 
 * Counts the characters of a field.
 *
 * @since 1.0.0
 * @since 2.9.1 Fix conflict with LearnDash and function name.
 *
 * @param JQuery_Object field
 * @param JQuery_Object counter
 */
function aioseopCountChars( field, counter ) {
	var extraTitleLength = parseInt( aioseop_count_chars.extraTitleLength, 10 ); // jshint ignore:line
	var extraCharacters = 0;
	var fieldSize = 0;

	if ( ( field.attr('name') === 'aiosp_title' ) && ( typeof extraTitleLength !== 'undefined' ) ) {
		extraCharacters = extraTitleLength;
	}

	counter.val( field.val().length + extraCharacters );
	if ( typeof field.attr('size') !== 'undefined' ) {
		fieldSize = field.attr('size');
	} else {
		fieldSize = field.attr('rows') * field.attr('cols');
	}
	
	if ( counter.val() > fieldSize ) {
		counter.removeClass().addClass('aioseop_count_chars_over_treshold');
	} 
	
	else if ( ( 'aiosp_title' === field.attr('name' ) ) || ( 'aiosp_home_title' === field.attr('name') ) ) {
		counter.removeClass().addClass('aioseop_count_chars_below_treshold');
		if ( counter.val() > ( fieldSize - 6 ) ) {
			counter.removeClass().addClass('aioseop_count_chars_nearing_treshold');
		} 
	} 
	
	else {
		counter.removeClass().addClass('aioseop_count_chars_below_treshold');
		if ( counter.val() > ( fieldSize - 10 ) ) {
			counter.removeClass().addClass('aioseop_count_chars_nearing_treshold');
		}
	}
}
