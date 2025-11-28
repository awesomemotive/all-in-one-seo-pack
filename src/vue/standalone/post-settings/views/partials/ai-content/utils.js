import { isBlockEditor, isClassicEditor } from '@/vue/utils/context'
import {
	usePostEditorStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export const aiFeatures = {
	aiAssistant : {
		slug    : 'ai-assistant',
		svg     : 'ai-content',
		strings : {
			name         : __('AI Assistant', td),
			description  : __('Leverage AI to generate high-quality, relevant content for your post quickly and efficiently.', td),
			buttonSubmit : __('Generate Content', td)
		},
		excludedPostTypes : [],
		isPopular         : true,
		clickCallback     : () => {
			const { wp } = window
			const blockEditorSelect   = wp.data.select('core/block-editor')
			const blockEditorDispatch = wp.data.dispatch('core/block-editor')
			const insertionPoint      = blockEditorSelect.getBlockInsertionPoint() || {}

			// Check if we can insert the block at the current insertion point.
			// This may fail for blocks that only accept specific children (e.g., list blocks).
			const canInsertAtPoint = blockEditorSelect.canInsertBlockType(
				'aioseo/ai-assistant',
				insertionPoint.rootClientId
			)

			if (canInsertAtPoint) {
				blockEditorDispatch.insertBlock(
					wp.blocks.createBlock('aioseo/ai-assistant'),
					insertionPoint.index,
					insertionPoint.rootClientId
				)
				return
			}

			// Can't insert at the current point (e.g., inside a list block).
			// Find the top-level block and insert after it.
			const selectedBlockClientId = blockEditorSelect.getSelectedBlockClientId()
			if (selectedBlockClientId) {
				const rootBlockClientId = blockEditorSelect.getBlockHierarchyRootClientId(selectedBlockClientId)
				const rootBlockIndex    = blockEditorSelect.getBlockIndex(rootBlockClientId)

				blockEditorDispatch.insertBlock(
					wp.blocks.createBlock('aioseo/ai-assistant'),
					rootBlockIndex + 1
				)
			} else {
				// No selection, insert at the end.
				blockEditorDispatch.insertBlock(
					wp.blocks.createBlock('aioseo/ai-assistant')
				)
			}
		}
	},
	imageGenerator : {
		slug    : 'image-generator',
		svg     : 'image-generator',
		strings : {
			name         : __('Image Generator', td),
			description  : __('Generate AI-powered images from text prompts to visually enhance your content and capture attention.', td),
			buttonSubmit : __('Generate Image', td)
		},
		excludedPostTypes : [],
		isPopular         : true
	},
	socialPosts : {
		slug    : 'social-posts',
		svg     : 'repurpose-content',
		strings : {
			name         : __('Social Posts', td),
			description  : __('Generate posts you can easily share on social media so you can reach a broader audience.', td),
			buttonSubmit : __('Generate Social Posts', td)
		},
		excludedPostTypes : []
	},
	faqs : {
		slug    : 'faqs',
		svg     : 'faq',
		strings : {
			name         : __('FAQs', td),
			description  : __('Generate helpful FAQs based on your content to enhance user engagement and boost SEO.', td),
			buttonSubmit : __('Generate FAQs', td)
		},
		excludedPostTypes : []
	},
	keyPoints : {
		slug    : 'key-points',
		svg     : 'key-points',
		strings : {
			name         : __('Key Points', td),
			description  : __('Extract and summarize the key points from your content to provide quick insights and improve readability.', td),
			buttonSubmit : __('Generate Key Points', td)
		},
		excludedPostTypes : []
	},
	metaTitle : {
		slug    : 'meta-title',
		svg     : 'meta-title',
		strings : {
			name         : __('SEO Titles', td),
			description  : __('Generate a compelling SEO title for your post to improve click-through rates and search engine visibility.', td),
			buttonSubmit : __('Generate SEO Titles', td)
		},
		excludedPostTypes : []
	},
	metaDescription : {
		slug    : 'meta-description',
		svg     : 'meta-description',
		strings : {
			name         : __('Meta Descriptions', td),
			description  : __('Stand out in search results with a meta description that sparks curiosity and drives clicks to your content.', td),
			buttonSubmit : __('Generate Meta Descriptions', td)
		},
		excludedPostTypes : []
	}
}

export const getAiFeatures = () => {
	const postEditorStore = usePostEditorStore()
	if (!postEditorStore?.currentPost?.postType) {
		return Object.values(aiFeatures)
	}

	return Object.values(aiFeatures).filter(feature => {
		if (feature.excludedPostTypes.includes(postEditorStore.currentPost.postType)) {
			return false
		}

		return !('ai-assistant' === feature.slug && !isBlockEditor())
	})
}

export const copyContent = (content) => {
	if (isBlockEditor()) {
		return content
	}

	if (isClassicEditor()) {
		// Fallback for older browsers or non-secure contexts
		function listener (e) {
			e.clipboardData.setData('text/html', content)
			e.clipboardData.setData('text/plain', content)
			e.preventDefault()
		}

		document.addEventListener('copy', listener)
		document.execCommand('copy')
		document.removeEventListener('copy', listener)
	}

	return content
}