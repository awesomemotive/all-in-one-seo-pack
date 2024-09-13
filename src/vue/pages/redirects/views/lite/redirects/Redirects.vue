<template>
	<div
		:class="{
			'aioseo-redirects': true,
			'core-card': !noCoreCard
			}"
	>
		<blur
			:noCoreCard="noCoreCard"
		/>

		<cta
			:cta-link="links.getPricingUrl('redirects', 'redirects-upsell', parentComponentContext ? parentComponentContext : null)"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('redirects', parentComponentContext ? parentComponentContext : null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="[
				strings.serverRedirects,
				strings.automaticRedirects,
				strings.redirectMonitoring,
				strings.monitoring404,
				strings.fullSiteRedirects,
				strings.siteAliases
			]"
			:hide-bonus="!licenseStore.isUnlicensed"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				<required-plans addon="aioseo-redirects" />

				{{ strings.redirectsDescription }}
			</template>
		</cta>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore(),
			links
		}
	},
	components : {
		Blur,
		Cta,
		RequiredPlans
	},
	props : {
		noCoreCard             : Boolean,
		parentComponentContext : String
	},
	data () {
		return {
			strings : {
				ctaButtonText : __('Unlock Redirects', td),
				ctaHeader     : sprintf(
					// Translators: 1 - "PRO".
					__('Redirects is a %1$s Feature', td),
					'PRO'
				),
				serverRedirects      : __('Fast Server Redirects', td),
				automaticRedirects   : __('Automatic Redirects', td),
				redirectMonitoring   : __('Redirect Monitoring', td),
				monitoring404        : __('404 Monitoring', td),
				fullSiteRedirects    : __('Full Site Redirects', td),
				siteAliases          : __('Site Aliases', td),
				redirectsDescription : __('Our Redirection Manager lets you easily create and manage redirects for broken links to avoid confusing search engines and users and prevents losing backlinks.', td)
			}
		}
	}
}
</script>