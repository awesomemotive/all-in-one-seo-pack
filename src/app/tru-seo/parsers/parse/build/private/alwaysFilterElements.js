/**
 * A config file that contains filters that should always apply.
 */

import { elementHasName, elementHasClass, elementHasID, elementIsNonVideoIframe } from './filterHelpers'

// These are elements that we don't want to include in the analysis and that can be child nodes of paragraphs or headings.
export const canBeChildOfParagraph = [ 'code', 'kbd', 'math', 'q', 'samp', 'script', 'var', '#comment', 'cite', 'form',
	'map', 'noscript', 'output' ]

const permanentFilters = [
	// Filters out specific blocks that don't need to be part of the analysis.
	// FAQ and How-to blocks are not filtered out and are included in analysis.
	// Some blocks enter the Paper as HTML comments, which are filtered out in `filterBeforeTokenizing.js` step.
	elementHasClass('aioseo-table-of-contents'),
	elementHasClass('aioseo-ai-summarize'),
	// Filters for Elementor widgets
	elementHasID('breadcrumbs'),
	elementHasClass('elementor-button-wrapper'),
	elementHasClass('elementor-divider'),
	elementHasClass('elementor-spacer'),
	elementHasClass('elementor-custom-embed'),
	elementHasClass('elementor-icon-wrapper'),
	elementHasClass('elementor-icon-box-wrapper'),
	elementHasClass('elementor-counter'),
	elementHasClass('elementor-progress-wrapper'),
	// This element is used for the progress bar widget title.
	elementHasClass('elementor-title'),
	elementHasClass('elementor-alert'),
	elementHasClass('elementor-soundcloud-wrapper'),
	elementHasClass('elementor-shortcode'),
	elementHasClass('elementor-menu-anchor'),
	// Visually hidden screen-reader text, e.g. a Social Icons network name.
	elementHasClass('elementor-screen-only'),
	elementHasClass('e-rating'),
	// Filters out HTML elements.
	/* Elements are filtered out when: they contain content outside of the author's control (incl. quotes and embedded
	content); their content isn't natural language (e.g. code); they contain metadata hidden from the page visitor
	(e.g. <style>); they are used to accept input from the visitor. Deprecated HTML elements are not included. */
	elementHasName('base'),
	elementHasName('blockquote'),
	elementHasName('canvas'),
	elementHasName('code'),
	// It seems that the <head> element is filtered out by the parser we employ, but it's included here for completeness.
	elementHasName('head'),
	// Filter out non-video iframes, but allow video oEmbed iframes (YouTube, Vimeo, etc.).
	elementIsNonVideoIframe(),
	elementHasName('input'),
	elementHasName('kbd'),
	elementHasName('link'),
	elementHasName('math'),
	elementHasName('meta'),
	elementHasName('meter'),
	elementHasName('noscript'),
	elementHasName('object'),
	elementHasName('portal'),
	elementHasName('pre'),
	elementHasName('progress'),
	elementHasName('q'),
	elementHasName('samp'),
	elementHasName('script'),
	elementHasName('slot'),
	elementHasName('style'),
	elementHasName('svg'),
	elementHasName('template'),
	elementHasName('textarea'),
	elementHasName('title'),
	elementHasName('var'),
	elementHasName('#comment'),
	elementHasName('cite'),
	elementHasName('form'),
	elementHasName('map'),
	elementHasName('noscript'),
	elementHasName('output')
]

export default permanentFilters