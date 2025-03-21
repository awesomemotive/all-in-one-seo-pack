<template>
	<core-alert
		id="bots-alert"
		type="yellow"
		v-html="strings.robotsAlert"
	/>
</template>

<script setup>
import links from '@/vue/utils/links'
import { __, sprintf } from '@/vue/plugins/translations'

import CoreAlert from '@/vue/components/common/core/alert/Index'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	robotsAlert : sprintf(
		// Translators: 1 - "View robots.txt" link.
		__('We\'re adding rules to your robots.txt file to block certain bots from crawling your website. This can help improve website performance and protect sensitive information. %1$s', td),
		links.getPlainLink(__('View robots.txt', td), '/robots.txt', true)
	) + '<p>' + __('This setting can also be managed under the Robots.txt Editor and will continue to work even if Crawl Cleanup is disabled.', td) + '</p>'
}
</script>

<style lang="scss">
.aioseo-app #bots-alert {
	p {
		margin-bottom: 0;
	}
}
</style>