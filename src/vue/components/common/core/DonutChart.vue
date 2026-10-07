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
			<circle
				v-for="(segment, index) in segments"
				:key="index"
				class="aioseo-donut-chart-score__circle"
				:stroke="segment.color"
				:stroke-dasharray="segment.dashArray"
				:stroke-dashoffset="segment.dashOffset"
				stroke-linecap="round"
				:stroke-width="strokeWidth"
				fill="none"
				cx="16.91549431"
				cy="16.91549431"
				r="15.91549431"
			/>
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
	data () {
		return {
			// The radius (r=15.915...) is chosen so the circumference ≈ 100, mapping ratios straight to lengths.
			circumference : 100,
			strokeWidth   : 2,
			// Constant gap between segments, in circumference units. Held fixed so gaps stay even at any segment size.
			segmentGap    : 2
		}
	},
	computed : {
		segments () {
			if (0 >= this.total || !this.parts?.length) {
				return []
			}

			// Each part carries a cumulative ratio (its own share plus every following segment's share).
			// Recover each segment's own share by differencing consecutive parts, then drop empty ones.
			const ownSegments = this.parts
				.map((part, index) => {
					const next = this.parts[index + 1]
					const own  = part.ratio - (next ? next.ratio : 0)

					return {
						color : part.color,
						ratio : 0 < own ? own : 0
					}
				})
				.filter(segment => 0 < segment.ratio)

			if (!ownSegments.length) {
				return []
			}

			// A single segment fills the whole ring; no gaps to draw.
			if (1 === ownSegments.length) {
				return [ {
					color      : ownSegments[0].color,
					dashArray  : `${this.circumference} 0`,
					dashOffset : 0
				} ]
			}

			// Reserve one constant gap per segment; share the rest proportionally.
			const totalGaps = this.segmentGap * ownSegments.length
			const drawable  = this.circumference - totalGaps
			const gap       = 0 < drawable ? this.segmentGap : 0
			const available = 0 < drawable ? drawable : this.circumference

			const segments = []
			let cursor = 0

			ownSegments.forEach(segment => {
				const arc      = (segment.ratio / 100) * available
				const arcStart = cursor + (gap / 2)

				// Round caps extend each end by half the stroke; subtract a full stroke so the visible arc
				// (caps included) matches its share and gaps stay even. Segments thinner than the caps
				// collapse to a dot (dash length 0) instead of disappearing.
				const dashLength = Math.max(0, arc - this.strokeWidth)
				const dashStart  = 0 < dashLength
					? arcStart + (this.strokeWidth / 2)
					: arcStart + (arc / 2)

				segments.push({
					color      : segment.color,
					dashArray  : `${dashLength} ${this.circumference - dashLength}`,
					dashOffset : -dashStart
				})

				cursor = arcStart + arc + (gap / 2)
			})

			return segments
		},
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