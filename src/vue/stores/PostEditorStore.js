import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

let cachedCurrentPost = null

const prepareCachedCurrentPost = (currentPost) => {
	const ignore = [ 'modalOpen', 'seo_score', 'page_analysis' ]
	const copy   = JSON.parse(JSON.stringify(currentPost))

	ignore.forEach((property) => {
		delete copy[property]
	})

	return JSON.stringify(copy)
}

export const usePostEditorStore = defineStore('PostEditorStore', {
	state : () => ({
		isDirty     : false,
		currentPost : {}
	}),
	getters : {
		newHeadlineAnaylzerData () {
			const newTitle = this.currentPost.headlineAnalyzer?.newData?.headline ? this.currentPost.headlineAnalyzer.newData.headline : ''
			let newResult = this.currentPost.headlineAnalyzer?.newData?.data[Object.keys(this.currentPost.headlineAnalyzer.newData.data)?.[0]] ? this.currentPost.headlineAnalyzer.newData.data[Object.keys(this.currentPost.headlineAnalyzer.newData.data)?.[0]] : null
		    newResult = newResult ? JSON.parse(newResult) : null

			return {
				newTitle,
				newResult
			}
		}
	},
	actions : {
		updateTitle (title) {
			this.currentPost.title = title

			window.aioseoBus.$emit('updateTitleKey')
		},
		updateDescription (description) {
			this.currentPost.description = description

			window.aioseoBus.$emit('updateDescriptionKey')
		},
		updatePostHeadlineAnalyzerData (data, headline) {
			this.currentPost.headlineAnalyzer = this.currentPost.headlineAnalyzer || {}
			this.currentPost.headlineAnalyzer.data = data
			this.currentPost.headlineAnalyzer.headline = headline

			if (!this.currentPost.headlineAnalyzer.previousHeadlines) {
				this.currentPost.headlineAnalyzer.previousHeadlines = []
			}

			// Add previous scores but don't add duplicates
			if (this.currentPost.headlineAnalyzer.data[Object.keys(this.currentPost.headlineAnalyzer.data)?.[0]]) {
				let currentResult = this.currentPost.headlineAnalyzer.data[Object.keys(this.currentPost.headlineAnalyzer.data)?.[0]]
				currentResult = JSON.parse(currentResult)

				const headlineExists = this.currentPost.headlineAnalyzer.previousHeadlines.some(item => item.headline === headline)

				if (!headlineExists) {
					this.currentPost.headlineAnalyzer.previousHeadlines.push({
						headline : headline,
						result   : currentResult,
						score    : currentResult.score
					})

					// save latest score
					this.currentPost.headlineAnalyzer.latestScore = currentResult.score
				}
			}
		},
		updateLatestScore (score) {
			this.currentPost.headlineAnalyzer.latestScore = score
		},
		shouldShowPrevScores () {
			this.currentPost.headlineAnalyzer.showPrevScores = true
		},
		updateNewHeadlineAnalyzerData (data, headline) {
			this.currentPost.headlineAnalyzer.newData = this.currentPost.headlineAnalyzer.newData || {}
			this.currentPost.headlineAnalyzer.newData.data = data
			this.currentPost.headlineAnalyzer.newData.headline = headline
			this.currentPost.headlineAnalyzer.newData.showPreview = true

			// Add new Headline tested data to the previous headlines list
			if (!this.currentPost.headlineAnalyzer.previousHeadlines) {
				this.currentPost.headlineAnalyzer.previousHeadlines = []
			}

			let currentResult = this.currentPost.headlineAnalyzer.newData.data[Object.keys(this.currentPost.headlineAnalyzer.newData.data)?.[0]]
			currentResult = JSON.parse(currentResult)

			const headlineExists = this.currentPost.headlineAnalyzer.previousHeadlines.some(item => item.headline === headline)

			if (!headlineExists) {
				this.currentPost.headlineAnalyzer.previousHeadlines.push({
					headline : headline,
					result   : currentResult,
					score    : currentResult.score
				})

				// save latest score
				this.currentPost.headlineAnalyzer.latestScore = currentResult.score
			}
		},
		toggleShowNewHeadlineAnalyzerData (show) {
			this.currentPost.headlineAnalyzer.showNewData = show
		},
		toggleShowNewHeadlineAnalyzerPreview (show) {
			this.currentPost.headlineAnalyzer.newData.showPreview = show
		},
		changeGeneralPreview (value) {
			this.currentPost.generalMobilePrev = value
		},
		saveCurrentPost (payload) {
			this.currentPost = payload

			return http.post(links.restUrl('post'))
				.send(payload)
				.then(() => {})
				.catch((error) => {
					console.error(`Unable to update the post data: ${error}`)
				})
		},
		updateState (value) {
			this.currentPost = value
		},
		savePostState () {
			// In some contexts, the state might not have loaded fully and still be an Observer object.
			if (!this.currentPost || !Object.keys(this.currentPost).length) {
				return
			}

			// Cache a stringified version the state.currentPost so we don't have a reference of the original state anymore.
			if (null === cachedCurrentPost) {
				cachedCurrentPost = prepareCachedCurrentPost(this.currentPost)
			}

			// If the currentPost changed, emit a global event.
			if (cachedCurrentPost !== prepareCachedCurrentPost(this.currentPost)) {
				window.aioseoBus.$emit('postSettingsUpdated')
			}

			const postField = document.querySelector('#aioseo-post-settings')
			if (postField) {
				postField.value = JSON.stringify(this.currentPost)
			}
			if ('term' === this.currentPost.context) {
				const termField = document.querySelector('#aioseo-term-settings')
				if (termField) {
					termField.value = JSON.stringify(this.currentPost)
				}
			}
		},
		disablePrimaryTermEducation () {
			this.currentPost.options.primaryTerm.productEducationDismissed = true

			return http.post(links.restUrl(`post/${this.currentPost.id}/disable-primary-term-education`))
		},
		disableLinkAssistantEducation () {
			this.currentPost.options.linkFormat.linkAssistantDismissed = true

			return http.post(links.restUrl(`post/${this.currentPost.id}/disable-link-format-education`))
		},
		incrementInternalLinkCount () {
			const count = this.currentPost.options.linkFormat.internalLinkCount || 0

			this.currentPost.options.linkFormat.internalLinkCount = count + 1

			return http.post(links.restUrl(`post/${this.currentPost.id}/update-internal-link-count`))
				.send({
					count
				})
		},
		getUserImage ({ userId }) {
			return http.get(links.restUrl(`user/${userId}/image`))
				.then(response => 200 === response.statusCode ? response.body.url : '')
		},
		getFirstAttachedImage ({ postId }) {
			return http.get(links.restUrl(`post/${postId}/first-attached-image`))
				.then(response => 200 === response.statusCode ? response.body.url : '')
		},
		getMediaData ({ mediaId }) {
			return http.get(links.restUrl(`media/${mediaId}`, 'wp/v2'))
				.then(response => 200 === response.statusCode ? response.body : {})
		},
		processContent ({ content }) {
			return http.post(links.restUrl(`post/${this.currentPost.id}/process-content`))
				.send({
					content
				})
				.then(response => {
					this.currentPost.processedContent = response.body.content
				})
		},
		fetchPostData (payload = {}) {
			return http.get(links.restUrl('post'))
				.query(payload)
				.then(response => response)
				.catch(error => {
					throw error
				})
		}
	}
})