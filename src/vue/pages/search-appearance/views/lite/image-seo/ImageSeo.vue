<template>
	<div class="aioseo-sa-image-seo">
		<blur />

		<cta
			:cta-link="links.getPricingUrl('image-seo', 'image-seo-upsell', null, 'liteUpgrade')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('image-seo', null, 'liteUpgrade')"
			:feature-list="features"
			:hide-bonus="!licenseStore.isUnlicensed"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>

			<template #description>
				<required-plans addon="aioseo-image-seo" />

				{{ strings.ctaDescription }}
			</template>
		</cta>
	</div>
</template>

<script setup>
import links from '@/vue/utils/links'
import {
	useLicenseStore
} from '@/vue/stores'

import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const licenseStore = useLicenseStore()

const strings = {
	titleAttributeFormat : __('Title Attribute Format', td),
	ctaDescription       : __('The Image SEO module is a premium feature that enables you to globally control the title, alt tag, caption, description and filename of the images on your site.', td),
	ctaButtonText        : __('Unlock Image SEO', td),
	ctaHeader            : sprintf(
		// Translators: 1 - "PRO".
		__('Image SEO is a %1$s Feature', td),
		'PRO'
	)
}

const features = [
	__('Autogenerate image attributes', td),
	__('Clean uploaded image filenames', td),
	__('Strip punctuation from image attributes', td),
	__('Convert casing of image attributes', td)
]
</script>

<style scoped lang="scss">
.aioseo-app .aioseo-sa-image-seo {
	min-height: 650px;

	.aioseo-cta .feature-list {
		max-width: 600px;
	}
}
</style>