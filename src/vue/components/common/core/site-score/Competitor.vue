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
					v-if="!snapshotFailed"
					class="mobile-snapshot-image__content"
					alt="Mobile Snapshot"
					:src="mobileSnapshot"
					@error="snapshotFailed = true"
				/>

				<div
					v-else
					class="mobile-snapshot-image__fallback"
				>
					<svg-image-seo />

					<span>{{ strings.snapshotUnavailable }}</span>
				</div>
			</div>

			<div
				v-if="snapshotFailed"
				class="mobile-snapshot-caption"
			>
				<svg-info />

				<span v-safe-html="strings.snapshotBlockedNotice" />
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

import links from '@/vue/utils/links'
import { getAssetUrl } from '@/vue/utils/helpers'
import { getSortedParts } from '@/vue/pages/seo-analysis/utils'
import iphoneFrame from '@/vue/assets/images/seo-analysis/iphone-frame.png'

import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import SvgRefresh from '@/vue/components/common/svg/Refresh'
import SvgImageSeo from '@/vue/components/common/svg/ImageSeo'
import SvgInfo from '@/vue/components/common/svg/Info'

import { __, sprintf } from '@/vue/plugins/translations'

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
		SvgRefresh,
		SvgImageSeo,
		SvgInfo
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
			isAnalyzing    : false,
			snapshotFailed : false,
			strings        : merge(this.composableStrings, {
				refreshResults        : __('Refresh Results', td),
				mobileSnapshot        : __('Mobile Snapshot', td),
				analyzing             : __('Analyzing...', td),
				snapshotUnavailable   : __('Preview unavailable', td),
				snapshotBlockedNotice : sprintf(
					// Translators: 1 - Opening link tag, 2 - Closing link tag.
					__('A firewall or security service such as Cloudflare may have blocked our screenshot. This doesn\'t affect the SEO score. %1$sLearn more%2$s', td),
					`<a href="${links.getDocUrl('seoAnalyzerIssues')}" target="_blank">`,
					'</a>'
				)
			})
		}
	},
	watch : {
		// A refreshed competitor analysis can return a new snapshot URL, so retry the load.
		mobileSnapshot () {
			this.snapshotFailed = false
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

			&__fallback {
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				gap: $gutter-half;
				width: 100%;
				height: 100%;
				padding: $gutter;
				text-align: center;
				background-color: $box-background;
				color: $placeholder-color;

				svg {
					width: 48px;
					height: 48px;
				}

				span {
					font-size: $font-sm;
				}
			}
		}

		.mobile-snapshot-caption {
			display: flex;
			align-items: flex-start;
			gap: 6px;
			margin: $gutter-half 0 0;
			font-size: $font-sm;
			font-weight: 400;
			line-height: 1.5;
			color: $placeholder-color;

			svg {
				flex-shrink: 0;
				width: 14px;
				height: 14px;
				margin-top: 1px;
				color: $placeholder-color;
			}

			a {
				font-weight: 600;
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