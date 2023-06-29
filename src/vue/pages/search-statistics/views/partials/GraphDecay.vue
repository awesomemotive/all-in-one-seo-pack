<template>
	<div class="aioseo-graph-decay">
		<apexchart
			width="100%"
			:height="height"
			ref="apexchart"
			:options="chartOptions"
			:series="getSeries"
			class="aioseo-graph-decay-chart"
		/>

		<div
			v-if="recovering"
			class="aioseo-graph-decay-recovering"
		>
			{{ strings.recovering }}
		</div>
	</div>
</template>

<script>
import numbers from '@/vue/utils/numbers'
import { DateTime } from 'luxon'
import VueApexCharts from 'vue3-apexcharts'
export default {
	components : {
		apexchart : VueApexCharts
	},
	props : {
		points : {
			type     : Object,
			required : true
		},
		peak : {
			type : Number,
			default () {
				return 0
			}
		},
		recovering : {
			type : Boolean,
			default () {
				return false
			}
		},
		height : {
			type : Number,
			default () {
				return 50
			}
		}
	},
	data () {
		return {
			strings : {
				recovering : this.$t.__('Slowly Recovering', this.$td),
				peak       : this.$t.__('Peak', this.$td)
			}
		}
	},
	computed : {
		getSeries () {
			const points = this.points
			const series = []

			Object.keys(points).forEach(key => {
				series.push({
					x : key,
					y : points[key]
				})
			})

			return [ {
				data : series
			} ]
		},
		chartOptions () {
			const peak = this.peak

			return {
				colors : [ function ({ value }) {
					return value === peak ? '#005AE0' : '#99C2FF'
				} ],
				chart : {
					type      : 'bar',
					sparkline : {
						enabled : true
					},
					zoom : {
						enabled : false
					},
					toolbar : {
						show : false
					},
					parentHeightOffset : 0,
					background         : '#fff'
				},
				grid : {
					show    : false,
					padding : {
						top    : 2,
						right  : 2,
						bottom : 0,
						left   : 2
					}
				},
				plotOptions : {
					bar : {
						columnWidth : '85%',
						barHeight   : '100%'
					}
				},
				fill : {
					type : 'solid'
				},
				tooltip : {
					enabled : true,
					x       : {
						show      : true,
						formatter : (seriesName) => {
							return DateTime.fromFormat(seriesName, 'yyyy-MM').setZone(DateTime.zone).toLocaleString({ month: 'long', year: 'numeric' })
						}
					},
					y : {
						formatter : (seriesName) => {
							const points = this.$t.sprintf(
								// Translators: 1 - The number of points.
								this.$t.__('%1$s points', this.$td),
								numbers.numberFormat(seriesName, 0)
							)

							let peakMarker = ''
							if (seriesName === peak) {
								peakMarker = `<span class="peak">${this.strings.peak}</span>`
							}

							return points + peakMarker
						}
					},
					marker : {
						show : false
					}
				}
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-graph-decay {
	position: relative;

	&-chart {
		border: 1px solid #CCE0FF;
	}

	&-recovering {
		font-weight: 700;
		font-size: 9px;
		line-height: 120%;
		color: $orange;
		margin-top: 4px;
	}

	.apexcharts-tooltip {
		&-title {
			background: #fff !important;
			border-bottom: 0 !important;
			padding: 10px 10px 0;
			margin-bottom: 0;
			font-weight: 700;
			font-size: 12px;
			line-height: 18px;
		}

		&-text {
			&-y-label {
				display: none;
			}

			&-y-value {
				margin-left: 0;
				font-weight: 400;

				.peak {
					background: rgb( $green, 0.2 );
					border-radius: 4px;
					padding: 2px 6px;
					margin-left: 4px;
					display: inline-block;
					vertical-align: middle;

					color: $green3;
					font-weight: 700;
					font-size: 12px;
					text-transform: uppercase;
				}
			}
		}
	}
}
</style>