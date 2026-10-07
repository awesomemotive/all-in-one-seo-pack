import { isBlockEditor, isClassicEditor } from '@/vue/utils/context'
import { truSeoShouldAnalyze } from '@/vue/utils/postData/helpers'
import {
	useAiAssistantStore,
	useAiImageGeneratorStore,
	usePostEditorStore,
	useSchemaStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import SvgFaq from '@/vue/components/common/svg/ai/Faq'
import SvgImageGenerator from '@/vue/components/common/svg/ai/ImageGenerator'
import SvgKeyPoints from '@/vue/components/common/svg/ai/KeyPoints'
import SvgMetaDescription from '@/vue/components/common/svg/ai/MetaDescription'
import SvgMetaTitle from '@/vue/components/common/svg/ai/MetaTitle'
import SvgRepurposeContent from '@/vue/components/common/svg/ai/RepurposeContent'
import SvgSparkles from '@/vue/components/common/svg/ai/Sparkles'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

// A dynamic `:is` with a string can't resolve from `<script setup>` bindings, and the
// upsell modal renders its icon outside this folder entirely — so callers get components.
const featureIcons = {
	'ai-content'        : SvgAiContent,
	faq                 : SvgFaq,
	'image-generator'   : SvgImageGenerator,
	'key-points'        : SvgKeyPoints,
	'meta-description'  : SvgMetaDescription,
	'meta-title'        : SvgMetaTitle,
	'repurpose-content' : SvgRepurposeContent,
	sparkles            : SvgSparkles
}

export const getFeatureIcon = feature => featureIcons[feature?.svg] || SvgAiContent

export const aiFeatures = {
	aiAssistant : {
		slug    : 'ai-assistant',
		group   : 'content',
		costKey : 'aiAssistant',
		svg     : 'ai-content',
		strings : {
			name         : __('AI Assistant', td),
			hint         : __('Write new sections in the editor', td),
			description  : __('Leverage AI to generate high-quality, relevant content for your post quickly and efficiently.', td),
			buttonSubmit : __('Generate Content', td)
		},
		excludedPostTypes : [],
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
	autoOptimize : {
		slug    : 'auto-optimize',
		hero    : true,
		costKey : 'truseoOptimizePost',
		svg     : 'ai-content',
		strings : {
			name         : __('Auto-Optimize', td),
			description  : __('Let Copilot optimize your entire post in one click — SEO title, meta description, content, and spelling.', td),
			buttonSubmit : __('Optimize', td)
		},
		excludedPostTypes : [],
		// Optimize persists a re-analysis (Page Analysis), so gate on that cap too —
		// without it FeatureCard shows the card disabled with a no-permission notice.
		permission        : 'aioseo_page_analysis',
		badge             : { text: __('New', td), color: 'blue' },
		clickCallback     : () => {
			useTruSeoHighlighterStore().openOptimizeModal()
		}
	},
	imageGenerator : {
		slug    : 'image-generator',
		group   : 'content',
		costKey : 'imageGenerator',
		svg     : 'image-generator',
		strings : {
			name         : __('Image Generator', td),
			hint         : __('Custom images from a prompt', td),
			description  : __('Generate AI-powered images from text prompts to visually enhance your content and capture attention.', td),
			buttonSubmit : __('Generate Image', td)
		},
		excludedPostTypes : [],
		// The generated image is stored as an attachment. Not `permission`: Lite's `allowed()`
		// returns true for any `canManage` user, even one denied `upload_files`.
		isAllowed         : () => useAiImageGeneratorStore().canUploadFiles,
		// Priced per model and quality, so its cost is only ever a floor.
		costIsMinimum     : true,
		badge             : { text: __('Updated', td), color: 'blue' }
	},
	schemas : {
		slug       : 'schemas',
		group      : 'listing',
		costKey    : 'schemas',
		contentKey : 'schemas',
		svg        : 'sparkles',
		strings    : {
			name         : __('Schema Generator', td),
			hint         : __('Structured data search engines read', td),
			description  : __('Generate AI-powered structured data schemas to help search engines better understand your content.', td),
			buttonSubmit : __('Generate Schema', td)
		},
		excludedPostTypes : [],
		permission        : 'aioseo_page_schema_settings',
		clickCallback     : async () => {
			const schemaStore          = useSchemaStore()
			schemaStore.tabs.generator = 'ai-schema'
			schemaStore.modalOpen      = true

			window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: 'schema' })
		}
	},
	socialPosts : {
		slug       : 'social-posts',
		group      : 'content',
		costKey    : 'socialPosts',
		contentKey : 'socialPosts',
		svg        : 'repurpose-content',
		strings    : {
			name         : __('Social Posts', td),
			hint         : __('Repurpose for each network', td),
			description  : __('Generate posts you can easily share on social media so you can reach a broader audience.', td),
			buttonSubmit : __('Generate Social Posts', td)
		},
		excludedPostTypes : []
	},
	faqs : {
		slug       : 'faqs',
		group      : 'content',
		costKey    : 'faqs',
		contentKey : 'faqs',
		svg        : 'faq',
		strings    : {
			name         : __('FAQs', td),
			hint         : __('Questions your readers actually ask', td),
			description  : __('Generate helpful FAQs based on your content to enhance user engagement and boost SEO.', td),
			buttonSubmit : __('Generate FAQs', td)
		},
		excludedPostTypes : []
	},
	keyPoints : {
		slug       : 'key-points',
		group      : 'content',
		costKey    : 'keyPoints',
		contentKey : 'keyPoints',
		svg        : 'key-points',
		strings    : {
			name         : __('Key Points', td),
			hint         : __('A skimmable summary up top', td),
			description  : __('Extract and summarize the key points from your content to provide quick insights and improve readability.', td),
			buttonSubmit : __('Generate Key Points', td)
		},
		excludedPostTypes : []
	},
	metaTitle : {
		slug       : 'meta-title',
		group      : 'listing',
		costKey    : 'titles',
		contentKey : 'titles',
		svg        : 'meta-title',
		strings    : {
			name         : __('SEO Titles', td),
			hint         : __('Headlines built to earn the click', td),
			description  : __('Generate a compelling SEO title for your post to improve click-through rates and search engine visibility.', td),
			buttonSubmit : __('Generate SEO Titles', td)
		},
		excludedPostTypes : [],
		permission        : 'aioseo_page_general_settings'
	},
	metaDescription : {
		slug       : 'meta-description',
		group      : 'listing',
		costKey    : 'descriptions',
		contentKey : 'descriptions',
		svg        : 'meta-description',
		strings    : {
			name         : __('Meta Descriptions', td),
			hint         : __('Stand out on the results page', td),
			description  : __('Stand out in search results with a meta description that sparks curiosity and drives clicks to your content.', td),
			buttonSubmit : __('Generate Meta Descriptions', td)
		},
		excludedPostTypes : [],
		permission        : 'aioseo_page_general_settings'
	}
}

export const getAiFeatures = () => {
	const aiAssistantStore = useAiAssistantStore()
	const postEditorStore  = usePostEditorStore()

	return Object.values(aiFeatures).filter(feature => {
		// Exclude AI Assistant if disabled via filter or not in block editor.
		// Note: We still show it if hidden by user, but FeatureCard will show a warning.
		if ('ai-assistant' === feature.slug) {
			return aiAssistantStore.isBlockEnabled && isBlockEditor()
		}

		// Auto-Optimize rewrites blocks in place (block-editor only) and drives a TruSEO
		// re-analysis, so hide it wherever the Optimization tab is — e.g. TruSEO-ineligible
		// post types like Location.
		if ('auto-optimize' === feature.slug) {
			return isBlockEditor() && truSeoShouldAnalyze()
		}

		if (postEditorStore?.currentPost?.postType) {
			if (feature.excludedPostTypes.includes(postEditorStore.currentPost.postType)) {
				return false
			}
		}

		return true
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