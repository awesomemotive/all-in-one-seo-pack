<template>
	<div class="aioseo-avada-integration">
		<core-score-button
			:show-score="truSeoShouldAnalyze()"
			:score="currentPost.seo_score"
			:class="[
				isSidebarActive ? 'aioseo-score-button--active' : '',
			]"
			@click.prevent="toggleSidebar"
		>
			<template #icon>
				<svg-aioseo-logo-gear />
			</template>
		</core-score-button>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { usePostEditorStore } from '@/vue/stores'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'
import emitter from 'tiny-emitter/instance'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'

const isSidebarActive = ref(false)

const { currentPost } = storeToRefs(usePostEditorStore())

const toggleSidebar = () => {
	isSidebarActive.value = !isSidebarActive.value
	const { FusionApp } = window

	if (isSidebarActive.value) {
		document.body.classList.add('aioseo-avada-sidebar--active')

		if (FusionApp.sidebarView.panelIsOpen()) {
			FusionApp.sidebarView.closeSidebar()
		}
	} else {
		document.body.classList.remove('aioseo-avada-sidebar--active')
	}
}

onMounted(() => {
	isSidebarActive.value = document.body.classList.contains('aioseo-avada-sidebar--active')

	emitter.on('fusionSidebarToggled', (isOpen) => {
		if (isOpen) {
			isSidebarActive.value = false
			document.body.classList.remove('aioseo-avada-sidebar--active')
		}
	})
})
</script>

<style lang="scss">
.aioseo-avada-integration {
	padding: 12px 0;

	@at-root .fusion-builder-preview-mode & {
		display: none;
	}

	> .aioseo-score-button {
		margin-left: 15px;
		cursor: pointer;
	}
}
</style>