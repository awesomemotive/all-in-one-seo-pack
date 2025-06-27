<template>
	<div class="aioseo-percent-circle">
		<transition name="fill-percent-circle">
			<svg
				viewBox="0 0 33.83098862 33.83098862"
				xmlns="http://www.w3.org/2000/svg"
			>
				<circle
					class="aioseo-percent-circle-score__background"
					stroke="#BFD6F7"
					stroke-width="2"
					fill="none"
					cx="16.91549431"
					cy="16.91549431"
					r="15.91549431"
				/>

				<circle
					class="aioseo-percent-circle-score__circle"
					stroke="#005AE0"
					stroke-width="2"
					:stroke-dasharray="`${percentage},100`"
					fill="none"
					cx="16.91549431"
					cy="16.91549431"
					r="15.91549431"
				/>
			</svg>
		</transition>

		<div class="aioseo-percent-circle-percent">
			<div>
				<util-animated-number
					:number="number"
					:from-number="fromNumber"
				/>%
			</div>
		</div>
	</div>
</template>

<script>
import UtilAnimatedNumber from '@/vue/components/common/util/AnimatedNumber'
export default {
	components : {
		UtilAnimatedNumber
	},
	props : {
		percentage : Number
	},
	data () {
		return {
			fromNumber : 0,
			number     : 0
		}
	},
	watch : {
		percentage : {
			deep : true,
			handler (percentage) {
				if (percentage !== this.number) {
					this.fromNumber = this.number
					this.number     = percentage
				}
			}
		}
	},
	mounted () {
		this.number = this.percentage
	}
}
</script>

<style lang="scss">
.aioseo-percent-circle {
	width: 40px;
	height: 40px;
	min-width: 40px;
	min-height: 40px;
	position: relative;
	cursor: pointer;

	.aioseo-percent-circle-score__circle {
		transform: rotate(-90deg);
		transform-origin: center;
		stroke: $blue;
	}

	@keyframes aioseo-percent-circle-score-fill {
		to { stroke-dasharray: 0 100; }
	}

	.aioseo-percent-circle-percent {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 12px;
		font-weight: bold;
	}

	.fill-percent-circle-enter-active, .fill-percent-circle-leave-active {
		.aioseo-percent-circle-score__circle {
			transition: stroke-dasharray 1s;
		}
	}
	.fill-percent-circle-enter-from, .fill-percent-circle-leave-to {
		.aioseo-percent-circle-score__circle {
			stroke-dasharray: 0 100;
		}
	}
}
</style>