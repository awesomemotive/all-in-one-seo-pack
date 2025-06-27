<template>
	<div class="aioseo-ai-content-social-post aioseo-ai-content-email">
		<p><span class="label">{{ strings.subject }}</span>: {{ softSanitizeHtml(postEditorStore.currentPost.ai.socialPosts?.email.subject) }}</p>
		<p><span class="label">{{ strings.preview }}</span>: {{ softSanitizeHtml(postEditorStore.currentPost.ai.socialPosts?.email.preview) }}</p>
		<p><span class="label">{{ strings.body }}</span>: <span v-html="emailContent"></span></p>
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import { softSanitizeHtml } from '@/vue/utils/strings'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const strings = {
			subject : __('Subject', td),
			preview : __('Preview', td),
			body    : __('Body', td)
		}

		return {
			postEditorStore : usePostEditorStore(),
			strings,
			softSanitizeHtml
		}
	},
	computed : {
		emailContent () {
			return softSanitizeHtml(this.postEditorStore.currentPost.ai.socialPosts?.email.content.replace(/\n/g, '<br />'))
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-content-email {
	.label {
		font-weight: 600;
	}
}
</style>