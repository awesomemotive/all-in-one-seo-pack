<template>
	<div class="checklist-info">
		<h4>{{ strings.whyConnect }}</h4>
		<p>{{ strings.description }}</p>

		<h4>{{ strings.whatHappens }}</h4>
		<p>{{ strings.connectionDescription }}</p>

		<h4>{{ strings.isFree }}</h4>
		<p>{{ strings.freeDescription }}</p>

		<p v-html="learnMoreLink" />
	</div>
</template>

<script>
import links from '@/vue/utils/links'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	name : 'ConnectBrokenLinkCheckerInfo',
	data () {
		return {
			strings : {
				whyConnect            : __('Why do I need to connect Broken Link Checker?', td),
				description           : __('Connecting Broken Link Checker activates the plugin\'s full functionality and allows it to begin scanning your site for broken links. Without completing the connection, the plugin remains dormant and won\'t monitor your site for issues.', td),
				whatHappens           : __('What happens when you connect?', td),
				connectionDescription : __('Once connected, Broken Link Checker will perform an initial scan of your entire site to identify existing broken links. It will then continuously monitor your content for new broken links as you add or update pages and posts. You\'ll receive notifications when broken links are found, and you can manage them all from a centralized dashboard. The plugin will also track the status of external links and alert you when they break over time.', td),
				isFree                : __('Is Broken Link Checker free to use?', td),
				freeDescription       : __('Broken Link Checker offers a free plan that monitors up to 250 links on your site, which is perfect for smaller websites. If you have a larger site with more links, paid plans are available with higher link quotas to ensure your entire site is monitored. All plans include the same core features like real-time monitoring and automated notifications.', td),
				viewPlans             : __('View BLC Plans', td)
			}
		}
	},
	computed : {
		learnMoreLink () {
			const url = links.utmUrl('seo-checklist', 'connect-blc', 'https://aioseo.com/pricing-broken-link-checker/')
			return `<a href="${url}" target="_blank" rel="noopener noreferrer">${this.strings.viewPlans} <span class="no-underline">→</span></a>`
		}
	}
}
</script>