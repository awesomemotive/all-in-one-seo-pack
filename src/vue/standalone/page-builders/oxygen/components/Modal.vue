<template>
	<core-modal
		:show="modalOpen"
		@close="modalOpen = false"
		:classes="[ 'aioseo-oxygen-modal' ]"
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
import { ref, onMounted, onBeforeUnmount } from 'vue'

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

const modalOpen = ref(false)

const toggleModal = () => {
	modalOpen.value = !modalOpen.value
}

const checkViewportSize = () => {
	if (!modalOpen.value) {
		return
	}

	try {
		const iframeWidth  = window.innerWidth
		const parentWidth  = window.parent.innerWidth

		// Close modal if the iframe is at least 10px smaller than the parent.
		if (iframeWidth + 10 < parentWidth) {
			modalOpen.value = false
		}
	} catch (e) {
		// Silently fail if we can't access the parent window.
		console.error('Unable to access parent window dimensions:', e)
	}
}

const handleClickOutside = (event) => {
	if (!modalOpen.value) {
		return
	}

	// Don't close if clicking inside any AIOSEO element, popover, media modal, or the toggle button.
	if (
		event.target.closest('.aioseo-app') ||
		event.target.closest('.el-popper') ||
		event.target.closest('.media-modal') ||
		event.target.closest('.aioseo-pagebuilder-toggle-button')
	) {
		return
	}

	modalOpen.value = false
}

onMounted(() => {
	document.addEventListener('aioseo-pagebuilder-toggle-modal', toggleModal)
	window.addEventListener('resize', checkViewportSize)

	try {
		window.parent.document.addEventListener('click', handleClickOutside, true)
	} catch (_e) {
		// Silently fail if we can't access the parent window (cross-origin).
	}
})

onBeforeUnmount(() => {
	document.removeEventListener('aioseo-pagebuilder-toggle-modal', toggleModal)
	window.removeEventListener('resize', checkViewportSize)

	try {
		window.parent.document.removeEventListener('click', handleClickOutside, true)
	} catch (_e) {
		// Silently fail if we can't access the parent window (cross-origin).
	}
})
</script>

<style lang="scss">
.aioseo-oxygen-modal {
	.modal-body {
		min-height: min(60vh, 500px);
	}

	.modal-header {
		gap: 10px;
	}
}
</style>