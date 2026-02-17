<template>
	<div class="checklist-info">
		<h4>{{ strings.whatAre }}</h4>
		<p>{{ strings.description }}</p>

		<h4>{{ strings.whatCanControl }}</h4>
		<p>{{ strings.controlDescription }}</p>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	name : 'ReviewContentTypesInfo',
	data () {
		return {
			strings : {
				whatAre            : __('Why should I configure these settings?', td),
				description        : __('Your post settings control how your different content types (posts, pages and custom post types) appear in search results and which ones are visible to search engines. The right settings ensure your content is optimized and ranks higher.', td),
				whatCanControl     : __('What settings can you control?', td),
				controlDescription : __('You can choose whether each post type shows in search results and set default title & description templates to automatically generate SEO-friendly meta data. You can also configure schema types to customize how posts appear in search results.', td)
			}
		}
	}
}
</script>