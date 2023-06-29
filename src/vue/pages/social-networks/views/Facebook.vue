<template>
	<div class="aioseo-facebook">
		<core-card
			slug="facebook"
			:header-text="strings.generalFacebookSettings"
		>
			<div
				class="aioseo-settings-row aioseo-section-description"
			>
				{{ strings.description }}
				<span
					v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'facebook', true)"
				/>
			</div>

			<core-settings-row
				:name="strings.enableOpenGraph"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.social.facebook.general.enable"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="facebook-default-image-source"
				v-if="optionsStore.options.social.facebook.general.enable"
				:name="strings.defaultImageSourcePosts"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="imageSourceOptions"
						:modelValue="getImageSourceOption(optionsStore.options.social.facebook.general.defaultImageSourcePosts)"
						@update:modelValue="value => optionsStore.options.social.facebook.general.defaultImageSourcePosts = value.value"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable && 'custom' === optionsStore.options.social.facebook.general.defaultImageSourcePosts"
				:name="strings.postCustomFieldName"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.social.facebook.general.customFieldImagePosts"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable"
				class="facebook-image"
				:name="strings.defaultFacebookImagePosts"
				align
			>
				<template #content>
					<core-image-uploader
						:description="strings.minimumSize"
						v-model="optionsStore.options.social.facebook.general.defaultImagePosts"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="facebook-default-image-source"
				v-if="optionsStore.options.social.facebook.general.enable"
				:name="strings.defaultImageSourceTerms"
				align
			>
				<template #content>
					<base-select
						v-if="!licenseStore.isUnlicensed"
						size="medium"
						:options="getTermImageSourceOptions()"
						:modelValue="getImageSourceOption(optionsStore.options.social.facebook.general.defaultImageSourceTerms)"
						@update:modelValue="value => optionsStore.options.social.facebook.general.defaultImageSourceTerms = value.value"
					/>

					<base-select
						v-if="licenseStore.isUnlicensed"
						size="medium"
						:options="getTermImageSourceOptions()"
						:modelValue="getImageSourceOption('default')"
						disabled
					/>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.defaultTermImageSourceUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable && 'custom' === optionsStore.options.social.facebook.general.defaultImageSourceTerms && !licenseStore.isUnlicensed"
				:name="strings.termsCustomFieldName"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.social.facebook.general.customFieldImageTerms"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable && !licenseStore.isUnlicensed"
				class="facebook-image"
				:name="strings.defaultFacebookImageTerms"
				align
			>
				<template #content>
					<core-image-uploader
						:description="strings.minimumSize"
						v-model="optionsStore.options.social.facebook.general.defaultImageTerms"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable"
				:name="strings.postTypeObjectTypes"
				align
			>
				<template #content>
					<table-row
						class="facebook-object-types"
						v-for="(postType, index) in rootStore.aioseo.postData.postTypes"
						:key="index"
					>
						<table-column>
							{{ postType.label }}
						</table-column>

						<table-column>
							<base-select
								size="medium"
								:options="objectTypeOptions"
								group-label="groupLabel"
								group-values="options"
								:modelValue="getObjectTypeOptions(optionsStore.dynamicOptions.social.facebook.general.postTypes[postType.name].objectType)"
								@update:modelValue="value => optionsStore.dynamicOptions.social.facebook.general.postTypes[postType.name].objectType = value.value"
							/>
						</table-column>
					</table-row>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable"
				:name="strings.taxonomyObjectTypes"
				align
			>
				<template #content>
					<table-row
						class="facebook-object-types"
						v-for="(taxonomy, index) in rootStore.aioseo.postData.taxonomies"
						:key="index"
					>
						<table-column>
							{{ taxonomy.label }} ({{ taxonomy.name }})
						</table-column>

						<table-column>
							<base-select
								v-if="!licenseStore.isUnlicensed"
								size="medium"
								:options="objectTypeOptions"
								group-label="groupLabel"
								group-values="options"
								:modelValue="getObjectTypeOptions(optionsStore.dynamicOptions.social.facebook.general.taxonomies[taxonomy.name].objectType)"
								@update:modelValue="value => optionsStore.dynamicOptions.social.facebook.general.taxonomies[taxonomy.name].objectType = value.value"
							/>

							<base-select
								v-if="licenseStore.isUnlicensed"
								size="medium"
								:options="objectTypeOptions"
								group-label="groupLabel"
								group-values="options"
								:modelValue="getObjectTypeOptions('article')"
								disabled
							/>
						</table-column>
					</table-row>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.taxonomyObjectTypesUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable"
				:name="strings.showFacebookAuthor"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.facebook.general.showAuthor"
						name="showFacebookAuthor"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.general.enable"
				:name="strings.siteName"
				align
			>
				<template #content>
					<core-html-tags-editor
						class="facebook-meta-input"
						v-model="optionsStore.options.social.facebook.general.siteName"
						:line-numbers="false"
						single
						@counter="count => updateCount(count, 'siteNameCount')"
						tags-context="homePage"
						:default-tags="[
							'site_title',
							'tagline',
							'separator_sa'
						]"
					>
						<template #tags-description>
							{{ strings.clickToAddSiteName }}
						</template>
					</core-html-tags-editor>

					<div
						class="max-recommended-count"
						v-html="maxRecommendedCount(siteNameCount, 95)"
					/>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			v-if="optionsStore.options.social.facebook.general.enable"
			slug="facebookHomePageSettings"
			:header-text="strings.homePageSettings"
		>
			<div
				v-if="rootStore.aioseo.data.staticHomePage"
				class="aioseo-settings-row aioseo-section-description"
			>
				<span v-html="strings.homePageDisabledDescription" />
				&nbsp;
				<span
					v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'staticHomePageFacebook', true)"
				/>
			</div>

			<core-settings-row
				:name="$constants.GLOBAL_STRINGS.preview"
			>
				<template #content>
					<core-facebook-preview
						:description="previewDescription"
						:image="optionsStore.options.social.facebook.homePage.image"
						:title="previewTitle"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				class="facebook-image"
				:name="strings.homePageImage"
			>
				<template #content>
					<core-image-uploader
						:description="strings.minimumSize"
						v-model="optionsStore.options.social.facebook.homePage.image"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				:name="strings.homePageTitle"
				align
			>
				<template #content>
					<core-html-tags-editor
						class="facebook-meta-input"
						v-model="optionsStore.options.social.facebook.homePage.title"
						:line-numbers="false"
						single
						@counter="count => updateCount(count, 'titleCount')"
						tags-context="homePage"
						:default-tags="[
							'site_title',
							'tagline',
							'separator_sa'
						]"
					>
						<template #tags-description>
							{{ strings.clickToAddHomePageTitle }}
						</template>
					</core-html-tags-editor>

					<div
						class="max-recommended-count"
						v-html="maxRecommendedCount(titleCount, 95)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				:name="strings.homePageDescription"
				align
			>
				<template #content>
					<core-html-tags-editor
						class="facebook-meta-input"
						v-model="optionsStore.options.social.facebook.homePage.description"
						:line-numbers="false"
						@counter="count => updateCount(count, 'descriptionCount')"
						tags-context="homePage"
						:default-tags="[
							'site_title',
							'tagline',
							'separator_sa'
						]"
					>
						<template #tags-description>
							{{ strings.clickToAddHomePageDescription }}
						</template>
					</core-html-tags-editor>

					<div
						class="max-recommended-count"
						v-html="maxRecommendedCount(descriptionCount, 200)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				class="facebook-home-page-object-type"
				:name="strings.objectType"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="objectTypeOptions"
						group-label="groupLabel"
						group-values="options"
						:modelValue="getObjectTypeOptions(optionsStore.options.social.facebook.homePage.objectType)"
						@update:modelValue="value => optionsStore.options.social.facebook.homePage.objectType = value.value"
					/>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			v-if="optionsStore.options.social.facebook.general.enable"
			slug="facebookAdvancedSettings"
			:toggles="optionsStore.options.social.facebook.advanced.enable"
		>
			<template #header>
				<base-toggle
					v-model="optionsStore.options.social.facebook.advanced.enable"
				/>

				<span>{{ strings.advancedSettings }}</span>
			</template>

			<core-settings-row
				:name="strings.facebookAdminId"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.social.facebook.advanced.adminId"
					/>

					<div class="aioseo-description">
						{{ strings.facebookAdminIdDescription }}
					</div>

					<div class="aioseo-description how-to">
						<a
							class="no-underline"
							:href="$links.getDocUrl('facebookAdminId')"
							target="_blank"
						>
							<svg-book />
						</a>
						<a
							:href="$links.getDocUrl('facebookAdminId')"
							target="_blank"
						>
							{{ strings.howToGetAdminId }}
						</a>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.facebookAppId"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.social.facebook.advanced.appId"
					/>

					<div class="aioseo-description">
						{{ strings.facebookAppIdDescription }}
					</div>

					<div class="aioseo-description how-to">
						<a
							class="no-underline"
							:href="$links.getDocUrl('facebookAppId')"
							target="_blank"
						>
							<svg-book />
						</a>
						<a
							:href="$links.getDocUrl('facebookAppId')"
							target="_blank"
						>
							{{ strings.howToGetAppId }}
						</a>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.facebookAuthorUrl"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.social.facebook.advanced.authorUrl"
					/>

					<div class="aioseo-description">
						{{ strings.facebookAuthorUrlDescription }}
					</div>

					<div class="aioseo-description how-to">
						<a
							class="no-underline"
							:href="$links.getDocUrl('facebookAuthorUrl')"
							target="_blank"
						>
							<svg-book />
						</a>
						<a
							:href="$links.getDocUrl('facebookAuthorUrl')"
							target="_blank"
						>
							{{ strings.howToGetAuthorUrl }}
						</a>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.generateArticleTags"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.facebook.advanced.generateArticleTags"
						name="generateArticleTags"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.advanced.generateArticleTags"
				:name="strings.useKeywordsInTags"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.facebook.advanced.useKeywordsInTags"
						name="useKeywordsInTags"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.advanced.generateArticleTags"
				:name="strings.useCategoriesInTags"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.facebook.advanced.useCategoriesInTags"
						name="useCategoriesInTags"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.facebook.advanced.generateArticleTags"
				:name="strings.usePostTagsInTags"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.facebook.advanced.usePostTagsInTags"
						name="usePostTagsInTags"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { ImageSourceOptions, MaxCounts, Tags } from '@/vue/mixins'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreFacebookPreview from '@/vue/components/common/core/FacebookPreview'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import SvgBook from '@/vue/components/common/svg/Book'
import TableColumn from '@/vue/components/common/table/Column'
import TableRow from '@/vue/components/common/table/Row'

export default {
	setup () {
		return {
			optionsStore : useOptionsStore(),
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		BaseRadioToggle,
		CoreAlert,
		CoreCard,
		CoreFacebookPreview,
		CoreHtmlTagsEditor,
		CoreImageUploader,
		CoreSettingsRow,
		SvgBook,
		TableColumn,
		TableRow
	},
	mixins : [ ImageSourceOptions, MaxCounts, Tags ],
	data () {
		return {
			separator        : undefined,
			siteNameCount    : 0,
			titleCount       : 0,
			descriptionCount : 0,
			option           : null,
			pagePostOptions  : [],
			strings          : {
				generalFacebookSettings   : this.$t.__('General Facebook Settings', this.$td),
				description               : this.$t.__('Enable this feature if you want Facebook and other social media to display a preview with images and a text excerpt when a link to your site is shared.', this.$td),
				enableOpenGraph           : this.$t.__('Enable Open Graph Markup', this.$td),
				defaultImageSourcePosts   : this.$t.__('Default Post Image Source', this.$td),
				defaultImageSourceTerms   : this.$t.__('Default Term Image Source', this.$td),
				width                     : this.$t.__('Width', this.$td),
				height                    : this.$t.__('Height', this.$td),
				postCustomFieldName       : this.$t.__('Post Custom Field Name', this.$td),
				termsCustomFieldName      : this.$t.__('Term Custom Field Name', this.$td),
				defaultFacebookImagePosts : this.$t.__('Default Post Facebook Image', this.$td),
				defaultFacebookImageTerms : this.$t.__('Default Term Facebook Image', this.$td),
				minimumSize               : this.$t.__('Minimum size: 200px x 200px, ideal ratio 1.91:1, 8MB max. (eg: 1640px x 856px or 3280px x 1712px for Retina screens). JPG, PNG, WEBP and GIF formats only.', this.$td),
				homePageSettings          : this.$t.__('Home Page Settings', this.$td),
				exampleSiteTitle          : this.$t.__('The Title of the Page or Site you are Sharing', this.$td),
				exampleSiteDescription    : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					this.$t.__('This is what your page configured with %1$s will look like when shared via Facebook. The site title and description will be automatically added.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				homePageImage                 : this.$t.__('Image', this.$td),
				siteName                      : this.$t.__('Site Name', this.$td),
				homePageTitle                 : this.$t.__('Title', this.$td),
				useHomePageTitle              : this.$t.__('Use the home page title', this.$td),
				clickToAddSiteName            : this.$t.__('Click on the tags below to insert variables into your site name.', this.$td),
				clickToAddHomePageTitle       : this.$t.__('Click on the tags below to insert variables into your home page title.', this.$td),
				homePageDescription           : this.$t.__('Description', this.$td),
				useHomePageDescription        : this.$t.__('Use the home page description', this.$td),
				clickToAddHomePageDescription : this.$t.__('Click on the tags below to insert variables into your description.', this.$td),
				advancedSettings              : this.$t.__('Advanced Settings', this.$td),
				facebookAdminId               : this.$t.__('Facebook Admin ID', this.$td),
				facebookAppId                 : this.$t.__('Facebook App ID', this.$td),
				facebookAuthorUrl             : this.$t.__('Facebook Author URL', this.$td),
				facebookAdminIdDescription    : this.$t.__('Enter your Facebook Admin ID here. You can enter multiple Facebook Admin IDs by separating them with a comma.', this.$td),
				facebookAppIdDescription      : this.$t.__('The Facebook App ID of the site\'s app. In order to use Facebook Insights, you must add the App ID to your page. Insights lets you view analytics for traffic to your site from Facebook. Find the App ID in your App Dashboard.', this.$td),
				facebookAuthorUrlDescription  : this.$t.__('Will be overriden if the Facebook author URL is present in the individual User Profile.', this.$td),
				howToGetAdminId               : this.$t.__('How to get your Facebook Admin ID', this.$td),
				howToGetAppId                 : this.$t.__('How to get your Facebook App ID', this.$td),
				howToGetAuthorUrl             : this.$t.__('How to get your Facebook Author URL', this.$td),
				showFacebookAuthor            : this.$t.__('Show Facebook Author', this.$td),
				postTypeObjectTypes           : this.$t.__('Default Post Type Object Types', this.$td),
				taxonomyObjectTypes           : this.$t.__('Default Taxonomy Object Types', this.$td),
				taxonomyObjectTypesUpsell     : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Learn more link.
					this.$t.__('Default Taxonomy Object Types are only available for licensed %1$s users. %2$s', this.$td),
					`<strong>${import.meta.env.VITE_SHORT_NAME} Pro</strong>`,
					this.$links.getUpsellLink('general-facebook-settings', this.$constants.GLOBAL_STRINGS.learnMore, 'default-taxonomy-object-types', true)
				),
				defaultTermImageSourceUpsell : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Learn more link.
					this.$t.__('Default Term Image Source is only available for licensed %1$s users. %2$s', this.$td),
					`<strong>${import.meta.env.VITE_SHORT_NAME} Pro</strong>`,
					this.$links.getUpsellLink('general-facebook-settings', this.$constants.GLOBAL_STRINGS.learnMore, 'default-term-image-soruce', true)
				),
				generateArticleTags         : this.$t.__('Automatically Generate Article Tags', this.$td),
				useKeywordsInTags           : this.$t.__('Use Keywords in Article Tags', this.$td),
				useCategoriesInTags         : this.$t.__('Use Categories in Article Tags', this.$td),
				usePostTagsInTags           : this.$t.__('Use Post Tags in Article Tags', this.$td),
				homePageDisabledDescription : this.$t.sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
					this.$t.__('You are using a static home page which is found under Pages. You can %1$sedit your home page settings%2$s directly to change the title, meta description and image.', this.$td),
					`<a href="${this.rootStore.aioseo.urls.staticHomePage}&aioseo-tab=social&social-tab=facebook&aioseo-scroll=aioseo-post-settings-facebook&aioseo-highlight=aioseo-post-settings-facebook">`,
					'</a>'
				),
				objectType : this.$t.__('Object Type', this.$td)
			}
		}
	},
	computed : {
		objectTypeOptions () {
			return this.$constants.OG_TYPE_OPTIONS
		},
		previewTitle () {
			if (this.rootStore.aioseo.data.staticHomePage) {
				return this.parseTags(this.rootStore.aioseo.data.staticHomePageOgTitle || '#site_title')
			}

			return this.parseTags(this.optionsStore.options.social.facebook.homePage.title || '#site_title')
		},
		previewDescription () {
			if (this.rootStore.aioseo.data.staticHomePage) {
				return this.parseTags(this.rootStore.aioseo.data.staticHomePageOgDescription || '#tagline')
			}

			return this.parseTags(this.optionsStore.options.social.facebook.homePage.description || '#tagline')
		}
	},
	methods : {
		getObjectTypeOptions (savedOption) {
			let option = null
			this.objectTypeOptions.forEach(group => {
				const localOption = group.options.find(o => o.value === savedOption)
				if (localOption) {
					option = localOption
				}
			})

			return option
		}
	}
}
</script>

<style lang="scss">
.aioseo-facebook {

	.inline-upsell {
		display: inline-flex;
		margin-top: 12px;
	}

	.facebook-image {
		img {
			margin-top: 20px;
			width: auto;
			max-width: 525px;
			max-height: 525px;
			height: auto;
		}

		&.vertical {
			img {
				max-width: 158px;
				max-height: 158px;
			}
		}
	}

	.how-to {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-top: 16px;
		font-size: 12px;
		line-height: 18px;

		svg.aioseo-book {
			width: 16px;
			height: 16px;
			vertical-align: middle;
		}
	}

	.facebook-default-image-source {

		.aioseo-select {
			max-width: 445px;
		}
	}

	.facebook-object-types {
		max-width: 445px;
		display: flex;
		flex-direction: column;
		align-items: flex-start;

		+ .facebook-object-types {
			margin-top: 12px;
		}

		.aioseo-table-column {
			width: 100%;
			padding: 0;

			&:first-child {
				font-weight: $font-bold;
				margin-bottom: 4px;
			}
		}
	}

	.facebook-home-page-object-type {

		.aioseo-select {
			max-width: 300px;
		}
	}
}
</style>