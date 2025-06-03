<template>
	<!-- @click.stop is a Vue directive that prevents the click event from propagating further up the DOM tree. This is required inside Elementor. -->
	<div @click.stop id="main-settings-cont">
		<alert
			v-if="'sidebar' !== $root.$data.screenContext"
		/>

		<main-view
			v-if="postEditorStore.currentPost.id"
		/>
	</div>
</template>

<script setup>
import { onBeforeMount, defineEmits } from 'vue'

import '@/vue/assets/scss/main.scss'

import {
	usePostEditorStore
} from '@/vue/stores'

import { useScrollAndHighlight } from '@/vue/composables/ScrollAndHighlight'

import MainView from './views/Main'
import Alert from './views/partials/Alert'

// We just need to call this to make sure the composable is used.
useScrollAndHighlight({
	scrollTimeout             : 1000,
	scrollAndHighlightTimeout : 2000
})

const postEditorStore = usePostEditorStore()
const emit = defineEmits([ 'beforeMount' ])

onBeforeMount(() => {
	emit('beforeMount')
})
</script>

<style lang="scss">
.aioseo-post-settings-sidebar-vue {
	> div {
		flex: 1;
	}

	.aioseo-loading-spinner {
		margin-top: 30px;
	}
}
</style>