<template>
	<div class="checklist-info">
		<h4>{{ strings.whyConfigure }}</h4>
		<p>{{ strings.description }}</p>

		<h4>{{ strings.whatCanControl }}</h4>
		<p>{{ strings.controlDescription }}</p>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	name : 'ReviewTaxonomiesInfo',
	data () {
		return {
			strings : {
				whyConfigure       : __('Why should I configure taxonomy settings?', td),
				description        : __('Taxonomy pages like categories and tags help organize your content and make it easier for visitors to find related posts. Configuring these settings ensures these pages are optimized for search engines and rank better.', td),
				whatCanControl     : __('What settings can you control?', td),
				controlDescription : __('You can choose whether each taxonomy (categories, tags, custom taxonomies) shows in search results or is hidden. You can set default title and meta description templates that automatically generate SEO-friendly meta data.', td)
			}
		}
	}
}
</script>