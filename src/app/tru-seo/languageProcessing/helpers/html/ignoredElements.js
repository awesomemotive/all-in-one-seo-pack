/**
 * The HTML tags that should be ignored.
 * @type {string[]}
 */
export const IGNORED_TAGS = [ 'script', 'style', 'code', 'pre', 'blockquote', 'textarea' ]

/**
 * The classes of HTML elements that should be ignored.
 * @type {string[]}
 */
export const IGNORED_CLASSES = [
	'elementor-button-wrapper',
	'elementor-divider',
	'elementor-spacer',
	'elementor-custom-embed',
	'elementor-icon-wrapper',
	'elementor-icon-box-wrapper',
	'elementor-counter',
	'elementor-progress-wrapper',
	'elementor-alert',
	'elementor-soundcloud-wrapper',
	'elementor-shortcode',
	'elementor-menu-anchor',
	'elementor-title',
	// Visually hidden screen-reader text, e.g. a Social Icons network name.
	'elementor-screen-only'
]