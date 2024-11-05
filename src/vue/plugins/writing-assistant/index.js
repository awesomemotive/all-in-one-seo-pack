import { useWritingAssistantStore } from '@/vue/stores'
import { isBlockEditor, isClassicEditor } from '@/vue/utils/context'
import { getPostEditedContent } from '@/vue/plugins/tru-seo/components/postContent'
import { debounce } from 'lodash-es'

export default class WritingAssistant {
	content = null
	updating = false
	writingAssistantStore = null

	constructor () {
		if (!window.aioseo.currentPost) {
			return
		}

		if (!window.aioseo.writingAssistantWatcherSet) {
			this.writingAssistantStore = useWritingAssistantStore()
			this.initWatchers()
			window.aioseo.writingAssistantWatcherSet = true
		}
	}

	initWatchers () {
		if (isBlockEditor()) {
			const interval = window.setInterval(() => {
				const post = window.wp.data.select('core/editor').getCurrentPost()
				if (post.id) {
					window.clearInterval(interval)
					this.watchBlockEditor()
				}
			}, 50)
		}

		if (isClassicEditor()) {
			const mceActiveInterval = window.setInterval(() => {
				if (!window.tinyMCE || !window.tinyMCE.activeEditor) {
					return
				}
				window.clearInterval(mceActiveInterval)
				this.watchClassicEditor()
			}, 50)
		}

		window.aioseoBus.$on('writingAssistantAnalyzeContent', () => {
			this.updateKeywordCount()
			this.updateContentAnalysis()
		})
	}

	watchBlockEditor () {
		window.wp.data.subscribe(() => {
			if (!this.writingAssistantStore.seoBoost.isLoggedIn) {
				return
			}
			this.updateKeywordCount()
			this.updateContentAnalysis()
		})
	}

	watchClassicEditor () {
		window.tinyMCE.get('content').on('keyup', () => {
			this.updateKeywordCount()
			this.updateContentAnalysis()
		})

		// Do a first run.
		this.updateKeywordCount()
		this.updateContentAnalysis()
	}

	getContent () {
		if (!this.writingAssistantStore.hasReport) {
			return false
		}

		return getPostEditedContent(true)
	}

	updateKeywordCount = debounce(() => {
		const content = this.getContent()
		if (false === content) {
			return
		}

		this.writingAssistantStore.countContentKeywords(content).finally(() => {
			this.updating = false
			// TODO: rerun the table sorting.
		})
	}, 250)

	updateContentAnalysis = debounce(() => {
		const content = this.getContent()
		if (false === content) {
			return
		}

		this.writingAssistantStore.analyzeContent(content).finally(() => {
			this.updating = false
		})
	}, 1000)
}

new WritingAssistant()