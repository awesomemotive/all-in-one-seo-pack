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
		currentPost : {},
		openAiError : null
	}),
	actions : {
		updateTitle (title) {
			this.currentPost.title = title

			window.aioseoBus.$emit('updateTitleKey')
		},
		updateDescription (description) {
			this.currentPost.description = description

			window.aioseoBus.$emit('updateDescriptionKey')
		},
		changeGeneralPreview (value) {
			this.currentPost.generalMobilePrev = value
		},
		changeSocialPreview (value) {
			this.currentPost.socialMobilePreview = value
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
		generateTitlesDescriptions (payload) {
			return http.post(links.restUrl('ai/generate/'))
				.send(payload)
				.then((response) => {
					if (!response.body.suggestions) {
						if (response.body.error) {
							this.openAiError = response.body.error
						}
						return
					}

					this.openAiError = null

					this.currentPost.open_ai[payload.type].suggestions = response.body.suggestions
					this.currentPost.open_ai[payload.type].usage       = response.body.usage
				})
		},
		saveOpenAiApiKey (apiKey) {
			return http.post(links.restUrl('ai/save-api-key'))
				.send({
					apiKey
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
		}
	}
})