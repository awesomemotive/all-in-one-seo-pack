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
				:cta-link="$links.getPricingUrl('schema-markup', 'schema-markup-upsell')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="$links.getUpsellUrl('schema-markup', null, $isPro ? 'pricing' : 'liteUpgrade')"
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
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Cta from '@/vue/components/common/cta/Index'
export default {
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
			schemaTypes : [
				{ value: 'none', label: this.$t.__('None', this.$td) },
				{ value: 'Article', label: this.$t.__('Article', this.$td) }
			],
			strings : {
				schemaType     : this.$t.__('Schema Type', this.$td),
				articleType    : this.$t.__('Article Type', this.$td),
				article        : this.$t.__('Article', this.$td),
				blogPost       : this.$t.__('Blog Post', this.$td),
				newsArticle    : this.$t.__('News Article', this.$td),
				ctaDescription : this.$t.__('Easily generate unlimited schema markup for your content to help you rank higher in search results. Our schema validator ensures your schema works out of the box.', this.$td),
				ctaButtonText  : this.$t.__('Unlock Schema Markup Generator', this.$td),
				ctaHeader      : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Schema Markup Generator is a %1$s Feature', this.$td),
					'PRO'
				)
			},
			features : [
				this.$t.__('Unlimited Schema', this.$td),
				this.$t.__('Validate with Google', this.$td),
				this.$t.__('Increase Rankings', this.$td),
				this.$t.__('Additional Schema Types', this.$td)
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