<template>
	<div class="aioseo-tools-export-taxonomies">
		<cta
			:cta-link="links.getPricingUrl('taxonomies', 'taxonomies', 'export-taxonomies')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('taxonomies', 'export-taxonomies', 'liteUpgrade')"
			:feature-list="strings.ctaFeatures"
			:floating="false"
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

<script>
import links from '@/vue/utils/links'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td    = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			links
		}
	},
	components : {
		Cta
	},
	data () {
		return {
			strings : {
				ctaHeader : sprintf(
					// Translators: 1 - "PRO".
					__('Exporting Terms is a %1$s Feature', td),
					'PRO'
				),
				ctaDescription : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('%1$s allows you to fully control your SEO and social meta for custom taxonomies, including the ability to import/export them.', td),
					import.meta.env.VITE_SHORT_NAME + ' Pro'
				),
				ctaFeatures : [
					__('SEO Title/Description', td),
					__('Social Meta', td),
					__('SEO Revisions', td),
					__('Import/Export', td)
				],
				ctaButtonText : __('Unlock Custom Taxonomies', td)
			}
		}
	}
}
</script>

<style lang="scss">
	.aioseo-export-content {
		.aioseo-tools-export-taxonomies {
			.aioseo-cta {
				margin-top: 0;

				.feature-list {
					margin-bottom: 0;

					.aioseo-col {
						display: block;

						svg {
							vertical-align: middle;
						}
					}
				}

				.actions {
					display: contents !important;

					a {
						margin-top: 16px !important;
					}
				}
			}
		}
	}
</style>