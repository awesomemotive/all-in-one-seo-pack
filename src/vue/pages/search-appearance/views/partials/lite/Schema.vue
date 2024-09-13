<template>
	<div class="aioseo-sa-ct-schema-lite">
		<core-blur>
			<core-settings-row
				:name="strings.schemaType"
				align
			>
				<template #content>
					<base-select
						size="medium"
						class="schema-type"
						:options="schemaTypes"
						:modelValue="getSchemaTypeOption('Article')"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.articleType"
				align
			>
				<template #content>
					<base-radio-toggle
						:name="`${object.name}articleType`"
						modelValue="BlogPosting"
						:options="[
							{ label: strings.article, value: 'Article' },
							{ label: strings.blogPost, value: 'BlogPosting' },
							{ label: strings.newsArticle, value: 'NewsArticle' },
						]"
					/>
				</template>
			</core-settings-row>
		</core-blur>

			<cta
				:cta-link="links.getPricingUrl('schema-markup', 'schema-markup-upsell')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('schema-markup', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
				:feature-list="features"
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
import {
	useRootStore
} from '@/vue/stores'

import links from '@/vue/utils/links'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		BaseRadioToggle,
		CoreBlur,
		CoreSettingsRow,
		Cta
	},
	props : {
		type : {
			type     : String,
			required : true
		},
		object : {
			type     : Object,
			required : true
		}
	},
	data () {
		return {
			links,
			schemaTypes : [
				{ value: 'none', label: __('None', td) },
				{ value: 'Article', label: __('Article', td) }
			],
			strings : {
				schemaType     : __('Schema Type', td),
				articleType    : __('Article Type', td),
				article        : __('Article', td),
				blogPost       : __('Blog Post', td),
				newsArticle    : __('News Article', td),
				ctaDescription : __('Easily generate unlimited schema markup for your content to help you rank higher in search results. Our schema validator ensures your schema works out of the box.', td),
				ctaButtonText  : __('Unlock Schema Markup Generator', td),
				ctaHeader      : sprintf(
					// Translators: 1 - "PRO".
					__('Schema Markup Generator is a %1$s Feature', td),
					'PRO'
				)
			},
			features : [
				__('Unlimited Schema', td),
				__('Validate with Google', td),
				__('Increase Rankings', td),
				__('Additional Schema Types', td)
			]
		}
	},
	methods : {
		getSchemaTypeOption (option) {
			return this.schemaTypes.find(st => st.value === option)
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-sa-ct-schema-lite {
	min-height: 580px;

	@media (max-width: 598px) {
		min-height: 640px;
		.aioseo-cta.floating{
			top: 0;
		}
	}

	@media (max-width: 420px) {
		min-height: 770px;
	}

	.aioseo-cta {
		.header-text {
			width: 100%;
			max-width: 600px;
		}
	}

	.schema-type {
		max-width: 250px;
	}
}
</style>