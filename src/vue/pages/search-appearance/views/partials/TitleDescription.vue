<template>
	<div class="aioseo-sa-ct-title-description">
		<core-settings-row
			:name="strings.showInSearchResults"
			align
		>
			<template #content>
				<base-radio-toggle
					v-if="edit"
					v-model="options.show"
					:name="`${object.name}ShowInSearch`"
					:options="[
						{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.yes, value: true }
					]"
				/>

				<base-radio-toggle
					v-if="!edit"
					:modelValue="true"
					:name="`${object.name}ShowInSearch`"
					:options="[
						{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.yes, value: true }
					]"
				/>

				<div class="aioseo-description">
					<span v-if="options.show">{{ noIndexDescription }}</span>

					<core-alert
						v-if="!options.show"
						type="blue"
					>
						{{ noindexAlertDescription }}
					</core-alert>
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="edit"
			:name="GLOBAL_STRINGS.preview"
		>
			<template #content>
				<core-google-search-preview
					:title="parseTags(options.title)"
					:description="parseTags(options.metaDescription)"
				/>
			</template>
		</core-settings-row>

		<core-settings-row :name="title">
			<template #content>
				<core-html-tags-editor
					v-if="edit"
					v-model="options.title"
					:line-numbers="false"
					single
					:tags-context="`${object.name}Title`"
					:default-tags="tags.getDefaultTags(type, object.name, 'title')"
				>
					<template #tags-description>
						{{ strings.clickToAddTitle }}
					</template>
				</core-html-tags-editor>

				<core-html-tags-editor
					v-if="!edit"
					:line-numbers="false"
					single
					:tags-context="`${object.name}Title`"
					:default-tags="tags.getDefaultTags(type, object.name, 'title')"
				>
					<template #tags-description>
						{{ strings.clickToAddTitle }}
					</template>
				</core-html-tags-editor>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="options.show"
			:name="strings.metaDescription"
		>
			<template #content>
				<core-html-tags-editor
					v-if="edit"
					v-model="options.metaDescription"
					:line-numbers="false"
					description
					:tags-context="`${object.name}Description`"
					:default-tags="tags.getDefaultTags(type, object.name, 'description')"
				>
					<template #tags-description>
						{{ strings.clickToAddDescription }}
					</template>
				</core-html-tags-editor>

				<core-html-tags-editor
					v-if="!edit"
					:line-numbers="false"
					:tags-context="`${object.name}Description`"
					:default-tags="tags.getDefaultTags(type, object.name, 'description')"
				>
					<template #tags-description>
						{{ strings.clickToAddDescription }}
					</template>
				</core-html-tags-editor>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import tags from '@/vue/utils/tags'

import { useTags } from '@/vue/composables/Tags'

import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreGoogleSearchPreview from '@/vue/components/common/core/GoogleSearchPreview'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup (props) {
		const {
			parseTags
		} = useTags({
			separator : props.separator
		})

		return {
			parseTags
		}
	},
	components : {
		BaseRadioToggle,
		CoreAlert,
		CoreGoogleSearchPreview,
		CoreHtmlTagsEditor,
		CoreSettingsRow
	},
	props : {
		type : {
			type     : String,
			required : true
		},
		object : {
			type     : Object,
			required : true
		},
		separator : {
			type     : String,
			required : true
		},
		options : {
			type     : Object,
			required : true
		},
		edit : {
			type : Boolean,
			default () {
				return true
			}
		}
	},
	data () {
		return {
			GLOBAL_STRINGS,
			tags,
			titleCount       : 0,
			descriptionCount : 0,
			strings          : {
				showInSearchResults   : __('Show in Search Results', td),
				clickToAddTitle       : __('Click on the tags below to insert variables into your title.', td),
				metaDescription       : __('Meta Description', td),
				clickToAddDescription : __('Click on the tags below to insert variables into your meta description.', td)
			}
		}
	},
	watch : {
		show (newVal) {
			if (newVal) {
				this.options.advanced.robotsMeta.noindex = false

				// We should turn the default settings back on, but only if all the robot settings are already the same as our defaults.
				if (
					false === this.options.advanced.robotsMeta.nofollow &&
					false === this.options.advanced.robotsMeta.noarchive &&
					false === this.options.advanced.robotsMeta.notranslate &&
					false === this.options.advanced.robotsMeta.noimageindex &&
					false === this.options.advanced.robotsMeta.nosnippet &&
					false === this.options.advanced.robotsMeta.noodp &&
					-1 === parseInt(this.options.advanced.robotsMeta.maxSnippet) &&
					-1 === parseInt(this.options.advanced.robotsMeta.maxVideoPreview) &&
					'large' === this.options.advanced.robotsMeta.maxImagePreview.toLowerCase()
				) {
					this.options.advanced.robotsMeta.default = true
				}
				return
			}

			this.options.advanced.robotsMeta.default = false
			this.options.advanced.robotsMeta.noindex = true
		}
	},
	computed : {
		title () {
			return sprintf(
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				__('%1$s Title', td),
				this.object.singular
			)
		},
		show () {
			return this.options.show
		},
		noIndexDescription () {
			return sprintf(
				// Translators: 1 - The plural name of the content type (e.g. "Posts" or "Categories").
				__('Choose whether your %1$s should be included in search results. If you select "No", then your %1$s will be noindexed and excluded from the sitemap so that search engines ignore them.', td),
				this.object.label
			)
		},
		noindexAlertDescription () {
			return sprintf(
				// Translators: 1 - The plural name of the content type (e.g. "Posts" or "Categories").
				__('Your %1$s will be noindexed and excluded from the sitemap so that search engines ignore them. You can still control how their page title looks like below.', td),
				this.object.label
			)
		}
	},
	methods : {

	}
}
</script>

<style lang="scss" scoped>
.aioseo-sa-ct-title-description {
	.aioseo-google-search-preview {
		border: 1px solid $border;
		padding: 16px;
	}
}
</style>