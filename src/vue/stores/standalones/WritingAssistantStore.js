import { defineStore } from 'pinia'
import { sortHelper } from '@/vue/utils/sort'
import escapeRegex from '@/app/tru-seo/analyzer/researches/helpers/escapeRegex'
import { getPostEditedTitle } from '@/vue/plugins/tru-seo/components/postTitle'
import { getPostExcerpt } from '@/vue/plugins/tru-seo/components/postExcerpt'
import http from '@/vue/utils/http'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useWritingAssistantStore = defineStore('WritingAssistantStore', {
	state : () => ({
		loaded           : false,
		keywordText      : '',
		report           : {},
		contentAnalysis  : {},
		analyzingContent : false,
		error            : null,
		processing       : false,
		polling          : false,
		postId           : 0,
		progress         : {
			percent : 0,
			text    : ''
		},
		progressRunning : false,
		sortFilters     : {
			optimizationWizard : {
				slug    : 'importance',
				sortDir : 'desc',
				perPage : 10,
				page    : 1,
				search  : ''
			},
			competitors : {
				slug    : 'googlePosition',
				sortDir : 'asc',
				perPage : 10,
				page    : 1,
				search  : ''
			}
		},
		seoBoost : {
			isLoggedIn : false,
			loginUrl   : ''
		},
		userInfo : {},
		updating : {
			userInfo      : false,
			reportHistory : false
		},
		reportHistory : []
	}),
	getters : {
		isProcessing : (state) => {
			// Check if we are processing.
			if (state.report?.uuid && !state.error) {
				return 100 !== parseInt(state.report?.progress)
			}

			return state.processing
		},
		processDone : (state) => {
			return 100 === parseInt(state.report?.progress)
		},
		getCompetitors : (state) => {
			return state.report.competitors.competitors || []
		},
		getCompetitorsSearched : (state) => {
			const searchString = state.sortFilters.competitors.search.toLowerCase()
			return state.getCompetitors.filter(competitor => {
				return competitor.url.toLowerCase().includes(searchString) ||
					       competitor.title.toLowerCase().includes(searchString)
			})
		},
		getCompetitorsPaged : (state) => {
			const from = (state.sortFilters.competitors.page - 1) * state.sortFilters.competitors.perPage
			const to = from + state.sortFilters.competitors.perPage
			return state.getCompetitorsSearched.slice(from, to)
		},
		getCompetitorsPages : (state) => {
			return Math.ceil(state.getCompetitorsSearched.length / state.sortFilters.competitors.perPage)
		},
		getKeywordCompetitorsSummary : (state) => {
			return state.report.competitors.summary || []
		},
		getKeywords : (state) => {
			return state.report.keywords || []
		},
		getKeywordsSearched : (state) => {
			const searchString = state.sortFilters.optimizationWizard.search.toLowerCase()
			return state.getKeywords.filter(keyword => {
				return keyword.text.toLowerCase().includes(searchString)
			})
		},
		getKeywordsPaged : (state) => {
			const from = (state.sortFilters.optimizationWizard.page - 1) * state.sortFilters.optimizationWizard.perPage
			const to = from + state.sortFilters.optimizationWizard.perPage
			return state.getKeywordsSearched.slice(from, to)
		},
		getKeywordsPages : (state) => {
			return Math.ceil(state.getKeywordsSearched.length / state.sortFilters.optimizationWizard.perPage)
		},
		getContentAnalysis : (state) => {
			return state.contentAnalysis || []
		},
		hasReport : (state) => {
			return state.report && 100 === parseInt(state.report?.progress)
		}
	},
	actions : {
		processKeyword ({ keyword, country, language }) {
			if (this.processing) {
				return
			}

			this.processing = true
			this.setReportProgress(0)
			this.error = null

			http.post('/writing-assistant/process')
			    .send({
				    postId   : this.postId,
				    keyword  : keyword,
				    country  : country,
				    language : language
			    })
			    .then(result => {
				    if (false === result.body.success) {
					    this.processing = false
					    this.error = result.body.error
					    return
				    }

				    this.keywordText = keyword
				    this.setReportProgress(5, __('processing keyword', td) + '...')

					// Reset tables filters and pagination.
				    this.resetTables()

				    // Start polling results.
				    this.pollKeyword()
			    })
		},
		pollKeyword () {
			this.processing      = true
			this.polling         = true
			this.error           = null

			// Get keywords and add to the state.
			http.get('/writing-assistant/keyword/' + this.postId)
			    .then(result => {
				    if (false === result.body?.success) {
					    this.processing = false
					    this.polling = false
					    this.error = result.body.error
					    this.report = {}
					    return
				    }

				    this.startReportProgress(result.body.progress)

				    // Trigger a keyword count and user info update.
				    if (100 === parseInt(result.body.progress)) {
					    this.updateUserInfo()
					    this.updateReportHistory()
					    this.report = result.body || []

					    // Give the frontend time to show the loading bar.
					    setTimeout(() => {
						    this.polling = false
						    this.processing = false
						    window.aioseoBus.$emit('writingAssistantAnalyzeContent')
					    }, 2000)

					    return
				    }

				    // Polling progress.
				    setTimeout(() => {
					    this.pollKeyword()
				    }, 8000)
			    })
		},
		openReport (keyword, country, language) {
			this.processKeyword({
				keyword,
				country,
				language
			})
		},
		async analyzeContent (content) {
			if (this.analyzingContent) {
				return Promise.resolve()
			}

			this.analyzingContent = true
			// Send post content to analysis.
			return http.post('/writing-assistant/content-analysis')
			           .send({
				           title       : getPostEditedTitle(),
				           description : getPostExcerpt(),
				           content     : content,
				           postId      : this.postId
			           })
			           .then(result => {
				           this.analyzingContent = false
				           this.contentAnalysis  = result.body || {}
			           })
		},
		async countContentKeywords (payload) {
			if (!payload || !this.getKeywords.length) {
				return Promise.resolve()
			}

			// Remove all tags.
			payload = payload.replace(/(<([^>]+)>)/gi, ' ')

			this.getKeywords.forEach((keyword) => {
				// Pattern searches for the keyword without any boundaries before or after it.
				keyword.contentCount = payload.match(new RegExp('\\b' + escapeRegex(keyword.text) + '\\b', 'gmi'))?.length || 0
			})

			// Sort the keywords again.
			if ('contentCount' === this.sortFilters.optimizationWizard.slug) {
				this.doOptimizationWizardSort({
					slug  : this.sortFilters.optimizationWizard.slug,
					order : this.sortFilters.optimizationWizard.sortDir
				})
			}

			return Promise.resolve()
		},
		doOptimizationWizardSort (payload) {
			// Forced order.
			let order = payload?.order || 'asc'

			// If we are sorting by the same column, reverse the order.
			if (!payload?.order && payload.slug === this.sortFilters.optimizationWizard.slug) {
				order = 'asc' === this.sortFilters.optimizationWizard.sortDir ? 'desc' : 'asc'
			}

			this.sortFilters.optimizationWizard.slug = payload.slug
			this.sortFilters.optimizationWizard.sortDir = order
			this.getKeywords.sort((a, b) => {
				return sortHelper(a, b, this.sortFilters.optimizationWizard.slug, this.sortFilters.optimizationWizard.sortDir)
			})
		},
		doOptimizationWizardPerPage (perPage) {
			this.sortFilters.optimizationWizard.page = 1
			this.sortFilters.optimizationWizard.perPage = perPage
		},
		doOptimizationWizardSearch (search) {
			this.sortFilters.optimizationWizard.page = 1
			this.sortFilters.optimizationWizard.search = search
		},
		doCompetitorSort (payload) {
			let order = 'asc'
			if (payload.slug === this.sortFilters.competitors.slug) {
				order = 'asc' === this.sortFilters.competitors.sortDir ? 'desc' : 'asc'
			}

			this.sortFilters.competitors.slug = payload.slug
			this.sortFilters.competitors.sortDir = order

			this.getCompetitors.sort((a, b) => {
				return sortHelper(a, b, this.sortFilters.competitors.slug, this.sortFilters.competitors.sortDir)
			})
		},
		doCompetitorPerPage (perPage) {
			this.sortFilters.competitors.page = 1
			this.sortFilters.competitors.perPage = perPage
		},
		doCompetitorSearch (search) {
			this.sortFilters.competitors.page = 1
			this.sortFilters.competitors.search = search
		},
		newReport () {
			this.report = {}
			this.contentAnalysis = {}
			this.error = null
		},
		updateUserInfo () {
			if (this.updating.userInfo) {
				return
			}

			this.updating.userInfo = true
			http.get('/writing-assistant/user-info')
			    .then(result => {
				    if (!result.body?.error) {
					    this.userInfo = result.body || {}
				    }
				    this.updating.userInfo = false
			    })
		},
		updateReportHistory () {
			if (this.updating.reportHistory) {
				return
			}

			this.updating.reportHistory = true
			http.get('/writing-assistant/report-history')
			    .then(result => {
				    if (!result.body?.error) {
					    this.reportHistory = result.body || {}
				    }
				    this.updating.reportHistory = false
			    })
		},
		setUserLoggedIn (isLoggedIn) {
			this.seoBoost.isLoggedIn = isLoggedIn

			if (isLoggedIn) {
				this.updateUserInfo()
				if (this.isProcessing) {
					this.pollKeyword()
				}
			}
		},
		async startReportProgress (progress) {
			// If we are already running a progress, don't start another one.
			if (this.progressRunning) {
				return
			}

			this.progressRunning = true

			const randBetween = (min, max) => Math.floor(Math.random() * (max - min + 1) + min)

			const loadingStages = [
				{ percent: randBetween(5, 10), duration: 2000, text: __('querying search engines', td) + '...' },
				{ percent: randBetween(15, 20), duration: 5000, text: __('fetching serps', td) + '...' },
				{ percent: randBetween(25, 30), duration: 6000, text: __('analyzing serps', td) + '...' },
				{ percent: randBetween(35, 40), duration: 10000, text: __('fetching competitor data', td) + '...' },
				{ percent: randBetween(45, 50), duration: 7000, text: __('fetching keywords data', td) + '...' },
				{ percent: randBetween(55, 60), duration: 7000, text: __('calculating keywords heading presence', td) + '...' },
				{ percent: randBetween(65, 70), duration: 7000, text: __('calculating keywords use suggestions', td) + '...' },
				{ percent: randBetween(75, 80), duration: 7000, text: __('calculating keywords importance', td) + '...' },
				{ percent: randBetween(85, 90), duration: 7000, text: __('fetching keywords examples', td) + '...' },
				{ percent: 100, duration: 1000, text: __('fetching report', td) + '...' }
			]

			let prevStageDuration = 0

			for (const stage of loadingStages.filter(stage => stage.percent >= progress)) {
				await new Promise(resolve => {
					setTimeout(() => {
						// Set lower than 100 so the progress bar feels like it's finishing to load.
						if (100 === stage.percent) {
							stage.percent = 97
							this.progressRunning = false
						}

						this.setReportProgress(stage.percent, stage.text)

						// Save fake progress in case user reloads page.
						if (100 !== progress) {
							http.post('/writing-assistant/set-report-progress')
							    .send({
								    postId   : this.postId,
								    progress : stage.percent
							    }).then(result => result.body)
						}

						resolve()
					}, prevStageDuration)
					prevStageDuration = stage.duration
				})
			}
		},
		setReportProgress (percent, text = '', timeout = 0) {
			setTimeout(() => {
				this.progress.percent = percent
				this.progress.text = text
			}, timeout)
		},
		resetTables () {
			this.sortFilters.optimizationWizard = {
				slug    : 'importance',
				sortDir : 'desc',
				perPage : 10,
				page    : 1,
				search  : ''
			}
			this.sortFilters.competitors = {
				slug    : 'googlePosition',
				sortDir : 'asc',
				perPage : 10,
				page    : 1,
				search  : ''
			}
		}
	}
})