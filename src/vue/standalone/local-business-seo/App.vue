<template>
	<div>
		<main-view
			v-if="postEditorStore.currentPost.id"
		/>
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import Main from './views/Main'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		'main-view' : Main
	}
}
</script>