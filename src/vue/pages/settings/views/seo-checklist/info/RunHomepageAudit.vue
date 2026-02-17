<template>
	<div class="checklist-info checklist-info-run-homepage-audit">
		<h4>{{ strings.whatIs }}</h4>
		<p>{{ strings.whatIsDescription }}</p>

		<h4>{{ strings.whyRun }}</h4>
		<ul>
			<li>
				{{ strings.identifyIssuesDescription }}
			</li>
			<li>
				{{ strings.actionableInsightsDescription }}
			</li>
			<li>
				{{ strings.benchmarkDescription }}
			</li>
		</ul>

		<p v-html="learnMoreLink" />
	</div>
</template>

<script>
import links from '@/vue/utils/links'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	data () {
		return {
			strings : {
				whatIs                        : __('What is the Homepage Audit?', td),
				whatIsDescription             : __('The Homepage Audit analyzes your homepage to identify technical SEO issues, content problems, and opportunities for improvement.', td),
				whyRun                        : __('Why should you run it?', td),
				identifyIssuesDescription     : __('Discover missing or duplicate meta tags, broken certificates, slow loading times, and other problems that could hurt your rankings.', td),
				actionableInsightsDescription : __('Receive specific recommendations on what to fix and how to fix it, prioritized by importance.', td),
				benchmarkDescription          : __('Establish a baseline score to measure improvements as you optimize your site.', td),
				learnMore                     : __('Learn More', td)
			}
		}
	},
	computed : {
		learnMoreLink () {
			return links.getDocLink(this.strings.learnMore, 'seoAnalyzer', true)
		}
	}
}
</script>

<style lang="scss" scoped>
// Component-specific styles can go here if needed
// Common styles are inherited from .checklist-info in SeoChecklist.vue
</style>