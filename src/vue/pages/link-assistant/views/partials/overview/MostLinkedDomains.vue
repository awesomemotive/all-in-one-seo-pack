<template>
	<core-card
		class="aioseo-link-assistant-linked-domains"
		slug="linkAssistantLinkedDomains"
		no-slide
		:header-text="strings.mostLinkedDomains"
	>
		<core-donut-chart-with-legend
			:parts="sortedParts"
			:total="totals.externalLinks"
			:label="strings.totalExternalLinks"
			:link="strings.link"
		/>

		<div class="domains-table">
			<table-row class="header-row">
				<table-column
					v-for="(column, index) in columns"
					:key="index"
					:class="column.slug"
				>
					{{ column.label }}
				</table-column>
			</table-row>

			<table-row
				v-for="(row, index) in mostLinkedDomains"
				:key="index"
				class="row"
				:class="{
					even : 0 === index % 2
				}"
			>
				<table-column class="domain">
					<div class="row">
						<img
							alt="Domain Favicon"
							class="favicon"
							:src="`https://www.google.com/s2/favicons?sz=32&domain=${row.name}`"
						/>
						<core-tooltip
							type="action"
						>
							<a class="domain-name" :href="`#/domains-report?hostname=${row.name}`">{{ row.name }}</a>

							<template #tooltip>
								<a
									class="tooltip-url"
									:href="`https://${row.name}`"
									target="_blank">
										{{ row.name }}
								</a>
							</template>
						</core-tooltip>
					</div>
				</table-column>
				<table-column class="count">
					<span>{{ numbers.numberFormat(row.count) }}</span>
				</table-column>
			</table-row>

			<table-row
				v-if="!mostLinkedDomains.length"
				class="row even"
			>

				<table-column class="domain">
					{{ strings.noResults }}
				</table-column>
			</table-row>
		</div>
	</core-card>
</template>

<script>
import numbers from '@/vue/utils/numbers'
import CoreCard from '@/vue/components/common/core/Card'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import TableColumn from '@/vue/components/common/table/Column'
import TableRow from '@/vue/components/common/table/Row'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreCard,
		CoreTooltip,
		CoreDonutChartWithLegend,
		TableColumn,
		TableRow
	},
	props : {
		totals : {
			type     : Object,
			required : true
		},
		mostLinkedDomains : {
			type     : Array,
			required : true
		}
	},
	data () {
		return {
			numbers,
			strings : {
				mostLinkedDomains  : __('Most Linked to Domains', td),
				totalExternalLinks : __('Total External Links', td),
				noResults          : __('No items found.', td),
				link               : sprintf(
					'<a href="%1$s">%2$s</a><a href="%1$s"> <span>&rarr;</span></a>',
					'#/domains-report?fullReport=1',
					__('See a Full Domains Report', td)
				)
			}
		}
	},
	computed : {
		sortedParts () {
			// Clone object before splicing so that it doesn't affect the store.
			const parts = this.mostLinkedDomains.map((x) => x).splice(0, 3)
			let   otherDomainsCount = this.totals.externalLinks

			if (parts[0]) {
				parts[0].color = '#005AE0'
				parts[0].ratio = 100
				otherDomainsCount = otherDomainsCount - parts[0].count
			}

			if (parts[1]) {
				parts[1].color = '#80ACF0'
				parts[1].ratio = (parts[1].count / this.totals.externalLinks) * 100
				otherDomainsCount = otherDomainsCount - parts[1].count
			}

			if (parts[2]) {
				parts[2].color = '#BFD6F7'
				parts[2].ratio = (parts[2].count / this.totals.externalLinks) * 100
				otherDomainsCount = otherDomainsCount - parts[2].count
			}

			if (otherDomainsCount) {
				parts.push({
					name  : __('other domains', td),
					color : '#E8E8EB',
					count : otherDomainsCount,
					ratio : (otherDomainsCount / this.totals.externalLinks) * 100,
					last  : true
				})
			}

			parts.filter(function (part) {
				return 0 !== part.count
			}).sort(function (part1, part2) {
				return parseInt(part2.count) > parseInt(part1.count) ? 1 : -1
			})

			// At this point, the ratios are still incorrect because they do not take the "size" of the other ratios into account.
			// This can be resolved by adding the ratios of the remaining parts to their own ratio.
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
		},
		columns () {
			return [
				{
					slug  : 'name',
					label : __('Domain', td)
				},
				{
					slug  : 'count',
					label : __('# of Links', td)
				}
			]
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-link-assistant-overview {
	.domains-table {
		margin-top: var(--aioseo-gutter);

		.row {
			display: flex;
			align-items: center;

			.favicon {
				width: 15px;
				margin: 0 10px 0 0;
			}

			.domain-name {
				color: $black;
				&:hover {
					color: $blue;
				}
			}
		}

		.aioseo-table-row {
			&.even {
				background-color: $box-background;
			}

			.aioseo-table-column {
				padding: 12px;

				&.domain {
					min-width: 0;

					span {
						white-space: nowrap;
						overflow: hidden;
						text-overflow: ellipsis;
					}

					a {
						text-decoration: none;
						white-space: nowrap;
						overflow: hidden;
						text-overflow: ellipsis;

						&:hover {
							color: $blue;
						}
					}

					.aioseo-tooltip {
						margin-left: 0;
						.popper a {
							color: white;
							text-decoration: underline;
							&:hover {
								text-decoration: none;
							}
						}
					}
				}

				&.count {
					min-width: 100px;
					flex: 0;
					align-items: flex-end;
				}
			}
		}
	}
}
</style>