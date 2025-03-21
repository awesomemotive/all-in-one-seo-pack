<template>
	<div class="aioseo-search-statistics-speed-test">
		<div v-if="loading" class="loading">
			<lottie :options="lottieOptions" :width="60" :height="60" class="loading-animation" />

			<div class="loading-title">{{ strings.loading }}</div>

			<div class="loading-progress">
				<div
					class="loading-progress-value"
					:style="{
						animationDuration : loadTime + 's'
					}"
				/>
			</div>
		</div>

		<template v-if="!loading">
			<div v-if="!isValidResult" class="invalid">
				<div class="invalid-title">{{ strings.unableToCalculate }}</div>

				<div class="invalid-description">{{ strings.pleaceMakeSure }}</div>

				<base-button
					type="blue"
					tag="button"
					size="medium"
					@click="$emit('refresh')"
				>
					{{ strings.refresh }}
				</base-button>
			</div>

			<div v-if="isValidResult" class="result">
				<div class="column">
					<div class="title">{{ strings.desktop }}</div>
					<div
						v-if="pageSpeed.desktop.score"
						class="score"
						:class="getColor(pageSpeed.desktop.score)"
					>
						<div class="icon"><svg-desktop /></div>
						<div class="score">{{ Math.floor( pageSpeed.desktop.score * 100 ) }}</div>
						<div class="time">{{ pageSpeed.desktop.interactive.value }}</div>
					</div>
				</div>
				<div class="column">
					<div class="title">{{ strings.mobile }}</div>
					<div
						v-if="pageSpeed.mobile.score"
						class="score"
						:class="getColor(pageSpeed.mobile.score)"
					>
						<div class="icon"><svg-mobile /></div>
						<div class="score">{{ Math.floor( pageSpeed.mobile.score * 100 ) }}</div>
						<div class="time">{{ pageSpeed.mobile.interactive.value }}</div>
					</div>
				</div>
				<div class="column">
					<div class="title">{{ refreshedString }}</div>

					<base-button
						type="gray"
						tag="button"
						size="medium"
						@click="$emit('refresh')"
					>
						<svg-refresh />
						{{ strings.refresh }}
					</base-button>
				</div>
			</div>
		</template>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import Lottie from '@/vue/components/common/core/Lottie'
import * as rocketAnimation from '@/vue/assets/lottie/rocket-flighting.json'
import dateFormat from '@/vue/utils/dateFormat'
import SvgDesktop from '@/vue/components/common/svg/Desktop'
import SvgMobile from '@/vue/components/common/svg/Mobile'
import SvgRefresh from '@/vue/components/common/svg/Refresh'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'refresh' ],
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		Lottie,
		SvgDesktop,
		SvgMobile,
		SvgRefresh
	},
	props : {
		pageSpeed : {
			type : Object,
			default () {
				return {}
			}
		},
		loading : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	data () {
		return {
			loadTime      : 10,
			lottieOptions : {
				animationData : rocketAnimation
			},
			strings : {
				refresh           : __('Refresh', td),
				desktop           : __('Desktop', td),
				mobile            : __('Mobile', td),
				loading           : __('Running speed test...', td),
				unableToCalculate : __('We were unable to calculate the page speed of this post.', td),
				pleaceMakeSure    : __('Please make sure this post is publicly accessible.', td)
			}
		}
	},
	computed : {
		refreshedString () {
			const refreshed = this.pageSpeed.refreshed
			if (!refreshed) {
				return ''
			}

			const format    = {
				date : this.rootStore.aioseo.data.dateFormat,
				time : this.rootStore.aioseo.data.timeFormat
			}

			return dateFormat(new Date(refreshed), `${format.date} ${format.time}`)
		},
		isValidResult () {
			if (!this.pageSpeed.desktop || !this.pageSpeed.mobile) {
				return false
			}

			if (0 === Object.values(this.pageSpeed.desktop).length || 0 === Object.values(this.pageSpeed.mobile).length) {
				return false
			}

			return true
		}
	},
	methods : {
		getColor (score) {
			score = score * 100
			switch (true) {
				case 30 >= score:
					return 'red'
				case 70 >= score:
					return 'orange'
				default:
					return 'green'
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-speed-test {
	.loading {
		&-animation {
			margin: 0 auto 16px;
		}

		&-title {
			font-weight: 700;
			font-size: 14px;
			text-align: center;
		}

		&-progress {
			margin-top: 16px;
			background: $gray;
			justify-content: flex-start;
			border-radius: 100px;
			align-items: center;
			position: relative;
			padding: 0;
			display: flex;
			height: 10px;
			width: 100%;

			&-value {
				animation: analyze-load 2s normal forwards;
				border-radius: 100px;
				background: $blue;
				height: 10px;
				width: 0;
			}

			@keyframes analyze-load {
				0% { width: 0; }
				100% { width: 100%; }
			}
		}
	}

	.invalid {
		text-align: center;

		&-title {
			font-weight: 700;
			font-size: 14px;
			line-height: 150%;
			margin-bottom: 8px;
		}

		&-description {
			font-weight: 400;
			font-size: 14px;
			line-height: 125%;
			margin-bottom: 16px;
		}
	}

	.result {
		display: flex;
		justify-content: space-between;

		.title {
			font-size: 14px;
			color: $black2;
			margin-bottom: 15px;
		}

		.aioseo-button {
			svg {
				width: 16px;
				height: 16px;
				margin-right: 12px;
				margin-top: 1.5px;
			}
		}

		.score {
			display: flex;
			align-items: center;

			.icon {
				display: flex;
				color: $black2;
				margin-right: 12px;
			}

			.score {
				font-weight: 700;
				font-size: 28px;
				margin-right: 8px;
			}

			.time {
				padding: 2px 8px;
				font-weight: 700;
				font-size: 13px;
				border-radius: 4px;
			}

			&.red {
				.score {
					color: $red;
				}

				.time {
					background-color: rgb( $red, 0.1 );
					color: $red;
				}
			}

			&.orange {
				.score {
					color: $orange;
				}

				.time {
					background-color: rgb( $orange, 0.1 );
					color: $orange;
				}
			}

			&.green {
				.score {
					color: $green;
				}

				.time {
					background-color: rgb( $green, 0.1 );
					color: $green;
				}
			}
		}
	}

	.column {
		&:last-child {
			text-align: right;
		}
	}
}
</style>