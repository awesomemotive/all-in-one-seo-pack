<template>
	<div>
		<template v-if="searchStatisticsStore.shouldShowSampleReports">
			<div
				class="keyword-rank-tracker-container"
			>
				<div>
					<span>{{ strings.withAioseo }}</span> <span v-html="learnMoreLink"/>
				</div>

				<div class="keyword-rank-tracker-container__card">
					<div class="keyword-rank-tracker-container__card__header">
						{{ strings.keywordPositions }}
					</div>

					<div class="keyword-rank-tracker-container__card__body">
						<graph
							:height="320"
							:series="positionSeries"
							:loading="false"
							legend-style="simple"
							:chart-overrides="{
							tooltip: {
								y : {
									formatter : (value) => parseFloat(value).toFixed(2)
								}
							}
						}"
						/>
					</div>
				</div>

				<keywords-table :keywords="parsedKeywords"/>
			</div>

			<template v-if="!searchStatisticsStore.shouldShowSampleReports">
				<core-blur class="keyword-rank-tracker-container">
					<div>
						<span>{{ strings.withAioseo }}</span> <span v-html="learnMoreLink"/>
					</div>

					<div class="keyword-rank-tracker-container__card">
						<div class="keyword-rank-tracker-container__card__header">
							{{ strings.keywordPositions }}
						</div>

						<div class="keyword-rank-tracker-container__card__body">
							<graph
								:height="320"
								:series="positionSeries"
								:loading="false"
								legend-style="simple"
								:chart-overrides="{
							tooltip: {
								y : {
									formatter : (value) => parseFloat(value).toFixed(2)
								}
							}
						}"
							/>
						</div>
					</div>

					<keywords-table :keywords="parsedKeywords"/>
				</core-blur>

				<connect-cta/>
			</template>
		</template>

		<template v-else>
			<core-blur
				class="keyword-rank-tracker-container"
				v-if="!searchStatisticsStore.shouldShowSampleReports"
			>
				<div>
					<span>{{ strings.withAioseo }}</span> <span v-html="learnMoreLink"/>
				</div>

				<div class="keyword-rank-tracker-container__card">
					<div class="keyword-rank-tracker-container__card__header">
						{{ strings.keywordPositions }}
					</div>

					<div class="keyword-rank-tracker-container__card__body">
						<graph
							:height="320"
							:series="positionSeries"
							:loading="false"
							legend-style="simple"
							:chart-overrides="{
							tooltip: {
								y : {
									formatter : (value) => parseFloat(value).toFixed(2)
								}
							}
						}"
						/>
					</div>
				</div>

				<keywords-table :keywords="parsedKeywords"/>
			</core-blur>

			<cta
				v-if="!searchStatisticsStore.shouldShowSampleReports"
				cta-second-button-action
				@cta-second-button-click="searchStatisticsStore.showSampleReports"
				:cta-link="$links.getPricingUrl('search-statistics', 'search-statistics-upsell', 'keyword-rank-tracker')"
				:button-text="strings.ctaButtonText"
				:second-button-text="strings.ctaSecondButtonText"
				cta-second-button-new-badge
				cta-second-button-visible
				:learn-more-link="$links.getUpsellUrl('search-statistics', 'keyword-rank-tracker', $isPro ? 'pricing' : 'liteUpgrade')"
				:feature-list="[
					strings.feature1,
					strings.feature2,
					strings.feature3,
					strings.feature4
				]"
				align-top
				:hide-bonus="!licenseStore.isUnlicensed"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<required-plans :core-feature="[ 'search-statistics' ]"/>

					{{ strings.ctaDescription }}
				</template>
			</cta>
		</template>
	</div>
</template>

<script setup>
import { computed, inject } from 'vue'

import {
	useKeywordRankTrackerStore,
	useLicenseStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import { useCta } from '@/vue/pages/search-statistics/composables/Cta'
import ConnectCta from '@/vue/pages/search-statistics/views/partials/pro/ConnectCta'
import CoreBlur from '@/vue/components/common/core/Blur'
import Cta from '@/vue/components/common/cta/Index'
import Graph from '@/vue/pages/search-statistics/views/partials/Graph'
import KeywordsTable from '../partials/keyword-rank-tracker/KeywordsTable'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import license from '@/vue/utils/license.js'

const $t = inject('$t')
const $td = inject('$td')
const $links = inject('$links')

const keywordRankTrackerStore = useKeywordRankTrackerStore()
const licenseStore = useLicenseStore()
const searchStatisticsStore = useSearchStatisticsStore()

const learnMoreLink = $links.getDocLink($t.__('Learn More', $td), 'keywordRankTracker', true)
const strings = {
	...useCta().strings,
	withAioseo       : $t.__('Below you can track how your page is performing in search results based on your keyword(s).', $td),
	keywordPositions : $t.__('Keyword Positions', $td)
}

const showConnectCta = computed(() => {
	return ((license.hasCoreFeature('search-statistics') && !searchStatisticsStore.isConnected) || searchStatisticsStore.unverifiedSite)
})
const parsedKeywords = computed(() => {
	return {
		rows : keywordRankTrackerStore.keywords.all.rows.slice(0, 3).map((row) => {
			return {
				...row,
				tracking : true
			}
		})
	}
})
const positionSeries = computed(() => {
	const keywordsByName = {}
	for (const keyword of parsedKeywords.value.rows) {
		if (keyword?.statistics?.history) {
			keywordsByName[keyword.name] = keyword
		}
	}

	const series = []
	for (const name in keywordsByName) {
		series.push({
			name : name,
			data : keywordsByName[name].statistics.history.map((item) => ({ x: item.date, y: item.position }))
		})
	}

	return series
})
</script>

<style lang="scss" scoped>
.keyword-rank-tracker-container {
	display: flex;
	flex-direction: column;
	gap: 20px;

	&__card {
		border: 1px solid $input-border;
		padding: 0 0 18px;

		&__header {
			align-items: center;
			background-color: $background;
			display: flex;
			font-size: 16px;
			font-weight: 600;
			line-height: normal;
			padding: 14px 16px;
		}
	}
}
</style>