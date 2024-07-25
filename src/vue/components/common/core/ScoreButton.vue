<template>
	<div
		class="aioseo-score-button"
		:class="scoreClass"
		:id="buttonId"
	>
		<slot name="icon" />

		<template v-if="showScore">
			{{ (score === 0) ? 'N/A' : `${score}/100` }}
		</template>
	</div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
	showScore : {
		type    : Boolean,
		default : true
	},
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
	if (!props.showScore) {
		return 'score-disabled'
	}

	if (70 <= props.score) {
		return 'score-green'
	}

	if (40 < props.score) {
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
	border: 1px solid currentcolor;
	border-radius: 2px;

	svg {
		height: 16px;
		vertical-align: middle;
	}

	&.score-red {
		color: $red !important;
	}

	&.score-orange {
		color: $orange !important;
	}

	&.score-green {
		color: $green !important;
	}

	&--active {
		&.score-red {
			background: $red !important;
			color: $white !important;
		}

		&.score-orange {
			background: $orange !important;
			color: $white !important;
		}

		&.score-green {
			background: $green !important;
			color: $white !important;
		}

		&.score-disabled {
			background: $blue !important;
			color: $white !important;
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