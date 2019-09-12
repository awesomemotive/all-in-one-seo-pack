/**
 * Handles the Preview Snippet on the Edit screen.
 *
 * @since 3.3.0
 * 
 * @package all-in-one-seo-pack
 * @package xregexp
 */

var docTitle            = '';
var snippetTitle        = '';
var snippetDescription  = '';
var aioseopTitle        = '';
var aioseopDescription  = '';
var timeout             = 0;

var isGutenberg              = aioseop_preview_snippet.isGutenberg;
var autogenerateDescriptions = aioseop_preview_snippet.autogenerateDescriptions;
var skipExcerpt              = aioseop_preview_snippet.skipExcerpt;

$(function() {
	snippetTitle        = $('#aiosp_snippet_title');
	snippetDescription  = $('#aioseop_snippet_description');
	aioseopTitle        = $('input[name="aiosp_title"]');
	aioseopDescription  = $('textarea[name="aiosp_description"]');

	aioseopUpdateMetabox();
});

/**
 * The aioseopUpdateMetabox() function.
 * 
 * Updates the preview snippet and input field placeholders in the meta box when a change happens.
 * 
 * @since 3.3.0
 */
function aioseopUpdateMetabox() {
	var inputFields = [aioseopTitle, aioseopDescription];

	if ('false' === isGutenberg) {
		docTitle = $('#title');
		var postExcerpt = $('#excerpt');

		inputFields.push(docTitle, postExcerpt);

		setTimeout(function () {
			tinymce.editors[0].on('KeyUp', function () {
				aioseopUpdatePreviewSnippet();
			});
		}, 1000);	
	}
	else {
		window._wpLoadBlockEditor.then(function() {
			setTimeout(function() {
				// https://developer.wordpress.org/block-editor/packages/packages-data/
				wp.data.subscribe(function() {
					clearTimeout(timeout);
					// This is needed because the code otherwise is triggered dozens of times.
					timeout = setTimeout(function() {
						aioseopUpdatePreviewSnippet();
					}, 100);
				});
			});
		});
	}

	inputFields.forEach(addEvent);
	function addEvent(item) {
		item.on('input', function() {
			aioseopUpdatePreviewSnippet();
		});
	}

	//Run once on page load.
	timeout = setTimeout(function() {
		aioseopUpdatePreviewSnippet();
	}, 1000);
}

function aioseopUpdatePreviewSnippet() {
	var postTitle 	 = '';
	var postContent  = '';
	var postExcerpt  = '';
		
	if ('false' === isGutenberg) {
		postTitle   = aioseopStripMarkup($.trim($('#title').val()));
		postContent = aioseopShortenDescription($('#content_ifr').contents().find('body')[0].innerHTML);
		postExcerpt = aioseopShortenDescription($.trim($('#excerpt').val()));
	}
	else {
		postTitle   = aioseopStripMarkup($.trim($('#post-title-0').val()));
		postContent = aioseopShortenDescription(wp.data.select('core/editor').getEditedPostAttribute('content'));
		postExcerpt = aioseopShortenDescription(wp.data.select('core/editor').getEditedPostAttribute('excerpt'));
	}

	var metaboxTitle       = aioseopStripMarkup($.trim($('input[name="aiosp_title').val()));
	var metaboxDescription = aioseopStripMarkup($.trim($('textarea[name="aiosp_description"]').val()));
		
	snippetTitle.text(postTitle);
	aioseopTitle.attr('placeholder', postTitle);

	if('' !== metaboxTitle) {
		snippetTitle.text(metaboxTitle);
	}

	if('on' === autogenerateDescriptions) {
		snippetDescription.text(postContent);
		aioseopDescription.attr('placeholder', postContent);
	
		if('on' !== skipExcerpt & '' !== postExcerpt) {
			snippetDescription.text(postExcerpt);
			aioseopDescription.attr('placeholder', postExcerpt);
		}
	}

	if('' !== metaboxDescription) {
		snippetDescription.text(metaboxDescription);
		aioseopDescription.attr('placeholder', metaboxDescription);
	}
}

/**
 * The aioseopShortenDescription() function.
 * 
 * Shortens the description to max. 160 characters without truncation.
 * 
 * @since 3.3.0
 * 
 * @param string description 
 */
function aioseopShortenDescription(description) {
	description = aioseopStripMarkup(description);
	if (160 < description.length) {
		var excessLength = description.length - 160;
		var regex = new XRegExp("[^\\pZ\\pP]*.{" + excessLength + "}$");
		description = XRegExp.replace(description, regex, '');
		description = description + " ...";
	}
	return description;
}

/**
 * The aioseopStripMarkup() function.
 * 
 * Strips all editor markup from a string.
 * 
 * @since 3.3.0
 * 
 * @param string content
 * @return string 
  */
function aioseopStripMarkup(content) {
	// Remove all HTML tags.
	content = content.replace(/(<[^ >][^>]*>)?/gm, '');
	// Remove all line breaks.
	content = content.replace(/\s\s+/g, ' ');
	return aioseopDecodeHtmlEntities(content.trim());
}

/**
 * The aioseopDecodeHtmlEntities() function.
 * 
 * Decodes HTML entities to characters.
 * 
 * @since 3.3.0
 * 
 * @param string encodedString
 * @return string
 */
function aioseopDecodeHtmlEntities(encodedString) {
	var textArea = document.createElement('textarea');
	textArea.innerHTML = encodedString;
	return textArea.value;
}
