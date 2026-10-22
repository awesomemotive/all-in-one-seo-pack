<template>
	<core-modal
		:show="modalOpen"
		@close="modalOpen = false"
		:classes="[ 'aioseo-divi-modal', { 'aioseo-divi-modal--minimized': minimized } ]"
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
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'

import { __, sprintf } from '@/vue/plugins/translations'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'

import {
	usePostEditorStore
} from '@/vue/stores'

import { isDivi5Wireframe } from '../divi5-helpers'

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

// The modal sits over the canvas, right where a jump lands, so a jump hides it. Hidden rather than
// closed: closing unmounts the panel, and reopening would start over on the first tab.
const minimized = ref(false)

const toggleModal = () => {
	if (minimized.value) {
		minimized.value = false

		return
	}

	modalOpen.value = !modalOpen.value
}

const minimizeModal = () => {
	// Wireframe modules render no text, so the jump has nothing to show behind the modal.
	if (modalOpen.value && !isDivi5Wireframe()) {
		minimized.value = true
	}
}

watch(modalOpen, (isOpen) => {
	if (!isOpen) {
		minimized.value = false
	}
})

watch(() => modalOpen.value && !minimized.value, (isOpen) => {
	document.dispatchEvent(new CustomEvent('aioseo-pagebuilder-modal-state', { detail: { isOpen } }))
})

const handleClickOutside = (event) => {
	if (!modalOpen.value || minimized.value) {
		return
	}

	// Removing a keyword unmounts its row, and the popper tears its teleported menu
	// out of the body-level portal on the same click — so by the time this bubbled
	// handler runs the clicked node is already detached and closest() sees nothing.
	// A detached target is always our own UI reacting to this very click, never a
	// genuine outside click, so treat it as inside.
	if (!event.target.isConnected) {
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
	document.addEventListener('aioseo-truseo-editor-jump', minimizeModal)
	document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
	document.removeEventListener('aioseo-pagebuilder-toggle-modal', toggleModal)
	document.removeEventListener('aioseo-truseo-editor-jump', minimizeModal)
	document.removeEventListener('click', handleClickOutside)
})
</script>

<style lang="scss">
.aioseo-divi-modal {
	&--minimized {
		display: none;
	}

	.modal-body {
		min-height: min(60vh, 500px);
	}

	.modal-header {
		gap: 10px;
	}

	.aioseo-tabs.internal {
		--tab-padding: 16px;

		.tabs-scroller {
			flex-grow: 1;
		}
	}
}
</style>