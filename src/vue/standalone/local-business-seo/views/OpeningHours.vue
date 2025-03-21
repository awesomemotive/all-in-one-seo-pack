<template>
	<div class="aioseo-tab-content aioseo-localseo-opening">
		<core-settings-row
			:name="strings.useDefaults"
			align
		>
			<template #content>
				<div class="aioseo-col col-xs-12 text-xs-left">
					<base-radio-toggle
						name="useDefaults"
						v-model="postEditorStore.currentPost.local_seo.openingHours.useDefaults"
						:options="[
							{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
							{ label: GLOBAL_STRINGS.yes, value: true }
						]"
					/>
				</div>
			</template>
		</core-settings-row>
		<div v-if="!postEditorStore.currentPost.local_seo.openingHours.useDefaults">
			<core-settings-row
				:name="strings.showOpeningHours"
				class="info-openinghours-row"
				align
			>
				<template #content>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<base-radio-toggle
							name="openingHours"
							v-model="postEditorStore.currentPost.local_seo.openingHours.show"
							:options="[
								{ label: GLOBAL_STRINGS.no, value: false, activeClass: 'dark' },
								{ label: GLOBAL_STRINGS.yes, value: true }
							]"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="postEditorStore.currentPost.local_seo.openingHours.show"
				:name="strings.labels"
				class="info-labels-row"
				align
			>
				<template #content>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<span class="field-description">{{ strings.closedLabel }}</span>
						<base-input
							type="text"
							size="medium"
							v-model="postEditorStore.currentPost.local_seo.openingHours.labels.closed"
						/>
						<span class="field-description mt-10">{{ strings.closedLabelDesc }}</span>
					</div>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<span class="field-description mt-8">{{ strings.open24Label }}</span>
						<base-input
							size="medium"
							v-model="postEditorStore.currentPost.local_seo.openingHours.labels.alwaysOpen"
						/>
						<span class="field-description mt-10">{{ strings.open24LabelDesc }}</span>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="postEditorStore.currentPost.local_seo.openingHours.show"
				:name="strings.settings"
				class="info-settings-row"
				align
			>
				<template #content>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<base-toggle
							v-model="postEditorStore.currentPost.local_seo.openingHours.alwaysOpen"
						>
							{{ strings.alwaysOpen }}
						</base-toggle>
					</div>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<base-toggle
							v-model="postEditorStore.currentPost.local_seo.openingHours.use24hFormat"
						>
							{{ strings.use24hFormat }}
						</base-toggle>
					</div>
					<!--<div class="aioseo-col col-xs-12 text-xs-left">
						<base-toggle
							v-model="postEditorStore.currentPost.local_seo.openingHours.twoSets"
						>
							{{ strings.twoSets }}
						</base-toggle>
					</div>
					<div class="aioseo-col col-xs-12 text-xs-left">
						<span class="field-description">{{ strings.timezone }}</span>
						<base-select
							size="medium"
							:options="selectTimezone"
							modelValue="none"
						/>
					</div>-->
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="postEditorStore.currentPost.local_seo.openingHours.show && !postEditorStore.currentPost.local_seo.openingHours.alwaysOpen"
				:name="strings.hours"
				class="info-hours-row"
				align
			>
					<template #content>
						<div
							class="aioseo-col col-xs-12 text-xs-left"
						>
							<div class="aioseo-col-flex text-xs-left" v-bind:key="index" v-for="(label, index) in weekdays">
								<div class="aioseo-col-day text-xs-left">
									{{ label }}
								</div>
								<div class="aioseo-col-hours text-xs-left">
									<base-select
										:disabled="getWeekDay(index).open24h || getWeekDay(index).closed"
										size="medium"
										:options="postEditorStore.currentPost.local_seo.openingHours.use24hFormat ? HOURS_24H_FORMAT : HOURS_12H_FORMAT"
										:modelValue="getSelectOptions(getWeekDay(index).openTime)"
										@update:modelValue="value => saveDay(index, 'openTime', value.value)"
									/>
									<span class="separator">-</span>
									<base-select
										:disabled="getWeekDay(index).open24h || getWeekDay(index).closed"
										size="medium"
										:options="postEditorStore.currentPost.local_seo.openingHours.use24hFormat ? HOURS_24H_FORMAT : HOURS_12H_FORMAT"
										:modelValue="getSelectOptions(getWeekDay(index).closeTime)"
										@update:modelValue="value => saveDay(index, 'closeTime', value.value)"
									/>
								</div>
								<div class="aioseo-col-alwaysopen text-xs-left">
									<base-checkbox
										:disabled="getWeekDay(index).closed"
										size="medium"
										v-model="getWeekDay(index).open24h"
									>
										{{ strings.open24h }}
									</base-checkbox>

									<base-checkbox
										size="medium"
										class="closed-label"
										v-model="getWeekDay(index).closed"
									>
										{{ strings.closed }}
									</base-checkbox>
								</div>
							</div>
						</div>
					</template>
			</core-settings-row>
		</div>
	</div>
</template>

<script>
import {
	GLOBAL_STRINGS,
	HOURS_12H_FORMAT,
	HOURS_24H_FORMAT
} from '@/vue/plugins/constants'
import {
	usePostEditorStore
} from '@/vue/stores'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			GLOBAL_STRINGS,
			HOURS_12H_FORMAT,
			HOURS_24H_FORMAT
		}
	},
	components : {
		BaseCheckbox,
		BaseRadioToggle,
		CoreSettingsRow
	},
	data () {
		return {
			selectTimezone : [
				{ value: 'none', label: __('Select your timezone', td) }
			],
			strings : {
				pageName         : __('Opening Hours', td),
				useDefaults      : __('Use Defaults', td),
				useDefaultsDesc  : __('Will default opening hours set globally', td),
				showOpeningHours : __('Show Opening Hours', td),
				labels           : __('Labels', td),
				closedLabel      : __('Closed', td),
				closedLabelDesc  : __('Displayed when the business is closed.', td),
				closed           : __('Closed', td),
				settings         : __('Settings', td),
				open24h          : __('Open 24h', td),
				open24Label      : __('Open 24h', td),
				open24LabelDesc  : __('Displayed when the business is open all day long.', td),
				alwaysOpen       : __('Open 24/7', td),
				use24hFormat     : __('Use 24h format', td),
				twoSets          : __('I have two sets of opening hours per day', td),
				timezone         : __('Timezone', td),
				hours            : __('Hours', td)
			},
			weekdays : {
				monday    : __('Monday', td),
				tuesday   : __('Tuesday', td),
				wednesday : __('Wednesday', td),
				thursday  : __('Thursday', td),
				friday    : __('Friday', td),
				saturday  : __('Saturday', td),
				sunday    : __('Sunday', td)
			}
		}
	},
	computed : {
		toggled : function () {
			return true
		},
		unToggled : function () {
			return false
		},
		closedLabel : {
			get () {
				return this.postEditorStore.currentPost.local_seo.openingHours.closedLabel
			},
			set (newValue) {
				this.postEditorStore.currentPost.local_seo.openingHours.closedLabel = newValue
			}
		}
	},
	methods : {
		getSelectOptions (option) {
			return this.postEditorStore.currentPost.local_seo.openingHours.use24hFormat
				? HOURS_24H_FORMAT.find(h => h.value === option)
				: HOURS_12H_FORMAT.find(h => h.value === option)
		},
		saveDay (day, hour, value) {
			this.postEditorStore.currentPost.local_seo.openingHours.days[day][hour] = value
		},
		getWeekDay (index) {
			return this.postEditorStore.currentPost.local_seo.openingHours.days[index]
		}
	}
}
</script>

<style lang="scss">
.aioseo-localseo-opening {
	.field-description {
		display: inline-block;
		margin: 10px 0;
		font-size: 14px;

		&.mt-8 {
			margin-top: 8px;
		}
	}

	.aioseo-input,
	.aioseo-select,
	.aioseo-multiselect {
		max-width: 480px;
	}

	.aioseo-col-flex {
		display: flex;
		align-items: center;
		padding: 12px 0;
		border-bottom: 1px solid $border;

		&:first-of-type {
			padding-top: 0;
		}

		&:last-of-type {
			padding-bottom: 0;
			border: none;
		}

		.aioseo-col-day {
			flex: 1
		}

		.aioseo-col-hours,
		.aioseo-col-alwaysopen {
			flex: 2
		}

		span.separator {
			margin: 0 5px;
		}

		.aioseo-select {
			display: inline-block;
			max-width: 120px;
			margin-bottom: 5px;
		}

		.multiselect--disabled {
			.multiselect__tags,
			.multiselect__single {
				background: $background;
			}
		}
	}
	.aioseo-col-alwaysopen {
		.aioseo-checkbox {
			padding: 0 10px;
		}
	}
}
</style>