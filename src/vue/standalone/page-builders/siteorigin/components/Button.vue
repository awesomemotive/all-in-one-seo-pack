<template>
	<div class="aioseo-site-origin-integration">
		<core-score-button
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
import emitter from 'tiny-emitter/instance'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'

const isSidebarActive = ref(false)

const { currentPost } = storeToRefs(usePostEditorStore())

const toggleSidebar = () => {
	isSidebarActive.value = !isSidebarActive.value

	document.body.classList.toggle('aioseo-site-origin-sidebar--active', isSidebarActive.value)
}

onMounted(() => {
	isSidebarActive.value = document.body.classList.contains('aioseo-site-origin-sidebar--active')

	emitter.on('siteOriginAioseoClosed', () => {
		isSidebarActive.value = false
		document.body.classList.remove('aioseo-site-origin-sidebar--active')
	})
})
</script>

<style lang="scss">
.aioseo-site-origin-integration {
	> .aioseo-score-button {
		margin-left: 10px;
		padding: 3px 6px;
		cursor: pointer;

		&--active {
			border-color: transparent;
		}
	}
}
</style>