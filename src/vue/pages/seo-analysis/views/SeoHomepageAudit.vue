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
			v-if="(isLicensed || optionsStore.internalOptions.internal.siteAnalysis.connectToken) && !analyzerStore.analyzeError && analyzerStore.homeResults.score && !analyzerStore.analyzing"
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
					:loading="isRefreshing"
				>
					<svg-refresh />
					{{ strings.refreshResults }}
				</base-button>
			</template>

			<homepage-audit-tab-list />
		</core-card>
	</div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'

import {
	useAnalyzerStore,
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import CoreCard from '@/vue/components/common/core/Card'
import CoreSeoSiteScoreAnalyze from '@/vue/components/AIOSEO_VERSION/core/seo-site-score/Analyze'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import HomepageAuditTabList from '@/vue/pages/seo-analysis/partials/tabs/HomepageAuditTabList'
import SvgRefresh from '@/vue/components/common/svg/Refresh'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const analyzerStore = useAnalyzerStore()
const licenseStore  = useLicenseStore()
const optionsStore  = useOptionsStore()
const rootStore     = useRootStore()

const isRefreshing = ref(false)

const strings = {
	completeSeoChecklist : __('Complete SEO Checklist', td),
	refreshResults       : __('Refresh Results', td),
	cardDescription      : __('These are the results our SEO Analzyer has generated after analyzing the homepage of your website.', td) +
			' ' + links.getDocLink(GLOBAL_STRINGS.learnMore, 'seoAnalyzer', true)
}

const isLicensed = computed(() => {
	return !licenseStore.isUnlicensed || rootStore.aioseo.data.isNetworkLicensed
})

async function refresh () {
	isRefreshing.value = true
	await analyzerStore.runSiteAnalyzer({
		refresh : true
	})
	isRefreshing.value = false
}
</script>

<style lang="scss">
.aioseo-seo-audit-checklist {
	.content > .aioseo-seo-site-score {
		display: flex;
		justify-content: center;
		min-height: 440px;
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
}
</style>