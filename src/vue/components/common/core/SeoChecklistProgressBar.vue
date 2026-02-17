<template>
	<div
		class="aioseo-seo-checklist-progress-bar"
		:class="{ 'inline-layout': inlineText }"
	>
		<div class="checklist-count">
			{{ progressText }}<slot />
		</div>

		<core-loading-bar
			:percent="percent"
			:show-number="false"
			:style="progressBarStyle"
		/>
	</div>
</template>

<script setup>
import { computed } from 'vue'
import CoreLoadingBar from '@/vue/components/common/core/LoadingBar'
import { useSeoChecklistStore } from '@/vue/stores/SeoChecklistStore'
import { sprintf } from '@/vue/plugins/translations'

const props = defineProps({
	maxWidth : {
		type    : String,
		default : null
	},
	inlineText : {
		type    : Boolean,
		default : true
	},
	// Translation string for progress text
	progressString : {
		type    : String,
		default : '%1$d / %2$d Tasks Completed'
	}
})

const seoChecklistStore = useSeoChecklistStore()

const completedCount = computed(() => seoChecklistStore.completedCount)
const totalCount = computed(() => seoChecklistStore.totalCount)

const percent = computed(() => {
	if (0 === totalCount.value) {
		return 0
	}

	return Math.round((completedCount.value / totalCount.value) * 100)
})

const progressColor = computed(() => {
	const p = percent.value

	if (25 > p) {
		return '#DF2A4A' // $red
	}

	if (50 > p) {
		return '#F18200' // $orange
	}

	if (75 > p) {
		return '#F5C842' // Warm yellow
	}

	return '#00AA63' // $green
})

const progressText = computed(() => {
	return sprintf(
		props.progressString,
		completedCount.value,
		totalCount.value
	)
})

const progressBarStyle = computed(() => {
	const style = {
		'--progress-color' : progressColor.value
	}

	if (props.maxWidth) {
		style.maxWidth = props.maxWidth
	}

	return style
})
</script>

<style lang="scss">
.aioseo-seo-checklist-progress-bar {
	.checklist-count {
		font-size: 14px;
		font-weight: 600;
		color: $black;
		margin-bottom: 8px;
	}

	.aioseo-loading-bar {
		.aioseo-loading-bar__bar {
			background-color: #D1D5DB;
			height: 12px;
		}

		.aioseo-loading-bar__progress {
			height: 12px;
			background: var(--progress-color, #22C55E);
			transition: background-color 0.3s ease;
		}
	}

	// Inline layout - progress bar and text side by side
	&.inline-layout {
		display: flex;
		align-items: center;
		gap: 16px;

		.checklist-count {
			white-space: nowrap;
			margin-bottom: 0;
			order: 2;
		}

		.aioseo-loading-bar {
			flex: 1;
			order: 1;
		}
	}
}
</style>