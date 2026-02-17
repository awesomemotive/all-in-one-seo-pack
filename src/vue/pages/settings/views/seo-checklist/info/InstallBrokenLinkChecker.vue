<template>
	<div class="checklist-info">
		<h4>{{ strings.whyInstall }}</h4>
		<p>{{ strings.description }}</p>

		<h4>{{ strings.whatItDoes }}</h4>
		<p>{{ strings.functionalityDescription }}</p>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	name : 'InstallBrokenLinkCheckerInfo',
	data () {
		return {
			strings : {
				whyInstall               : __('Why are broken links bad for SEO?', td),
				description              : __('Broken links create a frustrating experience for visitors and send negative signals to search engines about the quality of your site. When users click on a broken link and encounter a 404 error, they often leave your site entirely. Search engines also view excessive broken links as a sign of poor maintenance, which can hurt your rankings.', td),
				whatItDoes               : __('What does Broken Link Checker do?', td),
				functionalityDescription : __('Broken Link Checker automatically scans your entire site to identify broken links in your content. It monitors both internal links (to other pages on your site) and external links (to other websites). The plugin runs continuous scans to catch new broken links as they appear and comes with a free plan that includes up to 250 links per month.', td)
			}
		}
	}
}
</script>