<template>
	<div class="aioseo-twitter">
		<core-card
			slug="twitter"
			:header-text="strings.twitterCardSettings"
		>
			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}
				<span
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'twitter', true)"
				/>
			</div>

			<core-settings-row
				:name="strings.enableTwitterCard"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.social.twitter.general.enable"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.twitter.general.enable"
				class="default-card-type"
				:name="strings.defaultCardType"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="twitterCards"
						:modelValue="getCardOptions(optionsStore.options.social.twitter.general.defaultCardType)"
						@update:modelValue="value => optionsStore.options.social.twitter.general.defaultCardType = value.value"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="twitter-default-image-source"
				v-if="optionsStore.options.social.twitter.general.enable"
				:name="strings.defaultImageSourcePosts"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="imageSourceOptions"
						:modelValue="getImageSourceOption(optionsStore.options.social.twitter.general.defaultImageSourcePosts)"
						@update:modelValue="value => optionsStore.options.social.twitter.general.defaultImageSourcePosts = value.value"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.twitter.general.enable && 'custom' === optionsStore.options.social.twitter.general.defaultImageSourcePosts"
				:name="strings.postCustomFieldName"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.social.twitter.general.customFieldImagePosts"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.twitter.general.enable"
				class="twitter-image"
				:name="strings.defaultTwitterImagePosts"
				align
			>
				<template #content>
					<core-image-uploader
						:description="'summary' === optionsStore.options.social.twitter.general.defaultCardType ? strings.minimumSizeSummary : strings.minimumSizeSummaryWithLarge"
						v-model="optionsStore.options.social.twitter.general.defaultImagePosts"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="twitter-default-image-source"
				v-if="optionsStore.options.social.twitter.general.enable"
				:name="strings.defaultImageSourceTerms"
				align
			>
				<template #content>
					<base-select
						v-if="!licenseStore.isUnlicensed"
						size="medium"
						:options="getTermImageSourceOptions()"
						:modelValue="getImageSourceOption(optionsStore.options.social.twitter.general.defaultImageSourceTerms)"
						@update:modelValue="value => optionsStore.options.social.twitter.general.defaultImageSourceTerms = value.value"
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
				v-if="optionsStore.options.social.twitter.general.enable && 'custom' === optionsStore.options.social.twitter.general.defaultImageSourceTerms && !licenseStore.isUnlicensed"
				:name="strings.termsCustomFieldName"
				align
			>
				<template #content>
					<base-input
						size="medium"
						v-model="optionsStore.options.social.twitter.general.customFieldImageTerms"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.twitter.general.enable && !licenseStore.isUnlicensed"
				class="twitter-image"
				:name="strings.defaultTwitterImageTerms"
				align
			>
				<template #content>
					<core-image-uploader
						:description="'summary' === optionsStore.options.social.twitter.general.defaultCardType ? strings.minimumSizeSummary : strings.minimumSizeSummaryWithLarge"
						v-model="optionsStore.options.social.twitter.general.defaultImageTerms"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.twitter.general.enable"
				:name="strings.showTwitterAuthor"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.twitter.general.showAuthor"
						name="showTwitterAuthor"
						:options="[
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.twitter.general.enable"
				:name="strings.additionalData"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.twitter.general.additionalData"
						name="additionalData"
						:options="[
							{ label: GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.enabled, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.additionalDataDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="optionsStore.options.social.twitter.general.enable"
				:name="strings.useDataFromFacebook"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.social.twitter.general.useOgData"
						name="useOgData"
						:options="[
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
						]"
					/>
					<div class="aioseo-description">
						{{ strings.useOgDataDescription }}
					</div>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			v-if="optionsStore.options.social.twitter.general.enable"
			slug="twitterHomePageSettings"
			:header-text="strings.homePageSettings"
		>
			<div
				v-if="rootStore.aioseo.data.staticHomePage"
				class="aioseo-settings-row aioseo-section-description"
			>
				<span v-html="strings.homePageDisabledDescription" />
				&nbsp;
				<span
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'staticHomePageTwitter', true)"
				/>
			</div>

			<br
				v-if="rootStore.aioseo.data.staticHomePage"
			>

			<core-settings-row
				:name="GLOBAL_STRINGS.preview"
			>
				<template #content>
					<core-twitter-preview
						:card="optionsStore.options.social.twitter.homePage.cardType"
						:description="previewDescription"
						:image="optionsStore.options.social.twitter.homePage.image"
						:title="previewTitle"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				class="default-card-type"
				:name="strings.cardType"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="twitterCards"
						:modelValue="getCardOptions(optionsStore.options.social.twitter.homePage.cardType)"
						@update:modelValue="value => optionsStore.options.social.twitter.homePage.cardType = value.value"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				class="twitter-image"
				:name="strings.homePageImage"
				align
			>
				<template #content>
					<core-image-uploader
						:description="'summary' === optionsStore.options.social.twitter.homePage.cardType ? strings.minimumSizeSummary : strings.minimumSizeSummaryWithLarge"
						v-model="optionsStore.options.social.twitter.homePage.image"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				:name="strings.homePageTitle"
			>
				<template #content>
					<core-html-tags-editor
						class="twitter-meta-input"
						v-model="optionsStore.options.social.twitter.homePage.title"
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
						v-html="maxRecommendedCount(titleCount, 70)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!rootStore.aioseo.data.staticHomePage"
				:name="strings.homePageDescription"
			>
				<template #content>
					<core-html-tags-editor
						class="twitter-meta-input"
						v-model="optionsStore.options.social.twitter.homePage.description"
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
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
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
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTwitterPreview from '@/vue/components/common/core/TwitterPreview'

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
		CoreHtmlTagsEditor,
		CoreImageUploader,
		CoreSettingsRow,
		CoreTwitterPreview
	},
	data () {
		return {
			titleCount       : 0,
			descriptionCount : 0,
			option           : null,
			pagePostOptions  : [],
			strings          : {
				twitterCardSettings           : __('X (Twitter) Card Settings', td),
				description                   : __('Enable this feature if you want X to display a preview card with images and a text excerpt when a link to your site is shared.', td),
				enableTwitterCard             : __('Enable X Card', td),
				useDataFromFacebook           : __('Use Data from Facebook Tab', td),
				useOgDataDescription          : __('Choose whether you want to use the OG data from the Facebook tab in your individual pages/posts by default.', td),
				defaultCardType               : __('Default Card Type', td),
				summary                       : __('Summary', td),
				summaryLarge                  : __('Summary with Large Image', td),
				defaultImageSourcePosts       : __('Default Post Image Source', td),
				defaultImageSourceTerms       : __('Default Term Image Source', td),
				width                         : __('Width', td),
				height                        : __('Height', td),
				postCustomFieldName           : __('Post Custom Field Name', td),
				termsCustomFieldName          : __('Term Custom Field Name', td),
				defaultTwitterImagePosts      : __('Default Post X Image', td),
				defaultTwitterImageTerms      : __('Default Term X Image', td),
				minimumSizeSummary            : __('Minimum size: 144px x 144px, ideal ratio 1:1, 5MB max. JPG, PNG, WEBP and GIF formats only.', td),
				minimumSizeSummaryWithLarge   : __('Minimum size: 300px x 157px, ideal ratio 2:1, 5MB max. JPG, PNG, WEBP and GIF formats only.', td),
				homePageSettings              : __('Home Page Settings', td),
				homePageImage                 : __('Home Page Image', td),
				homePageTitle                 : __('Home Page Title', td),
				useHomePageTitle              : __('Use the home page title', td),
				clickToAddHomePageTitle       : __('Click on the tags below to insert variables into your home page title.', td),
				homePageDescription           : __('Description', td),
				useHomePageDescription        : __('Use the home page description', td),
				clickToAddHomePageDescription : __('Click on the tags below to insert variables into your description.', td),
				showTwitterAuthor             : __('Show X Author', td),
				homePageDisabledDescription   : sprintf(
					// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag.
					__('You are using a static home page which is found under Pages. You can %1$sedit your home page settings%2$s directly to change the title, meta description and image.', td),
					`<a href="${this.rootStore.aioseo.urls.staticHomePage}&aioseo-tab=social&social-tab=twitter&aioseo-scroll=aioseo-post-settings-twitter&aioseo-highlight=aioseo-post-settings-twitter">`,
					'</a>'
				),
				cardType                     : __('Card Type', td),
				additionalData               : __('Additional Data', td),
				additionalDataDescription    : __('Enable this option to show additional X data on your posts and pages (i.e., who the post was written by and how long it might take to read the article).', td),
				defaultTermImageSourceUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - Learn more link.
					__('Default Term Image Source is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('general-facebook-settings', GLOBAL_STRINGS.learnMore, 'default-term-image-source', true)
				)
			}
		}
	},
	computed : {
		twitterCards () {
			return [
				{ label: this.strings.summary, value: 'summary' },
				{ label: this.strings.summaryLarge, value: 'summary_large_image' }
			]
		},
		previewTitle () {
			if (this.rootStore.aioseo.data.staticHomePage) {
				return this.parseTags(this.rootStore.aioseo.data.staticHomePageTwitterTitle || '#site_title')
			}

			return this.parseTags(this.optionsStore.options.social.twitter.homePage.title || '#site_title')
		},
		previewDescription () {
			if (this.rootStore.aioseo.data.staticHomePage) {
				return this.parseTags(this.rootStore.aioseo.data.staticHomePageTwitterDescription || '#tagline')
			}

			return this.parseTags(this.optionsStore.options.social.twitter.homePage.description || '#tagline')
		}
	},
	methods : {
		getCardOptions (option) {
			return this.twitterCards.find(t => t.value === option)
		}
	}
}
</script>

<style lang="scss">
.aioseo-twitter {

	.inline-upsell {
		display: inline-flex;
		margin-top: 12px;
	}

	.twitter-image {

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

	.twitter-default-image-source {

		.aioseo-select {
			max-width: 445px;
		}
	}

	.default-card-type {

		.aioseo-select {
			max-width: 445px;
		}
	}
}
</style>