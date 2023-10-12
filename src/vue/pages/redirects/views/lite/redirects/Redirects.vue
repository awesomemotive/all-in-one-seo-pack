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
			:cta-link="$links.getPricingUrl('redirects', 'redirects-upsell', parentComponentContext ? parentComponentContext : null)"
			:button-text="strings.ctaButtonText"
			:learn-more-link="$links.getUpsellUrl('redirects', parentComponentContext ? parentComponentContext : null, $isPro ? 'pricing' : 'liteUpgrade')"
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
import {
	useLicenseStore
} from '@/vue/stores'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
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
				ctaButtonText : this.$t.__('Unlock Redirects', this.$td),
				ctaHeader     : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Redirects is a %1$s Feature', this.$td),
					'PRO'
				),
				serverRedirects      : this.$t.__('Fast Server Redirects', this.$td),
				automaticRedirects   : this.$t.__('Automatic Redirects', this.$td),
				redirectMonitoring   : this.$t.__('Redirect Monitoring', this.$td),
				monitoring404        : this.$t.__('404 Monitoring', this.$td),
				fullSiteRedirects    : this.$t.__('Full Site Redirects', this.$td),
				siteAliases          : this.$t.__('Site Aliases', this.$td),
				redirectsDescription : this.$t.__('Our Redirection Manager lets you easily create and manage redirects for broken links to avoid confusing search engines and users and prevents losing backlinks.', this.$td)
			}
		}
	}
}
</script>