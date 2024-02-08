<template>
	<core-blur>
		<div id="aioseo-author-seo">
			<core-card
				id="aioseo-author-seo-settings"
				slug="authorSeoSettings"
				:header-text="strings.settings"
				noSlide
			>
				<core-ui-element-slider :options="uiElementSliderOptions" plural/>

				<core-settings-row
					:name="strings.authorBioInjection"
				>
					<template #content>
						<base-radio-toggle
							value="true"
							name="authorBioInjection"
							:options="[
								{ label: $constants.GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
								{ label: $constants.GLOBAL_STRINGS.enabled, value: true }
							]"
						/>

						<div class="aioseo-description">
							{{ strings.authorBioInjectionDescription }}

							<span
								v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'eeatAuthorBioInjection', true)"
							/>
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.postTypes"
				>
					<template #content>
						<base-checkbox
							size="medium"
							value="true"
						>
							{{ strings.includeAllPostTypes }}
						</base-checkbox>

						<div class="aioseo-description">
							{{ strings.selectPostTypes }}
						</div>
					</template>
				</core-settings-row>
			</core-card>

			<core-card
				id="aioseo-author-seo-topics"
				slug="authorSeoTopics"
				:header-text="strings.experienceTopics"
				noSlide
			>
				<div
					class="aioseo-settings-row aioseo-section-description"
					v-html="strings.experienceTopicsDescription"
				/>

				<table class="topics-table">
					<thead>
						<tr>
							<td>
								<div class="tooltip-wrapper">
									{{ strings.name }}

									<core-tooltip>
										<svg-circle-question-mark />

										<template #tooltip>
											{{ strings.nameTooltip }}
										</template>
									</core-tooltip>
								</div>
							</td>
							<td>
								<div class="tooltip-wrapper">
									{{ strings.url }}

									<core-tooltip>
										<svg-circle-question-mark />

										<template #tooltip>
											{{ strings.urlTooltip }}
										</template>
									</core-tooltip>
								</div>
							</td>
							<td>
								<div class="tooltip-wrapper">
									{{ strings.sameAsUrls }}

									<core-tooltip>
										<svg-circle-question-mark />

										<template #tooltip>
											{{ strings.sameAsUrlsTooltip }}
										</template>
									</core-tooltip>
								</div>
							</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="(row, index) in rows"
							:class="{ even: 0 === index % 2 }"
							:key="index"
						>
							<td class="name">
								<base-input
									size="medium"
									v-model="row.name"
									append-icon="circle-close"
									append-icon-on-value
									@append-icon-click="row.name = ''"
								/>
							</td>
							<td class="url">
								<base-input
									size="medium"
									v-model="row.url"
									append-icon="circle-close"
									append-icon-on-value
									@append-icon-click="row.url = ''"
								/>
							</td>
							<td class="same-as-urls">
								<base-select
									size="medium"
									multiple
									taggable
									:options="getJsonValue(row.sameAsUrls) || []"
									:modelValue="getJsonValue(row.sameAsUrls) || []"
									:placeholder="strings.sameAsUrlsPlaceholder"
									:tag-placeholder="strings.tagPlaceholder"
								/>
							</td>
							<td class="actions">
								<core-tooltip
									type="action"
								>
									<svg-trash />

									<template #tooltip>
										{{ strings.delete }}
									</template>
								</core-tooltip>
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4">
								<base-button
									size="small-table"
									type="black"
								>
									<svg-circle-plus />
									{{ strings.addItem }}
								</base-button>
							</td>
						</tr>
					</tfoot>
				</table>
			</core-card>
		</div>
	</core-blur>
</template>

<script setup>
import { useJsonValues } from '@/vue/composables'
import { __, sprintf } from '@wordpress/i18n'

import BaseButton from '@/vue/components/common/base/Button'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseInput from '@/vue/components/common/base/Input'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import BaseSelect from '@/vue/components/common/base/Select'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreUiElementSlider from '@/vue/components/common/core/ui-element-slider/Index'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgTrash from '@/vue/components/common/svg/Trash'

const { getJsonValue } = useJsonValues()

const rows = [
	{
		name       : 'WordPress',
		url        : 'https://wordpress.org',
		sameAsUrls : []
	},
	{
		name       : 'SEO',
		url        : 'https://aioseo.com',
		sameAsUrls : []
	},
	{
		name       : 'Schema Markup',
		url        : 'https://schema.org',
		sameAsUrls : []
	}
]

const td = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	settings                    : __('Settings', td),
	experienceTopics            : __('Author Experience Topics (E-E-A-T)', td),
	experienceTopicsDescription : sprintf(
		// Translators: 1 - Opening <code> tag, 2 - Closing </code> tag.
		__('The following settings will be added directly to an author\'s schema meta data via the %1$sknowsAbout%2$s property. This property helps with the Experience aspect of Google\'s E-E-A-T guidelines. After setting the global options here, you can add them directly in an authors profile page.', td),
		'<code>',
		'</code>'
	),
	name                          : __('Name', td),
	url                           : __('URL', td),
	sameAsUrls                    : __('Same As URLs', td),
	addItem                       : __('Add Item', td),
	delete                        : __('Delete', td),
	nameTooltip                   : __('The name of the item the author knows about (e.g. "Amazon").', td),
	urlTooltip                    : __('The URL of the item the author knows about (e.g. "https://amazon.com").', td),
	sameAsUrlsTooltip             : __('Additional URLs to help identify the item (e.g. "https://en.wikipedia.org/wiki/Amazon_(company)").', td),
	sameAsUrlsPlaceholder         : __('Enter a URL and press enter', td),
	tagPlaceholder                : __('Press enter to insert a URL', td),
	authorBioInjection            : __('Append Author Bio to Posts', td),
	authorBioInjectionDescription : sprintf(
		// Translators: 1 - Opening <code> tag, 2 - Closing </code> tag.
		__('Choose whether %1$s should automatically append a compact author bio at the end of every post. You can also manually insert the author bio using the Author Bio block.', td),
		import.meta.env.VITE_AIOSEO_SHORT_NAME
	),
	postTypes           : __('Post Types', td),
	includeAllPostTypes : __('Include All Post Types', td),
	selectPostTypes     : __('Select the post types for which you want to automatically inject an author bio.', td)
}

const uiElementSliderOptions = {
	block : {
		desc : sprintf(
			'<p>The following blocks are available in the Block Editor:</p><ul style="list-style:disc; margin-left: 24px;"><li>AIOSEO - Author Bio</li><li>AIOSEO - Author Name</li><li>AIOSEO - Reviewer Name</li></ul>'
		)
	},
	shortcode : {
		multiple : [
			{
				copy        : '[aioseo_eeat_author_bio]',
				desc        : __('Use the following shortcode to display the author bio.', td),
				hasAdvanced : true,
				attributes  : [
					{
						name        : 'compact',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether the compact author bio should be output or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					}
				],
				attributesDescription : __('The following shortcode attributes can be used to override the default settings:', td)
			},
			{
				copy        : '[aioseo_eeat_author_tooltip]',
				desc        : __('Use the following shortcode to display the author name.', td),
				hasAdvanced : true,
				attributes  : [
					{
						name        : 'show-label',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the "Written By" label or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : 'show-image',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the author image or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : 'show-tooltip',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the popup when someone hovers over the name or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					}
				],
				attributesDescription : __('The following shortcode attributes can be used to override the default settings:', td)
			},
			{
				copy        : '[aioseo_eeat_reviewer_tooltip]',
				desc        : __('Use the following shortcode to display the reviewer name.', td),
				hasAdvanced : true,
				attributes  : [
					{
						name        : 'show-label',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the "Reviewed By" label or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : 'show-image',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the reviewer image or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : 'show-tooltip',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the popup when someone hovers over the name or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					}
				],
				attributesDescription : __('The following shortcode attributes can be used to override the default settings:', td)
			}
		]
	},
	php : {
		multiple : [
			{
				copy        : '<?php if( function_exists( \'aioseo_eeat_author_bio\' ) ) aioseo_eeat_author_bio(); ?>',
				desc        : __('Use the following PHP code anywhere in your theme\'s post templates or author archive template to display a bio for the author.', td),
				hasAdvanced : true,
				attributes  : [
					{
						name        : '$compact',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether the compact author bio should be output or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					}
				],
				attributesDescription : __('The following function arguments can be used to override the default settings:', td)
			},
			{
				copy        : '<?php if( function_exists( \'aioseo_eeat_author_tooltip\' ) ) aioseo_eeat_author_tooltip(); ?>',
				desc        : __('Use the following PHP code anywhere in your theme\'s post templates to display a bio for the post author.', td),
				hasAdvanced : true,
				attributes  : [
					{
						name        : '$showLabel',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the "Written By" label or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : '$showImage',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the author image or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : '$showTooltip',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the popup when someone hovers over the name or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					}
				],
				attributesDescription : __('The following function arguments can be used to override the default settings:', td)
			},
			{
				copy        : '<?php if( function_exists( \'aioseo_eeat_reviewer_tooltip\' ) ) aioseo_eeat_reviewer_tooltip(); ?>',
				desc        : __('Use the following PHP code anywhere in your theme\'s post templates to display a bio for the post reviewer.', td),
				hasAdvanced : true,
				attributes  : [
					{
						name        : '$showLabel',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the "Reviewed By" label or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : '$showImage',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the reviewer image or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					},
					{
						name        : '$showTooltip',
						description : sprintf(
							// Translators: 1 - The default value.
							__('Whether to display the popup when someone hovers over the name or not. Defaults to %1$s.', td),
							'<code>true</code>'
						)
					}
				],
				attributesDescription : __('The following function arguments can be used to override the default settings:', td)
			}
		]
	}
}
</script>

<style lang="scss">
@import '@/vue/assets/scss/redirects/table.scss';

.topics-table {
	thead tr .tooltip-wrapper {
		display: flex;
		align-items: center;
		gap: 5px;

		div {
			display: flex;

			svg {
				cursor: pointer;
			}
		}
	}

	.append-icon {
		svg {
			cursor: pointer;

			&:hover {
				color: $placeholder-color;
			}
		}
	}

	.same-as-urls {
		max-width: 300px;
		min-width: 300px;
	}

	svg.aioseo-circle-plus {
		width: 14px;
		height: 14px;
		margin-right: 8px;
	}
}
</style>