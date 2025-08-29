<template>
	<div class="aioseo-sa-ct-custom-fields lite">
		<core-blur>
			<core-settings-row
				:name="strings.customFields"
				align
			>
				<template #content>
					<base-textarea
						:min-height="200"
					/>

					<div class="aioseo-description">
						{{ strings.customFieldsDescription }}
					</div>
				</template>
			</core-settings-row>
		</core-blur>

		<cta
			:cta-link="links.getPricingUrl('custom-fields', 'custom-fields-upsell', `${object.name}-post-type`, 'liteUpgrade')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('custom-fields', object.name, 'liteUpgrade')"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				{{ strings.ctaDescription }}
			</template>
		</cta>
	</div>
</template>

<script setup>
import links from '@/vue/utils/links'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

defineProps({
	type : {
		type     : String,
		required : true
	},
	object : {
		type     : Object,
		required : true
	}
})

const strings = {
	customFields            : __('Custom Fields', td),
	customFieldsDescription : __('List of custom field names to include as post content for tags and the SEO Page Analysis. Add one per line.', td),
	ctaDescription          : sprintf(
		// Translators: 1 - Plugin short name ("AIOSEO"), 2 - "Pro".
		__('%1$s %2$s gives you advanced customizations for our page analysis feature, letting you add custom fields to analyze.', td),
		import.meta.env.VITE_SHORT_NAME,
		'Pro'
	),
	ctaButtonText : __('Unlock Custom Fields', td),
	ctaHeader     : sprintf(
		// Translators: 1 - "PRO".
		__('Custom Fields is a %1$s Feature', td),
		'PRO'
	)
}
</script>

<style lang="scss">
.aioseo-sa-ct-custom-fields {
	&.lite {
		min-height: 370px;

		@media (max-width: 598px) {
			.aioseo-cta.floating{
				top: 0;
			}
		}

		@media (max-width: 420px) {
			min-height: 450px;
		}
	}
}
</style>