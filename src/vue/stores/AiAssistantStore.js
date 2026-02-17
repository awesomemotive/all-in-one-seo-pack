import { defineStore } from 'pinia'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import {
	useOptionsStore
} from '@/vue/stores'

export const useAiAssistantStore = defineStore('AiAssistantStore', {
	state : () => ({
		markdownConverter : null,
		capability        : 'aioseo_page_ai_content_settings',
		options           : {
			input : {
				userPrompt : {
					maxlength : 1000
				}
			}
		},
		extend : {
			block                     : true,
			blockEditorInserterButton : true,
			paragraphPlaceholder      : true
		},
		isBlockHiddenByUser : false
	}),
	getters : {
		isBlockEnabled   : (state) => state.extend.block,
		isBlockAvailable : (state) => state.extend.block && !state.isBlockHiddenByUser,
		hasPermission    : (state) => allowed(state.capability),
		generationPrice  : () => {
			const optionsStore   = useOptionsStore()
			const costPerFeature = optionsStore.internalOptions?.internal?.ai?.costPerFeature || {}

			return costPerFeature.aiAssistant || 50
		}
	},
	actions : {
		async initMarkdownConverter () {
			if (this.markdownConverter) {
				return
			}

			const [
				{ default: MarkdownIt },
				{ default: markdownItFootnote },
				{ default: markdownItMark },
				{ default: markdownItSub },
				{ default: markdownItSup }
			] = await Promise.all([
				import('markdown-it'),
				import('markdown-it-footnote'),
				import('markdown-it-mark'),
				import('markdown-it-sub'),
				import('markdown-it-sup')
			])

			this.markdownConverter = new MarkdownIt({ breaks: true })
				.disable('image')
				.use(markdownItFootnote)
				.use(markdownItMark)
				.use(markdownItSub)
				.use(markdownItSup)
		},
		updateBlockHiddenByUser () {
			const preferencesStore = window.wp?.data?.select('core/preferences')
			if (!preferencesStore) {
				return
			}

			// WP 6.5+ uses 'core' scope, older versions use 'core/edit-post'.
			const hiddenBlocks = preferencesStore.get('core', 'hiddenBlockTypes') ||
				preferencesStore.get('core/edit-post', 'hiddenBlockTypes') ||
				[]

			const isHidden = hiddenBlocks.includes('aioseo/ai-assistant')

			// Only update if changed to avoid unnecessary reactivity triggers.
			if (this.isBlockHiddenByUser !== isHidden) {
				this.isBlockHiddenByUser = isHidden
			}
		}
	}
})