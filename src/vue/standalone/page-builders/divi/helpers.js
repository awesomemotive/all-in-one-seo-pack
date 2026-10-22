/* globals ETBuilderBackendDynamic */
import * as Divi5 from './divi5-helpers'
import * as Legacy from './legacy-helpers'
import { getScrapedContent } from './dom-helpers'
import { projectUnitContribution as projectDivi4Unit } from '@/vue/plugins/tru-seo/editors/divi4'
import { projectUnitContribution as projectDivi5Unit } from '@/vue/plugins/tru-seo/editors/divi5'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'

// Divi's BUILDER version rather than the theme's -- the Divi Builder plugin runs the builder under
// any theme ({@see utils/context.js:diviBuilderMajorVersion}).
export const isDivi5 = 5 <= (parseInt(window.aioseo?.diviBuilderVersion || window.aioseo?.theme?.templateVersion, 10) || 0)

export const isDivi5Ready = Divi5.isDivi5Ready

/**
 * Get the featured image ID.
 *
 * @returns {number} The featured image ID.
 */
export const getFeaturedImageId = () => {
	return isDivi5 ? Divi5.getDivi5FeaturedImageId() : Legacy.getLegacyFeaturedImageId()
}

/**
 * Get the permalink.
 *
 * @returns {string} The permalink.
 */
const getPermalink = () => {
	const url = isDivi5 ? Divi5.getDivi5Permalink() : Legacy.getLegacyPermalink()
	try {
		const link = new URL(url)
		link.searchParams.delete('PageSpeed')
		return link.href
	} catch {
		return url || ''
	}
}

/**
 * Get the title.
 *
 * @returns {string} The title.
 */
const getTitle = () => {
	return isDivi5 ? Divi5.getDivi5Title() : Legacy.getLegacyTitle()
}

/**
 * Get the excerpt.
 *
 * @returns {string} The excerpt.
 */
const getExcerpt = () => {
	return isDivi5 ? Divi5.getDivi5Excerpt() : Legacy.getLegacyExcerpt()
}

/**
 * Get the slug.
 *
 * @returns {string} The slug.
 */
const getSlug = () => {
	return isDivi5 ? Divi5.getDivi5Slug() : Legacy.getLegacySlug()
}

/**
 * Get the featured image URL.
 *
 * @returns {Promise<string>} The featured image URL.
 */
const getFeaturedImage = async () => {
	const { wp } = window
	const id     = getFeaturedImageId()
	if (!wp || !id) {
		return ''
	}

	try {
		const attachment = wp.media.attachment(id)
		if (!attachment.get('url')) {
			await attachment.fetch()
		}
		const url = attachment.get('url')
		if (!isDivi5 && ETBuilderBackendDynamic?.currentPage) {
			ETBuilderBackendDynamic.currentPage.thumbnailUrl = url
		}
		return url
	} catch {
		return ''
	}
}

/**
 * Gets the markup of every image, iframe and video on the canvas.
 * NOTE: The unit projection drops media ({@see highlighter/textStream.js:MEDIA_TAGS}), so the canvas
 * scrape is the one source that has the real tags.
 *
 * @returns {string[]} The outerHTML of each media element.
 */
const getMediaMarkup = () => {
	try {
		const html = isDivi5 ? Divi5.getDivi5Content() : getScrapedContent()
		const doc  = new DOMParser().parseFromString(html || '', 'text/html')

		return [ ...doc.body.querySelectorAll('img, iframe, video') ].map(node => node.outerHTML)
	} catch (_e) {
		return []
	}
}

/**
 * Get the content.
 *
 * @returns {string} The content.
 */
// Each Divi adapter's own projector, keyed by adapter id. Kept a map rather than a branch so
// adding the second one could not leave the first reachable through the wrong projector.
const UNIT_PROJECTORS = {
	'divi-4' : projectDivi4Unit,
	'divi-5' : projectDivi5Unit
}

const getContent = () => {
	const adapter = resolveAdapter()
	const project = UNIT_PROJECTORS[adapter?.id]

	// A mark is built by matching the analyzer's sentences against ONE UNIT's projection
	// ({@see composables/TruSeoHighlighter.js:setHighlightMarks}), so when a Divi adapter owns the
	// page the analyzed string has to be the join of those same projections. Scraping the canvas
	// instead concatenates across module boundaries, and a sentence that straddles two modules is
	// then present in the analysis and findable in neither unit -- an issue counted but never
	// paintable.
	//
	// For Divi 5 it also makes the wireframe view work without the view-flipping
	// getDivi5Content() resorts to: units read ATTRIBUTES, which exist whether or not a module is
	// rendered. Divi 4's units read STORED content, which is available in wireframe for the same
	// reason.
	if (project) {
		const contributions = (adapter.getContentUnits() || [])
			.map(unit => project(unit))
			.filter(Boolean)

		if (contributions.length) {
			return [ ...contributions, ...getMediaMarkup() ].join('\n')
		}
	}

	return isDivi5 ? Divi5.getDivi5Content() : getScrapedContent()
}

/**
 * Get all editor data.
 *
 * @param   {Object}  options                      Options.
 * @param   {boolean} options.includeFeaturedImage Whether to include the featured image URL (triggers a network request). Default: true.
 * @returns {Object}                               The editor data.
 */
export const getEditorData = ({ includeFeaturedImage = true } = {}) => ({
	content   : getContent(),
	title     : getTitle(),
	excerpt   : getExcerpt(),
	slug      : getSlug(),
	permalink : getPermalink(),
	...(includeFeaturedImage && { featuredImage: getFeaturedImage() })
})