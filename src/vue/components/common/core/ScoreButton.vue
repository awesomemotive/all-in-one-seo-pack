<template>
	<div
		class="aioseo-score-button"
		:class="scoreClass"
		:id="buttonId"
	>
		<slot name="icon" />

		{{ (score === 0) ? 'N/A' : `${score}/100` }}
	</div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
	score : {
		type    : Number,
		default : 0
	},
	postId : {
		type    : Number,
		default : 0
	}
})

const buttonId = computed(() => {
	return 0 < props.postId ? `score-button-${props.postId}` : null
})

const scoreClass = computed(() => {
	if (80 < props.score) {
		return 'score-green'
	}

	if (50 < props.score) {
		return 'score-orange'
	}

	if (1 < props.score) {
		return 'score-red'
	}

	return 'score-disabled'
})
</script>

<style lang="scss">
.aioseo-score-settings {
	display: flex;
	align-items: center;
	padding-bottom: 14px;

	svg {
		margin-right: 7px;
	}

	span {
		margin-right: 12px;
	}
}

.aioseo-score-button {
	display: inline-flex;
	flex-direction: row;
	justify-content: center;
	align-items: center;
	gap: 4px;
	padding: 5px 8px;
	font-size: 14px;
	font-weight: 700;
	line-height: 125%;
	color: #a1a1a1;
	border: 1px solid #a1a1a1;
	border-radius: 2px;

	svg {
		height: 16px;
		vertical-align: middle;
	}

	&.score-red,
	&.score-none {
		border-color: $red;
		color: $red !important;
	}

	&.score-orange {
		border-color: $orange;
		color: $orange !important;
	}

	&.score-green {
		border-color: $green;
		color: $green !important;
	}

	&--active {
		&.score-red,
		&.score-none {
			background: $red !important;
			color: $white !important;
			border-color: $white;
		}

		&.score-orange {
			background: $orange !important;
			color: $white !important;
			border-color: $white;
		}

		&.score-green {
			background: $green !important;
			color: $white !important;
			border-color: $white;
		}
	}

	&.classic-editor {
		background: #fff !important;
		display: inline-block !important;
		height: auto !important;

		span {
			margin-right: 0;
		}
	}
}
</style>