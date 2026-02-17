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
	name : 'ReviewArchivesInfo',
	data () {
		return {
			strings : {
				whyConfigure       : __('Why should I configure archive page settings?', td),
				description        : __('Archive pages display collections of posts organized by date, author, or other criteria. Configuring these settings ensures these pages are optimized for search engines and rank better.', td),
				whatCanControl     : __('What settings can you control?', td),
				controlDescription : __('You can choose whether archive pages (date archives, author archives, search results) show in search results or are hidden. You can set default title & description templates to automatically generate SEO-friendly meta data. We recommend that you noindex search results archives to prevent thin content issues while keeping author archives indexed to highlight content creators.', td)
			}
		}
	}
}
</script>