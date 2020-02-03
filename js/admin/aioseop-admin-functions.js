/**
 * Contains shared functions that are limited to the admin panel.
 *
 * @since 3.3.4
 * 
 * @package all-in-one-seo-pack
 */

(function () { 'use strict'; }());

var aioseopEditorUndefined = false;

/**
 * Checks whether the Gutenberg Editor is active.
 * 
 * @since 3.3.4
 * 
 * @return bool Whether or not the Gutenberg Editor is active.
 */
function aioseopIsGutenbergEditor() {
	return document.body.classList.contains('block-editor-page');
}

/**
 * Determines whether the visual tab is active in the Classic Editor.
 * 
 * @since 3.3.4
 * 
 * @return bool Whether or not the visual tab is active.
 */
function aioseopIsVisualTab() {
	if (jQuery('#wp-content-wrap').hasClass('tmce-active')) {
		return true;
	}
	return false;
}

/**
 * Sets the event listeners for the Classic Editor based on which tab is active.
 * 
 * @since 3.3.4
 * 
 * @param string functionName The name of the function that has to be called when the event is triggered.
 */
function aioseopSetClassicEditorEventListener(functionName) {
	if (aioseopIsVisualTab()) {
		setTimeout(function () {
			tinymce.editors[0].on('KeyUp', function () {
				functionName();
			});
		}, 500);
	} else {
		setTimeout(function () {
			jQuery('.wp-editor-area').on('change', function () {
				functionName();
			});
		}, 500);
	}
}

/**
 * Sets the event listener for the editor tab switch.
 * 
 * @since 3.3.4
 * 
 * @param string functionName The name of the function that needs to be called when the event is triggered.
 */
function aioseopSetClassicEditorTabSwitchEventListener(functionName) {
	jQuery('.wp-switch-editor').each(function () {
		jQuery(this).on('click', function () {
			setTimeout(function () {
				aioseopSetClassicEditorEventListener(functionName);
			});
		});
	});
}

/**
 * Gets the content of the active Classic Editor tab.
 * 
 * @since 3.3.4
 * @since 3.3.5 Use built-in function tinymce.activeEditor.getContent() to grab content.
 * 
 * @return string The content of the active editor tab.
 */
function aioseopGetClassicEditorContent() {
	if (aioseopIsVisualTab()) {
		return tinymce.activeEditor.getContent({format : 'raw'});
	}
	return jQuery('.wp-editor-area').val();
}

/**
 * Sets the event listener for the Gutenberg Editor.
 * 
 * @since 3.3.0
 * @since 3.4.0 Moved to its own function.
 * 
 * @param functionName The name of the function that needs to be called when the event is triggered.
 */
function aioseopSetGutenbergEditorEventListener(functionName) {
	if ('undefined' === typeof (window._wpLoadBlockEditor)) {
		aioseopEditorUndefined = true;
		return;
	}
	window._wpLoadBlockEditor.then(function () {
		setTimeout(function () {
			// https://developer.wordpress.org/block-editor/packages/packages-data/
			wp.data.subscribe(function () {
				clearTimeout(aioseopGutenbergEventTimeout);
				// This is needed because the code otherwise is triggered dozens of times.
				var aioseopGutenbergEventTimeout = setTimeout(function () {
					functionName();
				}, 200);
			});
		});
	});
}
