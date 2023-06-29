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
	useOptionsStore
} from '@/vue/stores'

import { JsonValues } from '@/vue/mixins'
import TableColumn from '@/vue/components/common/table/Column'
import TableRow from '@/vue/components/common/table/Row'

export default {
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	components : {
		TableColumn,
		TableRow
	},
	mixins : [ JsonValues ],
	props  : {
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
				postTypes  : this.$t.__('Post Types', this.$td),
				taxonomies : this.$t.__('Taxonomies', this.$td),
				priority   : this.$t.__('Priority', this.$td),
				frequency  : this.$t.__('Frequency', this.$td),
				homePage   : this.$t.__('Home Page', this.$td),
				archive    : this.$t.__('Date Archive Pages', this.$td),
				author     : this.$t.__('Author Pages', this.$td)
			}
		}
	},
	computed : {
		getFrequencyOptions () {
			return [ { label: this.$t.__('default', this.$td), value: 'default' } ].concat(this.$constants.FREQUENCY_OPTIONS)
		},
		getPriorityOptions () {
			return [ { label: this.$t.__('default', this.$td), value: 'default' } ].concat(this.$constants.PRIORITY_OPTIONS)
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