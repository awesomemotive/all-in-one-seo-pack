import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useDirtyOptionsStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

export const useSetupWizardStore = defineStore('SetupWizardStore', {
	state : () => ({
		currentStage : 'welcome',
		// The 'welcome' and 'success' stages are purposefully missing from this list because it is not one of the "steps".
		stages       : [
			'import',
			'category',
			'additional-information',
			'features',
			'search-appearance',
			'smart-recommendations',
			'search-console',
			'license-key'
		],
		importers : [],
		category  : {
			category        : 'blog',
			categoryOther   : null,
			siteTitle       : '',
			metaDescription : ''
		},
		additionalInformation : {
			siteRepresents          : 'organization',
			person                  : null,
			organizationName        : null,
			orgnaizationDescription : null,
			organizationLogo        : null,
			personName              : null,
			personLogo              : null,
			phone                   : null,
			socialShareImage        : null,
			social                  : {
				profiles : {
					sameUsername : {
						enable   : true,
						username : null,
						included : [
							'facebookPageUrl', 'twitterUrl', 'pinterestUrl', 'instagramUrl', 'youtubeUrl', 'linkedinUrl'
						]
					},
					urls : {
						facebookPageUrl : null,
						twitterUrl      : null,
						instagramUrl    : null,
						pinterestUrl    : null,
						youtubeUrl      : null,
						linkedinUrl     : null,
						tumblrUrl       : null,
						yelpPageUrl     : null,
						soundCloudUrl   : null,
						wikipediaUrl    : null,
						myspaceUrl      : null,
						googlePlacesUrl : null,
						wordPressUrl    : null
					}
				}
			}
		},
		features         : [],
		searchAppearance : {
			underConstruction : false,
			postTypes         : {
				postTypes : {
					all      : true,
					included : [
						'post',
						'page',
						'attachment',
						'product'
					]
				}
			},
			multipleAuthors         : true,
			redirectAttachmentPages : true,
			emailReports            : true
		},
		smartRecommendations : {
			accountInfo   : null,
			usageTracking : true
		},
		licenseKey             : null,
		showUsageTrackingModal : false
	}),
	getters : {
		shouldShowImportStep : () => {
			const rootStore = useRootStore()
			return rootStore.aioseo.importers.filter(plugin => plugin.canImport).length
		},
		getNextLink : state => {
			const link  = { name: state.stages[0] }
			const index = state.stages.findIndex(s => s === state.currentStage)
			if (-1 === index) {
				return link
			}

			link.name = state.stages[index + 1] ? state.stages[index + 1] : 'success'

			return link
		},
		getPrevLink : state => {
			const link  = { name: state.stages[0] }
			const index = state.stages.findIndex(s => s === state.currentStage)
			if (-1 === index) {
				return link
			}

			link.name = state.stages[index - 1] ? state.stages[index - 1] : 'welcome'

			return link
		},
		getCurrentStageCount : state => {
			const index = state.stages.findIndex(s => s === state.currentStage)
			if (-1 === index) {
				return 'success' === state.currentStage ? state.getTotalStageCount : 0
			}

			return index + 1
		},
		getTotalStageCount : state => state.stages.length
	},
	actions : {
		saveWizard (section) {
			const rootStore = useRootStore()
			const wizard    = { ...this.$state }

			// Force the usage tracking modal to be hidden.
			wizard.showUsageTrackingModal = false

			return http.post(links.restUrl('wizard'))
				.send({
					section,
					wizard,
					network : rootStore.aioseo.data.isNetworkAdmin
				})
				.then(response => {
					if (response.body.options) {
						const dirtyOptionsStore = useDirtyOptionsStore()
						const optionsStore      = useOptionsStore()

						optionsStore.options = response.body.options

						dirtyOptionsStore.updateOriginalOptions('options', response.body.options)
					}
				})
		},
		loadState (payload) {
			Object.keys(payload).forEach(key => {
				this[key] = payload[key]
			})
		}
	}
})