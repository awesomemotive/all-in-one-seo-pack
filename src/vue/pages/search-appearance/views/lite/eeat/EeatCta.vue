<template>
	<div>
		<eeat-blur />

		<cta
			:cta-link="links.getPricingUrl('eeat', 'eeat-upsell')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('eeat', null, $isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="features"
			alignTop
		>
			<template #header-text>
				{{ strings.headerText }}
			</template>

			<template #description>
				<required-plans addon="aioseo-eeat" />

				{{ strings.description }}
			</template>
		</cta>
	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import { AddonConditions } from '@/vue/mixins/AddonConditions'
import { JsonValues } from '@/vue/mixins/JsonValues'
import { useEeatCta } from '@/vue/composables/EeatCta'
import Cta from '@/vue/components/common/cta/Index'
import EeatBlur from '../../partials/eeat/EeatBlur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

import links from '@/vue/utils/links'

export default {
	setup () {
		const { addonSlug, features, strings } = useEeatCta()

		return {
			addonSlug,
			features,
			licenseStore : useLicenseStore(),
			links,
			strings
		}
	},
	components : {
		Cta,
		EeatBlur,
		RequiredPlans
	},
	mixins : [ AddonConditions, JsonValues ]

}
</script>