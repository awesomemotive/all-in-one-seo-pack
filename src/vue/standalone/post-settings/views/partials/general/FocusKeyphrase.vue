<template>
	<div class="aioseo-focus-keyphrase-panel">
		<base-input
			v-if="!postEditorStore.currentPost.keyphrases.focus || !postEditorStore.currentPost.keyphrases.focus.keyphrase"
			size="medium"
			:class="`add-focus-keyphrase-${$root.$data.screenContext}-input`"
			@keydown.enter="pressEnter"
		/>

		<div
			:class="`add-focus-keyphrase-${$root.$data.screenContext}-button`"
		>
			<base-button
				v-if="!postEditorStore.currentPost.keyphrases.focus || !postEditorStore.currentPost.keyphrases.focus.keyphrase"
				id="add-focus-keyphrase"
				class="add-keyphrase gray medium"
				@click="addKeyphraseEv"
			>
				<svg-circle-plus width="14" height="14" />
				{{ strings.addKeyphrase }}
			</base-button>

			<core-tooltip
				v-if="!postEditorStore.currentPost.keyphrases.focus || !postEditorStore.currentPost.keyphrases.focus.keyphrase"
			>
				<div
					class="disabled-button gray medium"
				>
					<svg-circle-plus width="14" height="14" />
					{{ strings.getAdditionalKeyphrases }}
				</div>

				<template #tooltip>
					<span>
						{{ strings.semrushGetAdditionalKeyphrases }}
						{{ strings.pleaseAddFocusKeyphrase }}
					</span>
				</template>
			</core-tooltip>
		</div>

		<core-keyphrase
			v-if="postEditorStore.currentPost.keyphrases.focus && postEditorStore.currentPost.keyphrases.focus.keyphrase"
			:index=0
			:keyphrase="postEditorStore.currentPost.keyphrases.focus.keyphrase"
			:score="postEditorStore.currentPost.keyphrases.focus.score"
			@saved="onSaved"
			@deleted="onDeleted"
			class="aioseo-keyphrase-tag"
		/>

		<core-loader
			class="analysis-loading"
			v-if="postEditorStore.currentPost.loading.focus && postEditorStore.currentPost.keyphrases.focus"
			dark
		/>

		<metaboxAnalysisDetail
			v-if="!postEditorStore.currentPost.loading.focus && postEditorStore.currentPost.keyphrases.focus && postEditorStore.currentPost.keyphrases.focus.keyphrase"
			:analysisItems="postEditorStore.currentPost.keyphrases.focus.analysis"
		/>

		<core-tooltip
			v-if="!postEditorStore.currentPost.loading.focus && postEditorStore.currentPost.keyphrases.focus && postEditorStore.currentPost.keyphrases.focus.keyphrase && rootStore.isPro && licenseStore.isUnlicensed"
		>
			<div
				class="disabled-button gray"
			>
				<svg-circle-plus width="14" height="14" />
				{{ strings.getAdditionalKeyphrases }}
			</div>

			<template #tooltip>
				<span
					v-html="strings.semrushTooltipLicenseKey"
				/>
			</template>
		</core-tooltip>

		<core-tooltip
			v-if="!postEditorStore.currentPost.loading.focus && postEditorStore.currentPost.keyphrases.focus && postEditorStore.currentPost.keyphrases.focus.keyphrase && (!rootStore.isPro || !licenseStore.isUnlicensed)"
			:disabled="!showSemrushTooltip || rootStore.isPro"
			:force-show="showSemrushTooltip && !rootStore.isPro"
		>
			<base-button
				class="add-keyphrase gray medium"
				@click="getAdditionalKeyphrases"
			>
				<svg-circle-plus width="14" height="14" />
				{{ strings.getAdditionalKeyphrases }}
			</base-button>

			<template #tooltip>
				<span>
					{{ strings.semrushTooltip }}
				</span>
			</template>
		</core-tooltip>

		<core-modal
			:show="semrushShowModal"
			@close="semrushShowModal = false"
			:classes="[ 'aioseo-focus-keyphrase-panel-modal' ]"
		>
			<template #headerTitle>
				{{ strings.modalTitle }}
			</template>

			<template #body>
				<div class="aioseo-modal-content has-padding">
					<core-alert
						v-if="licenseStore.isUnlicensed"
						type="blue"
						v-html="strings.upsell"
					/>

					<div class="aioseo-settings-row">
						<div class="settings-name">
							<div class="name">
								{{ strings.showResultsFor }}
							</div>
						</div>
						<base-select
							class="semrush-country-selector"
							size="medium"
							:options="semrushDatabase()"
							:placeholder="strings.selectPriceIndicator"
							v-model="semrushCountry"
						/>
					</div>

					<div class="results">
						<table
							aria-label="Additional Keyphrases"
							class="additional-keyphrases-table"
							cellpadding="0"
							cellspacing="0"
						>
							<thead>
								<tr class="keyphrases-header">
									<th class="keyphrase">{{ strings.keyphrase }}</th>
									<th class="keyphrase-volume">{{ strings.volume }}</th>
									<th class="keyphrase-trend">{{ strings.trend }}</th>
									<th
										v-if="!licenseStore.isUnlicensed"
										class="keyphrase-actions"
									/>
								</tr>
							</thead>
							<tbody class="keyphrases-rows">
								<template
									v-if="semrushStore.results.length && !loadingResults"
								>
									<tr
										class="keyphrase-row"
										:class="{ even: 0 === index % 2 }"
										v-for="(keyphrase, index) in semrushStore.results"
										:key="index"
									>
										<td class="keyphrase">
											{{ keyphrase[0] }}
										</td>
										<td class="keyphrase-volume">
											{{ keyphrase[1] }}
										</td>
										<td class="keyphrase-trend">
											<svg-area-chart
												:width="66"
												:height="24"
												:data="transformTrendDataToChartPoints(keyphrase[2])"
												:strokeWidth="1.8"
												strokeColor="#498afc"
												fillColor="#ade3fc"
											/>
										</td>
										<td
											v-if="!licenseStore.isUnlicensed"
											class="keyphrase-actions"
										>
											<div
												v-if="keyphrase[0] === postEditorStore.currentPost.keyphrases.focus.keyphrase.toLowerCase()"
												class="focus-keyphrase"
											>
												<svg-circle-check />
												{{ strings.focusKeyphrase }}
											</div>
											<div
												v-if="keyphrase[0] !== postEditorStore.currentPost.keyphrases.focus.keyphrase.toLowerCase()"
											>
												<base-button
													v-if="index !== removingAdditionalKeyphrase && (index === addingAdditionalKeyphrase || !hasAdditionalKeyphrase(keyphrase[0])) && postEditorStore.currentPost.maxAdditionalKeyphrases > postEditorStore.currentPost.keyphrases?.additional?.length"
													type="gray"
													size="medium"
													@click="addAdditionalKeyphrase(keyphrase[0], index)"
													:loading="index === addingAdditionalKeyphrase"
												>
													{{ strings.addAdditionalKeyphrase }}
												</base-button>

												<core-tooltip v-if="index !== removingAdditionalKeyphrase && (index === addingAdditionalKeyphrase || !hasAdditionalKeyphrase(keyphrase[0])) && postEditorStore.currentPost.maxAdditionalKeyphrases <= postEditorStore.currentPost.keyphrases?.additional?.length">
													<base-button
														type="gray"
														size="medium"
														:disabled="true"
														@click="addAdditionalKeyphrase(keyphrase[0], index)"
														:loading="index === addingAdditionalKeyphrase"
													>
														{{ strings.addAdditionalKeyphrase }}
													</base-button>

													<template #tooltip>
														<span>{{ strings.maxAmountReached }}</span>
													</template>
												</core-tooltip>

												<div
													class="remove-keyphrase"
													v-if="getAdditionalKeyphrase(keyphrase[0]) && index !== addingAdditionalKeyphrase && (index === removingAdditionalKeyphrase || hasAdditionalKeyphrase(keyphrase[0]))"
												>
													<span
														class="keyphrase-score"
														:class="scoreClass(getAdditionalKeyphrase(keyphrase[0]).score)"
														@click="goToAdditionalKeyphrase(keyphrase[0])"
													>{{ getAdditionalKeyphrase(keyphrase[0]).score }}/100</span>

													<core-tooltip
														type="action"
													>
														<svg-trash
															@click.native="removeAdditionalKeyphrase(keyphrase[0], index)"
														/>

														<template #tooltip>
															{{ strings.delete }}
														</template>
													</core-tooltip>
												</div>
											</div>
										</td>
									</tr>
								</template>

								<template
									v-if="!semrushStore.results.length || loadingResults"
								>
									<tr class="keyphrase-row">
										<td
											:colspan="licenseStore.isUnlicensed ? 3 : 4"
											class="no-results"
										>
											<div>
												<core-loader
													v-if="loadingResults"
													dark
												/>

												<span
													v-if="!loadingResults && !semrushStore.error"
												>
													{{ strings.noResults }}
												</span>

												<core-alert
													type="red"
													v-if="semrushStore.error && !semrushStore.error.includes('TOTAL LIMIT EXCEEDED')"
												>
													{{ semrushError }}
												</core-alert>

												<template
													v-if="semrushStore.error && semrushStore.error.includes('TOTAL LIMIT EXCEEDED')"
												>
													<div class="semrush-logo">
														<svg-logo-semrush />
													</div>
													<div class="semrush-upsell">
														<span>
															<strong v-html="strings.youHaveExceededSemrush" />
														</span>

														{{ ' ' }}

														<span v-html="strings.inOrderToUpgradeSemrush" />
													</div>
												</template>
											</div>
										</td>
									</tr>
								</template>
							</tbody>
						</table>
					</div>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import {
	COUNTRY_LIST,
	GLOBAL_STRINGS,
	SEMRUSH_DATABASE
} from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useConnectStore,
	useLicenseStore,
	useOptionsStore,
	usePostEditorStore,
	useRootStore,
	useSemrushStore,
	useSettingsStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import { getParams } from '@/vue/utils/params'

import TruSeo from '@/vue/plugins/tru-seo'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreKeyphrase from '@/vue/components/common/core/Keyphrase'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgAreaChart from '@/vue/components/common/svg/AreaChart'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgLogoSemrush from '@/vue/components/common/svg/logo/Semrush'
import SvgTrash from '@/vue/components/common/svg/Trash'
import metaboxAnalysisDetail from './MetaboxAnalysisDetail'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			connectStore    : useConnectStore(),
			licenseStore    : useLicenseStore(),
			optionsStore    : useOptionsStore(),
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore(),
			semrushStore    : useSemrushStore(),
			truSeo          : new TruSeo()
		}
	},
	components : {
		CoreAlert,
		CoreKeyphrase,
		CoreLoader,
		CoreModal,
		CoreTooltip,
		SvgAreaChart,
		SvgCircleCheck,
		SvgCirclePlus,
		SvgLogoSemrush,
		SvgTrash,
		metaboxAnalysisDetail
	},
	data () {
		const settingsStore = useSettingsStore()

		return {
			showSemrushTooltip          : false,
			loadingResults              : false,
			semrushShowModal            : false,
			addingAdditionalKeyphrase   : false,
			removingAdditionalKeyphrase : false,
			semrushCountry              : null,
			settingsStore,
			strings                     : {
				modalTitle : sprintf(
					// Translators: 1 - Semrush.
					__('Additional Keyphrases by %1$s', td),
					'Semrush'
				),
				addKeyphrase              : __('Add Focus Keyphrase', td),
				getAdditionalKeyphrases   : __('Get Additional Keyphrases', td),
				showResultsFor            : __('Show Results For:', td),
				keyphrase                 : __('Keyphrase', td),
				volume                    : __('Volume', td),
				trend                     : __('Trend', td),
				addAdditionalKeyphrase    : __('Add Keyphrase', td),
				removeAdditionalKeyphrase : __('Remove Keyphrase', td),
				noResults                 : __('No results', td),
				upsell                    : sprintf(
					// Translators: 1 - Plugin short name + Pro "AIOSEO Pro", 2 - Semrush, 3 - Link to learn more.
					__('Analyzing your content with %1$s keywords is only available to licensed %2$s users. %3$s', td),
					'Semrush',
					`<strong>${import.meta.env.VITE_SHORT_NAME} Pro</strong>`,
					links.getUpsellLink('semrush-keywords', GLOBAL_STRINGS.learnMore, null, true)
				),
				semrushGetAdditionalKeyphrases : sprintf(
					// Translators: 1 - Plugin short name "AIOSEO", 2 - Semrush.
					__('%1$s integrates directly with %2$s to provide you with actionable keyphrases to help you write better content.', td),
					import.meta.env.VITE_SHORT_NAME,
					'Semrush'
				),
				pleaseAddFocusKeyphrase : __('To use this feature, first add a focus keyphrase.', td),
				focusKeyphrase          : __('Focus Keyphrase', td),
				delete                  : __('Delete', td),
				semrushTooltip          : sprintf(
					// Translators: 1 - Semrush.
					__('Get Additional Keyphrases with %1$s!', td),
					'Semrush'
				),
				semrushTooltipLicenseKey : sprintf(
					// Translators: 1 - Opening link tag, 2 - Closing link tag, 3 - Semrush.
					__('%1$sA valid license key is required%2$s in order to connect with %3$s.', td),
					'<a href="' + this.rootStore.aioseo.urls.aio.settings + '">',
					'</a>',
					'Semrush'
				),
				youHaveExceededSemrush : sprintf(
					// Translators: 1 - Semrush.
					__('You have exceeded the number of requests allowed by your %1$s plan.', td),
					'Semrush'
				),
				inOrderToUpgradeSemrush : sprintf(
					// Translators: 1 - Link to learn more.
					__('In order to continue searching for additional keyphrases, you\'ll need to upgrade. %1$s', td),
					links.getUpsellLink('semrush-pricing', GLOBAL_STRINGS.learnMore, 'semrushPricing', true)
				),
				maxAmountReached : sprintf(
					// Translators: 1 - Number of maximum keywords.
					__('You have reached the maximum of %1$s additional keyphrases.', td),
					this.postEditorStore.currentPost.maxAdditionalKeyphrases
				)
			}
		}
	},
	watch : {
		semrushCountry : {
			deep : true,
			handler () {
				this.settingsStore.changeSemrushCountry(this.semrushCountry)
				this.getKeyphrases()
			}
		}
	},
	computed : {
		semrushError () {
			if (this.semrushStore.error.includes('TOTAL LIMIT EXCEEDED')) {
				return __('You have exceeded the limit for requests. Please try again later.', td)
			}

			return __('An error occurred while fetching keyphrases. Please try again later.', td)
		}
	},
	methods : {
		semrushDatabase () {
			const list = JSON.parse(JSON.stringify(COUNTRY_LIST))

			return list
				.map(country => {
					if ('GB' === country.value) {
						country.value = 'UK'
						country.label = 'United Kingdom'
					}

					if ('KR' === country.value) {
						country.label = 'South Korea'
					}

					return country
				})
				.filter(country => SEMRUSH_DATABASE.includes(country.value.toLowerCase()))
				.map(country => {
					country.label = country.label + ' - ' + country.value.toUpperCase()
					return country
				})
		},
		getAdditionalKeyphrases () {
			this.showSemrushTooltip = false
			if (!this.connectStore.isConnected) {
				this.openConnectPopup(this.rootStore.aioseo.urls.connect + '&semrush=true')
				return
			}

			if (!this.semrushStore.hasValidTokens) {
				this.openPopup('https://oauth.semrush.com/auth/login?client_id=aioseo&redirect_uri=https%3A%2F%2Foauth.semrush.com%2Foauth2%2Faioseo%2Fsuccess&ref=2190331110&response_type=code&scope=user.id')
				return
			}

			this.openModal()
		},
		transformTrendDataToChartPoints (trend) {
			const trendArray = trend.split(',')

			return trendArray.map((value, index) => ({ x: index, y: parseFloat(value) }))
		},
		openConnectPopup (url) {
			popup(
				url,
				this.connectWithAioseo,
				600,
				630,
				true,
				[ 'token' ],
				this.completedConnectCallback,
				this.closedConnectCallback
			)
		},
		openPopup (url) {
			popup(
				url,
				'Semrush Oauth',
				450,
				570,
				true,
				[ 'code' ],
				this.completedCallback,
				this.closedCallback,
				this.postMessageCallback
			)
		},
		async completedCallback (payload) {
			return this.semrushStore.authenticate(payload.code)
		},
		completedConnectCallback (payload) {
			return this.connectStore.saveConnectToken(payload.token)
		},
		openModal () {
			this.semrushShowModal = true
			if (this.semrushStore.error) {
				return
			}

			this.getKeyphrases()
		},
		getKeyphrases () {
			if (!this.postEditorStore.currentPost?.keyphrases?.focus?.keyphrase) {
				return
			}

			this.loadingResults = true
			this.semrushStore.getKeyphrases(this.semrushCountry.value)
				.then(() => {
					this.loadingResults = false
				})
				.catch((error) => {
					this.semrushShowModal = false
					this.loadingResults   = false
					console.error(error.message)
				})
		},
		closedCallback (reload) {
			if (reload) {
				this.openModal()
			}
		},
		closedConnectCallback (reload) {
			if (!reload) {
				return
			}

			if (this.semrushStore.hasValidTokens) {
				this.$nextTick(this.getAdditionalKeyphrases)
				return
			}

			// We can't trigger the additional keyphrases popup if they have not connected to Semrush, or the browser will block the popup.
			this.showSemrushTooltip = true
		},
		async postMessageCallback (event, popupWindow, triggerPostMessageCallback) {
			const { data, source, origin } = event

			// Check that the message comes from the expected origin.
			if ('https://oauth.semrush.com' !== origin || popupWindow !== source) {
				return
			}

			if ('semrush:oauth:success' === data.type) {
				// Stop listening to messages, since the popup is closed.
				window.removeEventListener('message', triggerPostMessageCallback, false)

				let params = {}
				try {
					const url = new URL(data.url)
					params = getParams(url.search)
				} catch (e) {}

				this.completedCallback(params)
					.then(() => {
						popupWindow.close()
						popupWindow = null
						this.closedCallback(true)
					})
			}

			if ('semrush:oauth:denied' === data.type) {
				popupWindow.close()
				// Stop listening to messages, since the popup is closed.
				window.removeEventListener('message', triggerPostMessageCallback, false)
				popupWindow = null
				this.closedCallback()
			}
		},
		onSaved (payload) {
			const { value }   = payload
			this.postEditorStore.currentPost.keyphrases.focus.keyphrase = value
			this.postEditorStore.currentPost.loading.focus = true

			this.postEditorStore.isDirty = true
			this.truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
		},
		onDeleted () {
			this.postEditorStore.currentPost.keyphrases.focus.keyphrase = ''
			this.postEditorStore.isDirty                = true
			this.truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
		},
		addKeyphraseEv () {
			const keyphraseInputComponent = document.getElementsByClassName(`add-focus-keyphrase-${this.$root.$data.screenContext}-input`)
			const keyphraseInput          = keyphraseInputComponent[0].querySelector('.medium')
			const keyphraseInputValue     = keyphraseInput?.value.trim()
			if (keyphraseInputValue) {
				this.postEditorStore.currentPost.keyphrases.focus = {
					keyphrase : keyphraseInputValue,
					score     : 0,
					analysis  : {}
				}
				this.postEditorStore.currentPost.loading.focus = true

				keyphraseInput.value = ''
				keyphraseInput.blur()

				this.postEditorStore.isDirty = true
				this.truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
			}
		},
		hasAdditionalKeyphrase (keyphrase) {
			const { additional } = this.postEditorStore.currentPost.keyphrases
			return additional.filter(k => k.keyphrase.toLowerCase() === keyphrase).length
		},
		getAdditionalKeyphrase (keyphrase) {
			const { additional } = this.postEditorStore.currentPost.keyphrases
			return additional.find(k => k.keyphrase.toLowerCase() === keyphrase)
		},
		scoreClass (score) {
			return 79 < score ? 'score-green' : (49 < score ? 'score-orange' : (0 < score ? 'score-red' : 'score-none'))
		},
		async addAdditionalKeyphrase (keyphrase, index) {
			if (this.postEditorStore.currentPost.maxAdditionalKeyphrases <= this.postEditorStore.currentPost.keyphrases?.additional?.length) {
				return
			}

			this.addingAdditionalKeyphrase = index
			const { additional }           = this.postEditorStore.currentPost.keyphrases
			const keyphraseIndex           = additional.push({ keyphrase, score: 0 })
			const keyphrasePanel           = document.getElementsByClassName('keyphrase-name')
			this.postEditorStore.currentPost.keyphrases.additional = additional

			this.postEditorStore.isDirty = true
			await this.truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
			await this.$nextTick()

			if (keyphrasePanel[keyphraseIndex]) {
				keyphrasePanel[keyphraseIndex].click()
			}

			this.addingAdditionalKeyphrase = false
		},
		goToAdditionalKeyphrase (keyphrase) {
			const { additional }             = this.postEditorStore.currentPost.keyphrases
			const keyphraseIndex             = additional.findIndex(k => k.keyphrase.toLowerCase() === keyphrase)
			if (-1 !== keyphraseIndex) {
				const keyphrasePanel = document.getElementsByClassName('keyphrase-name')
				if (keyphrasePanel[keyphraseIndex + 1]) {
					keyphrasePanel[keyphraseIndex + 1].click()
				}
				this.semrushShowModal = false
			}
		},
		removeAdditionalKeyphrase (keyphrase, index) {
			this.removingAdditionalKeyphrase = index
			const { additional }             = this.postEditorStore.currentPost.keyphrases
			const keyphraseIndex             = additional.findIndex(k => k.keyphrase.toLowerCase() === keyphrase)
			if (-1 !== keyphraseIndex) {
				additional.splice(keyphraseIndex, 1)
				this.postEditorStore.currentPost.keyphrases.additional = additional
				const keyphrasePanel = document.getElementsByClassName('keyphrase-name')
				if (keyphrasePanel[0]) {
					keyphrasePanel[0].click()
				}
			}

			this.$nextTick(() => {
				this.removingAdditionalKeyphrase = false
			})
		},
		toggleDescriptionEv (ev) {
			ev.target.parentElement.classList.toggle('toggled')
		},
		pressEnter (event) {
			const addButon = document.getElementById('add-focus-keyphrase')
			event.preventDefault()
			addButon.click()
		}
	},
	mounted () {
		const promises = []
		if (this.optionsStore.internalOptions.integrations.semrush.accessToken && this.semrushStore.expired) {
			promises.push(this.semrushStore.refresh())
		}

		this.semrushCountry = {
			value : this.settingsStore.settings.semrushCountry,
			label : this.semrushDatabase().find(country => country.value === this.settingsStore.settings.semrushCountry)?.label
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

	// These styles apply to a modal that needs to isolate from the main app.
	&-modal {
		.modal-body {
			max-height: calc(90vh - 70px);
			overflow: auto;

			.aioseo-modal-content {
				.aioseo-alert {
					margin-bottom: 20px;
				}
			}
		}

		.semrush-country-selector {
			max-width: 350px;
		}

		.additional-keyphrases-table {
			width: 100%;
			border: 1px solid $input-border;
			border-radius: 3px;

			.keyphrase-volume,
			.keyphrase-trend {
				text-align: center;
			}

			.keyphrase-actions {
				> div {
					display: flex;
					align-items: center;
					justify-content: flex-end;
				}

				.focus-keyphrase {
					display: flex;
					align-items: center;
					justify-content: flex-end;
					color: $green;
					min-width: 135px;

					svg {
						margin-right: 5px;
						width: 16px;
						height: 16px;
						color: $green;
					}
				}

				.keyphrase-score {
					border-radius: 3px;
					padding: 5px;
					font-weight: 700;
					font-size: 13px;
					cursor: pointer;
					border: 1px solid $blue;

					&.score-green {
						color: $green;
						border-color: $green;
					}

					&.score-orange {
						color: $orange;
						border-color: $orange;
					}

					&.score-red {
						color: $red;
						border-color: $red;
					}

					&:hover {
						background-color: $blue;
						color: #fff;

						&.score-green {
							background-color: $green;
						}

						&.score-orange {
							background-color: $orange;
						}

						&.score-red {
							background-color: $red;
						}
					}
				}

				.remove-keyphrase {
					display: flex;
					align-items: center;
					justify-content: flex-end;

					svg {
						width: 16px;
						height: 16px;
						cursor: pointer;

						&:hover {
							color: $red;
						}
					}
				}
			}

			.keyphrases-header {
				height: 50px;
				font-size: 14px;

				th {
					border-bottom: 1px solid $input-border;
					padding: 15px;

					&:first-of-type {
						padding-left: 30px;
					}

					&:last-of-type {
						padding-right: 30px;
					}
				}
			}

			.keyphrases-rows {
				font-size: 14px;

				tr.keyphrase-row {
					background-color: #fff;
					height: 70px;

					&:last-of-type {
						td {
							&:first-of-type {
								border-radius: 0 0 0 3px;
							}

							&:last-of-type {
								border-radius: 0 0 3px 0;
							}
						}
					}

					&.even {
						background-color: $box-background;
					}

					td {
						padding: 15px;

						&:first-of-type {
							padding-left: 30px;
						}

						&:last-of-type {
							padding-right: 30px;
						}

						&.no-results {
							> * {
								display: flex;
								align-items: center;
								justify-content: center;
							}

							.semrush-logo {
								padding: 0 30px 0 0;
								min-width: 150px;
							}
						}
					}

					&.loading {
						display: flex;
						align-items: center;
						justify-content: center;
					}
				}
			}
		}
	}
}
</style>