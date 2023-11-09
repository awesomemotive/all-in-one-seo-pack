<template>
	<div class="aioseo-app">
		<PostColumn
			v-if="$isPro"
			:post="post"
		/>

		<PostColumnLite
			v-if="!$isPro"
			:post="post"
		/>
	</div>
</template>

<script>
import PostColumn from './AIOSEO_VERSION/PostColumn'
import PostColumnLite from './lite/PostColumn'
import '@/vue/assets/scss/main.scss'
export default {
	components : {
		PostColumn,
		PostColumnLite
	},
	props : {
		post : Object
	}
}
</script>