/**
 * Counts characters of the title and description fields.
 *
 * @since 2.9.2
 * @since 3.2.0 Moved to its own file.
 * @since 3.3.0 Refactored to fix bugs.
 */

var extraTitleLength = +aioseopCharacterCounter.extraTitleLength;
var aioseopCharacterCounterIsGutenberg = aioseopCharacterCounter.isGutenberg;
var aioseopCharacterCounterAutogenerateDescriptions = aioseopCharacterCounter.autogenerateDescriptions;
var aioseopCharacterCounterTimeout = 0;

$(function () {
	"use strict";

	var inputField;
	var cntField;
	var fieldSize;

	aioseopInitCountChars();
	aioseopForceCountChars(); // Fire once on page load.

	/**
	 * Adds the relevant event listeners.
	 *
	 * @since ?
	 * @since 3.3.0 Refactored.
	 * 
	 * @return void
	 */
	function aioseopInitCountChars() {
		aioseopAddMetaboxListener();

		if ('false' === aioseopCharacterCounterIsGutenberg) {
			return aioseopAddClassicListener();
		} else if ('true' === aioseopCharacterCounterIsGutenberg) {
			return aioseopAddGutenbergListener();
		}
	}

	/**
	 * Add the event listeners for our metabox.
	 * 
	 * @since 3.3.0
	 *	
	 * @return void
	 */
	function aioseopAddMetaboxListener() {
		$('.aioseop_count_chars').on('keyup', function () {
			aioseopDefineCurrentField($(this));
			aioseopCountChars();
		});
	}

	/**
	 * Adds the event listeners for the Classic Editor.
	 * 
	 * @since 3.3.0
	 * 
	 * @return void
	 */
	function aioseopAddClassicListener() {
		setTimeout(function () {
			tinymce.editors[0].on('KeyUp', function () {
				aioseopForceCountChars();
			});
		}, 1000);

		$('#title, #excerpt').on('keyup', function () {
			aioseopDefineCurrentField($(this));
			aioseopForceCountChars();
		});
	}

	/**
	 * Adds the event listeners for the Gutenberg Editor.
	 * 
	 * @since 3.3.0
	 * 
	 * @return void
	 */
	function aioseopAddGutenbergListener() {
		window._wpLoadBlockEditor.then(function () {
			setTimeout(function () {
				// https://developer.wordpress.org/block-editor/packages/packages-data/
				wp.data.subscribe(function () {
					clearTimeout(aioseopCharacterCounterTimeout);
					// This is needed because the code otherwise is triggered dozens of times.
					aioseopCharacterCounterTimeout = setTimeout(function () {
						aioseopForceCountChars();
					}, 200);
				});
			});
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
		var extra = 0;

		if (aioseopCheckIfAutogenerated()) {
			aioseopChangeCountBackground();
			return;
		}

		if (('aiosp_title' === inputField.attr('name')) && ('undefined' !== typeof extraTitleLength)) {
			extra = extraTitleLength;
		}

		cntField.val(+inputField.val().length + extra);
		if ('undefined' !== typeof inputField.attr('size')) {
			fieldSize = +inputField.attr('size');
		} else {
			fieldSize = +inputField.attr('rows') * +inputField.attr('cols');
		}

		aioseopChangeCountBackground();
	}

	/**
	 * Checks if the title or description is autogenerated and, if so, counts the characters of the preview snippet.
	 * 
	 * @since 3.3.0
	 * 
	 * @return bool Whether or not the title/description is autogenerated.
	 */
	function aioseopCheckIfAutogenerated() {
		if (0 === +inputField.length) {
			return false;
		}

		if ('on' !== aioseopCharacterCounterAutogenerateDescriptions || 0 !== +inputField.val().length) {
			return false;
		}

		switch (inputField.attr('name')) {
			case 'aiosp_title':
			case 'aioseop_opengraph_settings_title':
				cntField.val(+$('#aiosp_snippet_title').parent()[0].innerText.length);
				break;
			default:
				cntField.val(+$('[name=aiosp_description]')[0].placeholder.length);
				break;
		}

		return true;
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
			return;
		}

		switch (inputField.attr('name')) {
			case 'aiosp_title':
			case 'aiosp_home_title':
				aioseopChangeCountBackgroundHelper(6);
				break;
			case 'aioseop_opengraph_settings_title':
			case 'aiosp_opengraph_hometitle':
				aioseopChangeCountBackgroundHelper(40);
				break;
			case 'aioseop_opengraph_settings_desc':
			case 'aiosp_opengraph_description':
				aioseopChangeCountBackgroundHelper(145);
				break;
			default:
				aioseopChangeCountBackgroundHelper(10);
				break;
		}
	}

	/**
	 * Helper function for aioseopChangeCountBackground().
	 * 
	 * @since 3.3.0
	 * 
	 * @param int characterTreshold The amount of characters that have to be deducted from the field size to set the treshold.
	 * @return void
	 */
	function aioseopChangeCountBackgroundHelper(characterTreshold) {
		if (+cntField.val() > (+fieldSize - +characterTreshold)) {
			cntField.removeClass().addClass('aioseop_count_chars_near_treshold');
		} else {
			cntField.removeClass().addClass('aioseop_count_chars_below_treshold');
		}
	}

	/**
	 * Get the character count for both fields on the Post Edit screen when the content changes.
	 * 
	 * We need to do this for both fields as we cannot known which field changed.
	 * 
	 * @since 3.3.0
	 * 
	 * @return void
	 */
	function aioseopForceCountChars() {
		aioseopDefineTitleField();
		aioseopCountChars();
		aioseopDefineDescriptionField();
		aioseopCountChars();
	}

	/**
	 * Sets the input field and counter field based on the object that triggered the event.
	 * 
	 * Acts as a helper function for both aioseopAddMetaboxListener and aioseopAddClassicListener to avoid duplicate code.
	 * 
	 * @since 3.3.0
	 * 
	 * @param Object eventTarget The current target object.
	 * @return void
	 */
	function aioseopDefineCurrentField(eventTarget) {
		inputField = eventTarget;
		cntField = eventTarget.parent().find('[name="' + eventTarget.attr('data-length-field') + '"]');
	}

	/**
	 * Sets the input field and counter field based on the title field.
	 * 
	 * Acts as a helper function for both aioseopForceCountChars to avoid duplicate code.
	 * 
	 * @since 3.3.0
	 * 
	 * @return void
	 */
	function aioseopDefineTitleField() {
		inputField = $('[name="aiosp_title"]');
		cntField = inputField.parent().find('[name="' + inputField.attr('data-length-field') + '"]');
	}

	/**
	 * Sets the input field and counter field based on the description field.
	 * 
	 * Acts as a helper function for both aioseopForceCountChars to avoid duplicate code.
	 * 
	 * @since 3.3.0
	 * 
	 * @return void
	 */
	function aioseopDefineDescriptionField() {
		inputField = $('[name="aiosp_description"]');
		cntField = inputField.parent().find('[name="' + inputField.attr('data-length-field') + '"]');
	}

});
