<template>
	<div class="aioseo-tab-content aioseo-post-advanced">
		<core-settings-row
			id="aioseo-post-robots-setting"
			:name="strings.robotsSetting"
			align
		>
			<template #content>
				<core-single-robots-meta />
			</template>
		</core-settings-row>

		<core-settings-row
			id="aioseo-post-canonical-url"
			:name="strings.canonicalUrl"
			align
		>
			<template #content>
				<base-input
					type="text"
					size="medium"
					:placeholder="strings.placeholder"
					v-model="postEditorStore.currentPost.canonicalUrl"
					@update:modelValue="postEditorStore.isDirty = true"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.priorityScore"
			align
		>
			<template #content>
				<div class="selectbox-row">
					<div class="select">
						<span>{{ strings.priority }}</span>
						<base-select
							:disabled="licenseStore.isUnlicensed"
							size="medium"
							:options="getPriorityOptions"
							:modelValue="licenseStore.isUnlicensed ? getPriority('default') : getPriority(postEditorStore.currentPost.priority)"
							@update:modelValue="value => licenseStore.isUnlicensed ? null : savePriority(value.value)"
						/>
					</div>
					<div class="separator">
						<span></span>
						-
					</div>
					<div class="select">
						<span>{{ strings.frequency }}</span>
						<base-select
							:disabled="licenseStore.isUnlicensed"
							size="medium"
							:options="getFrequencyOptions"
							:modelValue="licenseStore.isUnlicensed ? getFrequency('default') : getFrequency(postEditorStore.currentPost.frequency)"
							@update:modelValue="value => licenseStore.isUnlicensed ? null : saveFrequency(value.value)"
						/>
					</div>
				</div>

				<core-alert
					class="inline-upsell"
					v-if="licenseStore.isUnlicensed"
					type="blue"
				>
					<div v-html="strings.priorityFrequencyUpsell" />
				</core-alert>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="optionsStore.options.searchAppearance.advanced.useKeywords"
			:name="strings.keywords"
			align
		>
			<template #content>
				<base-select
					multiple
					taggable
					:options="getJsonValue(postEditorStore.currentPost.keywords) || []"
					:modelValue="getJsonValue(postEditorStore.currentPost.keywords) || []"
					@update:modelValue="values => postEditorStore.currentPost.keywords = setJsonValue(values)"
					:tag-placeholder="strings.tagPlaceholder"
				/>
			</template>
		</core-settings-row>
	</div>
</template>
<script>
import {
	useLicenseStore,
	useOptionsStore,
	usePostEditorStore
} from '@/vue/stores'

import { JsonValues } from '@/vue/mixins'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreSingleRobotsMeta from '@/vue/components/common/core/SingleRobotsMeta'
export default {
	setup () {
		return {
			licenseStore    : useLicenseStore(),
			optionsStore    : useOptionsStore(),
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		CoreAlert,
		CoreSettingsRow,
		CoreSingleRobotsMeta
	},
	mixins : [ JsonValues ],
	props  : {
		disabled : {
			type : Boolean,
			default () {
				return false
			}
		},
		type : {
			type     : String,
			required : false
		},
		object : {
			type     : Object,
			required : false
		}
	},
	data () {
		return {
			strings : {
				pageName                : this.$t.__('Advanced', this.$td),
				robotsSetting           : this.$t.__('Robots Setting', this.$td),
				robotsToggle            : this.$t.__('Use Default Settings', this.$td),
				canonicalUrl            : this.$t.__('Canonical URL', this.$td),
				placeholder             : this.$t.__('Enter a URL to change the default Canonical URL', this.$td),
				priorityScore           : this.$t.__('Priority Score', this.$td),
				priority                : this.$t.__('Priority', this.$td),
				frequency               : this.$t.__('Frequency', this.$td),
				priorityFrequencyUpsell : this.$t.sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					this.$t.__('Priority Score is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('post-advanced', this.$constants.GLOBAL_STRINGS.learnMore, 'priority-frequency', true)
				),
				keywords       : this.$t.__('Keywords', this.$td),
				tagPlaceholder : this.$t.__('Press enter to create a keyword', this.$td)
			}
		}
	},
	computed : {
		getPriorityOptions () {
			return [ { label: this.$t.__('default', this.$td), value: 'default' } ].concat(this.$constants.PRIORITY_OPTIONS)
		},
		getFrequencyOptions () {
			return [ { label: this.$t.__('default', this.$td), value: 'default' } ].concat(this.$constants.FREQUENCY_OPTIONS)
		}
	},
	methods : {
		getPriority (option) {
			option = 'default' !== option ? parseFloat(option) : option
			return this.getPriorityOptions.find(h => h.value === option)
		},
		savePriority (value) {
			this.postEditorStore.currentPost.priority    = value
			this.postEditorStore.isDirty = true
		},
		getFrequency (option) {
			return this.getFrequencyOptions.find(h => h.value === option)
		},
		saveFrequency (value) {
			this.postEditorStore.currentPost.frequency   = value
			this.postEditorStore.isDirty = true
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-advanced {
	.inline-upsell {
		display: inline-flex;

		margin-top: 12px;
	}
	.selectbox-row {
		display: flex;
		align-items: center;

		.separator {
			display: inline-block;
			margin: 0 20px;
			padding-bottom: 10px;
			align-self: flex-end;
		}
		.select {
			display: inline-block;
			width: 100%;
			max-width: 125px;
			> span {
				display: inline-block;
				font-size: 14px;
				margin-bottom: 10px;
			}
		}
	}
}
</style>