<template>
	<core-modal
		:show="modalOpen"
		@close="modalOpen = false"
		:classes="[ 'aioseo-bricks-modal' ]"
	>
		<template #headerTitle>
			<div class="modal-header__title">
				{{ strings.modalHeader }}
			</div>

			<core-score-button
				:show-score="truSeoShouldAnalyze()"
				:score="postEditorStore.currentPost.seo_score"
				class="aioseo-score-button--active"
			>
				<template #icon>
					<svg-aioseo-logo-gear
						width="16"
						height="16"
					/>
				</template>
			</core-score-button>
		</template>

		<template #body>
			<div>
				<post-settings />
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue'

import { __, sprintf } from '@/vue/plugins/translations'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'

import {
	usePostEditorStore
} from '@/vue/stores'

import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import PostSettings from '@/vue/standalone/post-settings/App'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'

const postEditorStore = usePostEditorStore()

const td      = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	modalHeader : sprintf(
		// Translators: 1 - The plugin short name ("AIOSEO").
		__('%1$s Settings', td),
		import.meta.env.VITE_SHORT_NAME
	)
}

const modalOpen  = ref(false)

const openModal = async (event) => {
	if (!modalOpen.value) {
		modalOpen.value = true
	}

	// Only switch tabs if explicitly requested (preserves persisted tab for score button).
	const tab = event?.detail?.tab
	if (tab) {
		await nextTick()

		window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: tab })
	}
}

onMounted(() => {
	document.addEventListener('aioseo-pagebuilder-toggle-modal', openModal)
})

onBeforeUnmount(() => {
	document.removeEventListener('aioseo-pagebuilder-toggle-modal', openModal)
})
</script>

<style lang="scss">
.aioseo-bricks-modal {
	.modal-body {
		min-height: min(60vh, 500px);
	}

	.modal-header {
		gap: 10px;
	}
}
</style>