<template>
	<core-card
		class="aioseo-link-assistant-link-ratio"
		slug="linkAssistantLinkRatio"
		no-slide
		:header-text="strings.header"
	>
		<core-donut-chart-with-legend
			:parts="sortedParts"
			:total="totals.totalLinks"
			:label="strings.totalLinks"
			:link="strings.linksReportLink"
		/>
	</core-card>
</template>

<script>
import { merge } from 'lodash-es'
import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'

import CoreCard from '@/vue/components/common/core/Card'
import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { strings } = useSeoSiteScore()

		return {
			composableStrings : strings
		}
	},
	components : {
		CoreCard,
		CoreDonutChartWithLegend
	},
	props : {
		totals : {
			type     : Object,
			required : true
		}
	},
	data () {
		return {
			strings : merge(this.composableStrings, {
				header          : __('Internal vs External vs Affiliate Links', td),
				totalLinks      : __('Total Links', td),
				linksReportLink : sprintf(
					'<a href="%1$s">%2$s</a><a href="%1$s"> <span>&rarr;</span></a>',
					'#/links-report?fullReport=1',
					__('See a Full Links Report', td)
				)
			})
		}
	},
	computed : {
		parts () {
			return [
				{
					slug  : 'external',
					name  : __('External Links', td),
					count : this.totals.externalLinks
				},
				{
					slug  : 'affiliate',
					name  : __('Affiliate Links', td),
					count : this.totals.affiliateLinks
				},
				{
					slug  : 'internal',
					name  : __('Internal Links', td),
					count : this.totals.internalLinks
				}
			]
		},
		sortedParts () {
			const parts = this.parts
			parts.sort(function (object1, object2) {
				return object2.count > object1.count ? 1 : -1
			})

			parts[0].ratio = 100
			parts[1].ratio = (parts[1].count / this.totals.totalLinks) * 100
			parts[2].ratio = (parts[2].count / this.totals.totalLinks) * 100

			parts.forEach((part) => {
				switch (part.slug) {
					case 'external': {
						part.color = '#005AE0'
						break
					}
					case 'internal': {
						part.color = '#00AA63'
						break
					}
					case 'affiliate': {
						part.color = '#F18200'
						break
					}
				}
			})

			parts.filter(function (part) {
				return 0 !== part.count
			})

			// At this point, the ratios are still incorrect because the donuts won't take the "size" of the other donuts into account.
			// This can be resolved by adding the ratios of the remaining parts to the ratio of the current part.
			parts.forEach((part, index) => {
				if (0 === index) {
					return part
				}

				parts.forEach((part2, index2) => {
					if (index < index2) {
						part.ratio = part.ratio + part2.ratio
					}
					return part
				})
				return part
			})
			return parts
		}
	}
}
</script>