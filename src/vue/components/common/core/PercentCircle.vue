<template>
	<div
		class="aioseo-percent-circle"
		:class="{ 'is-alert' : alert }"
	>
		<span class="aioseo-percent-circle__ring">
			<svg
				viewBox="0 0 33.83098862 33.83098862"
				xmlns="http://www.w3.org/2000/svg"
			>
				<circle
					class="aioseo-percent-circle__track"
					fill="none"
					stroke-width="3"
					cx="16.91549431"
					cy="16.91549431"
					r="15.91549431"
				/>

				<circle
					class="aioseo-percent-circle__arc"
					fill="none"
					stroke-width="3"
					stroke-linecap="round"
					:stroke-dasharray="alert ? '100,100' : `${boundedPercentage},100`"
					cx="16.91549431"
					cy="16.91549431"
					r="15.91549431"
				/>
			</svg>

			<svg
				v-if="alert"
				class="aioseo-percent-circle__alert"
				viewBox="0 0 24 24"
				fill="none"
				aria-hidden="true"
			>
				<path
					d="M12 6.4v7.1"
					stroke="currentColor"
					stroke-width="2.8"
					stroke-linecap="round"
				/>

				<circle
					cx="12"
					cy="18.1"
					r="1.5"
					fill="currentColor"
				/>
			</svg>

			<svg
				v-else-if="isComplete"
				class="aioseo-percent-circle__check"
				viewBox="0 0 24 24"
				fill="none"
				aria-hidden="true"
			>
				<path
					d="m6 12.4 4.2 4.1 7.8-9"
					stroke="currentColor"
					stroke-width="2.8"
					stroke-linecap="round"
					stroke-linejoin="round"
				/>
			</svg>
		</span>

		<span class="aioseo-percent-circle__text">
			<span class="aioseo-percent-circle__value">{{ value }}</span>

			<span
				v-if="label"
				class="aioseo-percent-circle__label"
			>{{ label }}</span>
		</span>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import numbers from '@/vue/utils/numbers'

const props = defineProps({
	percentage : {
		type    : Number,
		default : 0
	},
	// The figure the reader actually cares about. Falls back to the percentage when a caller has none.
	done : {
		type    : Number,
		default : null
	},
	total : {
		type    : Number,
		default : null
	},
	label : {
		type    : String,
		default : ''
	},
	// Something is wrong rather than merely unfinished — BLC's monthly quota being spent, where the ring
	// has no progress left to report.
	alert : {
		type    : Boolean,
		default : false
	}
})

// A late animation frame can carry an elapsed time past the tween's duration, and the arc length is
// drawn straight from this.
const boundedPercentage = computed(() => Math.min(100, Math.max(0, Math.round(props.percentage) || 0)))

// A closed ring reads much like an empty one at a glance, so the finished state says so outright.
const isComplete = computed(() => 100 === boundedPercentage.value)

// Rendered beside the ring rather than inside it: at this diameter the ring cannot hold four
// characters, which is what made an out-of-range figure spill across the icons next to it.
const value = computed(() => {
	if (null === props.done || null === props.total || !props.total) {
		return boundedPercentage.value + '%'
	}

	return numbers.numberFormat(props.done, 0) + ' / ' + numbers.numberFormat(props.total, 0)
})
</script>

<style lang="scss">
.aioseo-percent-circle {
	display: inline-flex;
	align-items: center;
	gap: 10px;
	height: 36px;
	padding: 0 12px 0 6px;
	border: 1px solid $input-border;
	border-radius: 999px;
	background: #fff;
	cursor: pointer;

	&:hover {
		border-color: #9FC0F5;
		background: $blue4;
	}

	.aioseo-percent-circle__ring {
		position: relative;
		flex: 0 0 auto;
		width: 24px;
		height: 24px;

		svg {
			display: block;
			width: 24px;
			height: 24px;
			transform: rotate(-90deg);
		}

		// The element selector is load-bearing: the rule above turns every svg in the ring a quarter turn
		// to put the arc's start at the top, and at equal specificity it would turn the mark too.
		svg.aioseo-percent-circle__check,
		svg.aioseo-percent-circle__alert {
			position: absolute;
			top: 50%;
			left: 50%;
			width: 13px;
			height: 13px;
			transform: translate(-50%, -50%);
			color: $blue;
		}
	}

	.aioseo-percent-circle__track {
		stroke: #DCE8FB;
	}

	.aioseo-percent-circle__arc {
		stroke: $blue;
		transition: stroke-dasharray .45s ease;
	}

	&.is-alert {
		border-color: #F4C0C9;
		background: #FDF3F5;

		&:hover {
			border-color: $red;
			background: #FDE7EB;
		}

		.aioseo-percent-circle__track {
			stroke: #F8D5DC;
		}

		.aioseo-percent-circle__arc {
			stroke: $red;
		}

		svg.aioseo-percent-circle__alert {
			color: $red;
		}

		.aioseo-percent-circle__value {
			color: $red2;
		}
	}

	.aioseo-percent-circle__text {
		display: flex;
		flex-direction: column;
		line-height: 1.2;
	}

	.aioseo-percent-circle__value {
		font-size: 12px;
		font-weight: 700;
		color: $black;
		font-variant-numeric: tabular-nums;
		white-space: nowrap;
	}

	.aioseo-percent-circle__label {
		font-size: 10px;
		font-weight: 600;
		letter-spacing: .05em;
		text-transform: uppercase;
		color: $placeholder-color;
		white-space: nowrap;
	}
}
</style>