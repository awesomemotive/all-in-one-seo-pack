<template>
	<div class="aioseo-search-statistics-top-keywords">
		<div
			v-if="0 === keywords.length"
			class="top-keywords-empty"
			:class="{ blurred : searchStatisticsStore.loading.keywords }"
		>
			<h3 class="title">{{ strings.noKeywords }}</h3>
		</div>

		<template v-if="0 < keywords.length">
			<div class="top-keywords-title">
				<div>{{ strings.keyword }}</div>
				<div>{{ strings.clicks }}</div>
				<div>{{ strings.keyword }}</div>
				<div>{{ strings.clicks }}</div>
			</div>

			<div
				class="top-keywords"
				:class="{ blurred : searchStatisticsStore.loading.keywords }"
			>
				<div
					v-for="i in 10"
					:key="i"
					class="top-keywords-row"
				>
					<div class="keyword">
						<template v-if="keywords[i-1]">
							<div class="count">
								{{ i }}.
							</div>

							<core-tooltip v-if="shouldLimitText(keywords[i-1].keyword)">
								<span class="limit-line">{{ decode(keywords[i-1].keyword) }}</span>

								<template #tooltip>
									{{ decode(keywords[i-1].keyword) }}
								</template>
							</core-tooltip>

							<span v-else>{{ decode(keywords[i-1].keyword) }}</span>

							<statistic
								type="clicks"
								:total="keywords[i-1].clicks"
								:difference="keywords[i-1].difference.clicks"
								:showDifference="false"
								:showZeroValues="true"
							/>
						</template>
					</div>
				</div>
			</div>
		</template>

		<core-loader v-if="searchStatisticsStore.loading.keywords" dark />
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import { decode } from 'he'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import Statistic from './Statistic'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreLoader,
		CoreTooltip,
		Statistic
	},
	data () {
		return {
			strings : {
				keyword    : this.$t.__('Keyword', this.$td),
				clicks     : this.$t.__('Clicks', this.$td),
				noKeywords : this.$t.__('You don\'t have any keywords yet.', this.$td)
			}
		}
	},
	computed : {
		keywords () {
			if (!this.searchStatisticsStore.data?.keywords?.topKeywords) {
				return []
			}
			return this.searchStatisticsStore.data.keywords.topKeywords.slice(0, 10)
		}
	},
	methods : {
		decode,
		shouldLimitText (line) {
			return 60 < decode(line).length
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-top-keywords {
	.aioseo-loading-spinner {
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		margin: auto;
	}

	.blurred {
		filter: blur(2px);
		pointer-events: none;
		user-select: none;
	}

	.top-keywords {
		display: grid;
		grid-auto-flow: column;
		grid-template-columns: repeat(2, 1fr);
		grid-template-rows: repeat(5, 1fr);
		margin-bottom: 20px;

		&-title {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			grid-gap: 24px;
			font-weight: 700;
			font-size: 14px;
			margin-bottom: 16px;

			> div {
				&:nth-child(even) {
					text-align: right;
				}
			}
		}

		&-row {
			display: flex;

			.keyword {
				display: flex;
				align-items: start;
				flex-basis: 100%;
				padding: 12px;
				background-color: $box-background;
				font-weight: 400;
				font-size: 14px;
				line-height: 125%;

				.limit-line {
					overflow: hidden;
					text-overflow: ellipsis;
					display: -webkit-box;
					-webkit-line-clamp: 2;
					line-clamp: 2;
					-webkit-box-orient: vertical;
				}

				.count {
					margin-right: 4px;
					font-weight: 700;
				}

				.statistic {
					margin-left: auto;
					padding-left: 12px;
					font-weight: 700;

					&-direction {
						font-size: 14px;
					}
				}

				.aioseo-tooltip {
					margin-left: 0;
				}
			}

			&:nth-last-child(-n+5) {
				.keyword {
					margin-left: 12px;
				}
			}

			// Select first five.
			&:nth-child(-n+5) {
				.keyword {
					margin-right: 12px;
				}
			}

			// Select odd after sixth.
			&:nth-child(n+6):nth-child(odd) {
				.keyword {
					background-color: $white;
				}
			}

			// Select even until five.
			&:nth-child(-n+5):nth-child(even) {
				.keyword {
					background-color: $white;
				}
			}
		}

		&-empty {
			text-align: center;

			.title {
				font-weight: 700;
				font-size: 16px;
				margin: 0 0 12px;
			}
		}
	}
}
</style>