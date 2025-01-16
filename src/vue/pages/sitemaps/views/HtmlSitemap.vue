<template>
	<div class="aioseo-html-sitemap">
		<core-card
			slug="htmlSitemap"
		>
			<template #header>
				<span>{{ strings.title }}</span>
			</template>

			<div class="aioseo-settings-row aioseo-section-description">
				{{ strings.description }}
				<span
					v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'htmlSitemap', true)"
				/>
			</div>

			<core-settings-row
				:name="strings.enableSitemap"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.sitemap.html.enable"
					/>
				</template>
			</core-settings-row>

			<html-sitemap-display-info
				v-if="optionsStore.options.sitemap.html.enable"
				:label="strings.displayLabel"
				:displayOptions="displayOptions"
				:url="optionsStore.options.sitemap.html.pageUrl"
			/>
		</core-card>

		<core-card
			class="aioseo-html-sitemap-settings"
			v-if="optionsStore.options.sitemap.html.enable"
			slug="htmlSitemapSettings"
		>
			<template #header>
				<span>{{ strings.settings }}</span>
			</template>

			<core-settings-row
				:name="strings.postTypes"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.sitemap.html.postTypes.all"
					>
						{{ strings.includeAllPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.sitemap.html.postTypes.all"
						:options="optionsStore.options.sitemap.html"
						type="postTypes"
						:excluded="[ 'attachment' ]"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostTypes }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.taxonomies"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.sitemap.html.taxonomies.all"
					>
						{{ strings.includeAllTaxonomies }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.sitemap.html.taxonomies.all"
						:options="optionsStore.options.sitemap.html"
						type="taxonomies"
						:excluded="['product_attributes']"
					/>

					<div class="aioseo-description">
						{{ strings.selectTaxonomies }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.sortOrder"
				class="aioseo-sort-order"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="sortOrders"
						:modelValue="getSortOrder(optionsStore.options.sitemap.html.sortOrder)"
						@update:modelValue="value => optionsStore.options.sitemap.html.sortOrder = value.value"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.sortDirection"
				class="aioseo-sort-direction"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="sortDirections"
						:modelValue="getSortDirection(optionsStore.options.sitemap.html.sortDirection)"
						@update:modelValue="value => optionsStore.options.sitemap.html.sortDirection = value.value"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.publicationDate"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.sitemap.html.publicationDate"
						name="publicationDate"
						:options="[
							{ label: GLOBAL_STRINGS.hide, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.show, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.publicationDateDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.compactArchives"
				align
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.sitemap.html.compactArchives"
						name="compactArchives"
						:options="[
							{ label: GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.enabled, value: true }
						]"
					/>

					<div
						class="aioseo-description"
						v-html="strings.compactArchivesDescription"
					/>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			slug="htmlSitemapAdvancedSettings"
			v-if="optionsStore.options.sitemap.html.enable"
			:toggles="optionsStore.options.sitemap.html.advancedSettings.enable"
		>
			<template #header>
				<base-toggle
					v-model="optionsStore.options.sitemap.html.advancedSettings.enable"
				/>

				<span>{{ strings.advancedSettings }}</span>
			</template>

			<div
				v-if="optionsStore.options.sitemap.html.advancedSettings.enable"
			>
				<core-settings-row
					:name="strings.excludePostsPages"
					class="aioseo-exclude-pages-posts"
					align
				>
					<template #content>
						<core-exclude-posts
							:options="optionsStore.options.sitemap.html.advancedSettings"
							type="posts"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.excludeTerms"
					class="aioseo-exclude-terms"
					align
				>
					<template #content>
						<core-exclude-posts
							:options="optionsStore.options.sitemap.html.advancedSettings"
							type="terms"
						/>
					</template>
				</core-settings-row>
			</div>
		</core-card>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useOptionsStore
} from '@/vue/stores'

import { useWidgets } from '@/vue/composables/Widgets'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreCard from '@/vue/components/common/core/Card'
import CoreExcludePosts from '@/vue/components/common/core/ExcludePosts'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import HtmlSitemapDisplayInfo from '@/vue/components/common/html-sitemap/DisplayInfo'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { strings } = useWidgets({ name: 'htmlSitemap' })

		return {
			optionsStore      : useOptionsStore(),
			composableStrings : strings,
			GLOBAL_STRINGS,
			links
		}
	},
	components : {
		BaseCheckbox,
		BaseRadioToggle,
		CoreCard,
		CoreExcludePosts,
		CorePostTypeOptions,
		CoreSettingsRow,
		HtmlSitemapDisplayInfo
	},
	data () {
		const attributes = [
			{
				name        : 'post_types',
				description : __('The post types (by slug, comma-separated) that are included in the sitemap.', td)
			},
			{
				name        : 'taxonomies',
				description : __('The taxonomies (by slug, comma-separated) that are included in the sitemap.', td)
			},
			{
				name        : 'label_tag',
				description : sprintf(
					// Translators: 1 - The default value.
					__('The HTML tag that is used for the label of each section. Defaults to %1$s.', td),
					'<code>h4</code>'
				)
			},
			{
				name        : 'show_label',
				description : sprintf(
					// Translators: 1 - The default value.
					__('Whether the labels should be shown or not. Defaults to %1$s.', td),
					'<code>true</code>'
				)
			},
			{
				name        : 'publication_date',
				description : __('Whether the publication date of posts should be shown.', td)
			},
			{
				name        : 'archives',
				description : __('Whether the regular sitemap or compact date archive sitemap is output.', td)
			},
			{
				name        : 'order',
				// Translators: 1 - "ASC", 2 - "DESC".
				description : sprintf(
					// Translators: 1 - HTML code opening tag, 2 - HTML code closing tag.
					__('The sort direction. The supported values are %1$s and %2$s.', td),
					'<code>ASC</code>',
					'<code>DESC</code>'
				)
			},
			{
				name        : 'order_by',
				description : sprintf(
					// Translators: 1 - HTML code opening tag, 2 - HTML code closing tag.
					__('The sort order. The supported values are %1$s, %2$s, %3$s and %4$s.', td),
					'<code>publish_date</code>',
					'<code>last_updated</code>',
					'<code>alphabetical</code>',
					'<code>id</code>'
				)
			}
		]

		return {
			sortOrders : [
				{ label: __('Publish Date', td), value: 'publish_date' },
				{ label: __('Last Updated Date', td), value: 'last_updated' },
				{ label: __('Alphabetical', td), value: 'alphabetical' },
				{ label: __('Post/Term ID', td), value: 'id' }
			],
			sortDirections : [
				{ label: __('Ascending', td), value: 'asc' },
				{ label: __('Descending', td), value: 'desc' }
			],
			displayOptions : {
				extra : {
					desc : __('Display the sitemap on a dedicated page:', td)
				},
				block : {
					copy : '',
					desc : sprintf(
						// Translators: 1 - Opening HTML strong tag, 2 - The plugin short name ("AIOSEO"), 3 - Closing HTML strong tag.
						__('To add this block, edit a page or post and search for the %1$s"%2$s - HTML Sitemap"%3$s block.', td),
						'<strong>',
						import.meta.env.VITE_SHORT_NAME,
						'</strong>'
					)
				},
				shortcode : {
					copy : '[aioseo_html_sitemap]',
					desc : sprintf(
						// Translators: 1 - Learn more link.
						__('Use the following shortcode to display the HTML Sitemap. %1$s', td),
						links.getDocLink(GLOBAL_STRINGS.learnMore, 'htmlSitemapShortcode', true)
					),
					hasAdvanced           : true,
					attributes            : attributes,
					attributesDescription : __('The following shortcode attributes can be used to override the default settings:', td)
				},
				widget : {
					copy : '',
					desc : this.composableStrings.visitWidgetsPage
				},
				php : {
					copy : '<?php if( function_exists( \'aioseo_html_sitemap\' ) ) aioseo_html_sitemap(); ?>',
					desc : sprintf(
						// Translators: 1 - Learn more link.
						__('Use the following PHP code anywhere in your theme to display the sitemap. %1$s', td),
						links.getDocLink(GLOBAL_STRINGS.learnMore, 'htmlSitemapFunction', true)
					),
					hasAdvanced           : true,
					attributes            : attributes,
					attributesDescription : __('The function accepts an associative array with the following arguments that can be used to override the default settings:', td)
				}
			},
			strings : {
				title                      : __('HTML Sitemap', td),
				enableSitemap              : __('Enable Sitemap', td),
				settings                   : __('HTML Sitemap Settings', td),
				description                : __('Using the custom-built tools below, you can add an HTML sitemap to your website and help visitors discover all your content. Adding an HTML sitemap to your website may also help search engines find your content more easily.', td),
				displayLabel               : __('Display HTML Sitemap', td),
				postTypes                  : __('Post Types', td),
				taxonomies                 : __('Taxonomies', td),
				includeAllPostTypes        : __('Include All Post Types', td),
				selectPostTypes            : __('Select which Post Types appear in your sitemap.', td),
				includeAllTaxonomies       : __('Include All Taxonomies', td),
				selectTaxonomies           : __('Select which Taxonomies appear in your sitemap.', td),
				sortOrder                  : __('Sort Order', td),
				sortDirection              : __('Sort Direction', td),
				publicationDate            : __('Publication Date', td),
				publicationDateDescription : __('This setting only applies to posts and pages.', td),
				compactArchives            : __('Compact Archives', td),
				compactArchivesDescription : sprintf(
					// Translators: 1 - "Learn More" link.
					__('This setting allows you to toggle between the regular sitemap or the compact date archive sitemap. %1$s', td),
					links.getDocLink(GLOBAL_STRINGS.learnMore, 'htmlSitemapCompactArchives', true)
				),
				advancedSettings  : __('Advanced Settings', td),
				// nofollowLinks              : __('No Follow Links', td),
				excludePostsPages : __('Exclude Posts / Pages', td),
				excludeTerms      : __('Exclude Terms', td)
			}
		}
	},
	methods : {
		getSortOrder (option) {
			return this.sortOrders.find(o => o.value === option)
		},
		getSortDirection (option) {
			return this.sortDirections.find(o => o.value === option)
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-html-sitemap {
	.aioseo-html-sitemap-settings .aioseo-select.medium {
		max-width: 300px;
	}
}
</style>