<template>
	<core-blur>
		<div class="aioseo-sa-image-seo">
			<core-settings-row
				v-if="['caption', 'description'].includes('title')"
				:name="strings.autogenerate"
				align
			>
				<template #content>
					<base-radio-toggle
						name="autogenerate"
						:options="[
							{ label: GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.enabled, value: true }
						]"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.attributeFormat"
			>
				<template #content>
					<core-html-tags-editor
						:line-numbers="false"
						single
						:tags-context="tags.title.context"
						:default-tags="tags.title.default"
					>
						<template #tags-description>
							{{ strings.clickToAddTags }}
						</template>
					</core-html-tags-editor>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.stripPunctuation"
				align
			>
				<template #content>
					<base-radio-toggle
						name="stripPunctuation"
						:options="[
							{ label: GLOBAL_STRINGS.disabled, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.enabled, value: true }
						]"
					/>

					<div class="global-robots-settings aioseo-description">
						<strong>{{ strings.punctuationCharactersToKeep }}</strong>

						<grid-row
							class="settings"
						>
							<grid-column
								v-for="(setting, index) in stripPunctuationSettings"
								:key="index"
								xl="3"
								md="4"
								sm="6"
							>
								<base-checkbox size="medium">
								{{ setting.label }}
								</base-checkbox>
							</grid-column>
						</grid-row>
					</div>
				</template>

			</core-settings-row>

			<core-settings-row
				:name="strings.casing"
				align
			>
				<template #content>
					<base-radio-toggle
						name="casing"
						:options="[
							{ label: GLOBAL_STRINGS.disabled, value: '', activeClass: 'dark' },
							{ label: casings.lowerCase.label, value: 'lower' },
							{ label: casings.titleCase.label, value: 'title' },
							{ label: casings.sentenceCase.label, value: 'sentence' }
						]"
					/>

					<div
						class="aioseo-description"
					>
						<span>{{strings.casingDescription}}</span><br/>
						<ul class="casing-options">
							<li
								v-for="(casing, index) in casings"
								:key="index"
							>
								<span>{{casing.label}}</span>
								<span>{{casing.description}}</span>
							</li>
						</ul>
					</div>
				</template>
			</core-settings-row>
		</div>
	</core-blur>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		BaseCheckbox,
		BaseRadioToggle,
		CoreBlur,
		CoreHtmlTagsEditor,
		CoreSettingsRow,
		GridColumn,
		GridRow
	},
	props : {
		activeTab : Object
	},
	data () {
		return {
			GLOBAL_STRINGS,
			stripPunctuationSettings : [
				{ value: 'dashes', label: __('Dashes (-)', td) },
				{ value: 'underscores', label: __('Underscores (_)', td) },
				{ value: 'numbers', label: __('Numbers (0-9)', td) },
				{ value: 'plus', label: __('Plus (+)', td) },
				{ value: 'apostrophe', label: __('Apostrophe (\')', td) },
				{ value: 'pound', label: __('Pound (#)', td) },
				{ value: 'ampersand', label: __('Ampersand (&)', td) }
			],
			strings : {
				attributeFormat : sprintf(
					// Translators: 1 - The type of format ("Title", "Alt Tag", "Caption" or "Description").
					__('%1$s Format', td),
					__('Title', td)
				),
				clickToAddTags : sprintf(
					// Translators: 1 - The name of the image attribute ("Title", "Alt Tag", "Caption" or "Description").
					__('Click on the tags below to insert variables into your %1$s attribute.', td),
					__('Title', td).toLowerCase()
				),
				stripPunctuation            : __('Strip Punctuation', td),
				punctuationCharactersToKeep : __('Punctuation Characters to Keep:', td),
				casing                      : __('Casing', td),
				casingDescription           : __('Choose which casing should be applied to the attribute.', td),
				wordsToStrip                : __('Words to Strip', td),
				autogenerate                : sprintf(
					// Translators: 1 - The image attribute name ("Caption" or "Description").
					__('Autogenerate %1$s on Upload', td),
					__('Title', td)
				)
			},
			tags : {
				title : {
					context : 'imageSeoTitle',
					default : [
						'image_title',
						'separator_sa',
						'site_title'
					]
				},
				altTag : {
					context : 'imageSeoAlt',
					default : [
						'alt_tag',
						'separator_sa',
						'site_title'
					]
				},
				caption : {
					context : 'imageSeoCaption',
					default : [
						'attachment_caption',
						'separator_sa',
						'site_title'
					]
				},
				description : {
					context : 'imageSeoDescription',
					default : [
						'attachment_description',
						'separator_sa',
						'site_title'
					]
				}
			},
			casings : {
				lowerCase : {
					label       : __('Lower Case', td),
					description : __('All letters are converted to lower case (small) letters.', td)
				},
				titleCase : {
					label       : __('Title Case', td),
					description : __('Major words are capitalized and minor words remain in their original casing.', td)
				},
				sentenceCase : {
					label       : __('Sentence Case', td),
					description : __('The first word of each sentence starts with a capital.', td)
				}
			}
		}
	}
}
</script>