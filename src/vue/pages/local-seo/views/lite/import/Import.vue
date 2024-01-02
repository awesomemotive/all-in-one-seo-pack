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
			:cta-link="$links.getPricingUrl('local-seo', 'local-seo-upsell', 'import')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="$links.getUpsellUrl('local-seo', null, 'home')"
			:feature-list="features"
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
import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'
import SvgDownload from '@/vue/components/common/svg/Download.vue'
export default {
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
			importSuccess : false,
			importError   : false,
			options       : {},
			plugin        : null,
			loading       : false,
			strings       : {
				importLocalSeoFromOtherPlugins : this.$t.__('Import Local SEO From Other Plugins', this.$td),
				ctaButtonText                  : this.$t.__('Unlock Local SEO', this.$td),
				ctaHeader                      : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Local SEO is a %1$s Feature', this.$td),
					'PRO'
				),
				ctaDescription : this.$t.__('Import your Local SEO settings and locations from other plugins.', this.$td)
			},
			features : [
				this.$t.__('Yoast SEO', this.$td),
				this.$t.__('Rank Math SEO', this.$td),
				this.$t.__('SEOPress', this.$td)
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