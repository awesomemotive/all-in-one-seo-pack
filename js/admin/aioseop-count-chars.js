/**
 * Counts characters of the title and description fields.
 *
 * @since 2.9.2
 * @since 3.2.0 Moved to its own file.
 * @since 3.3.0 Refactored to fix bugs.
 */

let extraTitleLength = parseInt( characterCounter.extraTitleLength, 10 );

$( function() {
	let inputField;
	let cntField;
	let fieldSize;

	aioseopInitCountChars();

	/**
	 * Adds the event listener to all elements with .aioseop_count_chars class.
	 *
	 * @since ?
	 * @since 3.3.0 Refactored.
	 * 
	 * @return void
	 */
	function aioseopInitCountChars() {
		$('.aioseop_count_chars').on('keyup keydown', function () {
			inputField = $(this);
			cntField = $(this).parent().find( '[name="' + $(this).attr('data-length-field') + '"]')
			aioseopCountChars();
		});
	}

	/**
	 * Counts the characters of a certain field and displays this on the front-end.
	 * 
	 *
	 * @since 1.0.0
	 * @since 2.9.1 Fix JS conflict with LearnDash and function name.
	 * @since 3.3.0 Refactored.
	 *
	 * @return void
	 */
	function aioseopCountChars() {
		let extra = 0;

		if ( ( 'aiosp_title' === inputField.attr('name') ) && ( 'undefined' !== typeof extraTitleLength) ) {
			extra = extraTitleLength;
		}

		cntField.val( +inputField.val().length + extra );

		if ('undefined' !== typeof inputField.attr('size')) {
			fieldSize = +inputField.attr('size');
		} else {
			fieldSize = +inputField.attr('rows') * +inputField.attr('cols');
		}

		aioseopChangeCountBackground();
	}

	/**
	 * Changes the background colour of the character counter field based on the amount of characters.
	 * 
	 * @since 3.3.0
	 * 
	 * @return void
	 */
	function aioseopChangeCountBackground() {

		if (+cntField.val() > +fieldSize) {
			cntField.removeClass().addClass('aioseop_count_chars_past_treshold');
		}

		else if (('aiosp_title' === inputField.attr('name')) || ('aiosp_home_title' === inputField.attr('name'))) {
			if (+cntField.val() > (+fieldSize - 6)) {
				cntField.removeClass().addClass('aioseop_count_chars_near_treshold');
			} else {
				cntField.removeClass().addClass('aioseop_count_chars_below_treshold');
			}
		}

		else {
			if (+cntField.val() > (+fieldSize - 10)) {
				cntField.removeClass().addClass('aioseop_count_chars_near_treshold');
			} else {
				cntField.removeClass().addClass('aioseop_count_chars_below_treshold');
			}
		}
	}

});
