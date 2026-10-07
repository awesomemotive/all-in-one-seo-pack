<template>
	<div class="aioseo-processing-popup">
		<div class="aioseo-processing-popup__head">
			<span class="aioseo-processing-popup__phase">{{ phaseLabel }}</span>

			<svg-close @click.native="$emit('close')" />
		</div>

		<div class="aioseo-processing-popup__title">{{ title }}</div>

		<p class="aioseo-processing-popup__counts">{{ countsLine }}</p>

		<div class="aioseo-processing-popup__bar">
			<div
				class="aioseo-processing-popup__fill"
				:style="{ width: boundedPercentage + '%' }"
			/>
		</div>

		<div
			v-if="1 < phases.length"
			class="aioseo-processing-popup__steps"
		>
			<span
				v-for="(step, index) in phases"
				:key="step.key"
				class="aioseo-processing-popup__step"
				:class="stepState(index)"
			>
				<span class="aioseo-processing-popup__pip">{{ isComplete || index < activeIndex ? '✓' : index + 1 }}</span>

				{{ step.label }}
			</span>
		</div>

		<div class="aioseo-processing-popup__foot">{{ strings.footer || defaultFooter }}</div>
	</div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue'

import numbers from '@/vue/utils/numbers'

import SvgClose from '@/vue/components/common/svg/Close'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const emit = defineEmits([ 'close' ])

const props = defineProps({
	strings : {
		type    : Object,
		default : () => ({})
	},
	percentage : {
		type    : Number,
		default : 0
	},
	// Optional throughout: a caller with no counts falls back to the percentage, and one with a single
	// phase gets no step row. Without that the component could not be shared.
	done : {
		type    : Number,
		default : null
	},
	total : {
		type    : Number,
		default : null
	},
	unit : {
		type    : String,
		default : ''
	},
	phases : {
		type    : Array,
		default : () => []
	},
	phase : {
		type    : String,
		default : ''
	}
})

const defaultFooter = __('Runs in the background — you can leave this page.', td)

// Clicking away closes it, as it does for every other panel in the app. The control that opens it is
// excluded, or its own click would close what it just opened.
const onDocumentPointerDown = (event) => {
	if (event.target.closest('.aioseo-processing-popup') || event.target.closest('.aioseo-percent-circle')) {
		return
	}

	emit('close')
}

const onKeydown = (event) => {
	if ('Escape' === event.key) {
		emit('close')
	}
}

onMounted(() => {
	document.addEventListener('mousedown', onDocumentPointerDown)
	document.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
	document.removeEventListener('mousedown', onDocumentPointerDown)
	document.removeEventListener('keydown', onKeydown)
})

const boundedPercentage = computed(() => Math.min(100, Math.max(0, Math.round(props.percentage) || 0)))

const activeIndex = computed(() => {
	const index = props.phases.findIndex(step => step.key === props.phase)

	return -1 === index ? 0 : index
})

// The control stays in the bar once everything is done, so the panel has to have something to say
// about that rather than reporting the last phase as though it were still running.
const isComplete = computed(() => {
	if (100 !== boundedPercentage.value) {
		return false
	}

	return 2 > props.phases.length || activeIndex.value === props.phases.length - 1
})

// The in-progress heading contradicts the panel once everything is done, and the control stays in the
// bar to be opened in that state.
const title = computed(() => {
	if (isComplete.value && props.strings.headerDone) {
		return props.strings.headerDone
	}

	return props.strings.header
})

const phaseLabel = computed(() => {
	if (isComplete.value) {
		return props.strings.done || __('Done', td)
	}

	if (1 < props.phases.length) {
		return sprintf(
			// Translators: 1 - The current step number, 2 - The total number of steps.
			__('Step %1$s of %2$s', td),
			activeIndex.value + 1,
			props.phases.length
		)
	}

	return props.strings.phase || __('Scanning', td)
})

// The count is what the reader wants; the percentage is what we have when there is no count.
const countsLine = computed(() => {
	if (null === props.done || null === props.total || !props.total) {
		return sprintf(
			// Translators: 1 - A percentage.
			__('%1$s%% complete', td),
			boundedPercentage.value
		)
	}

	return sprintf(
		// Translators: 1 - A number done, 2 - A number in total, 3 - What is being counted.
		__('%1$s of %2$s %3$s', td),
		numbers.numberFormat(props.done, 0),
		numbers.numberFormat(props.total, 0),
		props.unit
	).trim()
})

const stepState = (index) => {
	if (isComplete.value || index < activeIndex.value) {
		return 'is-done'
	}

	return index === activeIndex.value ? 'is-active' : ''
}
</script>

<style lang="scss">
.aioseo-processing-popup {
	position: absolute;
	top: calc(100% + 10px);
	right: 0;
	z-index: 1001;
	width: 340px;
	max-width: calc(100vw - 40px);
	overflow: hidden;
	border: 1px solid $input-border;
	border-radius: 10px;
	background: #fff;
	box-shadow: 0 10px 30px rgba(20, 27, 56, .14);
	color: $black;
	text-align: left;
	cursor: default;

	.aioseo-processing-popup__head {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: 12px;
		padding: 14px 16px 0;
	}

	.aioseo-processing-popup__phase {
		font-size: 10px;
		font-weight: 700;
		letter-spacing: .07em;
		text-transform: uppercase;
		color: $blue;
	}

	> .aioseo-processing-popup__head > svg {
		flex: 0 0 auto;
		width: 10px;
		height: 10px;
		min-width: 10px;
		color: $placeholder-color;
		cursor: pointer;

		&:hover {
			color: $black;
		}
	}

	.aioseo-processing-popup__title {
		margin: 6px 16px 2px;
		font-size: 14px;
		font-weight: 700;
		color: $black;
	}

	.aioseo-processing-popup__counts {
		margin: 0 16px 12px;
		font-size: 13px;
		color: $black2;
		font-variant-numeric: tabular-nums;
	}

	.aioseo-processing-popup__bar {
		height: 6px;
		margin: 0 16px;
		overflow: hidden;
		border-radius: 999px;
		background: $gray4;
	}

	.aioseo-processing-popup__fill {
		height: 100%;
		border-radius: 999px;
		background: $blue;
		transition: width .45s ease;
	}

	.aioseo-processing-popup__steps {
		display: flex;
		gap: 6px;
		margin: 14px 16px 0;
	}

	.aioseo-processing-popup__step {
		display: flex;
		flex: 1;
		align-items: center;
		gap: 7px;
		font-size: 11px;
		font-weight: 600;
		color: $placeholder-color;

		&.is-done {
			color: $black2;

			.aioseo-processing-popup__pip {
				background: $green;
				color: #fff;
			}
		}

		&.is-active {
			color: $black;

			.aioseo-processing-popup__pip {
				background: $blue;
				color: #fff;
			}
		}
	}

	.aioseo-processing-popup__pip {
		display: grid;
		flex: 0 0 auto;
		place-items: center;
		width: 16px;
		height: 16px;
		border-radius: 50%;
		background: $gray4;
		font-size: 9px;
		font-weight: 700;
		color: $placeholder-color;
		// !important because the app sets line-height on everything through
		// `*:not(.aioseo-button):not(.aioseo-input)`, which is three classes deep and beats any selector
		// this file can write. A 1.4 line box on a 9px glyph sat it 0.8px above the circle's centre.
		line-height: 1 !important;
	}

	.aioseo-processing-popup__foot {
		margin-top: 14px;
		padding: 10px 16px;
		border-top: 1px solid $gray2;
		background: #FAFBFC;
		font-size: 12px;
		line-height: 1.5;
		color: $placeholder-color;
	}

	@media screen and (max-width: 782px) {
		width: 300px;
	}
}

.fade-processing-popup-enter-active, .fade-processing-popup-leave-active {
	transition: opacity .25s, transform .25s ease-in-out;
}
.fade-processing-popup-enter-from, .fade-processing-popup-leave-to {
	opacity: 0;
	transform: translate3d(0, -8px, 0);
}
</style>