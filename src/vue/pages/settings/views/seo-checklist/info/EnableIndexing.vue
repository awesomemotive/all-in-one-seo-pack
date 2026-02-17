<template>
	<div class="checklist-info">
		<h4>{{ strings.whyEnable }}</h4>
		<p>{{ strings.description }}</p>
		<ul>
			<li>{{ strings.benefit1 }}</li>
			<li>{{ strings.benefit2 }}</li>
			<li>{{ strings.benefit3 }}</li>
		</ul>
		<p>
			<strong>{{ strings.noteLabel }}</strong> {{ strings.noteText }}
		</p>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	name : 'EnableIndexingInfo',
	data () {
		return {
			strings : {
				whyEnable   : __('Why enable indexing?', td),
				description : __('When your site is hidden from search engines, it\'s invisible to potential visitors. Enabling indexing allows:', td),
				benefit1    : __('Google and other search engines to discover and index your content.', td),
				benefit2    : __('Your pages to appear in search results.', td),
				benefit3    : __('Potential visitors to find your site through organic search.', td),
				noteLabel   : __('Note:', td),
				noteText    : __('If you\'re still developing your site, you can keep indexing disabled until you\'re ready to launch.', td)
			}
		}
	}
}
</script>