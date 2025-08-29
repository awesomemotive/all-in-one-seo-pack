<template>
	<div class="aioseo-breadcrumbs">

		<core-card
			slug="breadcrumbTemplates"
		>
			<template #header>
				<span>{{ strings.breadcrumbTemplates }}</span>
				<core-pro-badge />
			</template>
			<template #tooltip>
				{{ strings.breadcrumbTemplatesTooltip }}
			</template>

			<template #tabs>
				<div class="content">
					<core-blur>
						<core-main-tabs
							:tabs="tabs"
							:showSaveButton="false"
							:active="tab"
							internal
						/>
						<transition name="route-fade" mode="out-in">
							<component
								:is="tab"
								:active="tab"
							/>
						</transition>
					</core-blur>
					<cta
						:cta-link="links.getPricingUrl('breadcrumb-templates', 'breadcrumb-templates-upsell', null, 'liteUpgrade')"
						:button-text="strings.ctaButtonText"
						:learn-more-link="links.getUpsellUrl('breadcrumb-templates', null, 'liteUpgrade')"
						:feature-list="features"
					>
						<template #header-text>
							{{ strings.ctaHeader1 }}<br>
							{{ strings.ctaHeader2 }}
						</template>
						<template #description>
							{{ strings.ctaDescription }}
						</template>
					</cta>
				</div>
			</template>
		</core-card>
	</div>
</template>

<script>
import links from '@/vue/utils/links'

import ContentTypesLite from '../partials/Breadcrumbs/ContentTypesLite'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		ContentTypesLite,
		CoreBlur,
		CoreCard,
		CoreMainTabs,
		CoreProBadge,
		Cta
	},
	data () {
		return {
			links,
			strings : {
				breadcrumbTemplates        : __('Breadcrumb Templates', td),
				breadcrumbTemplatesTooltip : __('Override the default template for breadcrumbs on your site using our easy-to-use template editor.', td),
				ctaButtonText              : __('Unlock Breadcrumb Templates', td),
				ctaHeader1                 : sprintf(
					// Translators: 1 - "PRO".
					__('Breadcrumb Templates is a %1$s Feature', td),
					'PRO'
				),
				ctaDescription : __('Our template editor will allow you to easily customize how breadcrumbs are displayed on your site based on each post type or taxonomy.', td)
			},
			features : [
				__('Custom HTML templates', td),
				__('Content Types', td),
				__('Taxonomies', td),
				__('Archives', td)
			],
			tab  : 'ContentTypesLite',
			tabs : [
				{
					slug   : 'ContentTypesLite',
					name   : __('Content Types', td),
					access : 'aioseo_general_settings',
					pro    : true
				},
				{
					slug   : 'Taxonomies',
					name   : __('Taxonomies', td),
					access : 'aioseo_general_settings',
					pro    : true
				},
				{
					slug   : 'Archives',
					name   : __('Archives', td),
					access : 'aioseo_general_settings',
					pro    : true
				},
				{
					slug   : 'Advanced',
					name   : __('Advanced', td),
					access : 'aioseo_general_settings',
					pro    : true
				}
			]
		}
	},
	methods : {}
}
</script>

<style lang="scss" scoped>
.aioseo-breadcrumbs {
	.content {
		position: relative;
	}
}
</style>