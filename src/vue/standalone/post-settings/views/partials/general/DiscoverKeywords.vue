<template>
	<div
		v-if="keywords.length"
		class="aioseo-discover-keywords"
	>
		<template v-if="isAdditionalKeywordsAvailable">
			<svg-chart-growth
				class="aioseo-discover-keywords__lead"
				width="18"
				height="18"
			/>

			<span class="aioseo-discover-keywords__msg">
				{{ strings.discoverMsg }}

				<a
					href="#"
					class="aioseo-discover-keywords__link"
					@click.prevent="getAdditionalKeyphrases"
				>
					{{ strings.discoverLink }}

					<svg
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						stroke-width="2.4"
						stroke-linecap="round"
						stroke-linejoin="round"
					>
						<path d="M5 12h14M13 6l6 6-6 6" />
					</svg>
				</a>
			</span>
		</template>

		<template v-else>
			<span class="aioseo-discover-keywords__lead aioseo-discover-keywords__lead--lock">
				<svg
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2"
					stroke-linecap="round"
					stroke-linejoin="round"
				>
					<rect
						x="4"
						y="10"
						width="16"
						height="10"
						rx="2"
					/>
					<path d="M8 10V7a4 4 0 0 1 8 0v3" />
				</svg>
			</span>

			<span
				class="aioseo-discover-keywords__msg aioseo-discover-keywords__msg--upsell"
				v-safe-html="strings.liteUpsell"
			/>
		</template>
	</div>

	<!-- Semrush Modal -->
	<core-modal
		:show="semrushShowModal"
		@close="semrushShowModal = false"
		:classes="[ 'aioseo-keywords-table-semrush-modal' ]"
	>
		<template #headerTitle>
			{{ strings.modalTitle }}
		</template>

		<template #body>
			<div class="aioseo-modal-content has-padding">
				<core-alert
					v-if="licenseStore.isUnlicensed"
					type="blue"
					v-safe-html="strings.upsell"
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
						aria-label="Additional Keywords"
						class="additional-keyphrases-table"
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
											v-if="isFocusKeyword(keyphrase[0])"
											class="focus-keyphrase"
										>
											<svg-circle-check />
											{{ strings.focusKeyphrase }}
										</div>
										<div
											v-if="!isFocusKeyword(keyphrase[0])"
										>
											<base-button
												v-if="index !== removingAdditionalKeyphrase && (index === addingAdditionalKeyphrase || !hasAdditionalKeyphrase(keyphrase[0])) && !additionalKeywordLimitReached(postEditorStore.currentPost.maxAdditionalKeyphrases, postEditorStore.truseoData?.additionalKeywords?.length || 0)"
												type="gray"
												size="medium"
												@click="addAdditionalKeyphrase(keyphrase[0], index)"
												:loading="index === addingAdditionalKeyphrase"
											>
												{{ strings.addAdditionalKeyphrase }}
											</base-button>

											<core-tooltip v-if="index !== removingAdditionalKeyphrase && (index === addingAdditionalKeyphrase || !hasAdditionalKeyphrase(keyphrase[0])) && additionalKeywordLimitReached(postEditorStore.currentPost.maxAdditionalKeyphrases, postEditorStore.truseoData?.additionalKeywords?.length || 0)">
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
													@click="goToKeyword(keyphrase[0])"
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
														<strong v-safe-html="strings.youHaveExceededSemrush" />
													</span>

													{{ ' ' }}

													<span v-safe-html="strings.inOrderToUpgradeSemrush" />
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
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'

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
import { getTruSeoInstance } from '@/vue/plugins/tru-seo/TruSeoSingleton'
import { useKeywords } from '@/vue/standalone/post-settings/composables/Keywords'
import { updateStoreWithResults } from '@/vue/plugins/tru-seo/helpers/resultsHelper'

import BaseButton from '@/vue/components/common/base/Button'
import BaseSelect from '@/vue/components/common/base/Select'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgAreaChart from '@/vue/components/common/svg/AreaChart'
import SvgChartGrowth from '@/vue/components/common/svg/ChartGrowth'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgLogoSemrush from '@/vue/components/common/svg/logo/Semrush'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __, sprintf } from '@/vue/plugins/translations'
import { additionalKeywordLimitReached } from '@/vue/utils/postData/helpers'

const td = import.meta.env.VITE_TEXTDOMAIN

// Lets the host list scroll/expand the keyword the user clicked in the modal.
const emit = defineEmits([ 'navigate' ])

const connectStore = useConnectStore()
const licenseStore = useLicenseStore()
const optionsStore = useOptionsStore()
const postEditorStore = usePostEditorStore()
const rootStore = useRootStore()
const semrushStore = useSemrushStore()
const settingsStore = useSettingsStore()

const truSeo = { value: null }
const keywordsComposable = useKeywords(truSeo)

const semrushShowModal = ref(false)
const loadingResults = ref(false)
const semrushCountry = ref(null)
const showSemrushTooltip = ref(false)
const addingAdditionalKeyphrase = ref(false)
const removingAdditionalKeyphrase = ref(false)

const strings = {
	discoverMsg  : __('Not sure what to target? Find related keywords you can rank for.', td),
	discoverLink : __('Discover additional keywords', td),
	// getUpsellLink appends the arrow, so the link text must not carry one of its own.
	liteUpsell   : sprintf(
		// Translators: 1 - Link reading "upgrading to AIOSEO Pro".
		__('You\'ve run out of keywords. Unlock unlimited keywords and many more features by %1$s', td),
		links.getUpsellLink('post-settings', 'keywords', __('upgrading to AIOSEO Pro', td), 'liteUpgrade', true)
	),
	delete           : __('Delete', td),
	maxAmountReached : sprintf(
		// Translators: 1 - Number of maximum keywords.
		__('You have reached the maximum of %1$s additional keywords.', td),
		postEditorStore.currentPost.maxAdditionalKeyphrases
	),
	modalTitle : sprintf(
		// Translators: 1 - Semrush.
		__('Additional Keywords by %1$s', td),
		'Semrush'
	),
	showResultsFor         : __('Show Results For:', td),
	keyphrase              : __('Keyword', td),
	volume                 : __('Volume', td),
	trend                  : __('Trend', td),
	addAdditionalKeyphrase : __('Add Keyword', td),
	noResults              : __('No results', td),
	focusKeyphrase         : __('Focus Keyword', td),
	upsell                 : sprintf(
		// Translators: 1 - Plugin short name + Pro "AIOSEO Pro", 2 - Semrush, 3 - Link to learn more.
		__('Analyzing your content with %1$s keywords is only available to licensed %2$s users. %3$s', td),
		'Semrush',
		`<strong>${import.meta.env.VITE_SHORT_NAME} Pro</strong>`,
		links.getUpsellLink('post-settings', 'semrush-keywords', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
	),
	youHaveExceededSemrush : sprintf(
		// Translators: 1 - Semrush.
		__('You have exceeded the number of requests allowed by your %1$s plan.', td),
		'Semrush'
	),
	inOrderToUpgradeSemrush : sprintf(
		// Translators: 1 - Link to learn more.
		__('In order to continue searching for additional keywords, you\'ll need to upgrade. %1$s', td),
		links.getUpsellLink('post-settings', 'semrush-pricing', GLOBAL_STRINGS.learnMore, 'semrushPricing', true)
	),
	selectPriceIndicator : __('Select a country', td)
}

const keywords = computed(() => keywordsComposable.keywords.value)

const isAdditionalKeywordsAvailable = computed(() => keywordsComposable.isAdditionalKeywordsAvailable.value)

watch(semrushCountry, () => {
	// The country selector lives inside the modal, so skip the initial mount assignment —
	// otherwise we'd persist the country and hit Semrush before the user opts in.
	if (!semrushCountry.value || !semrushShowModal.value) {
		return
	}
	settingsStore.changeSemrushCountry(semrushCountry.value)
	getKeyphrases()
}, { deep: true })

const semrushError = computed(() => {
	if (semrushStore.error?.includes('TOTAL LIMIT EXCEEDED')) {
		return __('You have exceeded the limit for requests. Please try again later.', td)
	}

	return semrushStore?.error || __('An error occurred while fetching keywords. Please try again later.', td)
})

const scoreClass = (score) => {
	if (79 < score) return 'score-green'
	if (49 < score) return 'score-orange'
	if (0 < score) return 'score-red'

	return 'score-none'
}

const semrushDatabase = () => {
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
}

const getAdditionalKeyphrases = () => {
	showSemrushTooltip.value = false
	if (!connectStore.isConnected) {
		openConnectPopup(rootStore.aioseo.urls.connect + '&semrush=true')
		return
	}

	if (!semrushStore.hasValidTokens) {
		openPopup('https://oauth.semrush.com/auth/login?client_id=aioseo&redirect_uri=https%3A%2F%2Foauth.semrush.com%2Foauth2%2Faioseo%2Fsuccess&ref=2190331110&response_type=code&scope=user.id')
		return
	}

	openModal()
}

const transformTrendDataToChartPoints = (trend) => {
	const trendArray = trend.split(',')

	return trendArray.map((value, index) => ({ x: index, y: parseFloat(value) }))
}

const openConnectPopup = (url) => {
	popup(
		url,
		'Connect with AIOSEO',
		600,
		630,
		true,
		[ 'token' ],
		completedConnectCallback,
		closedConnectCallback
	)
}

const openPopup = (url) => {
	popup(
		url,
		'Semrush Oauth',
		450,
		570,
		true,
		[ 'code' ],
		completedCallback,
		closedCallback,
		postMessageCallback
	)
}

const completedCallback = async (payload) => {
	return semrushStore.authenticate(payload.code)
}

const completedConnectCallback = (payload) => {
	return connectStore.saveConnectToken(payload.token)
}

const openModal = () => {
	semrushShowModal.value = true
	if (semrushStore.error) {
		return
	}

	getKeyphrases()
}

const getKeyphrases = () => {
	// Gate on the canonical focus keyword — the exact value SemrushStore.getKeyphrases() sends, so
	// the guard and the request can't disagree. The pre-5.0 `keyphrases.focus` mirror this used to
	// read is empty for a term (there is no such column, so PHP sends a null focus) and for any post
	// whose keyword was set after the keyword columns landed, which skipped the request and left the
	// table showing "No results" with no error to explain it.
	if (!postEditorStore.truseoData?.focusKeyword) {
		return
	}

	// Semrush 400s without an active connection/token, so don't even try when disconnected.
	if (!connectStore.isConnected || !semrushStore.hasValidTokens) {
		return
	}

	loadingResults.value = true
	semrushStore.getKeyphrases(semrushCountry.value.value)
		.then(() => {
			loadingResults.value = false
		})
		.catch((error) => {
			semrushShowModal.value = false
			loadingResults.value   = false
			console.error(error.message)
		})
}

const closedCallback = (reload) => {
	if (reload) {
		openModal()
	}
}

const closedConnectCallback = (reload) => {
	if (!reload) {
		return
	}

	if (semrushStore.hasValidTokens) {
		nextTick(getAdditionalKeyphrases)
		return
	}

	showSemrushTooltip.value = true
}

const postMessageCallback = async (event, popupWindow, triggerPostMessageCallback) => {
	const { data, source, origin } = event

	if ('https://oauth.semrush.com' !== origin || popupWindow !== source) {
		return
	}

	if ('semrush:oauth:success' === data.type) {
		window.removeEventListener('message', triggerPostMessageCallback, false)

		let params = {}
		try {
			const url = new URL(data.url)
			params = getParams(url.search)
		} catch (e) {}

		completedCallback(params)
			.then(() => {
				popupWindow.close()
				popupWindow = null
				closedCallback(true)
			})
	}

	if ('semrush:oauth:denied' === data.type) {
		popupWindow.close()
		window.removeEventListener('message', triggerPostMessageCallback, false)
		popupWindow = null
		closedCallback()
	}
}

const hasAdditionalKeyphrase = (keyphrase) => {
	const additional = postEditorStore.truseoData?.additionalKeywords
	const needle     = keyphrase?.toLowerCase()
	return additional ? additional.filter(k => k.word.toLowerCase() === needle).length : 0
}

const getAdditionalKeyphrase = (keyphrase) => {
	const additional = postEditorStore.truseoData?.additionalKeywords
	const needle     = keyphrase?.toLowerCase()
	return additional ? additional.find(k => k.word.toLowerCase() === needle) : null
}

const isFocusKeyword = (keyphrase) => {
	return keyphrase?.toLowerCase() === postEditorStore.truseoData?.focusKeyword?.toLowerCase()
}

const addAdditionalKeyphrase = async (keyphrase, index) => {
	if (!isAdditionalKeywordsAvailable.value) {
		return
	}

	addingAdditionalKeyphrase.value = index
	const additionalOld = postEditorStore.currentPost.keyphrases?.additional || []
	const additional    = postEditorStore.truseoData?.additionalKeywords || []

	// Keep the old keyphrase for backward compatibility
	additionalOld.push({ keyphrase, score: 0 })

	additional.push({ word: keyphrase, score: 0 })

	// Keep the old keyphrase for backward compatibility
	postEditorStore.currentPost.keyphrases.additional = additionalOld
	postEditorStore.currentPost.additional_keywords = additional

	postEditorStore.isDirty = true

	setTimeout(async () => {
		try {
			const results = await truSeo?.value?.runAnalysis({
				postId : postEditorStore.currentPost.id
			})

			if (results) {
				updateStoreWithResults(results)
			}
		} catch (error) {
			console.error('TruSEO analysis failed:', error)
		}
	}, 300)

	await nextTick()

	addingAdditionalKeyphrase.value = false
}

const goToKeyword = (keyphrase) => {
	semrushShowModal.value = false

	emit('navigate', keyphrase)
}

const removeAdditionalKeyphrase = (keyphrase, index) => {
	removingAdditionalKeyphrase.value = index
	const additionalOld = postEditorStore.currentPost.keyphrases?.additional || []
	const additional    = postEditorStore.truseoData?.additionalKeywords || []

	const needle         = keyphrase?.toLowerCase()
	const keyphraseIndex = additional ? additional.findIndex(k => k.word.toLowerCase() === needle) : -1
	if (-1 !== keyphraseIndex) {
		// Keep the old keyphrase for backward compatibility
		additionalOld.splice(keyphraseIndex, 1)
		additional.splice(keyphraseIndex, 1)

		// Keep the old keyphrase for backward compatibility
		postEditorStore.currentPost.keyphrases.additional = additionalOld
		postEditorStore.truseoData.additionalKeywords = additional
	}

	nextTick(() => {
		removingAdditionalKeyphrase.value = false
	})
}

onMounted(async () => {
	if (optionsStore.internalOptions.integrations.semrush.accessToken && semrushStore.expired) {
		semrushStore.refresh()
	}

	truSeo.value = await getTruSeoInstance()

	semrushCountry.value = {
		value : settingsStore.settings.semrushCountry,
		label : semrushDatabase().find(country => country.value === settingsStore.settings.semrushCountry)?.label
	}
})
</script>

<style lang="scss">
// Prefixed class names throughout: this row also renders inside page-builder
// front-end panels, where generic names like .lead/.msg collide with theme CSS.
.aioseo-discover-keywords {
	display: flex;
	// Top-aligned so the icon sits on the message's first line once it wraps.
	align-items: flex-start;
	gap: 10px;

	&__lead {
		flex-shrink: 0;
		color: $blue;

		&--lock {
			display: inline-flex;
			color: $orange;

			svg {
				width: 18px;
				height: 18px;
			}
		}
	}

	&__msg {
		// Flex chooses lines by max-content width, so without shrinking the whole
		// message drops under the icon in narrow panels instead of wrapping itself.
		flex: 1 1 auto;
		min-width: 0;
		font-size: 12.5px;
		color: #64676e;
	}

	// Scoped to the upsell link so it doesn't also bold the discover link, which
	// now sits inside __msg to flow inline after the sentence.
	&__msg--upsell a {
		color: $blue;
		font-weight: 700;
		text-decoration: none;
		white-space: nowrap;

		&:hover {
			text-decoration: underline;
		}
	}

	&__link {
		font-size: 13px;
		font-weight: 400;
		color: $blue;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		gap: 6px;
		white-space: nowrap;

		&:hover {
			color: $blue;
			text-decoration: underline;
		}

		svg {
			width: 14px;
			height: 14px;
			flex-shrink: 0;
		}
	}
}

// Semrush Modal Styles
.aioseo-keywords-table-semrush-modal {
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
							display: block;
							width: 100%;
							padding-right: 20px;

							.aioseo-logo-semrush {
								display: block;
								max-width: 300px;
								width: 100%;
							}
						}
					}
				}
			}
		}
	}
}
</style>