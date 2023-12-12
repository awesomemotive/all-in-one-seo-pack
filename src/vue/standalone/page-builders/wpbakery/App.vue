<template>
	<div class="aioseo-wpbakery-integration">
		<core-score-button
			v-if="isFrontendEditor"
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
			v-scroll-to="{
				el: '#aioseo-settings',
				offset: -150,
			}"
		>
			<svg-aioseo-logo-gear />
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { usePostEditorStore } from '@/vue/stores'
import Modal from '../Modal.vue'
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