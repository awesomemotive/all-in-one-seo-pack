import { analyzers } from './analysis'
import { decodeSpecialChars, removeScriptTag } from '@/vue/utils/helpers'
import getLanguage from './researches/helpers/getLanguage.js'

import {
	calculateErrors,
	calculateScore,
	hasAdditionalKeyphrases,
	hasFocusKeyphrase,
	hasKeyphrases,
	parseTags
} from './utils'

const supportedLanguages = [
	// 'ar',
	// 'ca',
	'nl',
	'en',
	'es',
	'fr',
	'de',
	'he',
	// 'hu',
	'id',
	'it',
	'pl',
	'pt',
	'ru'
	// 'se'
]

class TruSeoAnalyzer {
	postId                = null
	postTitle             = null
	postParsedTitle       = null
	postParsedDescription = null
	postDescription       = null
	keyphrases            = null
	postContent           = null
	postParsedContent     = null
	postSlug              = null
	isAnalyzing           = false
	postEditedTitle       = null
	aioseo                = {}
	locale                = null
	domain                = null
	globalSeparator       = null
	dispatch              = []

	/**
	 * Runs the content analysis.
	 *
	 * @param {int}    postId   The Post ID.
	 * @param {Object} postData The postData of the currentPost.
	 * @param {string} content  The post content.
	 * @param {string} slug     The post slug.
	 *
	 * @returns {null} Returns nothing.
	 */
	async runAnalysis (
		{
			postId,
			postData,
			content,
			slug,
			postEditedTitle,
			aioseo,
			aioseoGlobals
		}
	) {
		let locale = aioseo.user.locale || 'en_US'
		if (!supportedLanguages.includes(getLanguage(locale))) {
			locale = 'en_US'
		}

		this.isAnalyzing           = true
		this.aioseo                = aioseo
		this.postId                = postId
		this.postContent           = removeScriptTag(content || '')
		this.postParsedContent     = decodeSpecialChars(content || '')
		this.postSlug              = slug
		this.postTitle             = postData.title || postData.metaDefaults.title || '#post_title'
		this.postParsedTitle       = decodeSpecialChars(parseTags(this.postTitle, this.aioseo.tags))
		this.postDescription       = postData.description || postData.metaDefaults.description
		this.postParsedDescription = decodeSpecialChars(parseTags(this.postDescription, this.aioseo.tags))
		this.keyphrases            = (postData.keyphrases) ? postData.keyphrases : null
		this.postEditedTitle       = decodeSpecialChars(postEditedTitle)
		this.locale                = locale
		this.domain                = this.aioseo.urls.domain

		// Set up the window/self object.
		if ('undefined' !== typeof window) {
			window.aioseoGlobals = aioseoGlobals
		}

		if ('undefined' !== typeof self) {
			self.aioseoGlobals = aioseoGlobals
		}

		// Analysis Actions
		await this.runAnalysisActions({ postId, postData })

		// Update state.
		this.dispatch.push({
			action : 'updateState',
			data   : postData
		})

		this.isAnalyzing = false

		return this.dispatch
	}

	async runAnalysisActions ({ postId, postData }) {
		const focusKeyphrase = hasFocusKeyphrase(this.keyphrases) ? this.keyphrases.focus.keyphrase : null
		const pageAnalysis   = await this.runPageAnalysis({ postId, focusKeyphrase, currentPost: postData })

		postData.seo_score      = pageAnalysis.seo_score
		postData.page_analysis  = pageAnalysis.page_analysis

		// Keyphrase Analysis.
		if (hasKeyphrases(this.keyphrases)) {
			if (hasFocusKeyphrase(this.keyphrases)) {
				const focusAnalysis       = await this.runFocusAnalysis({ postId, keyphrase: this.keyphrases.focus.keyphrase, currentPost: postData })
				postData.keyphrases.focus = focusAnalysis.keyphrases.focus
			}
			if (hasAdditionalKeyphrases(this.keyphrases)) {
				const additionalAnalysis = []
				this.keyphrases.additional.map(async (keyphrase, index) => {
					additionalAnalysis.push(await this.runAdditionalAnalysis({ postId, keyphrase: keyphrase.keyphrase, index, currentPost: postData }))
				})
				additionalAnalysis.forEach((additional, index) => {
					postData.keyphrases.additional.push(additional.keyphrases.additional[index])
				})
			}
		}

		return postData
	}

	async runFocusAnalysis ({ postId, keyphrase, currentPost }) {
		const focusAnalysis = {}

		focusAnalysis.keyphraseInTitle = analyzers.keyphraseInTitle(this.postParsedTitle, keyphrase, this.locale)
		focusAnalysis.keyphraseInDescription = analyzers.keyphraseInDescription(this.postParsedDescription, keyphrase, 'focus', this.locale)
		focusAnalysis.keyphraseLength = analyzers.keyphraseLength(keyphrase, 'focus')

		// Skip this if we're dealing with the static homepage.
		if (postId !== this.aioseo.data.staticHomePage) {
			focusAnalysis.keyphraseInURL = analyzers.keyphraseInURL(this.postSlug, keyphrase)
		}

		if (this.postParsedContent) {
			focusAnalysis.keyphraseInIntroduction = analyzers.keyphraseInIntroduction(this.postParsedContent, keyphrase, 'focus', this.locale)
			focusAnalysis.keyphraseInSubHeadings  = analyzers.keyphraseInSubHeadings(this.postParsedContent, keyphrase, this.locale)
			focusAnalysis.keyphraseInImageAlt     = analyzers.keyphraseInImageAlt(this.postParsedContent, keyphrase, 'focus')
			focusAnalysis.keywordDensity          = analyzers.keywordDensity(this.postParsedContent, keyphrase, 'focus')
		}

		const keyphraseScore = await calculateScore(focusAnalysis)

		if (this.aioseo.currentPost.id) {
			currentPost.keyphrases.focus.analysis = focusAnalysis
			currentPost.keyphrases.focus.score    = keyphraseScore
			currentPost.loading.focus = false
			return currentPost
		} else {
			this.aioseo.currentPost = null
			currentPost = {
				postId     : postId,
				keyphrases : {
					focus : {
						keyphrase : keyphrase,
						score     : keyphraseScore,
						analysis  : focusAnalysis
					}
				}
			}
			return currentPost
		}
	}

	async runAdditionalAnalysis ({ postId, keyphrase, index, currentPost }) {
		const additionalAnalysis = {}

		additionalAnalysis.keyphraseInDescription = analyzers.keyphraseInDescription(this.postParsedDescription, keyphrase, null, this.locale)
		additionalAnalysis.keyphraseLength = analyzers.keyphraseLength(keyphrase)

		if (this.postParsedContent) {
			additionalAnalysis.keyphraseInIntroduction = analyzers.keyphraseInIntroduction(this.postParsedContent, keyphrase, null, this.locale)
			additionalAnalysis.keyphraseInImageAlt     = analyzers.keyphraseInImageAlt(this.postParsedContent, keyphrase)
			additionalAnalysis.keywordDensity          = analyzers.keywordDensity(this.postParsedContent, keyphrase)
		}

		const additionalAnalysisScore = await calculateScore(additionalAnalysis)
		const keyphraseAnalysisResults = {}

		if (this.aioseo.currentPost.id) {
			currentPost.keyphrases.additional[index].analysis = additionalAnalysis
			currentPost.keyphrases.additional[index].score = additionalAnalysisScore
			currentPost.loading.additional[index] = false
		} else {
			currentPost.postId                 = postId
			currentPost.keyphrases             = { additional: [] }
			keyphraseAnalysisResults.keyphrase = keyphrase
			keyphraseAnalysisResults.analysis  = additionalAnalysis
			keyphraseAnalysisResults.score     = additionalAnalysisScore
			currentPost.keyphrases.additional.push(keyphraseAnalysisResults)
		}
		return currentPost
	}

	async runPageAnalysis ({ focusKeyphrase, currentPost }) {
		const title = {}
		let basic = {},
			readability = {},
			pagePercentage

		if (!this.postEditedTitle) {
			return currentPost
		}

		if (hasFocusKeyphrase(this.keyphrases)) {
			basic.keyphraseInContent = analyzers.keyphraseInContent(this.postParsedContent, focusKeyphrase, this.locale)
			basic.keyphraseInIntroduction = analyzers.keyphraseInIntroduction(this.postParsedContent, focusKeyphrase, 'focus', this.locale)
			basic.keyphraseInDescription = analyzers.keyphraseInDescription(this.postParsedDescription, focusKeyphrase, 'focus', this.locale)

			// Skip keyphraseInURL if we're on a static homepage.
			if (currentPost.id !== this.aioseo.data.staticHomePage) {
				basic.keyphraseInURL = analyzers.keyphraseInURL(this.postSlug, focusKeyphrase)
			}
			basic.keyphraseLength = analyzers.keyphraseLength(focusKeyphrase, 'focus')
			title.keyphraseInTitle = analyzers.keyphraseInTitle(this.postParsedTitle, focusKeyphrase, this.locale)
			title.keyphraseInBeginningTitle = analyzers.keyphraseInBeginningTitle(this.postParsedTitle, focusKeyphrase)
		}

		title.titleLength = analyzers.titleLength(this.postParsedTitle)
		// title.titleHasNumber = analyzers.titleHasNumber(this.postParsedTitle)
		// title.titleHasPowerWords = analyzers.titleHasPowerWords(this.postParsedTitle)
		// title.titleSentiment = analyzers.titleSentiment(this.postParsedTitle)

		basic.metadescriptionLength = analyzers.metadescriptionLength(this.postParsedDescription)
		basic.lengthContent = analyzers.lengthContent(this.postContent)
		basic.isInternalLink = analyzers.isInternalLink(this.postContent, this.domain)
		basic.isExternalLink = analyzers.isExternalLink(this.postContent, this.domain)
		// basic.isExternalLinkNoFollow = analyzers.isExternalLinkNoFollow(this.postContent, this.domain)

		readability.contentHasAssets = analyzers.contentHasAssets(this.postContent)
		readability.paragraphLength = analyzers.paragraphLength(this.postContent)
		readability.sentenceLength = analyzers.sentenceLength(this.postContent)
		readability.passiveVoice = analyzers.passiveVoice(this.postContent, this.locale)
		readability.transitionWords = analyzers.transitionWords(this.postContent, this.locale)
		readability.consecutiveSentences = analyzers.consecutiveSentences(this.postContent, this.locale)
		readability.subheadingsDistribution = analyzers.subheadingsDistribution(this.postContent)
		readability.calculateFleschReading = analyzers.calculateFleschReading(this.postContent, this.locale)

		const basicAnalysisScore = await calculateScore(basic)
		const titleAnalysisScore = await calculateScore(title)
		const readabilityAnalysisScore = await calculateScore(readability)
		basic.errors = await calculateErrors(basic)
		title.errors = await calculateErrors(title)
		readability.errors = await calculateErrors(readability)

		if (this.postEditedTitle && !this.postContent) {
			pagePercentage = Math.floor(titleAnalysisScore)
			basic = {}
			readability = {}

			basic.lengthContent = analyzers.lengthContent(this.postContent)
			readability.contentHasAssets = analyzers.contentHasAssets(this.postContent)
		} else {
			pagePercentage = Math.floor((basicAnalysisScore + titleAnalysisScore + readabilityAnalysisScore) / 3)
		}

		const pageAnalysis = { basic, title, readability }

		currentPost.page_analysis.analysis = pageAnalysis
		currentPost.seo_score              = pagePercentage

		return currentPost
	}
}

export default TruSeoAnalyzer