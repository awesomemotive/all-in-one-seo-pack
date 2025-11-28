<template>
	<div class="aioseo-focus-keyphrase-panel">
		<focus-keyphrase-input v-if="!hasFocusKeyphrase" :screen-context="$root.$data.screenContext"
			@add="handleAddKeyphrase" />

		<focus-keyphrase-display v-if="hasFocusKeyphrase"
			:keyphrase="postEditorStore.currentPost.keyphrases.focus.keyphrase"
			:score="postEditorStore.currentPost.keyphrases.focus.score" :loading="postEditorStore.currentPost.loading.focus"
			:analysis-items="postEditorStore.currentPost.keyphrases.focus.analysis" @saved="handleKeyphraseSaved"
			@deleted="handleKeyphraseDeleted" />

		<semrush-keywords-button :has-focus-keyphrase="hasFocusKeyphrase"
			:loading="postEditorStore.currentPost.loading.focus" :is-pro="rootStore.isPro"
			:is-unlicensed="licenseStore.isUnlicensed" :show-tooltip="showSemrushTooltip"
			:settings-url="rootStore.aioseo.urls.aio.settings" @click="handleSemrushButtonClick" />

		<semrush-keywords-modal :show="semrushShowModal" :keywords="semrushStore.results" :loading="loadingResults"
			:error="semrushStore.error" :focus-keyphrase="postEditorStore.currentPost.keyphrases.focus?.keyphrase || ''"
			:additional-keyphrases="postEditorStore.currentPost.keyphrases.additional"
			:adding-index="addingAdditionalKeyphrase" :removing-index="removingAdditionalKeyphrase"
			:max-additional-keyphrases="postEditorStore.currentPost.maxAdditionalKeyphrases"
			:is-unlicensed="licenseStore.isUnlicensed" :country-value="semrushCountry" @close="semrushShowModal = false"
			@add="handleAddAdditionalKeyphrase" @remove="handleRemoveAdditionalKeyphrase"
			@navigate="handleNavigateToAdditionalKeyphrase" @country-changed="handleCountryChanged" />
	</div>
</template>

<script>
import {
	useConnectStore,
	useLicenseStore,
	useOptionsStore,
	usePostEditorStore,
	useRootStore,
	useSemrushStore,
	useSettingsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import { openSemrushOAuthPopup, getSemrushAuthRequirement } from '@/vue/utils/semrushOAuth'
import { openConnectOAuthPopup } from '@/vue/utils/connectOAuth'
import { fetchSemrushKeywords, getSemrushDatabaseOptions } from '@/vue/utils/semrushKeywords'
import { addFocusKeyphrase, updateFocusKeyphrase, deleteFocusKeyphrase } from '@/vue/utils/focusKeyphraseManager'
import {
	addAdditionalKeyphrase,
	navigateToAdditionalKeyphrase,
	removeAdditionalKeyphrase
} from '@/vue/utils/additionalKeyphrasesManager'

import FocusKeyphraseInput from './focus-keyphrase/FocusKeyphraseInput'
import FocusKeyphraseDisplay from './focus-keyphrase/FocusKeyphraseDisplay'
import SemrushKeywordsButton from './focus-keyphrase/SemrushKeywordsButton'
import SemrushKeywordsModal from './focus-keyphrase/SemrushKeywordsModal'

export default {
	setup () {
		return {
			connectStore    : useConnectStore(),
			licenseStore    : useLicenseStore(),
			optionsStore    : useOptionsStore(),
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore(),
			semrushStore    : useSemrushStore(),
			settingsStore   : useSettingsStore(),
			truSeo          : new TruSeo()
		}
	},
	components : {
		FocusKeyphraseInput,
		FocusKeyphraseDisplay,
		SemrushKeywordsButton,
		SemrushKeywordsModal
	},
	data () {
		return {
			showSemrushTooltip          : false,
			loadingResults              : false,
			semrushShowModal            : false,
			addingAdditionalKeyphrase   : false,
			removingAdditionalKeyphrase : false,
			semrushCountry              : null
		}
	},
	computed : {
		hasFocusKeyphrase () {
			return !!(this.postEditorStore.currentPost.keyphrases.focus && this.postEditorStore.currentPost.keyphrases.focus.keyphrase)
		}
	},
	methods : {
		handleAddKeyphrase () {
			addFocusKeyphrase({
				postEditorStore : this.postEditorStore,
				truSeo          : this.truSeo,
				screenContext   : this.$root.$data.screenContext
			})
		},
		handleKeyphraseSaved (payload) {
			const { value } = payload
			updateFocusKeyphrase({
				postEditorStore : this.postEditorStore,
				truSeo          : this.truSeo,
				value           : value
			})
		},
		handleKeyphraseDeleted () {
			deleteFocusKeyphrase({
				postEditorStore : this.postEditorStore,
				truSeo          : this.truSeo
			})
		},
		async handleSemrushButtonClick () {
			this.showSemrushTooltip = false

			if (this.optionsStore.internalOptions.integrations.semrush.accessToken && this.semrushStore.expired) {
				await this.semrushStore.refresh()
			}

			const authRequirement = getSemrushAuthRequirement({
				isConnected    : this.connectStore.isConnected,
				hasValidTokens : this.semrushStore.hasValidTokens,
				connectUrl     : this.rootStore.aioseo.urls.connect
			})

			if (!authRequirement) {
				this.openModal()
				return
			}

			if ('connect' === authRequirement.type) {
				this.openConnectPopup(authRequirement.url)
				return
			}

			if ('semrush_oauth' === authRequirement.type) {
				this.openSemrushOAuthPopup()
			}
		},
		openConnectPopup (url) {
			openConnectOAuthPopup({
				url             : url,
				onAuthenticated : (token) => {
					return this.connectStore.saveConnectToken(token)
				},
				onClosed : (reload) => {
					this.handleConnectOAuthClosed(reload)
				}
			})
		},
		openSemrushOAuthPopup () {
			openSemrushOAuthPopup({
				onAuthenticated : (code) => {
					return this.semrushStore.authenticate(code)
				},
				onClosed : (reload) => {
					this.handleSemrushOAuthClosed(reload)
				}
			})
		},
		handleConnectOAuthClosed (reload) {
			if (!reload) {
				return
			}

			if (this.semrushStore.hasValidTokens) {
				this.$nextTick(this.handleSemrushButtonClick)
				return
			}

			// We can't trigger the additional keyphrases popup if they have not connected to Semrush, or the browser will block the popup.
			this.showSemrushTooltip = true
		},
		handleSemrushOAuthClosed (reload) {
			if (reload) {
				this.openModal()
			}
		},
		openModal () {
			this.semrushShowModal = true
			if (this.semrushStore.error) {
				return
			}

			this.getKeyphrases()
		},
		getKeyphrases () {
			fetchSemrushKeywords({
				semrushStore   : this.semrushStore,
				database       : this.semrushCountry.value,
				focusKeyphrase : this.postEditorStore.currentPost?.keyphrases?.focus?.keyphrase,
				onStart        : () => {
					this.loadingResults = true
				},
				onSuccess : () => {
					this.loadingResults = false
				},
				onError : () => {
					this.semrushShowModal = false
					this.loadingResults = false
				}
			})
		},
		handleCountryChanged (newCountry) {
			if (this.semrushCountry.value === newCountry.value && this.semrushCountry.label === newCountry.label) {
				return
			}

			this.settingsStore.changeSemrushCountry(newCountry)
			this.semrushCountry = newCountry
			this.getKeyphrases()
		},
		async handleAddAdditionalKeyphrase (keyphrase, index) {
			await addAdditionalKeyphrase({
				postEditorStore : this.postEditorStore,
				truSeo          : this.truSeo,
				keyphrase       : keyphrase,
				onStart         : () => {
					this.addingAdditionalKeyphrase = index
				},
				onSuccess : () => {
					this.addingAdditionalKeyphrase = false
				}
			})
		},
		handleRemoveAdditionalKeyphrase (keyphrase, index) {
			removeAdditionalKeyphrase({
				postEditorStore : this.postEditorStore,
				keyphrase       : keyphrase,
				onStart         : () => {
					this.removingAdditionalKeyphrase = index
				},
				onSuccess : () => {
					this.removingAdditionalKeyphrase = false
				},
				nextTick : async () => {
					await this.$nextTick()
				}
			})
		},
		handleNavigateToAdditionalKeyphrase (keyphrase) {
			navigateToAdditionalKeyphrase({
				postEditorStore : this.postEditorStore,
				keyphrase       : keyphrase,
				onSuccess       : () => {
					this.semrushShowModal = false
				}
			})
		}
	},
	mounted () {
		const countryOptions = getSemrushDatabaseOptions()
		this.semrushCountry = {
			value : this.settingsStore.settings.semrushCountry,
			label : countryOptions.find(country => country.value === this.settingsStore.settings.semrushCountry)?.label || this.settingsStore.settings.semrushCountry
		}
	}
}
</script>

<style lang="scss">
.aioseo-focus-keyphrase-panel {
	.add-focus-keyphrase-metabox-button {
		display: flex;
	}

	.add-focus-keyphrase-sidebar-button {
		.add-keyphrase {
			margin-bottom: 0 !important;
		}

		.aioseo-tooltip {
			margin-left: 0;
			display: block;

			.disabled-button {
				display: flex;
			}

			@at-root .is-page-builder & div:nth-child(2) {
				display: block;
			}
		}
	}
}
</style>