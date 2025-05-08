<template>
	<div class="aioseo-donut-chart-with-legend">
		<div class="chart-left">
			<core-donut-chart
				:parts="parts"
				:total="total"
				:label="label"
				:animatedNumber="animatedNumber"
			/>
		</div>

		<div class="chart-right">
			<div
				class="legend"
				v-for="(part, index) in sortedParts"
				:key="index"
			>
				<div class="legend-bullet"
					:style="`background-color: ${part.color}`"
				/>
				<span class="legend-amount">
					<util-animated-number
						v-if="animatedNumber"
						:number="parseInt(part.count)"
					/>

					<div
						v-else
						v-html="parseInt(part.count)"
					/>
				</span>

				<component
					:is="part.link ? 'a' : 'span'"
					:href="part.link || null"
					class="legend-label"
					:class="{'is-link': part.link}"
					@click="handleLabelClick($event, part)"
				>
					{{ part.name }}
				</component>
			</div>

			<div
				class="chart-more"
				v-if="link && total > 0"
			>
				<span v-html="link" />
			</div>
		</div>
	</div>
</template>

<script>
import CoreDonutChart from '@/vue/components/common/core/DonutChart'
import UtilAnimatedNumber from '@/vue/components/common/util/AnimatedNumber'
export default {
	emits      : [ 'onLabelClick' ],
	components : {
		CoreDonutChart,
		UtilAnimatedNumber
	},
	props : {
		parts : {
			type     : Array,
			required : true
		},
		total : {
			type     : Number,
			required : true
		},
		label : {
			type     : String,
			required : true
		},
		link : {
			type     : String,
			required : false
		},
		animatedNumber : {
			type : Boolean,
			default () {
				return true
			}
		}
	},
	computed : {
		sortedParts () {
			const clonedParts = this.parts.map(x => x)

			// Move "other domains" to last position regardless of its size/length.
			clonedParts.forEach(function (object, index) {
				if (object.last) {
					clonedParts.push(clonedParts[index])
					clonedParts.splice(index, 1)
				}
			})

			return clonedParts
		}
	},
	methods : {
		handleLabelClick (event, part) {
			if (part?.emitValue) {
				event.preventDefault()

				this.$emit('onLabelClick', part.emitValue)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-donut-chart-with-legend {
	display: flex;
	font-size: 14px;
	color: $black;

	.chart-left {
		max-width: 200px;
		max-height: 200px;
		position: relative;
	}

	.chart-right {
		flex: 1 1 80%;
		font-size: 14px;
		line-height: 125%;
		margin-left: 32px;
		margin-top: auto;
		margin-bottom: auto;

		.legend {
			display: flex;
			align-items: center;

			+ .legend {
				margin-top: 12px;
			}

			span {
				display: inline-block;
			}

			.legend-bullet {
				width: 12px;
				height: 12px;
				min-width: 12px;
				border-radius: 50%;
				margin-right: 8px;
			}

			.legend-amount {
				font-weight: bold;
				margin-right: 8px;
				min-width: 30px;
				text-align: left;
			}

			.legend-label {
				line-height: 130%;

				&.is-link {
					text-decoration: none;

					&:hover {
						text-decoration: underline;
					}
				}
			}
		}

		.chart-more {
			margin-top: 21px;
			margin-left: 6px;
			color: $blue;
			font-weight: bold;
			cursor: pointer;

			a {
				text-decoration: underline;

				&:not(:first-of-type),
				&:hover {
					text-decoration: none;
				}
			}
		}
	}

	@media screen and (max-width: 1200px) {
		display: block !important;

		.chart-left {
			margin-left: auto;
			margin-right: auto;
		}

		.chart-right {
			margin-left: auto;
			margin-right: auto;
			margin-top: 20px;
			display: flex;
			flex-direction: column;
		}
	}
}
</style>