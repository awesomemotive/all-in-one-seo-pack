<template>
	<core-card noSlide>
		<template #header>
			<span>{{ strings.authorInfo }}</span>
			<core-pro-badge />
		</template>

		<eeat-blur />

		<cta
			:cta-link="links.getPricingUrl('eeat', 'eeat-upsell')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('eeat', null, $isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="features"
		>
			<template #header-text>
				{{ strings.headerText }}
			</template>

			<template #description>
				<required-plans addon="aioseo-eeat" />

				{{ strings.description }}
			</template>
		</cta>
	</core-card>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import { AddonConditions } from '@/vue/mixins/AddonConditions'
import { JsonValues } from '@/vue/mixins/JsonValues'
import { useEeatCta } from '@/vue/composables/EeatCta'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'
import EeatBlur from '../partials/EeatBlur'
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
		CoreCard,
		CoreProBadge,
		Cta,
		EeatBlur,
		RequiredPlans
	},
	mixins : [ AddonConditions, JsonValues ]

}
</script>