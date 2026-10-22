import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { getAllResultsGrouped, KEYWORD_ASSESSMENT_IDS } from '@/app/tru-seo/helpers/resultsFilter'
import { decodeSpecialChars } from '@/vue/utils/helpers'
import { withoutEphemeralAnalysisData } from '@/vue/utils/postData/analysisPayload'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

let cachedCurrentPost = null

const prepareCachedCurrentPost = (currentPost) => {
	// Ignore UI state and analysis-related properties at any nesting level.
	// `truseo`/`additional_keywords` are analysis output regenerated on every load —
	// truseo re-adds the highlightSentences/marks that are stripped before saving, so
	// a fresh scan never matches the persisted copy — and must not count as an unsaved
	// change. The user's keyword text is tracked via `keyphrases`.
	// `highlightingEnabled` is a per-post viewing preference persisted on its own
	// endpoint — excluded so toggling the highlighter never trips an unsaved-changes prompt.
	// `showReviewCta` is server-computed UI state dismissed through its own endpoint — same reason.
	const ignore = new Set([ 'modalOpen', 'seo_score', 'page_analysis', 'truseo', 'additional_keywords', 'headlineAnalyzer', 'loading', 'score', 'analysis', 'ai', 'highlightingEnabled', 'showReviewCta' ])

	return JSON.stringify(currentPost, (key, value) => ignore.has(key) ? undefined : value)
}

export const usePostEditorStore = defineStore('PostEditorStore', {
	state : () => ({
		isDirty            : false,
		currentPost        : {},
		isFetchingPostData : false
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
		},
		truseoData () {
			const {
				truseo,
				focus_keyword: focusKeyword,
				additional_keywords: additionalKeywords
			} = this.currentPost

			const focusKeywordAnalysis = truseo?.focus_keyword || null
			const generalAnalysis      = truseo?.general || null

			// Keyword checks belong to the Keywords tab, so keep them out of the basic/readability
			// lists unconditionally — deriving this from the keyword's own results meant that with
			// no keyword set they all fell through into Basics as "Add a focus keyword" filler and
			// inflated its issue count. Scores are computed from the raw `general` map, so
			// excluding here does not affect them.
			const excludeIds = new Set(KEYWORD_ASSESSMENT_IDS)
			if (focusKeyword && focusKeywordAnalysis?.items) {
				Object.keys(focusKeywordAnalysis.items).forEach(id => excludeIds.add(id))
			}
			additionalKeywords?.forEach(keyword => {
				if (keyword?.items) {
					Object.keys(keyword.items).forEach(id => excludeIds.add(id))
				}
			})

			const allResults = generalAnalysis ? getAllResultsGrouped(generalAnalysis, excludeIds) : null

			// Synonyms still live on the keyphrases column (out of scope for the focus_keyword/truseo split).
			const focusKeywordSynonyms = this.currentPost.keyphrases?.focus?.synonyms || ''

			return {
				focusKeyword,
				focusKeywordSynonyms,
				additionalKeywords,
				truseo : {
					focus_keyword : focusKeywordAnalysis,
					general       : {
						basic       : allResults?.basic,
						readability : allResults?.readability,
						spelling    : allResults?.spelling
					}
				}
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
			this.currentPost.headlineAnalyzer.previousHeadlines = this.currentPost.headlineAnalyzer.previousHeadlines || []

			const rawResult = data?.[Object.keys(data || {})?.[0]]
			if (!rawResult) {
				return
			}

			const result = JSON.parse(rawResult)

			this.recordAnalyzedHeadline(headline, result)
			this.currentPost.headlineAnalyzer.latestScore = result.score
		},
		// Keeps the list of headlines scored for this post, newest last. A headline
		// belongs here as soon as it has a score — the user doesn't have to apply it —
		// so they can pick it up again from the sidebar's "Previous Scores" list.
		// Headlines reach us both entity-encoded (from the analyzer API) and decoded
		// (from the headline editor field), so dedupe on the decoded text.
		recordAnalyzedHeadline (headline, result) {
			if (!headline || !result) {
				return
			}

			this.currentPost.headlineAnalyzer = this.currentPost.headlineAnalyzer || {}
			this.currentPost.headlineAnalyzer.previousHeadlines = this.currentPost.headlineAnalyzer.previousHeadlines || []

			const decoded = decodeSpecialChars(headline)
			if (this.currentPost.headlineAnalyzer.previousHeadlines.some(item => decodeSpecialChars(item.headline) === decoded)) {
				return
			}

			this.currentPost.headlineAnalyzer.previousHeadlines.push({
				headline : headline,
				result   : result,
				score    : result.score
			})
		},
		updateLatestScore (score) {
			this.currentPost.headlineAnalyzer.latestScore = score
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
		// Mirror the Content Analysis card's live headline preview into the shared
		// state the sidebar reads, so both surfaces analyze the same headline. Unlike
		// updateNewHeadlineAnalyzerData, this skips previousHeadlines — a transient
		// preview shouldn't land in the "Previous Scores" list on every keystroke.
		setNewHeadlineAnalyzerPreview (data, headline) {
			this.currentPost.headlineAnalyzer = this.currentPost.headlineAnalyzer || {}
			this.currentPost.headlineAnalyzer.newData = {
				data,
				headline,
				showPreview : true
			}
			this.currentPost.headlineAnalyzer.showNewData = true
		},
		clearNewHeadlineAnalyzerPreview () {
			if (!this.currentPost.headlineAnalyzer) {
				return
			}

			this.currentPost.headlineAnalyzer.newData     = null
			this.currentPost.headlineAnalyzer.showNewData = false
		},
		changeGeneralPreview (value) {
			this.currentPost.generalMobilePrev = value
		},
		saveCurrentPost (payload) {
			// Must match PostsTerms::updatePosts (REST) — only aioseo_page_general_settings may persist.
			if (!allowed('aioseo_page_general_settings')) {
				return Promise.resolve(false)
			}

			this.currentPost = payload

			return http.post(links.restUrl('post'))
				.send(withoutEphemeralAnalysisData(payload))
				.then(() => true)
				.catch((error) => {
					console.error(`Unable to update the post data: ${error}`)

					return false
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
				this.isDirty = true

				cachedCurrentPost = prepareCachedCurrentPost(this.currentPost)

				window.aioseoBus.$emit('postSettingsUpdated')
			}

			const postField = document.querySelector('#aioseo-post-settings')
			const termField = 'term' === this.currentPost.context
				? document.querySelector('#aioseo-term-settings')
				: null

			if (postField || termField) {
				const fieldValue = JSON.stringify(withoutEphemeralAnalysisData(this.currentPost))

				if (postField) {
					postField.value = fieldValue
				}

				if (termField) {
					termField.value = fieldValue
				}
			}
		},
		disablePrimaryTermEducation () {
			if (!allowed('aioseo_page_general_settings')) {
				return Promise.resolve()
			}

			this.currentPost.options.primaryTerm.productEducationDismissed = true

			return http.post(links.restUrl(`post/${this.currentPost.id}/disable-primary-term-education`))
		},
		disableLinkAssistantEducation () {
			if (!allowed('aioseo_page_general_settings')) {
				return Promise.resolve()
			}

			this.currentPost.options.linkFormat.linkAssistantDismissed = true

			return http.post(links.restUrl(`post/${this.currentPost.id}/disable-link-format-education`))
		},
		saveTruSeoHighlighting (enabled) {
			const post = this.currentPost

			// Post-only, permission-gated preference. Terms carry no options.truSeo and
			// the endpoint is post-specific; users who can't persist post settings keep a
			// working session-only toggle. In every skipped case the in-memory toggle stands.
			if ('term' === post?.context || !post?.options?.truSeo || !allowed('aioseo_page_general_settings')) {
				return Promise.resolve()
			}

			post.options.truSeo.highlightingEnabled = enabled

			// The toggle calls this fire-and-forget. superagent requests are lazy — they
			// only dispatch once a .then/.catch is attached — so chain here to guarantee
			// the request is actually sent; a bare return would never reach the server.
			return http.post(links.restUrl(`post/${post.id}/tru-seo-highlighting`))
				.send({ enabled })
				.then(() => true)
				.catch(() => false)
		},
		incrementInternalLinkCount () {
			if (!allowed('aioseo_page_general_settings')) {
				return Promise.resolve()
			}

			const count = this.currentPost.options.linkFormat.internalLinkCount || 0

			this.currentPost.options.linkFormat.internalLinkCount = count + 1

			return http.post(links.restUrl(`post/${this.currentPost.id}/update-internal-link-count`))
				.send({
					count
				})
		},
		getUserImage ({ userId }) {
			if (!allowed('aioseo_page_social_settings')) {
				return Promise.resolve('')
			}

			return http.get(links.restUrl(`user/${userId}/image`))
				.then(response => 200 === response.statusCode ? response.body.url : '')
		},
		getFirstAttachedImage ({ postId }) {
			if (!allowed('aioseo_page_social_settings')) {
				return Promise.resolve('')
			}

			return http.get(links.restUrl(`post/${postId}/first-attached-image`))
				.then(response => 200 === response.statusCode ? response.body.url : '')
		},
		getMediaData ({ mediaId }) {
			return http.get(links.restUrl(`media/${mediaId}`, 'wp/v2'))
				.then(response => 200 === response.statusCode ? response.body : {})
		},
		processContent ({ content, integration }) {
			if (!allowed('aioseo_page_general_settings')) {
				return Promise.resolve()
			}

			return http.post(links.restUrl(`post/${this.currentPost.id}/process-content`))
				.send({
					content,
					integration
				})
				.then(response => {
					this.currentPost.processedContent = response.body.content
				})
				.catch(error => {
					throw error
				})
		},
		fetchPostData (payload = {}) {
			this.isFetchingPostData = true

			return http.get(links.restUrl('post'))
				.query(payload)
				.then(response => response)
				.catch(error => {
					throw error
				})
				.finally(() => {
					this.isFetchingPostData = false
				})
		}
	}
})