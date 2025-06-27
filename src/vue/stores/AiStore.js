import { defineStore } from 'pinia'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useOptionsStore,
	usePostEditorStore
} from '@/vue/stores'

import { getPostEditedContent } from '@/vue/plugins/tru-seo/components/postContent'
import { getPostPermalink } from '@/vue/plugins/tru-seo/components/postPermalink'

export const useAiStore = defineStore('AiStore', {
	state : () => ({
		faqs : {
			tone     : '',
			audience : ''
		},
		keyPoints : {
			tone     : '',
			audience : ''
		},
		socialPosts : {
			tone     : '',
			audience : '',
			selected : []
		},
		metaTitle : {
			tone     : '',
			audience : ''
		},
		metaDescription : {
			tone     : '',
			audience : ''
		},
		isModalOpened : false
	}),
	getters : {
		isFreeAndOutOfCredits : (state) => {
			const optionsStore = useOptionsStore()

			// Check if we have a trial access token.
			if (!optionsStore.internalOptions.internal.ai.isTrialAccessToken || state.isModalOpened) {
				return false
			}

			// Check that we don't have a license with credits.
			if (1 < optionsStore.internalOptions.internal.ai.credits?.remaining) {
				return false
			}

			return true
		}
	},
	actions : {
		storeAccessToken (accessToken) {
			const optionsStore = useOptionsStore()
			optionsStore.internalOptions.internal.ai.accessToken = accessToken

			return http.post(links.restUrl('ai/auth'))
				.send({
					accessToken
				})
				.then(response => {
					if (response.body.error) {
						console.error(response.body.error)

						return
					}

					const optionsStore = useOptionsStore()
					optionsStore.internalOptions.internal.ai = response.body.aiOptions

					return response
				})
		},
		generateSocialPosts () {
			const postEditorStore = usePostEditorStore()
			const media           = this.socialPosts.selected.map(item => item.slug)

			return http.post(links.restUrl('ai/social-posts'))
				.send({
					postId      : postEditorStore.currentPost.id,
					postContent : getPostEditedContent(),
					permalink   : getPostPermalink(),
					options     : {
						media    : media,
						tone     : this.socialPosts.tone,
						audience : this.socialPosts.audience
					}
				})
				.then(response => {
					if (response.body.error) {
						console.error(response.body.error)

						return
					}

					const postEditorStore = usePostEditorStore()
					postEditorStore.currentPost.ai.socialPosts = response.body.snippets

					const optionsStore = useOptionsStore()
					optionsStore.internalOptions.internal.ai = response.body.aiOptions

					return response
				})
		},
		generateFaqs (rephrase = false) {
			const postEditorStore = usePostEditorStore()

			return http.post(links.restUrl('ai/faqs'))
				.send({
					postId      : postEditorStore.currentPost.id,
					postContent : getPostEditedContent(),
					options     : {
						tone     : this.faqs.tone,
						audience : this.faqs.audience
					},
					rephrase : rephrase,
					faqs     : postEditorStore.currentPost.ai.faqs
				})
				.then(response => {
					if (response.body.error) {
						console.error(response.body.error)
						return Promise.reject(response.body.error)
					}

					postEditorStore.currentPost.ai.faqs = response.body.faqs

					const optionsStore = useOptionsStore()
					optionsStore.internalOptions.internal.ai = response.body.aiOptions

					return response
				})
		},
		generateKeyPoints (rephrase = false) {
			const postEditorStore = usePostEditorStore()

			return http.post(links.restUrl('ai/key-points'))
				.send({
					postId      : postEditorStore.currentPost.id,
					postContent : getPostEditedContent(),
					options     : {
						tone     : this.keyPoints.tone,
						audience : this.keyPoints.audience
					},
					rephrase,
					keyPoints : postEditorStore.currentPost.ai.keyPoints
				})
				.then(response => {
					if (response.body.error) {
						console.error(response.body.error)

						return
					}

					postEditorStore.currentPost.ai.keyPoints = response.body.keyPoints

					const optionsStore = useOptionsStore()
					optionsStore.internalOptions.internal.ai = response.body.aiOptions

					return response
				})
		},
		generateMetaTitles (rephrase = false) {
			const postEditorStore = usePostEditorStore()
			const focusKeyword    = postEditorStore.currentPost.keyphrases.focus.keyphrase

			return http.post(links.restUrl('ai/meta/title'))
				.send({
					postId      : postEditorStore.currentPost.id,
					postContent : getPostEditedContent(),
					focusKeyword,
					options     : {
						tone     : this.metaTitle.tone,
						audience : this.metaTitle.audience
					},
					rephrase,
					titles : postEditorStore.currentPost.ai.titles
				})
				.then(response => {
					if (response.body.error) {
						return Promise.reject(response.body.error)
					}

					postEditorStore.currentPost.ai.titles = response.body.titles

					const optionsStore = useOptionsStore()
					optionsStore.internalOptions.internal.ai = response.body.aiOptions

					return response
				})
		},
		generateMetaDescriptions (rephrase = false) {
			const postEditorStore = usePostEditorStore()
			const focusKeyword    = postEditorStore.currentPost.keyphrases.focus.keyphrase

			return http.post(links.restUrl('ai/meta/description'))
				.send({
					postId      : postEditorStore.currentPost.id,
					postContent : getPostEditedContent(),
					focusKeyword,
					options     : {
						tone     : this.metaDescription.tone,
						audience : this.metaDescription.audience
					},
					rephrase,
					descriptions : postEditorStore.currentPost.ai.descriptions
				})
				.then(response => {
					if (response.body.error) {
						return Promise.reject(response.body.error)
					}

					postEditorStore.currentPost.ai.descriptions = response.body.descriptions

					const optionsStore = useOptionsStore()
					optionsStore.internalOptions.internal.ai = response.body.aiOptions

					return response
				})
		},
		addFaq (faq) {
			this.faqData.faqs.push(faq)
		},
		removeFaq (faq) {
			this.faqData.faqs = this.faqData.faqs.filter(item => item.id !== faq.id)
		},
		addKeyPoint (keyPoint) {
			this.keyPointsData.keyPoints.push(keyPoint)
		},
		removeKeyPoint (keyPoint) {
			this.keyPointsData.keyPoints = this.keyPointsData.keyPoints.filter(item => item.id !== keyPoint.id)
		},
		formatFaqs () {
			let faqString = ''
			this.faqData.faqs.forEach((faq) => {
				faqString += `Q: ${faq.question}\nA: ${faq.answer}\n\n`
			})

			return faqString
		},
		formatKeyPoints () {
			let keyPointString = ''
			this.keyPointsData.keyPoints.forEach((keyPoint) => {
				keyPointString += `${keyPoint.title}\n ${keyPoint.explanation}\n\n`
			})

			return keyPointString
		},
		deactivate () {
			return http.post(links.restUrl('ai/deactivate'))
				.send()
				.then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}

					const optionsStore = useOptionsStore()

					if (response?.body?.aiData) {
						optionsStore.internalOptions.internal.ai = response.body.aiData
					}

					return response
				})
		}
	}
})