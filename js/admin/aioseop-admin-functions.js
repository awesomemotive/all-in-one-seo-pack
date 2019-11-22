/**
 * Contains shared functions that are limited to the admin panel.
 *
 * @since 3.3.4
 * 
 * @package all-in-one-seo-pack
 */

/**
 * Checks whether the Classic Editor is active.
 * 
 * @since 3.3.4
 * 
 * @return bool Whether or not the Classic Editor is active.
 */
function aioseopIsClassicEditor() {
    if ("undefined" !== typeof tinyMCE) {
        if (null === tinyMCE.activeEditor || false !== tinyMCE.activeEditor.isHidden()) {
            return true;
        }
    }
    return false;
}

/**
 * Determines whether the visual tab is active in the Classic Editor.
 * 
 * @since 3.3.4
 * 
 * @return bool Whether or not the visual tab is active.
 */
function aioseopIsVisualTab() {
    if ($('#wp-content-wrap').hasClass('tmce-active')) {
        return true;
    }
    return false;
}
