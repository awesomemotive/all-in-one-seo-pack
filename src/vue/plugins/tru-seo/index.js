import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore,
	useTagsStore
} from '@/vue/stores'

import { markRaw } from 'vue'

import { getPostEditedContent } from '@/vue/plugins/tru-seo/components/postContent'
import { getPostEditedPermalink } from '@/vue/plugins/tru-seo/components/postPermalink'
import { getPostEditedTitle } from '@/vue/plugins/tru-seo/components/postTitle'
import { isBlockEditor } from '@/vue/plugins/tru-seo/components/helpers'

import { decodeHTMLEntities } from '@/vue/utils/helpers'

import TruSeoWorker from '@/app/tru-seo/analyzer/main.js?worker'

class TruSeo {
	postId                = null
	postTitle             = null
	postParsedTitle       = null
	postParsedDescription = null
	postDescription       = null
	keyphrases            = null
	postContent           = null
	postSlug              = null
	isAnalyzing           = false
	worker                = null

	/**
	 * Runs the content analysis.
	 *
	 * @param {int}     postId       The Post ID.
	 * @param {Object}  postData     The postData of the currentPost.
	 * @param {string}  content      The post content.
	 * @param {string}  slug         The post slug.
	 *
	 * @returns {null} Returns nothing.
	 */
	async runAnalysis (
		{
			postId,
			postData,
			content  = getPostEditedContent(),
			slug     = getPostEditedPermalink()
		}
	) {
		const postEditorStore = usePostEditorStore()
		const optionsStore    = useOptionsStore()
		postData              = postData || { ...postEditorStore.currentPost } // TODO: Verify that this works as expected since it is different logic.
		if (
			!optionsStore.options.advanced?.truSeo ||
			!postEditorStore.currentPost.page_analysis ||
			this.isAnalyzing
		) {
			return
		}

		// If a worker is running, terminate that worker and start over.
		if (this.worker) {
			this.worker.terminate()
		}

		const aioseoGlobals = {
			separator : decodeHTMLEntities(optionsStore.options.searchAppearance.global.separator)
		}

		const rootStore = useRootStore()
		const tagsStore = useTagsStore()
		const aioseo    = {
			...markRaw(rootStore.aioseo),
			tags        : markRaw(tagsStore.tags),
			currentPost : markRaw(postEditorStore.currentPost)
		}

		const analysisData = JSON.parse(JSON.stringify({
			postId,
			postData,
			content,
			aioseo,
			slug,
			postEditedTitle : getPostEditedTitle(),
			aioseoGlobals
		}))

		let dispatch = []
		if (import.meta.env.PROD || import.meta.env.VITE_TRUSEO_WEB_WORKER) {
			this.worker     = import.meta.env.PROD
				? new TruSeoWorker()
				: new Worker(rootStore.aioseo.urls.truSeoWorker, {
					type : 'module'
				})

			// Listen for a response and then terminate the worker.
			this.worker.addEventListener('message', e => {
				if ('tru-seo-analysis-complete' === e.data.event) {
					dispatch = e.data.analysis
					this.dispatchActions(dispatch, analysisData)

					this.worker.terminate()
				}
			})

			// Send the data!
			this.worker.postMessage({
				event : 'tru-seo-analysis',
				analysisData
			})
		} else {
			// NOTE: This cannot go inside a separate method or the production build process completely fails to run.
			const { default: TruSeoAnalyzer } = await import('@/app/tru-seo/analyzer/analyze')

			const analyze = new TruSeoAnalyzer()

			dispatch = await analyze.runAnalysis(analysisData)

			this.dispatchActions(dispatch, analysisData)
		}
	}

	setSidebarButtonScore (score) {
		const button      = document.getElementById('aioseo-post-settings-sidebar-button')
		const postScore   = document.getElementById('aioseo-post-score')
		if (!button || !postScore) {
			return
		}
		button.className  = 80 < score ? 'score-green' : 50 < score ? 'score-orange' : 'score-red'
		if (!isBlockEditor()) {
			button.classList.add('aioseo-score-button', 'classic-editor')
			score = `${score}/100`
		}
		postScore.textContent = score
	}

	dispatchActions (dispatch, analysisData) {
		const postEditorStore = usePostEditorStore()
		dispatch.forEach(d => {
			if ('updateState' === d.action) {
				// Update the sidebar score.
				if ('attachment' !== postEditorStore.currentPost.postType && analysisData.postEditedTitle) {
					this.setSidebarButtonScore(d.data.seo_score)
				}
			}

			postEditorStore[d.action](d.data)
		})
	}
}

export default TruSeo