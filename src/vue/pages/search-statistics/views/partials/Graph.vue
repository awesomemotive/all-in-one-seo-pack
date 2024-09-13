<template>
	<div
		v-if="isMounted"
		class="aioseo-graph"
	>
		<apexchart
			width="100%"
			:height="height"
			ref="apexchart"
			:options="chartOptions"
			:series="getSeries"
			:class="chartClasses"
		/>

		<core-loader v-if="loading" dark />

		<core-popper
			ref="timelineMarkersPopper"
			:options="{ placement : 'top' }"
			@show="handleTimelineMarkersTooltip"
			@hide="handleTimelineMarkersTooltip"
		>
			<span class="popper">
				<graph-timeline-markers
					v-if="timelineMarkersDate"
					:date="timelineMarkersDate"
					:timelineMarkers="timelineMarkers"
					@update="handleTimelineMarkersTooltipUpdate"
				/>
			</span>
		</core-popper>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import numbers from '@/vue/utils/numbers'
import { getAssetUrl } from '@/vue/utils/helpers'
import dateFormat from '@/vue/utils/dateFormat'
import VueApexCharts from 'vue3-apexcharts'
import CoreLoader from '@/vue/components/common/core/Loader'
import CorePopper from '@/vue/components/common/core/Popper'
import GraphTimelineMarkers from './AIOSEO_VERSION/GraphTimelineMarkers'
import SvgGoogle from '@/vue/assets/images/logos/google.svg'
import SvgWordPress from '@/vue/assets/images/logos/wordpress.svg'
import SvgAioseo from '@/vue/assets/images/logos/aioseo.svg'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		apexchart : VueApexCharts,
		CoreLoader,
		CorePopper,
		GraphTimelineMarkers
	},
	props : {
		series : {
			type     : Array,
			required : true
		},
		chartOverrides : {
			type    : Object,
			default : () => ({})
		},
		height : {
			type : Number,
			default () {
				return 350
			}
		},
		legendStyle : {
			type : String,
			default () {
				return 'custom'
			}
		},
		loading : {
			type    : Boolean,
			default : false
		},
		timelineMarkers : {
			type    : Object,
			default : () => ({})
		},
		multiAxis   : Boolean,
		preset      : String,
		invertYAxis : Boolean
	},
	data () {
		return {
			isMounted     : false,
			reversedYAxis : false,
			colors        : [ '#005AE0', '#00AA63', '#F18200', '#DF2A4A', '#8B5CF6', '#D946EF' ],
			presets       : {
				overview : {
					chart : {
						type      : 'area',
						sparkline : {
							enabled : true
						}
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
					xaxis : {
						show : false
					},
					yaxis : {
						show   : false,
						labels : {
							show      : false,
							formatter : (val) => {
								return val ? numbers.compactNumber(val) : 0
							}
						}
					}
				},
				keywordsDistribution : {
					chart : {
						type : 'bar',
						zoom : {
							enabled : false
						},
						toolbar : {
							show : false
						}
					},
					fill : {
						type : 'solid'
					},
					stroke : {
						width : 0
					},
					xaxis : {
						type : 'category'
					},
					yaxis : {
						forceNiceScale : false,
						tickAmount     : 2,
						max            : 100,
						labels         : {
							formatter : (value) => value.toFixed(0) + '%'
						}
					},
					tooltip : {}
				}
			},
			timelineMarkersDate : null
		}
	},
	computed : {
		getSeries () {
			const series = this.series

			if (!this.invertYAxis || !series.length) {
				return series
			}

			// Extract the positions from the intervals.
			const positions         = series[0].data.map((interval) => interval.y)
			const reversedPositions = []

			// Convert positions to an array of objects with a value and index property.
			const positionsWithIndex = positions.map((value, index) => ({ value, index }))

			// Sort the array of objects by value.
			let sortedPositions = positionsWithIndex.sort((a, b) => a.value - b.value)

			// Find the first and last values in the numbers array and add them to the reversedPositions array.
			const actuallyReverseNumbers = (fnNum) => {
				reversedPositions[fnNum[0].index] = fnNum[fnNum.length - 1].value
				reversedPositions[fnNum[fnNum.length - 1].index] = fnNum[0].value

				// Remove the first and last values from the fnNum array.
				fnNum = fnNum.slice(1, fnNum.length - 1)

				return fnNum
			}

			// While the reversedPositions array is not full, find the next highest and lowest values and add them to the reversedPositions array.
			while (sortedPositions.length) {
				sortedPositions = actuallyReverseNumbers(sortedPositions)
			}

			// Loop through the intervals and update the position from the reversedPositions array.
			series[0].data.forEach((interval, index) => {
				interval.label = interval.y
				interval.y     = reversedPositions[index]
			})

			return series
		},
		chartDefaults () {
			const series = this.series

			return {
				colors : this.colors,
				chart  : {
					type : 'area',
					zoom : {
						enabled : false
					},
					toolbar : {
						show : false
					},
					animations : {
						enabled          : true,
						easing           : 'easeout',
						speed            : 600,
						animateGradually : {
							enabled : true,
							delay   : 50
						}
					},
					parentHeightOffset : 0
				},
				fill : {
					type     : 'gradient',
					gradient : {
						shadeIntensity : 1,
						opacityFrom    : 0.4,
						opacityTo      : 0.9,
						stops          : [ 0, 100 ]
					}
				},
				dataLabels : {
					enabled : false
				},
				stroke : {
					curve : 'smooth',
					width : 2
				},
				title : {
					show : false
				},
				grid : {
					show            : true,
					strokeDashArray : 0,
					borderColor     : '#D0D1D7',
					yaxis           : {
						lines : {
							show : true
						}
					},
					xaxis : {
						lines : {
							show : false
						}
					},
					padding : {
						top    : 20,
						right  : 20,
						bottom : 20,
						left   : 20
					}
				},
				xaxis : {
					type   : 'datetime',
					labels : {
						show              : true,
						minHeight         : 35,
						trim              : false,
						rotateAlways      : false,
						offsetY           : 6,
						datetimeFormatter : {
							year  : 'yyyy',
							month : 'MMM \'yy',
							day   : 'd MMM',
							hour  : '' // We just have date time ranges, leave it empty.
						}
					},
					tooltip : {
						enabled : false,
						x       : {
							formatter : (seriesName, opts) => {
								const newVal = new Date(`${series[opts.seriesIndex]?.data[opts.dataPointIndex]?.x} 00:00:00`)
								return dateFormat(newVal, this.rootStore.aioseo.data.dateFormat)
							}
						}
					},
					axisBorder : {
						show    : true,
						color   : '#D0D1D7',
						height  : 1,
						width   : '100%',
						offsetX : 0,
						offsetY : 0
					},
					axisTicks : {
						show       : true,
						borderType : 'solid',
						color      : '#E8E8EB',
						height     : 12,
						offsetX    : 0,
						offsetY    : 0
					}
				},
				yaxis : [
					{
						labels : {
							show      : true,
							formatter : (val, index, options) => {
								if (!this.invertYAxis || !options?.config) {
									return val ? numbers.compactNumber(val) : 0
								}

								const yAxis = [ ...options?.globals?.yAxisScale[0].result ].reverse()
								if (yAxis[index]) {
									val = yAxis[index]
								}

								return val ? numbers.compactNumber(val) : 0
							}
						}
					}
				],
				tooltip : {
					enabled : true,
					x       : {
						formatter : (seriesName, opts) => {
							const newVal = new Date(`${series[opts.seriesIndex]?.data[opts.dataPointIndex]?.x} 00:00:00`)
							return dateFormat(newVal, this.rootStore.aioseo.data.dateFormat)
						}
					},
					y : {
						formatter : (seriesName, opts) => {
							return this.invertYAxis && series[opts.seriesIndex]?.data[opts.dataPointIndex].label
								? series[opts.seriesIndex]?.data[opts.dataPointIndex].label
								: numbers.compactNumber(series[opts.seriesIndex]?.data[opts.dataPointIndex].y)
						}
					}
				},
				legend : {
					show                : true,
					showForSingleSeries : false,
					showForNullSeries   : true,
					showForZeroSeries   : true,
					position            : 'bottom',
					horizontalAlign     : 'center',
					floating            : false,
					fontSize            : '14px',
					fontWeight          : 400,
					formatter           : (seriesName, opts) => {
						const name = series[opts.seriesIndex]?.legend?.name || seriesName
						if ('simple' === this.legendStyle) {
							return [ name ]
						}

						let total = series[opts.seriesIndex]?.legend?.total || ''
						if (!isNaN(total)) {
							total = numbers.compactNumber(total)
						}

						return [ `<strong>${total}</strong>`, name ]
					},
					inverseOrder          : false,
					width                 : undefined,
					height                : undefined,
					tooltipHoverFormatter : undefined,
					customLegendItems     : [],
					offsetX               : 0,
					offsetY               : 0,
					markers               : {
						width       : 16,
						height      : 16,
						strokeWidth : 0,
						strokeColor : '#fff',
						fillColors  : undefined,
						radius      : 16,
						customHTML  : () => {
							if ('simple' === this.legendStyle) {
								return ''
							}

							return '<span class="marker-checkbox"><svg viewBox="0 0 14 11" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.8542 1.37147C11.44 0.785682 12.3897 0.785682 12.9755 1.37147C13.5613 1.95726 13.5613 2.907 12.9755 3.49279L6.04448 10.4238C5.74864 10.7196 5.35996 10.8661 4.97222 10.8631C4.58548 10.8653 4.19805 10.7189 3.90298 10.4238L1.0243 7.5451C0.438514 6.95931 0.438514 6.00956 1.0243 5.42378C1.61009 4.83799 2.55983 4.83799 3.14562 5.42378L4.97374 7.2519L10.8542 1.37147Z" fill="currentColor" /></svg></span>'
						},
						onClick : undefined,
						offsetX : 0,
						offsetY : 0
					},
					itemMargin : {
						horizontal : 0,
						vertical   : 0
					},
					onItemClick : {
						toggleDataSeries : true
					},
					onItemHover : {
						highlightDataSeries : true
					}
				},
				annotations : {
					points : this.annotationsPoints
				}
			}
		},
		multiAxisDefaults () {
			const series = this.series

			const defaults = {
				yaxis : []
			}

			series.forEach((s, i) => {
				defaults.yaxis.push({
					title : {
						text : s.name.replace('Search ', '')
					},
					seriesName : s.name,
					opposite   : 1 === i,
					labels     : {
						show      : true,
						formatter : (val) => {
							return val ? numbers.compactNumber(val) : 0
						}
					}
				})
			})

			return defaults
		},
		annotationsPoints () {
			const points = []

			if (this.timelineMarkers) {
				Object.keys(this.timelineMarkers).forEach(key => {
					const point = {
						x           : new Date(key).getTime(),
						y           : 0,
						yAxisIndex  : 0,
						seriesIndex : 0,
						mouseEnter  : (_pointer, event) => {
							let target = event.target

							if ('circle' === event.relatedTarget.tagName.toLowerCase()) {
								target = event.relatedTarget
							}

							this.timelineMarkersDate = key
							this.showTimelineMarkersTooltip(target)
						},
						mouseLeave : (_pointer, event) => {
							if ('string' === event.toElement.className && event.toElement.className.includes('popper')) {
								return
							}

							this.$refs.timelineMarkersPopper.doClose()
						},
						label : {
							text        : this.timelineMarkers[key].length,
							borderWidth : 0,
							offsetY     : 23,
							style       : {
								background : 'transparent',
								color      : '#141B38',
								fontSize   : '12px',
								fontWeight : 700
							}
						},
						marker : {
							size        : 12,
							strokeWidth : 1,
							strokeColor : '#D0D1D7',
							cssClass    : 'marker-circle'
						},
						image : {
							width  : 17,
							height : 17
						}
					}

					const types = this.timelineMarkers[key].map(a => a.type)
					if (1 === types.length) {
						point.label = {}

						switch (types[0]) {
							case 'aioseoRevision':
								point.image.path = getAssetUrl(SvgAioseo)
								break
							case 'googleUpdate':
								point.image.path = getAssetUrl(SvgGoogle)
								break
							case 'wpRevision':
								point.image.path = getAssetUrl(SvgWordPress)
								break
						}
					}

					points.push(point)
				})
			}

			return points
		},
		chartPreset () {
			if (this.preset && this.presets[this.preset]) {
				return this.presets[this.preset]
			}

			return {}
		},
		chartOptions () {
			let options = {
				...this.chartDefaults,
				...this.chartPreset,
				...this.chartOverrides
			}

			if (this.multiAxis) {
				options = {
					...options,
					...this.multiAxisDefaults
				}
			}

			return options
		},
		chartClasses () {
			const seriesCount = this.series.length
			let legendColumns = 4
			if (4 < seriesCount) {
				legendColumns = 3
			}

			return [
				this.loading ? 'blurred' : '',
				this.preset ? this.preset : '',
				`legend-${this.legendStyle}`,
				`legend-columns-${legendColumns}`
			].filter(n => n).map(className => 'aioseo-graph-' + className)
		}
	},
	methods : {
		handleTimelineMarkersTooltip (tooltip) {
			tooltip.referenceElm?.classList.remove('active-point')

			if (tooltip.showPopper) {
				tooltip.referenceElm?.classList.add('active-point')
			}
		},
		handleTimelineMarkersTooltipUpdate (args) {
			const tooltip = this.$refs.timelineMarkersPopper
			tooltip.updatePopper()

			// Close the tooltip if the modal is open.
			args.modal ? tooltip.doClose() : tooltip.doShow()

			this.handleTimelineMarkersTooltip(tooltip)
		},
		showTimelineMarkersTooltip (reference) {
			const tooltip = this.$refs.timelineMarkersPopper

			tooltip.referenceElm?.classList.remove('active-point')
			reference?.classList.add('active-point')

			// If the reference element changed, we need to recreate the instance.
			tooltip.destroyPopper()
			tooltip.doDestroy()

			tooltip.referenceElm = reference

			tooltip.createPopper()
			tooltip.doShow()
		}
	},
	// The following was added to prevent errors when loading the post detail page in search statistics.
	// The errors would look similar to: Error: <g> attribute transform: Expected number, "translate(NaN, 0)".
	// By adding the mounted and beforeUnmount hooks, we can prevent the errors from occurring.
	// See: https://github.com/apexcharts/vue-apexcharts/issues/108
	mounted () {
		this.isMounted = true
	},
	beforeUnmount () {
		this.isMounted = false
	}
}
</script>

<style lang="scss">
.aioseo-graph {
	position: relative;

	.apexcharts-legend {
		padding: 0;
		inset: auto 0 0 0 !important;
	}

	.apexcharts-point-annotations {
		cursor: pointer;

		text,
		rect {
			pointer-events: none;
		}

		.active-point {
			stroke: $blue;
			fill: $blue4;
		}

		.marker-circle {
			pointer-events: none;
		}
	}

	.aioseo-loading-spinner {
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		margin: auto;
	}

	&-blurred {
		filter: blur(2px);
		pointer-events: none;
		user-select: none;
	}

	&-legend-simple {
		.apexcharts-legend {
			display: flex;
			flex-direction: row;
			gap: 12px;

			&-marker {
				width: 12px !important;
				height: 12px !important;
				margin-right: 8px;
			}
		}
	}

	&-legend-custom {
		.apexcharts-legend {
			display: grid;
			grid-column-gap: 16px;
			grid-row-gap: 16px;
			$gap: 8px;
			overflow-y: hidden;

			&-series {
				border: 1px solid $blue;
				border-radius: 2px;
				position: relative;
				padding: $gap $gap $gap 32px;
				margin: 0;

				&.apexcharts-inactive-legend {
					opacity: 1;
					border-color: $input-border;
				}

				.apexcharts-legend-text {
					display: inline-flex;
					line-height: 125%;
					font-size: 14px;
					color: $black2-hover;

					strong {
						margin-right: 8px;
						font-size: 16px;
						color: $black;
					}
				}
			}

			&-marker {
				.marker-checkbox {
					position: absolute;
					cursor: pointer;
					top: 0;
					left: -24px;
					width: 16px;
					height: 16px;
					background-color: $blue;
					transition: 0.2s;
					border-radius: 3px;
					display: flex;
					align-items: center;
					justify-content: center;

					&::before {
						position: absolute;
						content: "";
						height: 14px;
						width: 14px;
						bottom: 1px;
						transition: 0.2s;
						font-size: 18px;
						line-height: 28px;
						border-radius: 2px;
					}

					svg {
						color: $white;
						width: 12px;
						height: 12px;
					}
				}

				&.apexcharts-inactive-legend {
					opacity: 1;

					.marker-checkbox {
						background-color: $input-border;

						&::before {
							background-color: $white;
						}
					}
				}
			}

			&-text {
				position: absolute;
				left: 0;
				padding-left: 72px;
				padding-top: $gap;
				padding-bottom: $gap;
				padding-right: $gap;
				width: 100%;

				strong {
					display: inline-block;
					font-size: 16px;
					vertical-align: middle;
					margin-right: 4px;
				}
			}
		}
	}

	&-legend-columns-4 {
		.apexcharts-legend {
			grid-template-columns: repeat(4, 1fr);
		}
	}

	&-legend-columns-3 {
		.apexcharts-legend {
			grid-template-columns: repeat(3, 1fr);
		}
	}
}
</style>