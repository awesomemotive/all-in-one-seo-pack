<template>
	<div class="aioseo-sa-ct-advanced">
		<core-settings-row
			:name="strings.robotsSetting"
		>
			<template #content>
				<core-robots-meta
					:options="options.advanced.robotsMeta"
					:mainOptions="options"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="showBulk"
			:name="strings.bulkEditing"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="options.advanced.bulkEditing"
					:name="`${object.name}BulkEditing`"
					:options="[
						{ label: GLOBAL_STRINGS.disabled, value: 'disabled' },
						{ label: GLOBAL_STRINGS.enabled, value: 'enabled' },
						{ label: strings.readOnly, value: 'read-only' },
					]"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.removeCategoryBase"
			v-if="'taxonomies' === type && 'category' === object.name"
			align
		>
			<template #content>
				<base-radio-toggle
					v-model="optionsStore.options.searchAppearance.advanced.removeCategoryBase"
					name="removeCategoryBase"
					:options="[
						{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
						{ label: GLOBAL_STRINGS.yes, value: true }
					]"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="!noMetaBox && (!licenseStore.isUnlicensed || 'taxonomies' !== type)"
			:name="strings.otherOptions"
		>
			<template #content>
				<!--div class="other-options">
					<base-toggle
						v-model="options.advanced.showDateInGooglePreview"
					>
						{{ strings.showDateInGooglePreview }}
					</base-toggle>
				</div>
				<div class="other-options">
					<base-toggle
						v-model="options.advanced.showPostThumbnailInSearch"
					>
						{{ showPostThumbnailInSearch }}
					</base-toggle>
				</div-->
				<div class="other-options">
					<base-toggle
						v-model="options.advanced.showMetaBox"
					>
						{{ showMetaBox }}
					</base-toggle>
				</div>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="optionsStore.options.searchAppearance.advanced.useKeywords && includeKeywords"
			:name="strings.keywords"
			align
		>
			<template #content>
				<base-select
					multiple
					taggable
					:options="getJsonValue(options.advanced.keywords) || []"
					:modelValue="getJsonValue(options.advanced.keywords) || []"
					@update:modelValue="values => options.advanced.keywords = setJsonValue(values)"
					:tag-placeholder="strings.tagPlaceholder"
				/>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { useJsonValues } from '@/vue/composables/JsonValues'

import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreRobotsMeta from '@/vue/components/common/core/RobotsMeta'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getJsonValue,
			setJsonValue
		} = useJsonValues()

		return {
			GLOBAL_STRINGS,
			getJsonValue,
			licenseStore : useLicenseStore(),
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore(),
			setJsonValue
		}
	},
	components : {
		BaseRadioToggle,
		CoreRobotsMeta,
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
		options : {
			type     : Object,
			required : true
		},
		showBulk        : Boolean,
		noMetaBox       : Boolean,
		includeKeywords : Boolean
	},
	data () {
		return {
			titleCount       : 0,
			descriptionCount : 0,
			strings          : {
				robotsSetting           : __('Robots Meta Settings', td),
				bulkEditing             : __('Bulk Editing', td),
				readOnly                : __('Read Only', td),
				otherOptions            : __('Other Options', td),
				showDateInGooglePreview : __('Show Date in Google Preview', td),
				keywords                : __('Keywords', td),
				removeCategoryBase      : __('Remove Category Base Prefix', td)
			}
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
		showPostThumbnailInSearch () {
			return sprintf(
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				__('Show %1$s Thumbnail in Google Custom Search', td),
				this.object.singular
			)
		},
		showMetaBox () {
			return sprintf(
				// Translators: 1 - The plugin name ("All in One SEO")
				__('Show %1$s Meta Box', td),
				import.meta.env.VITE_SHORT_NAME
			)
		}
	}
}
</script>

<style lang="scss">
.aioseo-sa-ct-advanced {
	.inline-upsell {
		display: inline-flex;

		margin-top: 12px;
	}

	.other-options {
		margin-top: 10px;

		&:first-child {
			margin-top: 0;
		}
	}
}
</style>