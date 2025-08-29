<template>
	<div>
		<eeat-blur />

		<cta
			:cta-link="links.getPricingUrl('eeat', 'eeat-upsell', null, 'liteUpgrade')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('eeat', null, 'liteUpgrade')"
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

<script setup>
import { useEeatCta } from '@/vue/composables/EeatCta'

import Cta from '@/vue/components/common/cta/Index'
import EeatBlur from '../../partials/eeat/EeatBlur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

import links from '@/vue/utils/links'

const {
	features,
	strings
} = useEeatCta()
</script>