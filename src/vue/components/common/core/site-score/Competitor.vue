<template>
	<div class="aioseo-site-score-competitor">
		<div
			class="aioseo-seo-site-score-score"
		>
			<core-donut-chart-with-legend
				:parts="sortedParts"
				:total="parseInt(score)"
				:label="description"
				maxTotal="100"
				:loading="isAnalyzing || loading"
				:loadingText="strings.analyzing"
			/>
		</div>

		<base-button
			class="refresh-results"
			type="gray"
			size="small"
			@click="refresh"
			:loading="isAnalyzing"
		>
			<svg-refresh />
			{{ strings.refreshResults }}
		</base-button>

		<div
			v-if="mobileSnapshot"
			class="mobile-snapshot"
		>
			<div>{{ strings.mobileSnapshot }}</div>
			<div class="mobile-snapshot-image">
				<img
					class="mobile-snapshot-image__frame"
					:src="getAssetUrl(iphoneFrame)"
					alt="Mobile Snapshot iPhone Frame"
				/>

				<img
					class="mobile-snapshot-image__content"
					alt="Mobile Snapshot"
					:src="mobileSnapshot"
				/>
			</div>
		</div>
	</div>
</template>

<script>
import { ref } from 'vue'

import {
	useAnalyzerStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'

import { getAssetUrl } from '@/vue/utils/helpers'
import { getSortedParts } from '@/vue/pages/seo-analysis/utils'
import iphoneFrame from '@/vue/assets/images/seo-analysis/iphone-frame.png'

import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import SvgRefresh from '@/vue/components/common/svg/Refresh'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup (props) {
		const {
			description,
			strings
		} = useSeoSiteScore({
			score : ref(props.score)
		})

		return {
			analyzerStore     : useAnalyzerStore(),
			composableStrings : strings,
			description,
			iphoneFrame,
			getAssetUrl
		}
	},
	components : {
		CoreDonutChartWithLegend,
		SvgRefresh
	},
	props : {
		score   : Number,
		loading : Boolean,
		site    : {
			type     : String,
			required : true
		},
		summary : {
			type : Object,
			default () {
				return {}
			}
		},
		mobileSnapshot : String
	},
	data () {
		return {
			isAnalyzing : false,
			strings     : merge(this.composableStrings, {
				refreshResults : __('Refresh Results', td),
				mobileSnapshot : __('Mobile Snapshot', td),
				analyzing      : __('Analyzing...', td)
			})
		}
	},
	computed : {
		sortedParts () {
			const goodCount = this.summary.good || 0
			const warningsCount = this.summary.recommended || 0
			const criticalCount = this.summary.critical || 0
			const totalCount = goodCount + warningsCount + criticalCount

			return getSortedParts({
				good     : goodCount,
				warnings : warningsCount,
				issues   : criticalCount,
				total    : totalCount
			}, 'competitor', false)
		}
	},
	methods : {
		refresh () {
			this.isAnalyzing = true
			this.analyzerStore.runSiteAnalyzer({
				url     : this.site,
				refresh : true
			}).then(() => {
				this.isAnalyzing = false
				this.$emit('refresh')
			})
		}
	}
}
</script>

<style lang="scss">
.aioseo-site-score-competitor {
	position: relative;
	display: flex;
	align-items: flex-start;
	justify-content: center;
	flex-direction: column;

	.aioseo-seo-site-score-score {
		position: relative;
		width: 100%;
		max-width: 200px;

		.aioseo-donut-chart-with-legend {
			flex-direction: column !important;
			gap: 20px;
			margin-bottom: 16px;

			.chart-right {
				margin-left: 0;
			}
		}
	}

	.refresh-results {
		.aioseo-refresh {
			width: 14px;
			height: 14px;
			margin-right: 10px;
		}
	}

	.mobile-snapshot {
		width: 250px;
		max-width: 100%;
		margin-top: 30px;

		&-image {
			position: relative;
			padding: 10px 4px 0;
			width: 100%;
			height: 445px;
			overflow: hidden;
			border-radius: 35px;

			&__frame {
				position: absolute;
				z-index: 2;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}

			&__content {
				width: 100%;
				height: auto;
			}
		}

		div {
			font-weight: 600;
			font-size: 16px;
			margin-bottom: 10px;
		}
	}
}
</style>