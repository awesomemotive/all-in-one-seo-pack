<template>
	<div class="aioseo-search-statistics-link-assistant">
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

<script>
import links from '@/vue/utils/links'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Blur,
		Cta
	},
	data () {
		return {
			strings : {
				ctaHeader : sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Plugin short name ("AIOSEO"), 3 - "Pro", 4 - Closing HTML link tag.
					__('%1$sUpgrade to %2$s %3$s%4$s to see Link Suggestions', td),
					`<a href="${links.getPricingUrl('search-statistics', 'search-statistics-upsell')}" target="_blank">`,
					import.meta.env.VITE_SHORT_NAME,
					'Pro',
					'</a>'
				),
				ctaDescription : __('Get internal link suggestions for all your content including orphaned posts that have no internal links.', td)
			}
		}
	}
}
</script>