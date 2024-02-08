<template>
	<div class="preview-wrapper">
		<core-facebook-preview
			:description="facebookData.description"
			:image="facebookData.image"
			:title="facebookData.title"
		/>
	</div>
</template>

<script>
import { getFacebookSnippetData } from '@/vue/utils/html'
import CoreFacebookPreview from '@/vue/components/common/core/FacebookPreview'

export default {
	components : {
		CoreFacebookPreview
	},
	data () {
		return {
			facebookData : getFacebookSnippetData()
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