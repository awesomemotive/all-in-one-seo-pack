import { defineStore } from 'pinia'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import {
	useOptionsStore
} from '@/vue/stores'

import MarkdownIt from 'markdown-it'
import markdownItFootnote from 'markdown-it-footnote'
import markdownItMark from 'markdown-it-mark'
import markdownItSub from 'markdown-it-sub'
import markdownItSup from 'markdown-it-sup'

export const useAiAssistantStore = defineStore('AiAssistantStore', {
	state : () => ({
		markdownConverter : new MarkdownIt({ breaks: true })
			.disable('image')
			.use(markdownItFootnote)
			.use(markdownItMark)
			.use(markdownItSub)
			.use(markdownItSup),
		capability : 'aioseo_page_ai_content_settings',
		options    : {
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