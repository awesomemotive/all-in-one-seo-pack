<template>
	<div class="aioseo-app">
		<PostColumn
			v-if="rootStore.isPro"
			:post="post"
		/>

		<PostColumnLite
			v-if="!rootStore.isPro"
			:post="post"
		/>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import PostColumn from './AIOSEO_VERSION/PostColumn'
import PostColumnLite from './lite/PostColumn'
import '@/vue/assets/scss/main.scss'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		PostColumn,
		PostColumnLite
	},
	props : {
		post : Object
	}
}
</script>