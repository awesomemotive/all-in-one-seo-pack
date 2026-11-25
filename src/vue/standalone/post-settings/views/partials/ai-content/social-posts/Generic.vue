<template>
	<div :class="[
			'aioseo-ai-content-social-post',
			className
		]"
	>
		<p v-safe-html="content"/>
	</div>
</template>

<script>

import {
	usePostEditorStore
} from '@/vue/stores'

import { sanitizeMarkup } from '@/vue/utils/strings'

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			sanitizeMarkup
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
			return sanitizeMarkup(this.postEditorStore.currentPost.ai.socialPosts[this.slug]?.replace(/\n/g, '<br />'))
		},
		className () {
			return `aioseo-ai-content-${this.slug}`
		}
	}
}
</script>