<template>
	<!-- @click.stop is a Vue directive that prevents the click event from propagating further up the DOM tree. This is required inside Elementor. -->
	<div @click.stop id="main-settings-cont">
		<alert
			v-if="'sidebar' !== $root.$data.screenContext"
		/>

		<core-loader
			v-if="postEditorStore.isFetchingPostData"
			dark
			class="aioseo-loading-spinner--post-settings"
		/>

		<main-view v-else />
	</div>
</template>

<script setup>
import { onBeforeMount } from 'vue'

import '@/vue/assets/scss/main.scss'

import {
	usePostEditorStore
} from '@/vue/stores'

import { useScrollAndHighlight } from '@/vue/composables/ScrollAndHighlight'

import Alert from './views/partials/Alert'
import CoreLoader from '@/vue/components/common/core/Loader'
import MainView from './views/Main'

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
#main-settings-cont {
	.aioseo-loading-spinner {
		&--post-settings {
			margin-left: auto;
			margin-right: auto;
			position: relative;
		}
	}
}

.aioseo-post-settings-sidebar-vue {
	> div {
		flex: 1;
	}

	.aioseo-loading-spinner {
		margin-top: 30px;
	}

	.aioseo-button .aioseo-loading-spinner {
		margin-top: 0;
	}
}
</style>