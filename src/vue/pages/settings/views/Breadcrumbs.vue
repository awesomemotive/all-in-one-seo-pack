<template>
	<div class="aioseo-breadcrumbs">
		<core-card
			slug="enableBreadcrumbs"
			:header-text="strings.breadcrumbs"
		>
			<template #tooltip>
				{{ strings.descriptionTooltip }}
			</template>

			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}
				<span v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'breadcrumbsDisplay', true)"/>
			</div>

			<core-settings-row
				:name="strings.enableBreadcrumbs"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.breadcrumbs.enable"
					/>
				</template>
			</core-settings-row>

			<core-ui-element-slider
				v-if="optionsStore.options.breadcrumbs.enable"
				:label="strings.showBreadcrumbsOnYourWebsite"
				:options="displayInfo"
			/>
		</core-card>

		<core-card
			v-if="optionsStore.options.breadcrumbs.enable"
			slug="breadcrumbSettings"
			:header-text="strings.breadcrumbSettings"
		>
			<template #tooltip>
				{{ strings.breadcrumbTooltip }}
			</template>

			<core-settings-row
				:name="$constants.GLOBAL_STRINGS.preview"
			>
				<template #content>
					<div class="previews-box main-preview">
						<template
							v-for="(previewItem, preview_index) in previews"
							:key="preview_index"
						>
							<preview
								:preview-data="previewItem.preview"
								:label="previewItem.label"
							/>
						</template>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.separator"
			>
				<template #content>
					<core-settings-separator
						:options-separator="optionsStore.options.breadcrumbs.separator"
						@update:separator="value => optionsStore.options.breadcrumbs.separator = value"
						show-more-slug="breadcrumbsShowMoreSeparators"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.homepageLink"
			>
				<template #content>
					<div class="homepage-link">
						<base-radio-toggle
							v-model="optionsStore.options.breadcrumbs.homepageLink"
							name="homepageLink"
							:options="[
								{ label: $constants.GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
								{ label: $constants.GLOBAL_STRINGS.on, value: true }
							]"
						/>

						<div class="homepage-link-label">
							{{ strings.homepageLabel }}

							<base-input
								size="medium"
								:modelValue="sanitizeString(optionsStore.options.breadcrumbs.homepageLabel)"
								@update:modelValue="value => optionsStore.options.breadcrumbs.homepageLabel = sanitizeString(value)"
								v-model="optionsStore.options.breadcrumbs.homepageLabel"
							/>
						</div>
					</div>

					<div class="aioseo-description">
						{{ strings.homepageDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.breadcrumbPrefix"
			>
				<template #content>
					<base-input
						:modelValue="sanitizeString(optionsStore.options.breadcrumbs.breadcrumbPrefix)"
						@update:modelValue="value => optionsStore.options.breadcrumbs.breadcrumbPrefix = sanitizeString(value)"
						size="medium"
					/>

					<div class="aioseo-description">
						{{ strings.breadcrumbPrefixDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="0 < rootStore.aioseo.data.staticBlogPage"
				:name="strings.showBlogHome"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.breadcrumbs.showBlogHome"
						name="showBlogHome"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.on, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.archiveFormat"
			>
				<template #content>
					<core-html-tags-editor
						:modelValue="sanitizeString(optionsStore.options.breadcrumbs.archiveFormat)"
						@update:modelValue="value => optionsStore.options.breadcrumbs.archiveFormat = sanitizeString(value)"
						:line-numbers="false"
						single
						checkUnfilteredHtml
						tags-context="breadcrumbs-format-archive"
						:minimum-line-numbers="3"
						:showAllTagsLink="false"
						:default-tags="[
							'breadcrumb_archive_post_type_name',
						]"
						tagsDescription=""
					/>

					<div class="aioseo-description">
						{{ strings.archiveFormatDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.searchResultFormat"
			>
				<template #content>
					<core-html-tags-editor
						:modelValue="sanitizeString(optionsStore.options.breadcrumbs.searchResultFormat)"
						@update:modelValue="value => optionsStore.options.breadcrumbs.searchResultFormat = sanitizeString(value)"
						:line-numbers="false"
						single
						checkUnfilteredHtml
						tags-context="breadcrumbs-format-search"
						:minimum-line-numbers="3"
						:showAllTagsLink="false"
						:default-tags="[
							'breadcrumb_search_string',
						]"
						tagsDescription=""
					/>

					<div class="aioseo-description">
						{{ strings.searchResultFormatDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.errorFormat404"
			>
				<template #content>
					<core-html-tags-editor
						:modelValue="sanitizeString(optionsStore.options.breadcrumbs.errorFormat404)"
						@update:modelValue="value => optionsStore.options.breadcrumbs.errorFormat404 = sanitizeString(value)"
						:line-numbers="false"
						:allowTags="false"
						:disableEmoji="false"
						single
						checkUnfilteredHtml
						:minimum-line-numbers="3"
						:showAllTagsLink="false"
						tagsDescription=""
					/>
					<div class="aioseo-description">
						{{ strings.errorFormat404Description }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.currentItem"
			>
				<template #content>
					<div class="aioseo-description first">
						<base-toggle
							v-model="optionsStore.options.breadcrumbs.showCurrentItem"
							class="current-item"
						/>
						{{ strings.showCurrentItem }}
					</div>

					<div class="aioseo-description" v-if="optionsStore.options.breadcrumbs.showCurrentItem">
						<base-toggle
							v-model="optionsStore.options.breadcrumbs.linkCurrentItem"
							class="current-item"
						/>
						{{ strings.linkCurrentItem }}
					</div>
				</template>
			</core-settings-row>
		</core-card>

		<breadcrumbs-lite
			v-if="licenseStore.isUnlicensed"
		/>

		<breadcrumbs
			v-if="optionsStore.options.breadcrumbs.enable && !licenseStore.isUnlicensed"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { decode } from 'he'
import { sanitizeString } from '@/vue/utils/strings'
import { useWidgets } from '@/vue/composables'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import Breadcrumbs from './AIOSEO_VERSION/Breadcrumbs'
import BreadcrumbsLite from './lite/Breadcrumbs'
import CoreCard from '@/vue/components/common/core/Card'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreSettingsSeparator from '@/vue/components/common/core/SettingsSeparator'
import CoreUiElementSlider from '@/vue/components/common/core/ui-element-slider/Index'
import Preview from './partials/Breadcrumbs/Preview'
export default {
	setup () {
		const { strings } = useWidgets({ name: 'breadcrumbs' })

		return {
			licenseStore      : useLicenseStore(),
			optionsStore      : useOptionsStore(),
			rootStore         : useRootStore(),
			composableStrings : strings
		}
	},
	components : {
		BaseRadioToggle,
		Breadcrumbs,
		BreadcrumbsLite,
		CoreCard,
		CoreHtmlTagsEditor,
		CoreSettingsRow,
		CoreSettingsSeparator,
		CoreUiElementSlider,
		Preview
	},
	data () {
		return {
			displayInfo : {
				block : {
					copy : '',
					desc : this.$t.sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Learn More" link.
						this.$t.__('To add this block, edit a page or post and search for the "%1$s - Breadcrumbs" block. %2$s', this.$td),
						import.meta.env.VITE_SHORT_NAME,
						this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'breadcrumbsDisplay', true)
					)
				},
				shortcode : {
					copy : '[aioseo_breadcrumbs]',
					desc : this.$t.sprintf(
						// Translators: 1 - Learn more link.
						this.$t.__('Use the following shortcode to display the current breadcrumbs. %1$s', this.$td),
						this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'breadcrumbsShortcode', true)
					)
				},
				widget : {
					copy : '',
					desc : `${this.composableStrings.visitWidgetsPage} ${this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'breadcrumbsDisplay', true)}`
				},
				php : {
					copy : '<?php if( function_exists( \'aioseo_breadcrumbs\' ) ) aioseo_breadcrumbs(); ?>',
					desc : this.$t.sprintf(
						// Translators: 1 - Learn more link.
						this.$t.__('Use the following PHP code anywhere in your theme (in the loop) to display the breadcrumbs. %1$s', this.$td),
						this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'breadcrumbsFunction', true)
					)
				}
			},
			strings : {
				description : this.$t.sprintf(
					// Translators: 1 - The plugin name ("AIOSEO").
					this.$t.__('Breadcrumbs are an essential part of SEO. By default %1$s will automatically add breadcrumbs to the schema markup that we add to your site and you don\'t need to make any changes for that to work. Breadcrumbs can also be used as a secondary navigation system that tells users where they are on a website relative to the homepage.', this.$td), import.meta.env.VITE_SHORT_NAME
				),
				descriptionTooltip           : this.$t.__('The purpose of breadcrumb navigation is to help users navigate around your website. It also helps search engines understand the structure and hierarchy of links on a web page.', this.$td),
				breadcrumbs                  : this.$t.__('Breadcrumbs', this.$td),
				enableBreadcrumbs            : this.$t.__('Enable Breadcrumbs', this.$td),
				showBreadcrumbsOnYourWebsite : this.$t.__('Show Breadcrumbs on Your Website', this.$td),
				breadcrumbSettings           : this.$t.__('Breadcrumb Settings', this.$td),
				breadcrumbTooltip            : this.$t.sprintf(
					// Translators: 1 - The plugin name ("AIOSEO").
					this.$t.__('These settings will affect all the breadcrumbs displayed by %1$s throughout your site.', this.$td), import.meta.env.VITE_SHORT_NAME
				),
				separator                     : this.$t.__('Separator', this.$td),
				homepageLink                  : this.$t.__('Homepage Link', this.$td),
				homepageLabel                 : this.$t.__('Homepage label', this.$td),
				homepageDescription           : this.$t.__('Label used for homepage link (first item) in breadcrumbs.', this.$td),
				breadcrumbPrefix              : this.$t.__('Breadcrumb Prefix', this.$td),
				breadcrumbPrefixDescription   : this.$t.__('Prefix for breadcrumb path.', this.$td),
				archiveFormat                 : this.$t.__('Archive Format', this.$td),
				archiveFormatDescription      : this.$t.__('Format the label used for archives page.', this.$td),
				searchResultFormat            : this.$t.__('Search Result Format', this.$td),
				searchResultFormatDescription : this.$t.__('Format the label used for the search results page.', this.$td),
				errorFormat404                : this.$t.__('404 Error Format', this.$td),
				errorFormat404Description     : this.$t.__('Format the label used for the 404 error page.', this.$td),
				currentItem                   : this.$t.__('Current Item', this.$td),
				showCurrentItem               : this.$t.__('Show current item', this.$td),
				linkCurrentItem               : this.$t.__('Link current item', this.$td),
				home                          : this.$t.__('Home', this.$td),
				category                      : this.$t.__('Category', this.$td),
				subcategory                   : this.$t.__('Subcategory', this.$td),
				articleTitle                  : this.$t.__('Article Title', this.$td),
				searchKeyword                 : this.$t.__('search key word goes here', this.$td),
				categoryName                  : this.$t.__('Category Name', this.$td),
				breadcrumbTemplates           : this.$t.__('Breadcrumb Templates', this.$td),
				categoryHierarchy             : this.$t.__('Category Hierarchy', this.$td),
				categoryHierarchyDescription  : this.$t.__('Display complete category hierarchy even if not selected on each individual post.', this.$td),
				blog                          : this.$t.__('Blog', this.$td),
				blogCrumb                     : this.$t.__('Blog Page Title', this.$td),
				showBlogHome                  : this.$t.__('Show Blog Home', this.$td)
			}
		}
	},
	methods : {
		getRootPreview () {
			const homepageLabel = this.optionsStore.options.breadcrumbs.homepageLabel ? this.optionsStore.options.breadcrumbs.homepageLabel : 'Home'
			return [
				this.optionsStore.options.breadcrumbs.breadcrumbPrefix ? this.optionsStore.options.breadcrumbs.breadcrumbPrefix : '',
				this.optionsStore.options.breadcrumbs.homepageLink ? homepageLabel : ''
			].filter(item => !!item).map(item => decode(item))
		},
		getPostPreview () {
			return [ ...this.getRootPreview(), ...[
				this.optionsStore.options.breadcrumbs.showBlogHome ? 'Blog Home' : '',
				this.strings.category,
				this.strings.subcategory,
				this.strings.articleTitle
			] ].filter(item => !!item).map(item => decode(item))
		},
		getArchivePreview () {
			return [ ...this.getRootPreview(), ...[
				this.getArchivesOfText
			] ].filter(item => !!item).map(item => decode(item))
		},
		getSearchPreview () {
			return [ ...this.getRootPreview(), ...[
				this.getSearchForText
			] ].filter(item => !!item).map(item => decode(item))
		},
		get404Preview () {
			return [ ...this.getRootPreview(), ...[
				this.optionsStore.options.breadcrumbs.errorFormat404
			] ].filter(item => !!item).map(item => decode(item))
		},
		sanitizeString
	},
	computed : {
		getSearchForText () {
			return this.optionsStore.options.breadcrumbs.searchResultFormat.replace(new RegExp('#breadcrumb_search_string', 'g'), `<strong>${this.strings.searchKeyword}</strong>`)
		},
		getArchivesOfText () {
			return this.optionsStore.options.breadcrumbs.archiveFormat.replace(new RegExp('#breadcrumb_archive_post_type_name', 'g'), `<strong>${this.strings.categoryName}</strong>`)
		},
		getPagedText () {
			return this.optionsStore.options.breadcrumbs.pagedFormat.replace(new RegExp('#breadcrumb_format_page_number', 'g'), '<strong>2</strong>')
		},
		previews () {
			return [
				{ label: 'Post', preview: this.getPostPreview() },
				{ label: 'Archive', preview: this.getArchivePreview() },
				{ label: 'Search', preview: this.getSearchPreview() },
				{ label: '404', preview: this.get404Preview() }
			]
		}
	}
}
</script>

<style lang="scss">
.aioseo-breadcrumbs {

	.alert {
		margin-top: 24px;
	}

	svg.aioseo-shortcode {
		width: 100%;
		height: auto;
		max-width: 60px;
	}

	svg.aioseo-gutenberg-block {
		width: 59px;
		height: 54px;

		rect {
			width: 100%;
			width: 56px;
			height: 51px;
		}
	}

	svg.aioseo-php {
		width: 110px;
	}

	.previews-box {
		padding: 12px;
		border: 1px solid $input-border;
		border-radius: 4px;

		+ .aioseo-row {
			margin-top: 16px;
		}
	}

	.settings-content > div:not(.previews-box) > .aioseo-preview-box {
		padding: 12px;
		border: 1px solid $input-border;
		border-radius: 4px;

		+ .aioseo-row {
			margin-top: 16px;

			> .aioseo-col:first-child {
				margin-top: 0;
			}

			+ .aioseo-row {
				margin-top: 16px;
			}
		}
	}

	.aioseo-preview-box {
		font-size: $font-md;
		line-height: 22px;

		+ .aioseo-preview-box {
			margin-top: 8px;
		}

		.label {
			font-weight: $font-bold;
			min-width: 75px;
			display: inline-block;
		}

		.breadcrumb-preview {
			padding-bottom: 20px;

			&:last-child {
				padding-bottom: 0;
			}

			span:last-child {
				font-weight: $font-bold;
			}
		}

		.aioseo-breadcrumb-separator {
			color: $placeholder-color;
			padding: 0 4px;
		}

		.aioseo-breadcrumb {
			padding: 0 4px;
		}

		.last.noLink {
			font-weight: $font-bold;

			a {
				font-weight: $font-bold;
			}
		}

		.link, a {
			color: $blue;
			text-decoration: underline;
			font-weight: $font-bold;
			cursor: pointer;
			pointer-events: none;
		}

		.noLink {

			a {
				color: inherit;
				text-decoration: none;
				cursor: inherit;
				pointer-events: none;
			}
		}
	}

	.homepage-link {
		display: flex;
		align-items: center;

		.homepage-link-label {
			font-size: $font-md;
			line-height: 22px;
			flex: 1 1 auto;
			margin: 0 0 0 $gutter;
			display: flex;
			align-items: center;

			.aioseo-input-container {
				flex: 1;
			}

			.aioseo-input {
				margin-left: 10px;
				max-width: 250px;
			}
		}

		@media only screen and (max-width: 782px) {
			display: block;

			.homepage-link-label {
				margin: 20px 0 0;

				.aioseo-input {
					max-width: 215px;
				}
			}
		}
	}

	.content:is([active="ContentTypes"], [active="Taxonomies"], [active="Archives"]) {

		.settings-name:not(.no-name) {
			margin-top: 4px;
		}
	}
}
</style>