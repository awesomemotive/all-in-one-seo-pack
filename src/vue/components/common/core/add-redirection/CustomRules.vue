<template>
	<div class="custom-rules">
		<table
			class="redirects-options-table"
			cellspacing="0"
			cellpadding="0"
			aria-label="Custom Rules"
		>
			<thead>
			<tr>
				<td colspan="2">{{ strings.customRules }}</td>
			</tr>
			</thead>
			<tbody>
			<tr
				class="rule"
				v-for="(match, index) in customRules"
				:class="{ even: 0 === index % 2 }"
				:key="index"
			>
				<td class="rule-settings">
					<div class="rule-row">
						<div class="rule-option">
							<base-select
								:options="filteredTypes"
								size="medium"
								:placeholder="strings.selectMatchRule"
								:modelValue="getRuleValue('type', index)"
								@update:modelValue="updateRule('type', $event, index)"
							/>
							<base-select
								v-if="getType(index, 'options') || getType(index, 'taggable')"
								:options="getType(index, 'options') || []"
								size="medium"
								:modelValue="getRuleValue('value', index)"
								@update:modelValue="updateRule('value', $event, index)"
								:multiple="getType(index, 'multiple') || getType(index, 'taggable')"
								:taggable="getType(index, 'taggable')"
								:placeholder="getType(index, 'placeholder') || strings.selectAValue"
							/>
							<!-- Key field when there's a key/value pair. -->
							<base-input
								v-if="getType(index, 'keyValuePair')"
								:modelValue="getRuleValue('key', index)"
								@update:modelValue="updateRule('key', $event, index)"
								size="medium"
								:placeholder="getType(index, 'placeholderKey') || strings.key"
							/>
							<!-- Key field when there's a key/value pair. -->
							<!-- Value field if there are no options and the option is not taggable -->
							<base-input
								v-if="!getType(index, 'options') && !getType(index, 'taggable') && !getType(index, 'dateRange')"
								:modelValue="getRuleValue('value', index)"
								@update:modelValue="updateRule('value', $event, index)"
								size="medium"
								:placeholder="getType(index, 'placeholder') || strings.value"
								:disabled="!getType(index)"
							/>
							<div
								v-if="getType(index, 'dateRange')"
								class="date-range"
							>
								<base-date-picker
									type="datetime"
									size="large"
									:placeholder="strings.startDate"
									:dateFormat="rootStore.aioseo.data.dateFormat + ' - ' + rootStore.aioseo.data.timeFormat"
									:defaultValue="dateStringToLocalJs(getRuleValue('scheduleStart', index))"
									@change="value => updateDate(value, 'scheduleStart', index)"
									:isDisabledDate="isDisabledStartDate"
								/>
								<base-date-picker
									type="datetime"
									size="large"
									:placeholder="strings.endDate"
									:dateFormat="rootStore.aioseo.data.dateFormat + ' - ' + rootStore.aioseo.data.timeFormat"
									:defaultValue="dateStringToLocalJs(getRuleValue('scheduleEnd', index))"
									@change="value => updateDate(value, 'scheduleEnd', index)"
									:isDisabledDate="(date) => isDisabledEndDate(date, index)"
								/>
							</div>
							<!-- Value field if there are no options and the option is not taggable/multiple -->
							<base-toggle
								v-if="getType(index, 'regex')"
								:modelValue="getRuleValue('regex', index)"
								@update:modelValue="updateRule('regex', $event, index)"
							>
								{{ strings.regex }}
							</base-toggle>
						</div>
						<div
							class="rule-error"
							v-if="!!rulesErrors[index]">
							<core-alert
								type="red"
								size="small"
							>
								{{ rulesErrors[index] }}
							</core-alert>
						</div>
					</div>
				</td>
				<td class="actions">
					<core-tooltip
						class="action"
						type="action"
					>
						<svg-trash
							@click.native="removeRule(index)"
						/>

						<template #tooltip>
							{{ strings.delete }}
						</template>
					</core-tooltip>
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="2">
					<base-button
						size="small-table"
						type="black"
						@click="addRule(null)"
					>
						<svg-circle-plus />
						{{ strings.add }}
					</base-button>
				</td>
			</tr>
			</tfoot>
		</table>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { Date as DateMixin } from '@/vue/mixins/Date'
import { DateTime } from 'luxon'

import BaseButton from '@/vue/components/common/base/Button'
import BaseDatePicker from '@/vue/components/common/base/DatePicker'
import BaseInput from '@/vue/components/common/base/Input'
import BaseSelect from '@/vue/components/common/base/Select'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgTrash from '@/vue/components/common/svg/Trash'

const matchDefaults = {
	type  : null,
	key   : null,
	value : null,
	regex : null
}
export default {
	emits : [ 'redirects-custom-rule-error' ],
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		BaseButton,
		BaseDatePicker,
		BaseInput,
		BaseSelect,
		CoreAlert,
		CoreTooltip,
		SvgCirclePlus,
		SvgTrash
	},
	props : {
		editCustomRules : Array
	},
	mixins : [ DateMixin ],
	data () {
		return {
			DateTime,
			strings : {
				customRules     : this.$t.__('Custom Rules', this.$td),
				selectMatchRule : this.$t.__('Select Rule', this.$td),
				delete          : this.$t.__('Delete', this.$td),
				add             : this.$t.__('Add Custom Rule', this.$td),
				regex           : this.$t.__('Regex', this.$td),
				selectAValue    : this.$t.__('Select a Value or Add a New One', this.$td),
				key             : this.$t.__('Key', this.$td),
				value           : this.$t.__('Value', this.$td),
				startDate       : this.$t.__('Start Date', this.$td),
				endDate         : this.$t.__('End Date', this.$td)
			},
			customRules : [],
			rulesErrors	: [],
			types       : [
				{
					label     : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.schedule,
					value     : 'schedule',
					taggable  : false,
					regex     : false,
					dateRange : true
				},
				{
					label       : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.login,
					value       : 'login',
					placeholder : this.$t.__('Select Status', this.$td),
					singleRule  : true,
					options     : [
						{ label: this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.loggedin, value: 'loggedin' },
						{ label: this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.loggedout, value: 'loggedout' }
					]
				},
				{
					label       : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.role,
					value       : 'role',
					multiple    : true,
					placeholder : this.$t.__('Select Roles', this.$td),
					options     : Object.entries(this.rootStore.aioseo.user.roles).map((item) => {
						return { label: item[1], value: item[0] }
					})
				},
				{
					label      : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.referrer,
					value      : 'referrer',
					regex      : true,
					singleRule : true
				},
				{
					label    : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.agent,
					value    : 'agent',
					regex    : true,
					taggable : true,
					multiple : true,
					options  : [
						{
							label   : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.mobile,
							value   : 'mobile',
							docLink : this.$links.getDocLink(this.$t.__('Learn more', this.$td), 'redirectCustomRulesUserAgent', true)
						},
						{
							label   : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.feeds,
							value   : 'feeds',
							docLink : this.$links.getDocLink(this.$t.__('Learn more', this.$td), 'redirectCustomRulesUserAgent', true)
						},
						{
							label   : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.libraries,
							value   : 'libraries',
							docLink : this.$links.getDocLink(this.$t.__('Learn more', this.$td), 'redirectCustomRulesUserAgent', true)
						}
					]
				},
				{
					label        : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.cookie,
					value        : 'cookie',
					keyValuePair : true,
					regex        : true
				},
				{
					label       : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.ip,
					value       : 'ip',
					placeholder : this.$t.__('Enter an IP Address', this.$td),
					taggable    : true,
					regex       : true,
					singleRule  : true
				},
				{
					label       : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.server,
					value       : 'server',
					placeholder : this.$t.__('Enter the Server Name', this.$td),
					regex       : true,
					singleRule  : true
				},
				{
					label        : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.header,
					value        : 'header',
					keyValuePair : true,
					regex        : true
				},
				{
					label       : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.wp_filter,
					value       : 'wp_filter',
					placeholder : this.$t.__('Enter a WordPress Filter Name', this.$td),
					taggable    : true
				},
				{
					label       : this.$constants.REDIRECTS_CUSTOM_RULES_LABELS.locale,
					value       : 'locale',
					taggable    : true,
					regex       : true,
					placeholder : this.$t.__('Enter a Locale Code, e.g.: en_GB, es_ES', this.$td),
					singleRule  : true
				}
			]
		}
	},
	computed : {
		hasCustomRules () {
			return 0 < this.customRules.length
		},
		filteredTypes () {
			return this.types.map(type => {
				type.$isDisabled = false
				if (type.singleRule && this.customRules.find(rule => type.value === rule.type)) {
					type.$isDisabled = true
				}
				return type
			})
		}
	},
	methods : {
		isDisabledStartDate (date) {
			const today = new Date()
			today.setHours(0, 0, 0, 0)
			return date < today
		},
		isDisabledEndDate (date, index) {
			const startDate = this.getRuleValue('scheduleStart', index)

			if (startDate) {
				date.setHours(23, 59, 59, 0)
				return this.dateStringToLocalJs(startDate) > date
			}

			return this.isDisabledStartDate(date)
		},
		removeRule (index) {
			this.customRules.splice(index, 1)
			if (!this.hasCustomRules) {
				this.addRule(null)
			}
		},
		addRule (row, preventDuplicates = false) {
			if (!row) {
				row = JSON.parse(JSON.stringify(matchDefaults))
			}

			if (!preventDuplicates || (preventDuplicates && 0 === this.customRules.filter(header => header === row).length)) {
				this.customRules.push(row)
			}
		},
		updateRule (type, value, index) {
			const rule = this.customRules[index]

			value = 'undefined' !== typeof value.value ? value.value : value
			value = 'object' === typeof value && value.length ? value.map(item => item.value) : value

			rule[type] = value

			if ('type' === type) {
				rule.value = ''
			}

			this.customRules[index] = rule
		},
		getRuleValue (type, index, raw = false) {
			if (!this.customRules[index]) {
				return
			}

			const match = this.customRules[index]

			let value = match[type], typeOptions = null

			if (raw) {
				return value
			}

			switch (type) {
				case 'type':
					value = this.types.find(option => value === option.value)
					break
				case 'value':
					typeOptions = this.getType(index, 'options')

					if (typeOptions) {
						if ('object' === typeof value) {
							value = value.map(val => typeOptions.find(option => val === option.value) || val).filter(item => !!item)
						} else {
							value = typeOptions.find(option => value === option.value) || value
						}
					}

					if (this.getType(index, 'taggable')) {
						value = 'object' === typeof value
							? value.map(val => {
								return 'undefined' === typeof val.label ? { label: val, value: val } : val
							})
							: []
					}
					break
			}

			return value
		},
		getType (index, key) {
			const currentType = this.getRuleValue('type', index)
			if (key) {
				return currentType && 'undefined' !== typeof currentType[key] ? currentType[key] : false
			}
			return currentType
		},
		validationError () {
			let hasError = false,
				start    = null,
				end      = null

			this.customRules.forEach((customRule, index) => {
				this.rulesErrors[index] = null
				switch (customRule.type) {
					case 'schedule':
						start = this.getRuleValue('scheduleStart', index)
						end   = this.getRuleValue('scheduleEnd', index)

						if (start && end) {
							if (start > end) {
								this.rulesErrors[index] = this.$t.__('The Start Date must be lower than the End Date.', this.$td)
								hasError = true
							}

							if (start === end) {
								this.rulesErrors[index] = this.$t.__('Start Date and End Date must be different.', this.$td)
								hasError = true
							}
						}
						break
				}
			})
			this.$emit('redirects-custom-rule-error', hasError)
		},
		updateDate (value, dateSchedule, index) {
			const date = null !== value ? DateTime.fromJSDate(value).toUTC().toString() : ''
			this.updateRule(dateSchedule, date, index)
		}
	},
	mounted () {
		if (this.editCustomRules) {
			this.customRules = this.editCustomRules
		}
		if (!this.hasCustomRules) {
			this.addRule(null)
		}
	},
	watch : {
		customRules : {
			deep : true,
			handler () {
				this.validationError()
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.custom-rules {
	width: 100%;
	margin-top: 14px;
	.rule {
		.rule-settings {
			display: flex;
			flex-direction: row;
			align-items: center;
			flex: 1;

			.rule-row {
				width: 100%;
				display: flex;
				flex-direction: column;
				align-items: baseline;
				gap: 15px;

				.rule-error {
					width: 100%;
				}

				.rule-option {
					display: flex;
					width: 100%;

					> .aioseo-select:first-child {
						width: 100%;
						max-width: 250px;

						.aioseo-modal & {
							max-width: 200px;
						}
					}

					> * {
						margin: 0 16px 0 0;

						&:last-child{
							margin-right: 0;
						}

						&.aioseo-toggle {
							margin: 0 10px 0 4px;
						}
					}
				}
			}
		}

		.actions{
			flex: 0;
			vertical-align: top !important;
			padding-top: 27px !important;
		}

		.logical {
			flex: 0;
		}
	}

	svg {
		width: 14px;
		height: 14px;
		cursor: pointer;

		&.aioseo-trash {
			color: $gray2;
			width: 20px;
			height: 20px;

			&:hover {
				color: $red;
			}
		}
	}

	.aioseo-tooltip {
		margin: 0;
		display: flex;
	}

	.aioseo-button {
		svg {
			color: #fff;
			margin-right: 6px;
			vertical-align: middle;
		}
	}

	.date-range {
		flex: 1;
		display: flex;
		width: 100%;
		flex-direction: row;
		gap: 15px;

		.aioseo-datepicker {
			flex: 1;
		}
	}

	.redirects-options-table {
		thead, tfoot {
			td {
				height: 50px;
			}
		}
	}
}
</style>