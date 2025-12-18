import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { customFieldsContent } from './customFields'
import { getPostEditedPermalink } from './postPermalink'
import { flattenBlocks } from '@/vue/utils/helpers'
import {
	isBlockEditor,
	isClassicEditor,
	isElementorEditor,
	isDiviEditor,
	isSeedProdEditor,
	isWPBakeryEditor,
	isAvadaEditor,
	isSiteOriginEditor,
	isThriveArchitectEditor,
	isBricksEditor,
	isOxygenEditor,
	isPageBuilderEditor
} from '@/vue/utils/context'
import { isTinyMceEmpty } from '@/vue/standalone/post-settings/utils/classicEditor'
import { getEditorData as getElementorData } from '@/vue/standalone/page-builders/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/page-builders/divi/helpers'
import { getEditorData as getSeedProdData } from '@/vue/standalone/page-builders/seedprod/helpers'
import { getEditorData as getWPBakeryData } from '@/vue/standalone/page-builders/wpbakery/helpers'
import { getEditorData as getAvadaData } from '@/vue/standalone/page-builders/avada/helpers'
import { getEditorData as getSiteOriginData } from '@/vue/standalone/page-builders/siteorigin/helpers'
import { getEditorData as getThriveArchitectData } from '@/vue/standalone/page-builders/thrive-architect/helpers'
import { getEditorData as getBricksData } from '@/vue/standalone/page-builders/bricks/helpers'
import { getEditorData as getOxygenData } from '@/vue/standalone/page-builders/oxygen/helpers'

const base64regex            = /base64,(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)/g
const blockPrefixesToProcess = [ 'acf', 'aioseo', 'core' ]

/**
 * Retrieves the content from the active page builder editor.
 *
 * @returns {string} The content from the active page builder editor.
 */
const getEditorContent = () => {
	const editorMap = [
		{ isEditor: isElementorEditor, getData: getElementorData },
		{ isEditor: isDiviEditor, getData: getDiviData },
		{ isEditor: isSeedProdEditor, getData: getSeedProdData },
		{ isEditor: isWPBakeryEditor, getData: getWPBakeryData },
		{ isEditor: isAvadaEditor, getData: getAvadaData },
		{ isEditor: isSiteOriginEditor, getData: getSiteOriginData },
		{ isEditor: isThriveArchitectEditor, getData: getThriveArchitectData },
		{ isEditor: isBricksEditor, getData: getBricksData },
		{ isEditor: isOxygenEditor, getData: getOxygenData }
	]

	for (const editor of editorMap) {
		if (editor.isEditor()) {
			return editor.getData()?.content ?? ''
		}
	}

	return ''
}

/**
 * Parses postContent for reusable blocks and replaces their refs with content.
 *
 * @param {string} content Content from the block editor.
 *
 * @returns {string} Post content with reusable block refs replaced by their content.
 */
const getReusableBlockContent = (content) => {
	if (!content.includes('<!-- wp:block {"ref":')) {
		return content
	}

	const allBlocks = window.wp.blocks?.rawHandler({ HTML: content })
	const blocksWithChildren = flattenBlocks(allBlocks)
	blocksWithChildren.forEach(block => {
		if ('core/block' === block.name) {
			const reData = window.wp.data.select('core').getEntityRecord('postType', 'wp_block', block.attributes?.ref)

			if (reData?.content?.raw) {
				content = content.replace(`<!-- wp:block {"ref":${block.attributes?.ref}} /-->`, reData.content.raw)
			}
		}
	})

	return content
}

/**
 * Parses postContent for blocks and replaces their markup with content.
 *
 * @param {string} content Content from the block editor.
 * @param {Array}  prefix  The block prefixes to process content.
 *
 * @returns {string} Post content with block markup replaced by their content.
 */
const getProcessedBlockContent = (content, prefix) => {
	const blocks = window.wp.data.select('core/block-editor').getBlocks()
	blocks.forEach(block => {
		const [ namePrefix, nameSuffix ] = block.name.split('/')
		if (prefix.includes(namePrefix)) {
			// Bail if it's a core block that's not a table.
			if ('core' === namePrefix && 'table' !== nameSuffix) {
				return
			}

			// Bail if it's an aioseo faq block.
			if ('aioseo' === namePrefix && 'faq' === nameSuffix) {
				return
			}

			const element = document.getElementById('block-' + block.clientId)
			if (element && element.innerText) {
				const blockName = 'core' === namePrefix ? nameSuffix : block.name
				const pattern = `<!-- wp:${blockName}.*?/wp:${blockName} -->|<!-- wp:${blockName}.*?/-->`

				content = content.replace(new RegExp(pattern, 's'), element.innerText)
			}
		}
	})

	return content
}

/**
 * Returns the stored post content.
 *
 * @returns {string} Post Content
 */
export const getPostContent = () => {
	const tagsStore = useTagsStore()
	if (tagsStore.liveTags.post_content) {
		return tagsStore.liveTags.post_content
	}

	let postContent = ''
	if (isClassicEditor() && !isPageBuilderEditor()) {
		if (window.tinyMCE || document.querySelector('#wp-content-wrap.html-active')) {
			postContent = classicContent()
		} else {
			const classicInterval = window.setInterval(() => {
				if (window.tinyMCE) {
					window.clearInterval(classicInterval)
					postContent = classicContent()
				}
			}, 50)
		}
	}

	if (isBlockEditor()) {
		postContent = window.wp.data.select('core/editor').getCurrentPost().content || ''
		postContent = getReusableBlockContent(postContent)
		postContent = getProcessedBlockContent(postContent, blockPrefixesToProcess)
	}

	if (isPageBuilderEditor()) {
		postContent = getEditorContent()
	}

	const postEditorStore = usePostEditorStore()
	if (postEditorStore.currentPost.descriptionIncludeCustomFields) {
		postContent = postContent + customFieldsContent()
	}

	// Replace base64 stuff, since we don't need it to analyze the content.
	postContent = postContent.replace(base64regex, '')

	if (postContent) {
		tagsStore.updatePostContent(postContent)
	}

	return postContent
}

/**
 * Returns the edited post content.
 *
 * @param   {boolean} ignoreCustomFields Whether to ignore custom fields.
 * @returns {string}                     Post content
 */
export const getPostEditedContent = (ignoreCustomFields = false) => {
	let postContent = ''

	if (isClassicEditor() && !isPageBuilderEditor()) {
		if (window.tinyMCE || document.querySelector('#wp-content-wrap.html-active')) {
			postContent = classicContent()
		} else {
			const classicInterval = window.setInterval(() => {
				if (window.tinyMCE) {
					window.clearInterval(classicInterval)
					postContent = classicContent()
				}
			}, 50)
		}
	}

	if (isBlockEditor()) {
		postContent = window.wp.data.select('core/editor').getEditedPostContent()
		postContent = getReusableBlockContent(postContent)
		postContent = getProcessedBlockContent(postContent, blockPrefixesToProcess)
	}

	if (isPageBuilderEditor()) {
		postContent = getEditorContent()
	}

	const postEditorStore = usePostEditorStore()
	if (!ignoreCustomFields && postEditorStore.currentPost.descriptionIncludeCustomFields) {
		postContent = postContent + customFieldsContent()
	}

	// Replace base64 stuff, since we don't need it to analyze the content.
	postContent = postContent.replace(base64regex, '')

	return postContent
}

export const maybeUpdatePostContent = async (run = true) => {
	let postContent      = getPostContent()
	const newPostContent = getPostEditedContent()
	if (postContent !== newPostContent) {
		postContent = newPostContent

		const postEditorStore = usePostEditorStore()
		const tagsStore       = useTagsStore()
		tagsStore.updatePostContent(postContent)

		if (!run) {
			return
		}

		(new TruSeo()).runAnalysis({
			postId   : postEditorStore.currentPost.id,
			postData : { ...postEditorStore.currentPost },
			content  : getPostEditedContent(),
			slug     : getPostEditedPermalink()
		})
	}
}

const classicContent = () => {
	let cc

	const editor = window.tinyMCE ? window.tinyMCE.get('content') : ''
	if (document.querySelector('#wp-content-wrap.tmce-active') && editor) {
		cc = editor.getContent({ format: isTinyMceEmpty(editor) ? 'html' : 'raw' })
	} else {
		// No tinyMCE. Let's see if there's a proper #content textarea.
		const textEditor = document.querySelector('textarea#content')
		cc = textEditor ? textEditor.value : ''
	}
	return cc
}