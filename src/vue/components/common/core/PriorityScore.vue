<template>
	<div class="aioseo-priority-score">
		<table-row class="header-row">
			<table-column />

			<table-column>
				{{ strings.priority }}
			</table-column>

			<table-column>
				{{ strings.frequency }}
			</table-column>
		</table-row>

		<table-row
			v-for="(row, index) in filteredRows"
			:key="index"
		>
			<table-column>
				{{ getLabel(row) }}
			</table-column>

			<table-column>
				<base-select
					size="medium"
					:options="getPriorityOptions"
					:modelValue="getJsonValue(priority[row].priority)"
					@update:modelValue="values => priority[row].priority = setJsonValue(values)"
				/>
			</table-column>

			<table-column>
				<base-select
					size="medium"
					:options="getFrequencyOptions"
					:modelValue="getJsonValue(priority[row].frequency)"
					@update:modelValue="values => priority[row].frequency = setJsonValue(values)"
				/>
			</table-column>
		</table-row>
	</div>
</template>

<script>
import {
	FREQUENCY_OPTIONS,
	PRIORITY_OPTIONS
} from '@/vue/plugins/constants'
import {
	useOptionsStore
} from '@/vue/stores'

import { useJsonValues } from '@/vue/composables/JsonValues'

import TableColumn from '@/vue/components/common/table/Column'
import TableRow from '@/vue/components/common/table/Row'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getJsonValue,
			setJsonValue
		} = useJsonValues()

		return {
			getJsonValue,
			optionsStore : useOptionsStore(),
			setJsonValue
		}
	},
	components : {
		TableColumn,
		TableRow
	},
	props : {
		priority : {
			type     : Object,
			required : true
		},
		rows : {
			type     : Array,
			required : true
		},
		labels : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			strings : {
				postTypes  : __('Post Types', td),
				taxonomies : __('Taxonomies', td),
				priority   : __('Priority', td),
				frequency  : __('Frequency', td),
				homePage   : __('Home Page', td),
				archive    : __('Date Archive Pages', td),
				author     : __('Author Pages', td)
			}
		}
	},
	computed : {
		getFrequencyOptions () {
			return [ { label: __('default', td), value: 'default' } ].concat(FREQUENCY_OPTIONS)
		},
		getPriorityOptions () {
			return [ { label: __('default', td), value: 'default' } ].concat(PRIORITY_OPTIONS)
		},
		filteredRows () {
			let filteredRows = this.rows
			if (!this.optionsStore.options.sitemap.general.date) {
				filteredRows = filteredRows.filter(rowName => 'archive' !== rowName)
			}

			if (!this.optionsStore.options.sitemap.general.author) {
				filteredRows = filteredRows.filter(rowName => 'author' !== rowName)
			}

			return filteredRows
		}
	},
	methods : {
		getLabel (row) {
			return this.labels[row] || this.strings[row]
		}
	}
}
</script>

<style lang="scss">
.aioseo-priority-score {
	max-width: 350px;

	.header-row {
		font-size: 14px;
	}
}
</style>