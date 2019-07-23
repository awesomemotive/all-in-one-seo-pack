/**
 * Script for Counting Characters
 *
 * @summary Binds input elements and counts characters for Title and Description on Post Edit, Post New,
 *          & AIOSEOP General Settings screens.
 *
 * @author Michael Torbert.
 * @author Semper Fi Web Design.
 * @copyright https://semperplugins.com
 * @version 2.9.2
 */

var aiosp_title_extra = parseInt( aioseop_count_chars.aiosp_title_extra, 10 ); // jshint ignore:line

jQuery( document ).ready( function() {
	aioseopInitCounting();
	aioseopAdjustPreviewSnippet();
});

/**
 * The aioseopAdjustPreviewSnippet() function.
 * 
 * Adjusts the preview snippet when the title or description changes.
 * 
 * @since 3.2.0 Refactored code and use document title if aioseop_title is blank.
 */
function aioseopAdjustPreviewSnippet() {
	var docTitle = $('#title');
	var aioseopTitle = $('#aiosp_title_wrapper input');
	var aioseopDescription = $("#aiosp_description_wrapper textarea");
	var snippetTitle = $("#aiosp_snippet_title");
	var snippetDescription = $("#aioseop_snippet_description");
	var isGutenberg = aioseop_count_chars.isGutenberg; // jshint ignore:line

	if ( 'true' === isGutenberg ) {
		window._wpLoadBlockEditor.then( function() {
			setTimeout( function() {

				var gutenbergDocTitle = $('#post-title-0');
				$('#post-title-0').on("input", function() {
					populate_snippet_field( snippetTitle, aioseopTitle );
					if( '' === aioseopTitle.val() ) {
						populate_snippet_field( snippetTitle, gutenbergDocTitle );
					}
				});

				aioseopTitle.on("input", function() {
					populate_snippet_field( snippetTitle, aioseopTitle );
					if( '' === aioseopTitle.val() ) {
						populate_snippet_field( snippetTitle, gutenbergDocTitle );
					}
				});

			});
		});
	}

	else {
		docTitle.on("input", function() {
			if ( '' === aioseopTitle.val() ) {
				populate_snippet_field( snippetTitle, docTitle );
			}
		});

		aioseopTitle.on("input", function() {
			populate_snippet_field( snippetTitle, aioseopTitle );
			if( '' === aioseopTitle.val() ) {
				populate_snippet_field( snippetTitle, docTitle );
			}
		});
	}

	aioseopDescription.on("input", function() {
		populate_snippet_field( snippetDescription, aioseopDescription );
	});
}

/**
 * The aioseopPopulatePreviewSnippet() function.
 * 
 * Populates a field in the preview snippet.
 * 
 * @since 3.2.0
 * 
 * @param jQuery_Object $snippetField 
 * @param jQuery_Object $inputField 
 */
function populate_snippet_field( snippetField, inputField ) {
	snippetField.text( inputField.val().replace( /<(?:.|\n)*?>/gm, "" ) );
}

/**
 * AIOSEOP Init Counting
 *
 * @since ?
 */
function aioseopInitCounting(){
	/* ² them characters */
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
