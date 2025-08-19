<template>
	<div class="aioseo-seo-audit-checklist">
		<core-card
			slug="connectOrScore"
			no-slide
			:toggles="false"
		>
			<template #header>
				<span>{{ strings.siteOverview }}</span>
			</template>

			<div v-if="analyzerStore.issuesResults.isLoading" class="aioseo-seo-audit-checklist__loader">
				<core-loader dark />
			</div>

			<core-donut-chart-with-legend
				v-else
				:parts="sortedParts"
				:total="parseInt(analyzerStore.issueResultsTotalCounts)"
				:label="strings.totalPostsLabel"
				:loading="analyzerStore.issuesResults.isLoading"
				:loading-text="strings.loadingText"
				:animatedNumber="false"
			/>
		</core-card>

		<core-card
			v-if="isProAndLicensed || optionsStore.internalOptions.internal.siteAnalysis.connectToken"
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

			<site-audit-global-tab />
		</core-card>
	</div>
</template>

<script setup>
import { computed, onBeforeMount, onMounted } from 'vue'
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
import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SiteAuditGlobalTab from '@/vue/pages/seo-analysis/partials/AIOSEO_VERSION/tabs/SiteAuditGlobalTab'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { getSortedParts } from '@/vue/pages/seo-analysis/utils'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const analyzerStore = useAnalyzerStore()
const licenseStore  = useLicenseStore()
const optionsStore  = useOptionsStore()
const rootStore     = useRootStore()
const settingsStore = useSettingsStore()

const strings = {
	completeSeoChecklist : __('Site Audit Report', td),
	cardDescription      : __('These are the results our SEO Analzyer has generated after analyzing the pages of your website.', td) +
			' ' + links.getDocLink(GLOBAL_STRINGS.learnMore, 'seoAnalyzer', true),
	siteOverview    : __('Site Overview', td),
	totalPostsLabel : __('Total Checks', td),
	loadingText     : __('Analyzing...', td)
}

const isProAndLicensed = computed(() => {
	return rootStore.isPro && (!licenseStore.isUnlicensed || rootStore.aioseo.data.isNetworkLicensed)
})

const sortedParts = computed(() => {
	const good     = analyzerStore?.issuesResults?.counts?.passed || 0
	const warnings = analyzerStore?.issuesResults?.counts?.warning || 0
	const issues   = analyzerStore?.issuesResults?.counts?.error || 0
	const total    = analyzerStore?.issueResultsTotalCounts || 0

	const sortedParts = getSortedParts({
		good,
		warnings,
		issues,
		total
	})

	return sortedParts
})

async function fetchData () {
	if (isProAndLicensed.value) {
		await analyzerStore.fetchAllUrls({
			limit  : settingsStore.settings.tablePagination.seoAnalysis,
			offset : 0
		})
		await analyzerStore.fetchSitePagesAnalysisResults()
	}
}

onMounted(async () => {
	analyzerStore.issuesResults.isLoading = true

	window.aioseoBus.$on('changes-saved', async () => {
		await fetchData()
	})
})

onBeforeMount(async () => {
	await fetchData()
})
</script>

<style lang="scss">
.aioseo-seo-audit-checklist {
	&__loader {
		width: 100%;
		min-height: 200px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

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

		&.dark-blue {
			background-color: $blue3;
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

		&.gray {
			background-color: $placeholder-color;
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

	.aioseo-donut-chart-with-legend {
		justify-content: center;

		.chart-right {
			flex: unset;
		}

		.legend-label {
			cursor: pointer;
			text-decoration: none;
			color: $blue;

			&:hover {
				text-decoration: underline;
			}
		}
	}
}
</style>