<template>
	<div class="aioseo-seo-audit-checklist">
		<core-card
			slug="connectOrScore"
			hide-header
			no-slide
			:toggles="false"
		>
			<core-seo-site-score-analyze />
		</core-card>

		<core-card
			v-if="((rootStore.isPro && licenseStore.licenseKey) || optionsStore.internalOptions.internal.siteAnalysis.connectToken) && homeResults?.score"
			slug="completeSeoChecklist"
			no-slide
			:toggles="false"
		>
			<template #header>
				<span>{{ strings.completeSeoChecklist }}</span>

				<core-tooltip>
					<svg-circle-question-mark />

					<template #tooltip>
						<span v-html="strings.cardDescription"/>
					</template>
				</core-tooltip>
			</template>

			<template #header-extra>
				<base-button
					class="refresh-results"
					type="gray"
					size="small"
					@click="refresh"
					:loading="analyzerStore.analyzing"
				>
					<svg-refresh />
					{{ strings.refreshResults }}
				</base-button>
			</template>

			<template #tabs>
				<core-main-tabs
					:tabs="tabs"
					:showSaveButton="false"
					:active="settingsStore.settings.internalTabs.seoAuditChecklist"
					internal
					@changed="processChangeTab"
					skinny-tabs
				>
					<template #var-tab="{ tab }">
						<span
							class="round"
							:class="tab.analyze.classColor"
						>
							{{ tab.analyze.count || 0 }}
						</span>

						<span class="label">{{ tab.label }}</span>
					</template>
				</core-main-tabs>
			</template>

			<core-seo-site-analysis-results
				v-if="null !== homeResults?.results"
				:section="settingsStore.settings.internalTabs.seoAuditChecklist"
				:all-results="homeResults.results"
				show-instructions
			/>
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useAnalyzerStore,
	useLicenseStore,
	useOptionsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreSeoSiteAnalysisResults from '@/vue/components/common/core/SeoSiteAnalysisResults'
import CoreSeoSiteScoreAnalyze from '@/vue/components/AIOSEO_VERSION/core/seo-site-score/Analyze'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgRefresh from '@/vue/components/common/svg/Refresh'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			analyzerStore : useAnalyzerStore(),
			licenseStore  : useLicenseStore(),
			optionsStore  : useOptionsStore(),
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore()
		}
	},
	components : {
		CoreCard,
		CoreMainTabs,
		CoreSeoSiteAnalysisResults,
		CoreSeoSiteScoreAnalyze,
		CoreTooltip,
		SvgCircleQuestionMark,
		SvgRefresh
	},
	data () {
		return {
			internalDebounce : false,
			homeResults      : {
				results : [],
				score   : null
			},
			strings : {
				completeSeoChecklist : __('Complete SEO Checklist', td),
				refreshResults       : __('Refresh Results', td),
				cardDescription      : __('These are the results our SEO Analzyer has generated after analyzing the homepage of your website.', td) +
					' ' + links.getDocLink(GLOBAL_STRINGS.learnMore, 'seoAnalyzer', true)
			}
		}
	},
	computed : {
		tabs () {
			const score = this.homeResults?.score
			return [
				{
					slug    : 'all-items',
					label   : __('All Items', td),
					analyze : {
						classColor : 'black',
						count      : score
							? this.analyzerStore.allItemsCount()
							: 0
					}
				},
				{
					slug    : 'critical',
					label   : __('Important Issues', td),
					analyze : {
						classColor : 'red',
						count      : score
							? this.analyzerStore.criticalCount()
							: 0
					}
				},
				{
					slug    : 'recommended-improvements',
					label   : __('Recommended Improvements', td),
					analyze : {
						classColor : 'blue',
						count      : score
							? this.analyzerStore.recommendedCount()
							: 0
					}
				},
				{
					slug    : 'good-results',
					label   : __('Good Results', td),
					analyze : {
						classColor : 'green',
						count      : score
							? this.analyzerStore.goodCount()
							: 0
					}
				}
			]
		}
	},
	methods : {
		processChangeTab (value) {
			if (this.internalDebounce) {
				return
			}

			this.internalDebounce = true
			this.settingsStore.changeTab({ slug: 'seoAuditChecklist', value })

			// Debouncing a little here to save extra API calls.
			setTimeout(() => {
				this.internalDebounce = false
			}, 50)
		},
		async refresh () {
			this.analyzerStore.analyzing = true
			await this.analyzerStore.runSiteAnalyzer({
				refresh : true
			})

			this.analyzerStore.getSiteAnalysisResults().then(() => {
				this.homeResults = this.analyzerStore.homeResults
				this.analyzerStore.analyzing = false
			})
		}
	},
	mounted () {
		this.homeResults = this.analyzerStore.homeResults
	}
}
</script>

<style lang="scss">
.aioseo-seo-audit-checklist {
	.round {
		position: relative;
		border-radius: 50%;
		width: 24px;
		min-width: 24px;
		max-width: 24px;
		height: 24px;
		line-height: 24px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin-right: 10px;
		font-size: 12px;
		color: #fff;
		font-weight: 600;

		&.red {
			background-color: $red;
		}

		&.blue {
			background-color: $blue;
		}

		&.orange {
			background-color: $orange;
		}

		&.green {
			background-color: $green;
		}

		&.black {
			background-color: $black2;
		}
	}

	.aioseo-card {
		.header-title {
			display: inline-flex;
			flex: 1;
		}

		.header-extra {
			.refresh-results {
				margin-right: 10px;

				.aioseo-refresh {
					width: 14px;
					height: 14px;
					margin-right: 10px;
				}
			}
		}
	}
}
</style>