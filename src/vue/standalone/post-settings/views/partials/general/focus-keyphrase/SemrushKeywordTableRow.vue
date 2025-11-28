<template>
	<tr
		class="keyphrase-row"
		:class="{ even: 0 === index % 2 }"
	>
		<td class="keyphrase">
			{{ keyword[0] }}
		</td>
		<td class="keyphrase-volume">
			{{ keyword[1] }}
		</td>
		<td class="keyphrase-trend">
			<svg-area-chart
				:width="66"
				:height="24"
				:data="trendData"
				:strokeWidth="1.8"
				strokeColor="#498afc"
				fillColor="#ade3fc"
			/>
		</td>
		<td
			v-if="!isUnlicensed"
			class="keyphrase-actions"
		>
			<!-- Focus keyphrase indicator -->
			<div
				v-if="isFocusKeyphrase"
				class="focus-keyphrase"
			>
				<svg-circle-check />
				{{ strings.focusKeyphrase }}
			</div>

			<!-- Add/Remove actions -->
			<div v-if="!isFocusKeyphrase">
				<!-- Add button (when not added and below max) -->
				<base-button
					v-if="!isRemoving && (isAdding || !hasAdditional) && !maxReached"
					type="gray"
					size="medium"
					@click="handleAdd"
					:loading="isAdding"
				>
					{{ strings.addAdditionalKeyphrase }}
				</base-button>

				<!-- Disabled add button with tooltip (when max reached) -->
				<core-tooltip
					v-if="!isRemoving && (isAdding || !hasAdditional) && maxReached"
				>
					<base-button
						type="gray"
						size="medium"
						:disabled="true"
						:loading="isAdding"
					>
						{{ strings.addAdditionalKeyphrase }}
					</base-button>

					<template #tooltip>
						<span>{{ strings.maxAmountReached }}</span>
					</template>
				</core-tooltip>

				<!-- Score and remove button (when already added) -->
				<div
					class="remove-keyphrase"
					v-if="hasAdditional && !isAdding && (isRemoving || hasAdditional)"
				>
					<span
						class="keyphrase-score"
						:class="scoreClass"
						@click="handleNavigate"
					>{{ additionalScore }}/100</span>

					<core-tooltip type="action">
						<svg-trash @click.native="handleRemove" />

						<template #tooltip>
							{{ strings.delete }}
						</template>
					</core-tooltip>
				</div>
			</div>
		</td>
	</tr>
</template>

<script>
import { transformTrendDataToChartPoints } from '@/vue/utils/semrushKeywords'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgAreaChart from '@/vue/components/common/svg/AreaChart'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'add', 'remove', 'navigate' ],
	components : {
		CoreTooltip,
		SvgAreaChart,
		SvgCircleCheck,
		SvgTrash
	},
	props : {
		keyword : {
			type     : Array,
			required : true
		},
		index : {
			type     : Number,
			required : true
		},
		focusKeyphrase : {
			type     : String,
			required : true
		},
		hasAdditional : {
			type    : Boolean,
			default : false
		},
		additionalScore : {
			type    : Number,
			default : 0
		},
		isAdding : {
			type    : Boolean,
			default : false
		},
		isRemoving : {
			type    : Boolean,
			default : false
		},
		maxReached : {
			type    : Boolean,
			default : false
		},
		isUnlicensed : {
			type    : Boolean,
			default : false
		},
		maxAdditionalKeyphrases : {
			type     : Number,
			required : true
		}
	},
	computed : {
		isFocusKeyphrase () {
			return this.keyword[0] === this.focusKeyphrase.toLowerCase()
		},
		trendData () {
			return transformTrendDataToChartPoints(this.keyword[2])
		},
		scoreClass () {
			const score = this.additionalScore
			return 79 < score ? 'score-green' : (49 < score ? 'score-orange' : (0 < score ? 'score-red' : 'score-none'))
		},
		strings () {
			return {
				focusKeyphrase         : __('Focus Keyword', td),
				addAdditionalKeyphrase : __('Add Keyword', td),
				delete                 : __('Delete', td),
				maxAmountReached       : sprintf(
					// Translators: 1 - Number of maximum keywords.
					__('You have reached the maximum of %1$s additional keywords.', td),
					this.maxAdditionalKeyphrases
				)
			}
		}
	},
	methods : {
		handleAdd () {
			this.$emit('add', this.keyword[0], this.index)
		},
		handleRemove () {
			this.$emit('remove', this.keyword[0], this.index)
		},
		handleNavigate () {
			this.$emit('navigate', this.keyword[0])
		}
	}
}
</script>

<style lang="scss" scoped>
.keyphrase-row {
	background-color: #fff;
	height: 70px;

	&:last-of-type {
		td {
			&:first-of-type {
				border-radius: 0 0 0 3px;
			}

			&:last-of-type {
				border-radius: 0 0 3px 0;
			}
		}
	}

	&.even {
		background-color: $box-background;
	}

	td {
		padding: 15px;

		&:first-of-type {
			padding-left: 30px;
		}

		&:last-of-type {
			padding-right: 30px;
		}
	}
}

.keyphrase-actions {
	> div {
		display: flex;
		align-items: center;
		justify-content: flex-end;
	}

	.focus-keyphrase {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		color: $green;
		min-width: 135px;

		svg {
			margin-right: 5px;
			width: 16px;
			height: 16px;
			color: $green;
		}
	}

	.keyphrase-score {
		border-radius: 3px;
		padding: 5px;
		font-weight: 700;
		font-size: 13px;
		cursor: pointer;
		border: 1px solid $blue;

		&.score-green {
			color: $green;
			border-color: $green;
		}

		&.score-orange {
			color: $orange;
			border-color: $orange;
		}

		&.score-red {
			color: $red;
			border-color: $red;
		}

		&:hover {
			background-color: $blue;
			color: #fff;

			&.score-green {
				background-color: $green;
			}

			&.score-orange {
				background-color: $orange;
			}

			&.score-red {
				background-color: $red;
			}
		}
	}

	.remove-keyphrase {
		display: flex;
		align-items: center;
		justify-content: flex-end;

		svg {
			width: 16px;
			height: 16px;
			cursor: pointer;

			&:hover {
				color: $red;
			}
		}
	}
}
</style>