<template>
	<div class="aioseo-search-statistics-keyword-rankings">
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
					__('%1$sUpgrade your %2$s %3$s%4$s plan to see Keyword Positions', td),
					`<a href="${links.getPricingUrl('search-statistics', 'search-statistics-upsell')}" target="_blank">`,
					import.meta.env.VITE_SHORT_NAME,
					'Pro',
					'</a>'
				),
				ctaDescription : __('Track how well keywords are ranking in search results over time based on their position and average CTR. This can help you understand the performance of keywords and identify any trends or fluctuations.', td)
			}
		}
	}
}
</script>