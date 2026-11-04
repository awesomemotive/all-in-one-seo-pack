<template>
	<div>
		<core-settings-row
			id="aioseo-post-robots-setting"
			:name="strings.robotsMeta"
			align
		>
			<template #content>
				<core-single-robots-meta />
			</template>
		</core-settings-row>

		<core-settings-row
			id="aioseo-post-breadcrumbs-setting"
			:name="strings.breadcrumbs"
			align
		>
			<template #content>
				<breadcrumbs />
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
							float-dropdown
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
							float-dropdown
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
					float-dropdown
					multiple
					taggable
					:options="postEditorStore.currentPost.keywords || []"
					:modelValue="postEditorStore.currentPost.keywords || []"
					@update:modelValue="values => postEditorStore.currentPost.keywords = values"
					:tag-placeholder="strings.tagPlaceholder"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			v-if="
				'post' === postEditorStore.currentPost.context &&
				!['attachment', 'web-story'].includes(postEditorStore.currentPost.postType) &&
				!isPageBuilderEditor()
			"
			id="aioseo-post-settings-cornerstone-content-row"
			class="cornerstone-content-row"
		>
			<template #name>
				{{ strings.cornerstoneContent }}

				<core-pro-badge
					v-if="licenseStore.isUnlicensed"
				/>
			</template>

			<template #content>
				<cornerstone-content
					@changeTab="newTab => $emit('changeTab', newTab)"
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
	usePostEditorStore
} from '@/vue/stores'

import Breadcrumbs from '../../Breadcrumbs'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreSingleRobotsMeta from '@/vue/components/common/core/SingleRobotsMeta'
import CornerstoneContent from '../general/CornerstoneContent'

import { useUrl } from '@/vue/composables/Url'
import { isPageBuilderEditor } from '@/vue/utils/context'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'changeTab' ],
	setup () {
		const {
			decodeUrl
		} = useUrl()

		return {
			licenseStore    : useLicenseStore(),
			optionsStore    : useOptionsStore(),
			postEditorStore : usePostEditorStore(),
			decodeUrl,
			isPageBuilderEditor
		}
	},
	components : {
		Breadcrumbs,
		CoreAlert,
		CoreProBadge,
		CoreSettingsRow,
		CoreSingleRobotsMeta,
		CornerstoneContent
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
				cornerstoneContent      : __('Cornerstone Content', td),
				robotsMeta              : __('Robots Meta', td),
				breadcrumbs             : __('Breadcrumbs', td),
				canonicalUrl            : __('Canonical URL', td),
				validUrl                : __('Please enter a valid URL.', td),
				placeholder             : __('Enter a URL to change the default Canonical URL', td),
				priorityScore           : __('Priority Score', td),
				priority                : __('Priority', td),
				frequency               : __('Frequency', td),
				priorityFrequencyUpsell : sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					__('Priority Score is a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('post-settings', 'priority-frequency', GLOBAL_STRINGS.learnMore, 'liteUpgrade', true)
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
	.cornerstone-content-row {
		.cornerstone-content-panel {
			display: flex;
			flex-flow: column;

			p {
				order: 2;
			}
		}
	}

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
		gap: 20px;

		.separator {
			display: inline-block;
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

	&.sidebar {
		.selectbox-row {
			gap: 5px;
		}
	}
}
</style>