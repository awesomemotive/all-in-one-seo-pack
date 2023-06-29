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
						{ label: $constants.GLOBAL_STRINGS.disabled, value: 'disabled' },
						{ label: $constants.GLOBAL_STRINGS.enabled, value: 'enabled' },
						{ label: strings.readOnly, value: 'read-only' },
					]"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="'taxonomies' === type && 'category' === object.name"
			align
		>
			<template #name>
				{{ strings.removeCatBase }}
				<core-pro-badge
					v-if="licenseStore.isUnlicensed"
				/>
			</template>

			<template #content>
				<base-radio-toggle
					:disabled="licenseStore.isUnlicensed"
					v-model="removeCatBase"
					name="removeCatBase"
					:options="[
						{ label: $constants.GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
						{ label: $constants.GLOBAL_STRINGS.yes, value: true }
					]"
				/>

				<core-alert
					class="inline-upsell"
					v-if="licenseStore.isUnlicensed"
					type="blue"
				>
					<div v-html="strings.removeCatBaseUpsell" />
				</core-alert>
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
import {
	useLicenseStore,
	useOptionsStore
} from '@/vue/stores'

import { JsonValues, MaxCounts } from '@/vue/mixins'

import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreRobotsMeta from '@/vue/components/common/core/RobotsMeta'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			optionsStore : useOptionsStore()
		}
	},
	components : {
		BaseRadioToggle,
		CoreAlert,
		CoreProBadge,
		CoreRobotsMeta,
		CoreSettingsRow
	},
	mixins : [ JsonValues, MaxCounts ],
	props  : {
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
				robotsSetting           : this.$t.__('Robots Meta Settings', this.$td),
				bulkEditing             : this.$t.__('Bulk Editing', this.$td),
				readOnly                : this.$t.__('Read Only', this.$td),
				otherOptions            : this.$t.__('Other Options', this.$td),
				showDateInGooglePreview : this.$t.__('Show Date in Google Preview', this.$td),
				keywords                : this.$t.__('Keywords', this.$td),
				removeCatBase           : this.$t.__('Remove Category Base Prefix', this.$td),
				removeCatBaseUpsell     : this.$t.sprintf(
					// Translators: 1 - The plugin short name name ("AIOSEO") + Pro, 2 - "Learn more".
					this.$t.__('This feature is only for licensed %1$s users. %2$s', this.$td),
					`<strong>${import.meta.env.VITE_SHORT_NAME} Pro</strong>`,
					this.$links.getUpsellLink('search-appearance-advanced', this.$constants.GLOBAL_STRINGS.learnMore, 'remove-category-base-prefix', true)
				)
			}
		}
	},
	computed : {
		removeCatBase : {
			get () {
				return this.$isPro ? this.optionsStore.options.searchAppearance.advanced.removeCatBase : false
			},
			set (newValue) {
				this.optionsStore.options.searchAppearance.advanced.removeCatBase = newValue
			}
		},
		title () {
			return this.$t.sprintf(
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				this.$t.__('%1$s Title', this.$td),
				this.object.singular
			)
		},
		showPostThumbnailInSearch () {
			return this.$t.sprintf(
				// Translators: 1 - The type of page (Post, Page, Category, Tag, etc.).
				this.$t.__('Show %1$s Thumbnail in Google Custom Search', this.$td),
				this.object.singular
			)
		},
		showMetaBox () {
			return this.$t.sprintf(
				// Translators: 1 - The plugin name ("All in One SEO")
				this.$t.__('Show %1$s Meta Box', this.$td),
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