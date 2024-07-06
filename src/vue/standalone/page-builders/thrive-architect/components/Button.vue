<template>
	<div class="aioseo-thrive-score-button">
		<core-score-button
			:score="currentPost.seo_score"
			:class="[
				'Settings' === buttonContext ? 'aioseo-score-button--active' : '',
			]"
		>
			<template #icon>
				<svg-aioseo-logo-gear />
			</template>
		</core-score-button>
	</div>
</template>

<script setup>
import { defineProps } from 'vue'
import { storeToRefs } from 'pinia'
import { usePostEditorStore } from '@/vue/stores'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'

const { currentPost } = storeToRefs(usePostEditorStore())

defineProps({
	buttonContext : {
		type : String
	}
})

</script>

<style lang="scss">
.aioseo-thrive-score-button {
	> .aioseo-score-button {
		padding: 4px 5px;
		font-size: 13px;

		svg {
			height: 16px;
		}

		&.score-red {
			border-color: $red !important;
		}

		&.score-orange {
			border-color: $orange !important;
		}

		&.score-green {
			border-color: $green !important;
		}
	}
}
</style>