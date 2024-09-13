<template>
	<core-card
		class="aioseo-link-assistant-linking-opportunities"
		slug="linkAssistantLinkOpportunities"
		no-slide
		:header-text="strings.linkingOpportunities"
	>
		<template #tabs>
			<core-main-tabs
				:tabs="tabs"
				:showSaveButton="false"
				:active="activeTab"
				@changed="value => activeTab = value"
				internal
			/>
		</template>

		<div>
			<div class="linking-opportunities-table">
				<table-row
					class="header-row"
					v-if="opportunities?.length"
				>
					<table-column
						v-for="(column, index) in columns"
						:key="index"
						:class="column.slug"
					>
						<div class="row">
							<div v-if="!column.tooltipIcon">
								{{ column.label }}
							</div>

							<div class="aioseo-tooltip-wrapper" v-if="column.tooltipIcon">
								<core-tooltip class="action">
									<component :is="column.tooltipIcon"></component>

									<template #tooltip>
										<span v-html="column.label" />
									</template>
								</core-tooltip>
							</div>
						</div>
					</table-column>
				</table-row>

				<table-row
					v-for="(row, index) in opportunities"
					:key="index"
					class="row"
					:class="{
						even : 0 === index % 2
					}"
				>
					<table-column class="post-title">
						<div class="row">
							<core-tooltip
								type="action"
							>
								<router-link :to="{
									name : 'links-report',
									query : {
										postTitle : row.postTitle
									}
								}">
									{{ row.postTitle }}
								</router-link>

								<template #tooltip>
									<a
										class="tooltip-url"
										:href="row.permalink"
										target="_blank"
									>
										{{ row.postTitle }}
									</a>
								</template>
							</core-tooltip>
						</div>
					</table-column>

					<table-column
						class="internal-inbound"
						v-if="'inbound' === activeTab"
					>
						<span class="count">{{ row.inboundSuggestions }}</span>
					</table-column>

					<table-column
						class="internal-outbound"
						v-if="'outbound' === activeTab"
					>
						<span class="count">{{ row.outboundSuggestions }}</span>
					</table-column>
				</table-row>

				<table-row
					v-if="!opportunities?.length"
					class="row even"
				>

					<table-column class="post-title">
						{{ strings.noResults }}
					</table-column>
				</table-row>
			</div>

			<div class="links-report-link"
				v-if="opportunities?.length"
			>
				<span v-html="link" />
			</div>
		</div>
	</core-card>
</template>

<script>
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgLinkInternalInbound from '@/vue/components/common/svg/link/InternalInbound'
import SvgLinkInternalOutbound from '@/vue/components/common/svg/link/InternalOutbound'
import TableColumn from '@/vue/components/common/table/Column'
import TableRow from '@/vue/components/common/table/Row'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreCard,
		CoreMainTabs,
		CoreTooltip,
		SvgLinkInternalInbound,
		SvgLinkInternalOutbound,
		TableColumn,
		TableRow
	},
	props : {
		linkingOpportunities : {
			type     : Object,
			required : true
		}
	},
	data () {
		return {
			activeTab : 'inbound',
			strings   : {
				linkingOpportunities : __('Linking Opportunities', td),
				noResults            : __('No items found.', td)
			},
			link : sprintf(
				'<a class="links-report-link" href="%1$s">%2$s</a><a href="%1$s"> <span>&rarr;</span></a>',
				'#/links-report?linkingOpportunities=1',
				__('See All Linking Opportunities', td)
			),
			tabs : [
				{
					slug : 'inbound',
					name : __('Inbound Suggestions', td)
				},
				{
					slug : 'outbound',
					name : __('Outbound Suggestions', td)
				}
			],
			columns : [
				{
					slug  : 'post-title',
					label : __('Post Title', td)
				},
				{
					slug  : 'suggestions-count',
					label : __('Count', td)
				}
			]
		}
	},
	computed : {
		opportunities () {
			return this.linkingOpportunities[this.activeTab]
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-link-assistant-overview .aioseo-link-assistant-linking-opportunities {
	.linking-opportunities-table {
		.row {
			display: flex;
			align-items: center;
		}

		.aioseo-table-row {
			&.even {
				background-color: $box-background;
			}

			.aioseo-table-column {
				padding: 12px;

				&.post-title {
					min-width: 0;

					div {
						white-space: nowrap;
						overflow: hidden;
						text-overflow: ellipsis;

						span {
							white-space: nowrap;
							overflow: hidden;
							text-overflow: ellipsis;
						}

						a {
							color: $black;
							text-decoration: none;

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
				}

				&.suggestions-count .row {
					margin-left: auto;
				}

				&.internal-inbound,
				&.internal-outbound {
					min-width: 60px;
					flex: 0;
					text-align: right;
				}

				.aioseo-tooltip-wrapper {
					display: flex;
					.aioseo-tooltip {
						margin: 0;
					}
				}
			}

			&.header-row > .aioseo-table-column {
				padding-block: 0 14px;
			}
		}
	}

	.links-report-link {
		margin-top: var(--aioseo-gutter);
		color: $blue;
		cursor: pointer;
		font-weight: bold;
		font-size: 14px;
		a {
			text-decoration: underline;
			&:not(:first-of-type),
			&:hover {
				text-decoration: none;
			}
		}
	}
}
</style>