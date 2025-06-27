<template>
	<div :class="[
			'aioseo-ai-content-social-post',
			className
		]"
	>
		<p v-html="content"/>
	</div>
</template>

<script>

import {
	usePostEditorStore
} from '@/vue/stores'

import { softSanitizeHtml } from '@/vue/utils/strings'

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			softSanitizeHtml
		}
	},
	props : {
		slug : {
			type     : String,
			required : true
		}
	},
	computed : {
		content () {
			return softSanitizeHtml(this.postEditorStore.currentPost.ai.socialPosts[this.slug]?.replace(/\n/g, '<br />'))
		},
		className () {
			return `aioseo-ai-content-${this.slug}`
		}
	}
}
</script>