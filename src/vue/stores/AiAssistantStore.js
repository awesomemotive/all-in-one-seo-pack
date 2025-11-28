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
			blockEditorInserterButton : true
		}
	}),
	getters : {
		hasPermission   : (state) => allowed(state.capability),
		generationPrice : () => {
			const optionsStore   = useOptionsStore()
			const costPerFeature = optionsStore.internalOptions?.internal?.ai?.costPerFeature || {}

			return costPerFeature.aiAssistant || 50
		}
	}
})