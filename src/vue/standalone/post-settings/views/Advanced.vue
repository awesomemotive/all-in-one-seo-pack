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
					ref="canonicalUrlInput"
					type="text"
					size="medium"
					:placeholder="strings.placeholder"
					v-model="postEditorStore.currentPost.canonicalUrl"
					@input="inputEventDecodeUrl"
					@update:modelValue="postEditorStore.isDirty = true"
				/>

				<core-alert
					v-if="error"
					class="cannonical-url-error"
					type="red"
					size="small">
					{{ strings.validUrl }}
				</core-alert>

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
					:options="postEditorStore.currentPost.keywords || []"
					:modelValue="postEditorStore.currentPost.keywords || []"
					@update:modelValue="values => postEditorStore.currentPost.keywords = values"
					:tag-placeholder="strings.tagPlaceholder"
				/>
			</template>
		</core-settings-row>
	</div>
</template>
<script>
import {
	FREQUENCY_OPTIONS,
	GLOBAL_STRINGS,
	PRIORITY_OPTIONS
} from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useOptionsStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreSingleRobotsMeta from '@/vue/components/common/core/SingleRobotsMeta'

import { useUrl } from '@/vue/composables/Url'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			decodeUrl
		} = useUrl()

		return {
			licenseStore    : useLicenseStore(),
			optionsStore    : useOptionsStore(),
			postEditorStore : usePostEditorStore(),
			rootStore      	: useRootStore(),
			decodeUrl
		}
	},
	components : {
		CoreAlert,
		CoreSettingsRow,
		CoreSingleRobotsMeta
	},
	props : {
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
				pageName                : __('Advanced', td),
				robotsSetting           : __('Robots Setting', td),
				robotsToggle            : __('Use Default Settings', td),
				canonicalUrl            : __('Canonical URL', td),
				validUrl               	: __('Please enter a valid URL.', td),
				placeholder             : __('Enter a URL to change the default Canonical URL', td),
				priorityScore           : __('Priority Score', td),
				priority                : __('Priority', td),
				frequency               : __('Frequency', td),
				priorityFrequencyUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					__('Priority Score is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('post-advanced', GLOBAL_STRINGS.learnMore, 'priority-frequency', true)
				),
				keywords       : __('Keywords', td),
				tagPlaceholder : __('Press enter to create a keyword', td)
			},
			error : false
		}
	},
	computed : {
		getPriorityOptions () {
			return [ { label: __('default', td), value: 'default' } ].concat(PRIORITY_OPTIONS)
		},
		getFrequencyOptions () {
			return [ { label: __('default', td), value: 'default' } ].concat(FREQUENCY_OPTIONS)
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
		},
		inputEventDecodeUrl (event) {
			const value = event.target.value
			this.postEditorStore.currentPost.canonicalUrl = ''
			this.postEditorStore.currentPost.canonicalUrl = this.decodeUrl(value)
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
	.cannonical-url-error {
		margin: 8px 0;
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