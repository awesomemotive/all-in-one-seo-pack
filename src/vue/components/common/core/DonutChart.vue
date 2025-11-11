<template>
	<div
		class="aioseo-donut-chart"
	>
		<svg
			viewBox="0 0 33.83098862 33.83098862"
			xmlns="http://www.w3.org/2000/svg"
			v-if="0 === total"
		>
			<circle
				class="aioseo-donut-chart-score__circle"
				stroke="#e8e8eb"
				stroke-dasharray="100"
				stroke-linecap="round"
				:stroke-width="2"
				fill="none"
				cx="16.91549431"
				cy="16.91549431"
				r="15.91549431"
			/>
		</svg>

		<svg
			viewBox="0 0 33.83098862 33.83098862"
			xmlns="http://www.w3.org/2000/svg"
			v-if="0 < total"
		>
			<template
				v-for="(part, index) in parts"
				:key="index"
			>
				<circle
					v-if="Math.round(part.ratio) && 0 !== index"
					class="aioseo-donut-chart-score__circle"
					stroke="#FFFFFF"
					:stroke-dasharray="`${Math.round(part.ratio)} ${parseFloat(99 - Math.round(part.ratio))}`"
					stroke-linecap="round"
					:stroke-width="2.5"
					fill="none"
					cx="16.91549431"
					cy="16.91549431"
					r="15.91549431"
				/>
				<circle
					v-if="Math.round(part.ratio)"
					class="aioseo-donut-chart-score__circle"
					:stroke="part.color"
					:stroke-dasharray="`${100 === Math.round(part.ratio) ? 100 : Math.round(part.ratio) - 1} 100`"
					stroke-linecap="round"
					:stroke-width="2"
					fill="none"
					cx="16.91549431"
					cy="16.91549431"
					r="15.91549431"
				/>
			</template>
		</svg>

		<div class="total-amount-wrapper">
			<div
				class="total-amount"
			>
				<div class="total">
					<util-fit-text
						:max="32"
						:constrain-to-element="$el"
						class="total-container"
						:class="getClass"
					>
						<util-animated-number v-if="animatedNumber" :number="parseInt(total)" />
						<div v-else v-html="parseInt(total)" />

						<span
							v-if="maxTotal"
							class="total-max"
						>
							/ {{ parseInt(maxTotal) }}
						</span>
					</util-fit-text>
				</div>

				<div
					class="label"
					:class="{ [getColorClass] : isLabelColored }"
					v-html="label"
				/>
			</div>
		</div>
	</div>
</template>

<script>
import UtilAnimatedNumber from '@/vue/components/common/util/AnimatedNumber'
import UtilFitText from '@/vue/components/common/util/FitText'
export default {
	components : {
		UtilAnimatedNumber,
		UtilFitText
	},
	props : {
		parts : {
			type     : Array,
			required : true
		},
		label : {
			type     : String,
			required : true
		},
		total : {
			type     : Number,
			required : true
		},
		animatedNumber : {
			type : Boolean,
			default () {
				return true
			}
		},
		maxTotal : {
			type     : String,
			required : false
		},
		isLabelColored : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	computed : {
		getColorClass () {
			if (!this.maxTotal) {
				return ''
			}

			switch (true) {
				case 33 >= this.total:
					return 'red'
				case 66 >= this.total:
					return 'orange'
				default:
					return 'green'
			}
		},
		getClass () {
			const colorClass = this.getColorClass

			return `total-container-large ${colorClass}`
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-donut-chart,
.aioseo-blc-app .aioseo-donut-chart {
	position: relative;
	display: flex;

	svg {
		width: 100%;
		circle {
			transform: rotate(270deg);
			transform-origin: center;
		}
	}

	.total-amount-wrapper {
		position: absolute;
		left: 0;
		top: 0;
		right: 0;
		bottom: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		color: $black;
		margin: 20px;

		.total-amount {
			text-align: center;

			.total {
				font-size: 32px;
				font-weight: bold;
				line-height: 40px;
			}

			.total-container {
				display: flex;
				align-items: baseline;
				justify-content: center;
				gap: 4px;

				&.total-container-large {
					> span,
					> div {
						font-size: 32px;
						font-weight: 700;
					}

					&.red {
						color: $red;
					}

					&.orange {
						color: $orange;
					}

					&.green {
						color: $green;
					}
				}

				.total-max {
					font-size: 13px !important;
					font-weight: 600;
					line-height: 150%;
					color: $placeholder-color;
				}
			}

			.label {
				margin-top: 5px;
				line-height: 150%;

				&.red {
					color: $red;
					font-weight: bold;
				}

				&.orange {
					color: $orange;
					font-weight: bold;
				}

				&.green {
					color: $green;
					font-weight: bold;
				}
			}
		}
	}
}
</style>