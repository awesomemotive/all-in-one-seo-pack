<template>
	<core-card
		class="aioseo-local-seo-import-others-lite"
		slug="importOtherPluginsLocalSeo"
		:toggles="false"
		no-slide
	>
		<template #header-icon>
			<svg-download />
		</template>

		<template #header>
			<span>{{ strings.importLocalSeoFromOtherPlugins }}</span>
			<core-pro-badge />
		</template>

		<blur />

		<cta
			:cta-link="links.getPricingUrl('local-seo', 'local-seo-upsell', 'import', 'liteUpgrade')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('local-seo', 'local-seo-upsell', 'liteUpgrade')"
			:feature-list="features"
			:hide-bonus="!licenseStore.isUnlicensed"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				<required-plans addon="aioseo-local-business" />

				{{ strings.ctaDescription }}
			</template>
		</cta>
	</core-card>
</template>

<script>
import links from '@/vue/utils/links'

import {
	useLicenseStore
} from '@/vue/stores'

import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'
import SvgDownload from '@/vue/components/common/svg/Download'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const licenseStore = useLicenseStore()

		return {
			licenseStore
		}
	},
	components : {
		SvgDownload,
		Blur,
		Cta,
		CoreCard,
		CoreProBadge,
		RequiredPlans
	},
	data () {
		return {
			links,
			importSuccess : false,
			importError   : false,
			options       : {},
			plugin        : null,
			loading       : false,
			strings       : {
				importLocalSeoFromOtherPlugins : __('Import Local SEO From Other Plugins', td),
				ctaButtonText                  : __('Unlock Local SEO', td),
				ctaHeader                      : sprintf(
					// Translators: 1 - "PRO".
					__('Local SEO is a %1$s Feature', td),
					'PRO'
				),
				ctaDescription : __('Import your Local SEO settings and locations from other plugins.', td)
			},
			features : [
				__('Yoast SEO', td),
				__('Rank Math SEO', td),
				__('SEOPress', td)
			]
		}
	},
	computed : {
		plugins () {
			return [ {
				value       : 'yoast-seo',
				label       : 'yoast-seo',
				canImport   : false,
				version     : '1.0.0',
				$isDisabled : false
			} ]
		}
	}
}
</script>

<style lang="scss">
.aioseo-local-seo-import-others-lite {
	position: relative;

	&.aioseo-card .content {
		padding-bottom: 340px;
	}

	.aioseo-button.import {
		margin-top: 16px;
	}
}
</style>