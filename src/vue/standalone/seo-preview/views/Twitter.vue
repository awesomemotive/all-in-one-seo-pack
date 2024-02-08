<template>
	<div class="preview-wrapper">
		<core-twitter-preview
			:card="twitterData.card"
			:description="twitterData.description"
			:image="twitterData.image"
			:title="twitterData.title"
		/>
	</div>
</template>

<script>
import { getTwitterSnippetData } from '@/vue/utils/html'
import CoreTwitterPreview from '@/vue/components/common/core/TwitterPreview'

export default {
	components : {
		CoreTwitterPreview
	},
	data () {
		return {
			twitterData : getTwitterSnippetData()
		}
	}
}
</script>

<style lang="scss" scoped>
.preview-wrapper {
	margin: 20px;

	> * {
		padding: 0;
	}
}
</style>