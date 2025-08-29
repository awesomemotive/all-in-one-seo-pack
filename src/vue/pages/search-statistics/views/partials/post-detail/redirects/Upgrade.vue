<template>
	<div class="aioseo-search-statistics-redirects">
		<blur />

		<cta :type="4">
			<template #header-text>
				<span v-html="strings.ctaHeader" />
			</template>
			<template #description>
				{{ strings.ctaDescription }}
			</template>
		</cta>
	</div>
</template>

<script setup>
import links from '@/vue/utils/links'
import {
	useRootStore
} from '@/vue/stores'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const rootStore = useRootStore()

const strings = {
	ctaHeader : sprintf(
		// Translators: 1 - Opening HTML link tag, 2 - Plugin short name ("AIOSEO"), 3 - "Pro", 4 - Closing HTML link tag.
		__('%1$sUpgrade to %2$s %3$s%4$s to see Redirects', td),
		`<a href="${links.getPricingUrl('search-statistics', 'search-statistics-upsell', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')}" target="_blank">`,
		import.meta.env.VITE_SHORT_NAME,
		'Pro',
		'</a>'
	),
	ctaDescription : __('Our Redirection Manager lets you easily create and manage redirects for broken links to avoid confusing search engines and users and prevents losing backlinks.', td)
}
</script>