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
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'facebook', true)"
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
				id="aioseo-facebook-default-image-source"
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
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
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
						@counter="count => siteNameCount = count.length"
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
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'staticHomePageFacebook', true)"
				/>
			</div>

			<core-settings-row
				:name="GLOBAL_STRINGS.preview"
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
						@counter="count => titleCount = count.length"
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
						@counter="count => descriptionCount = count.length"
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
							:href="links.getDocUrl('facebookAdminId')"
							target="_blank"
						>
							<svg-book />
						</a>
						<a
							:href="links.getDocUrl('facebookAdminId')"
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
							:href="links.getDocUrl('facebookAppId')"
							target="_blank"
						>
							<svg-book />
						</a>
						<a
							:href="links.getDocUrl('facebookAppId')"
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
							:href="links.getDocUrl('facebookAuthorUrl')"
							target="_blank"
						>
							<svg-book />
						</a>
						<a
							:href="links.getDocUrl('facebookAuthorUrl')"
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
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
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
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
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
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
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
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import {
	GLOBAL_STRINGS,
	OG_TYPE_OPTIONS
} from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { useImage } from '@/vue/composables/Image'
import { useMaxCounts } from '@/vue/composables/MaxCounts'
import { useTags } from '@/vue/composables/Tags'

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

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getImageSourceOption,
			getTermImageSourceOptions,
			imageSourceOptions
		} = useImage()

		const {
			maxRecommendedCount
		} = useMaxCounts()

		const {
			parseTags
		} = useTags({
			separator : undefined
		})

		return {
			GLOBAL_STRINGS,
			getImageSourceOption,
			getTermImageSourceOptions,
			imageSourceOptions,
			licenseStore : useLicenseStore(),
			links,
			maxRecommendedCount,
			optionsStore : useOptionsStore(),
			parseTags,
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
	data () {
		return {
			siteNameCount    : 0,
			titleCount       : 0,
			descriptionCount : 0,
			option           : null,
			pagePostOptions  : [],
			strings          : {
				generalFacebookSettings   : __('General Facebook Settings', td),
				description               : __('Enable this feature if you want Facebook and other social media to display a preview with images and a text excerpt when a link to your site is shared.', td),
				enableOpenGraph           : __('Enable Open Graph Markup', td),
				defaultImageSourcePosts   : __('Default Post Image Source', td),
				defaultImageSourceTerms   : __('Default Term Image Source', td),
				width                     : __('Width', td),
				height                    : __('Height', td),
				postCustomFieldName       : __('Post Custom Field Name', td),
				termsCustomFieldName      : __('Term Custom Field Name', td),
				defaultFacebookImagePosts : __('Default Post Facebook Image', td),
				defaultFacebookImageTerms : __('Default Term Facebook Image', td),
				minimumSize               : __('Minimum size: 200px x 200px, ideal ratio 1.91:1, 8MB max. (eg: 1640px x 856px or 3280px x 1712px for Retina screens). JPG, PNG, WEBP and GIF formats only.', td),
				homePageSettings          : __('Home Page Settings', td),
				exampleSiteTitle          : __('The Title of the Page or Site you are Sharing', td),
				exampleSiteDescription    : sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					__('This is what your page configured with %1$s will look like when shared via Facebook. The site title and description will be automatically added.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				homePageImage                 : __('Image', td),
				siteName                      : __('Site Name', td),
				homePageTitle                 : __('Title', td),
				useHomePageTitle              : __('Use the home page title', td),
				clickToAddSiteName            : __('Click on the tags below to insert variables into your site name.', td),
				clickToAddHomePageTitle       : __('Click on the tags below to insert variables into your home page title.', td),
				homePageDescription           : __('Description', td),
				useHomePageDescription        : __('Use the home page description', td),
				clickToAddHomePageDescription : __('Click on the tags below to insert variables into your description.', td),
				advancedSettings              : __('Advanced Settings', td),
				facebookAdminId               : __('Facebook Admin ID', td),
				facebookAppId                 : __('Facebook App ID', td),
				facebookAuthorUrl             : __('Facebook Author URL', td),
				facebookAdminIdDescription    : __('Enter your Facebook Admin ID here. You can enter multiple Facebook Admin IDs by separating them with a comma.', td),
				facebookAppIdDescription      : __('The Facebook App ID of the site\'s app. In order to use Facebook Insights, you must add the App ID to your page. Insights lets you view analytics for traffic to your site from Facebook. Find the App ID in your App Dashboard.', td),
				facebookAuthorUrlDescription  : __('Will be overriden if the Facebook author URL is present in the individual User Profile.', td),
				howToGetAdminId               : __('How to get your Facebook Admin ID', td),
				howToGetAppId                 : __('How to get your Facebook App ID', td),
				howToGetAuthorUrl             : __('How to get your Facebook Author URL', td),
				showFacebookAuthor            : __('Show Facebook Author', td),
				postTypeObjectTypes           : __('Default Post Type Object Types', td),
				taxonomyObjectTypes           : __('Default Taxonomy Object Types', td),
				taxonomyObjectTypesUpsell     : sprintf(
					// Translators: 1 - "PRO", 2 - Learn more link.
					__('Default Taxonomy Object Types is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-facebook-settings', GLOBAL_STRINGS.learnMore, 'default-taxonomy-object-types', true)
				),
				defaultTermImageSourceUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - Learn more link.
					__('Default Term Image Source is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-facebook-settings', GLOBAL_STRINGS.learnMore, 'default-term-image-source', true)
				),
				generateArticleTags         : __('Automatically Generate Article Tags', td),
				useKeywordsInTags           : __('Use Keywords in Article Tags', td),
				useCategoriesInTags         : __('Use Categories in Article Tags', td),
				usePostTagsInTags           : __('Use Post Tags in Article Tags', td),
				homePageDisabledDescription : sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
					__('You are using a static home page which is found under Pages. You can %1$sedit your home page settings%2$s directly to change the title, meta description and image.', td),
					`<a href="${this.rootStore.aioseo.urls.staticHomePage}&aioseo-tab=social&social-tab=facebook&aioseo-scroll=aioseo-post-settings-facebook&aioseo-highlight=aioseo-post-settings-facebook">`,
					'</a>'
				),
				objectType : __('Object Type', td)
			}
		}
	},
	computed : {
		objectTypeOptions () {
			return OG_TYPE_OPTIONS
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