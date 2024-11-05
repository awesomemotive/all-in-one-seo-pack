<template>
	<core-card
		slug="writingAssistantCta"
		class="writing-assistant-cta"
	>
		<template #header>
			{{ strings.seoBoost }}
		</template>
		<template #tooltip>
			{{ strings.tooltipSeoboost }}
		</template>

		<core-blur>
			<core-settings-row :name="strings.connect">
				<template #content>
					<base-button type="blue">
						{{ strings.connect }}
					</base-button>
				</template>
			</core-settings-row>
			<core-settings-row :name="strings.defaultCountry">
				<template #content>
					<base-select
						class="select-auto"
						size="medium"
						:options="[{
								label: 'United States',
								value: 'us'
							}, {
								label: 'United Kingdom',
								value: 'uk'
							}]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row :name="strings.defaultLanguage">
				<template #content>
					<base-select
						class="select-auto"
						size="medium"
						:options="[{
								label: 'English',
								value: 'en'
							}, {
								label: 'French',
								value: 'fr'
							}]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row :name="strings.postTypes">
				<template #content>
					<base-select
						class="select-auto"
						size="medium"
						:options="[{
								label: 'English',
								value: 'en'
							}, {
								label: 'French',
								value: 'fr'
							}]"
					/>
				</template>
			</core-settings-row>
		</core-blur>
		<cta
			:cta-link="links.getPricingUrl('seoboost', 'seoboost-upsell', '', 'https://seoboost.com/pricing/')"
			:show-link="false"
			:button-text="strings.ctaButtonText"
			:feature-list="features"
			:hide-bonus="true"
			:type="1"
		>
			<template #header-text>
				{{ strings.ctaHeader1 }}
			</template>
			<template #description>
				{{ strings.ctaDescription }}
			</template>
			<template #featured-image>
				<img
					:src="getAssetUrl(loginImg)"
				/>
			</template>
		</cta>
	</core-card>
</template>

<script setup>
import loginImg from '@/vue/assets/images/writing-assistant/login.svg'
import { getAssetUrl } from '@/vue/utils/helpers'
import links from '@/vue/utils/links.js'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'
import BaseSelect from '@/vue/components/common/base/Select'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import {
	__,
	sprintf
} from '@/vue/plugins/translations.js'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	seoBoost        : __('Writing Assistant by SEOBoost', td),
	tooltipSeoboost : __('Integrate seamlessly with SEOBoost via AIOSEO to supercharge your WordPress content.', td),
	defaultCountry  : __('Default Country', td),
	defaultLanguage : __('Default Language', td),
	postTypes       : __('Post Types', td),
	connect         : __('Connect to SEOBoost', td),
	ctaButtonText   : __('Get Writing Assistant Now!', td),
	ctaDescription  : __('Integrate seamlessly with SEOBoost via AIOSEO to supercharge your WordPress content.', td),
	ctaHeader1      : sprintf(
		// Translators: 1 - "PRO".
		__('Writing Assistant + SEOBoost: Elevate Your Content Strategy', td),
		'PRO'
	)
}

const features = [
	__('Keyword Research: Discover high-impact keywords tailored to your niche.', td),
	__('Top Competitor Analysis: Gain insights into your top 30 competitors.', td),
	__('Easy Content Optimization: Get actionable suggestions to enhance your content.', td),
	__('NLP-generated Keyword Suggestions: Leverage AI-driven suggestions for maximum reach.', td)
]

</script>

<style lang="scss">
.writing-assistant-cta.aioseo-card {
	.content {
		padding-bottom: 250px !important;
	}
}
</style>