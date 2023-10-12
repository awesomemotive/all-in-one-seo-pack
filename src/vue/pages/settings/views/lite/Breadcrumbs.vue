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
						:cta-link="$links.getPricingUrl('breadcrumb-templates', 'breadcrumb-templates-upsell')"
						:button-text="strings.ctaButtonText"
						:learn-more-link="$links.getUpsellUrl('breadcrumb-templates', null, $isPro ? 'pricing' : 'liteUpgrade')"
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
import ContentTypesLite from '../partials/Breadcrumbs/ContentTypesLite'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'
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
			strings : {
				breadcrumbTemplates        : this.$t.__('Breadcrumb Templates', this.$td),
				breadcrumbTemplatesTooltip : this.$t.__('Override the default template for breadcrumbs on your site using our easy-to-use template editor.', this.$td),
				ctaButtonText              : this.$t.__('Unlock Breadcrumb Templates', this.$td),
				ctaHeader1                 : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Breadcrumb Templates is a %1$s Feature', this.$td),
					'PRO'
				),
				ctaDescription : this.$t.__('Our template editor will allow you to easily customize how breadcrumbs are displayed on your site based on each post type or taxonomy.', this.$td)
			},
			features : [
				this.$t.__('Custom HTML templates', this.$td),
				this.$t.__('Content Types', this.$td),
				this.$t.__('Taxonomies', this.$td),
				this.$t.__('Archives', this.$td)
			],
			tab  : 'ContentTypesLite',
			tabs : [
				{
					slug   : 'ContentTypesLite',
					name   : this.$t.__('Content Types', this.$td),
					access : 'aioseo_general_settings',
					pro    : true
				},
				{
					slug   : 'Taxonomies',
					name   : this.$t.__('Taxonomies', this.$td),
					access : 'aioseo_general_settings',
					pro    : true
				},
				{
					slug   : 'Archives',
					name   : this.$t.__('Archives', this.$td),
					access : 'aioseo_general_settings',
					pro    : true
				},
				{
					slug   : 'Advanced',
					name   : this.$t.__('Advanced', this.$td),
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