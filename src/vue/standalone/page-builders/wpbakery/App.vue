<template>
	<div class="aioseo-wpbakery-integration">
		<core-score-button
			v-if="isFrontendEditor"
			:show-score="truSeoShouldAnalyze()"
			:score="currentPost.seo_score"
			:class="[
				isModalOpen ? 'aioseo-score-button--active' : ''
			]"
			@click.prevent="isModalOpen = !isModalOpen"
		>
			<template #icon>
				<svg-aioseo-logo-gear />
			</template>
		</core-score-button>

		<Modal
			v-if="isFrontendEditor"
			:score="currentPost.seo_score"
			v-model:is-open="isModalOpen"
		/>

		<div
			v-if="!isFrontendEditor"
			class="aioseo-gear-icon"
		>
			<svg-aioseo-logo-gear />
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { usePostEditorStore } from '@/vue/stores'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'
import Modal from '../Modal'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'

const isModalOpen = ref(false)

const { currentPost } = storeToRefs(usePostEditorStore())

defineProps({
	isFrontendEditor : Boolean
})
</script>

<style lang="scss">
.aioseo-wpbakery-integration {
	padding: 12px 30px;

	> .aioseo-score-button {
		background: $white;
		cursor: pointer;
	}

	.aioseo-gear-icon {
		width: 28px;
		height: 28px;
		color: $white;
		margin: 2px 0;
		cursor: pointer;
	}
}
</style>