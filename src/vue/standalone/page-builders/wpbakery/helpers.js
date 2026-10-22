import { usePostEditorStore } from '@/vue/stores'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { projectUnitContribution } from '@/vue/plugins/tru-seo/editors/wpbakery'
import { escapeText } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'

/**
 * Parses the server-rendered content.
 *
 * @returns {?Document} The parsed document, or null when there is no content yet.
 */
const parseProcessedContent = () => {
	const processedContent = usePostEditorStore().currentPost.processedContent
	if (!processedContent) {
		return null
	}

	try {
		return new DOMParser().parseFromString(processedContent, 'text/html')
	} catch (_e) {
		return null
	}
}

/**
 * Gets the markup of every image, iframe and video in the server-rendered content.
 * NOTE: The unit projection drops media ({@see highlighter/textStream.js:MEDIA_TAGS}), and the back-end
 * builder only previews thumbnails, so the rendered content is the one source that has the real tags.
 *
 * @returns {string[]} The outerHTML of each media element.
 */
const getMediaMarkup = () => {
	const doc = parseProcessedContent()

	return doc ? [ ...doc.body.querySelectorAll('img, iframe, video') ].map(node => node.outerHTML) : []
}

/**
 * Gets each Single Image caption in the server-rendered content, as a paragraph.
 * NOTE: the caption comes from the attachment rather than a param, so no unit carries it, and the
 * back-end builder never renders it at all.
 *
 * @returns {string[]} One paragraph per non-empty caption.
 */
const getCaptionMarkup = () => {
	const doc = parseProcessedContent()
	if (!doc) {
		return []
	}

	return [ ...doc.body.querySelectorAll('.vc_figure-caption') ]
		.map(node => node.textContent.trim())
		.filter(Boolean)
		.map(text => `<p>${escapeText(text)}</p>`)
}

/**
 * Gets the post content.
 *
 * TWO SOURCES, and which one is used decides whether a mark can be placed at all. A mark's range is
 * measured against its unit's projection, so when the builder owns the page the analyzed string has
 * to be the join of those same projections -- feeding the server-rendered shortcode output instead
 * would leave every offset pointing into a string no unit shares.
 *
 * The server-rendered content stays the source everywhere else: Classic Mode has no paintable units
 * (the user is editing raw shortcodes), and a frontend editor whose iframe has not finished loading
 * has none yet either. Both keep the score they have always had.
 *
 * @returns {string} The post content.
 */
const getContent = () => {
	const adapter = resolveAdapter()

	if ('wpbakery' === adapter?.id) {
		const contributions = (adapter.getContentUnits() || [])
			.map(unit => projectUnitContribution(unit))
			.filter(Boolean)

		if (contributions.length) {
			return [ ...contributions, ...getCaptionMarkup(), ...getMediaMarkup() ].join('\n')
		}
	}

	const postEditorStore = usePostEditorStore()

	return postEditorStore.currentPost.processedContent
}

/**
 * Gets the post title.
 *
 * @param {string} mode The editor mode.
 * @returns {string} The post title.
 */
const getTitle = (mode) => {
	if ('admin_frontend_editor' === mode) {
		const { vc } = window

		return vc.builder.getTitle() || document.querySelector('#vc_page-title-field')?.value
	}

	return document.getElementById('#title')?.value || ''
}

/**
 * Gets the post excerpt.
 *
 * NOTE: Only the front-end editor renders #vc_post_excerpt, and only for post types with excerpt support.
 *
 * @param {string} mode The editor mode.
 * @returns {string} The post excerpt.
 */
const getExcerpt = (mode) => {
	if ('admin_frontend_editor' === mode) {
		return document.querySelector('#vc_post_excerpt')?.value || ''
	}

	return ''
}

/**
 * Gets the post slug.
 *
 * @param {string} mode The editor mode.
 * @returns {string} The post slug.
 */
const getSlug = (mode) => {
	if ('admin_frontend_editor' === mode) {
		const { vc_iframe_src : src } = window
		const pathParts = new URL(src).pathname.split('/').filter(n => n)

		return pathParts[pathParts.length - 1] || document.querySelector('#vc_post_name')?.value
	}

	return document.querySelector('#post_name')?.value || ''
}

/**
 * Gets the post permalink.
 *
 * @param {string} mode The editor mode.
 * @returns {string} The post permalink.
 */
const getPermalink = (mode) => {
	if ('admin_frontend_editor' === mode) {
		const { vc_iframe_src : src } = window

		return src.replace(new URL(src).search, '')
	}

	return document.querySelector('#sample-permalink a')?.href || ''
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	const { vc_mode : vcMode } = window
	const slug = getSlug(vcMode) || cleanForSlug(getTitle(vcMode))

	return {
		content       : getContent(),
		title         : getTitle(vcMode),
		excerpt       : getExcerpt(vcMode),
		slug          : slug,
		permalink     : getPermalink(vcMode),
		featuredImage : ''
	}
}

export const getSaveButtons = (onlyVisible = false) => {
	const buttons = Array.from(document.querySelectorAll('#vc_button-update, #vc_button-save-draft, #vc_button-save-as-pending'))

	if (onlyVisible) {
		return buttons.filter($button => $button.offsetParent && $button.offsetWidth && $button.offsetHeight)
	}

	return buttons
}