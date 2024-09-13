<template>
	<a
		class="aioseo-link-count"
		:href="getLink"
	>
		<div class="aioseo-link-count-top">
			<component
				:is="getType.icon"
			/>
			<util-animated-number
				:number="parseInt(count)"
			/>
		</div>
		<div class="aioseo-link-count-bottom">
			<span>{{ getType.name }}</span>

			<core-tooltip
				v-if="'orphaned' === type"
			>
				<div
					class="disabled-button gray"
				>
					<svg-circle-question-mark />
				</div>

				<template #tooltip>
					<span>
						{{ strings.orphanedPostsDescription }}
					</span>
				</template>
			</core-tooltip>
		</div>
	</a>
</template>

<script>
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgLinkAffiliate from '@/vue/components/common/svg/link/Affiliate'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'
import SvgLinkInternalInbound from '@/vue/components/common/svg/link/InternalInbound'
import SvgLinkOrphaned from '@/vue/components/common/svg/link/Orphaned'
import SvgSearch from '@/vue/components/common/svg/Search'
import UtilAnimatedNumber from '@/vue/components/common/util/AnimatedNumber'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreTooltip,
		SvgCircleQuestionMark,
		SvgLinkAffiliate,
		SvgLinkExternal,
		SvgLinkInternalInbound,
		SvgLinkOrphaned,
		SvgSearch,
		UtilAnimatedNumber
	},
	props : {
		type : {
			type     : String,
			required : true
		},
		count : {
			type     : Number,
			required : true
		}
	},
	data () {
		return {
			strings : {
				orphanedPostsDescription : __('Orphaned posts are posts that have no inbound internal links yet and may be more difficult to find by search engines.', td)
			},
			icons : [
				{
					type : 'posts',
					name : __('Posts Crawled', td),
					icon : 'svg-search'
				},
				{
					type : 'external',
					name : __('External Links', td),
					icon : 'svg-link-external'
				},
				{
					type : 'internal',
					name : __('Internal Links', td),
					icon : 'svg-link-internal-inbound'
				},
				{
					type : 'affiliate',
					name : __('Affiliate Links', td),
					icon : 'svg-link-affiliate'
				},
				{
					type : 'orphaned',
					name : __('Orphaned Posts', td),
					icon : 'svg-link-orphaned'
				}
			]
		}
	},
	computed : {
		getType () {
			return this.icons.find(object => object.type === this.type)
		},
		getLink () {
			switch (this.type) {
				case 'posts':
				case 'affiliate':
				case 'internal':
					return '#/links-report?fullReport=1'
				case 'external':
					return '#/domains-report'
				case 'orphaned':
					return '#/links-report?orphaned-posts=1'
				default:
					return ''
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-link-count {
	display: flex;
	padding: 0 20px;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-decoration: none;

	.aioseo-link-count-top {
		display: flex;
		align-items: center;
			font-size: 32px;
			line-height: 40px;
			font-weight: $font-bold;

		span {
			color: $black;
		}

		svg {
			margin-right: 11px;
			width: 100%;
			max-height: 18px;

			&.aioseo-search {
				$color: $black2-hover;
			}

			&.aioseo-link-external {
				color: $blue;
			}

			&.aioseo-link-internal-inbound {
				color: $green;
			}

			&.aioseo-link-affiliate {
				max-height: 20px;
				color: $orange;
			}

			&.aioseo-link-orphaned {
				margin-right: 10px;
				color: $red;
			}
		}
	}
	.aioseo-link-count-bottom {
		display: flex;
		margin-top: 8px;
		font-size: 14px;
		line-height: 18px;

		span {
			color: $black2;
		}

		.aioseo-tooltip {
			display: inline-block;
			margin-left: 8px;
			margin-top: 1.5px;

			svg.aioseo-circle-question-mark {
				display: inline-flex;
				width: 15px;
				height: 15px;
			}
		}
	}

	@media screen and (max-width: 912px) {
		.aioseo-row {
			justify-content: center;
			row-gap: 15px;

			.counter {
				display: inline-flex;

				&.aioseo-col {
					flex-basis: auto;
					padding: 5px 20px;
				}
			}
		}
	}
}
</style>