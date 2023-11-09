<template>
	<!-- @click.stop is a Vue directive that prevents the click event from propagating further up the DOM tree. This is required inside Elementor. -->
	<div @click.stop>
		<alert v-if="'sidebar' !== this.$root._data.screenContext" />

		<main-view
			v-if="postEditorStore.currentPost.id"
		/>
	</div>
</template>

<script>
import '@/vue/assets/scss/main.scss'

import {
	usePostEditorStore
} from '@/vue/stores'

import MainView from './views/Main'
import { ScrollAndHighlight } from '@/vue/mixins/ScrollAndHighlight'
import Alert from './views/partials/Alert'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		Alert,
		MainView
	},
	mixins : [ ScrollAndHighlight ],
	data () {
		return {
			scrollContainer           : '.interface-interface-skeleton__content',
			scrollTimeout             : 1000,
			ScrollAndHighlightTimeout : 2000
		}
	}
}
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