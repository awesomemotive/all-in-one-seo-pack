<template>
	<div class="aioseo-wp-bulk-actions alignleft actions bulkactions">
		<select
			v-model="bulkAction"
			:disabled="disableTable"
		>
			<option value="-1">{{ strings.bulkActions }}</option>

			<option
				v-for="(option, index) in bulkOptions"
				:key="index"
				:value="option.value"
			>{{ option.label }}</option>
		</select>

		<button
			class="button action"
			@click="'-1' !== bulkAction ? $emit('process-bulk-action', bulkAction) : null"
			:disabled="disableTable"
		>
			{{strings.apply}}
		</button>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'process-bulk-action' ],
	props : {
		bulkOptions : {
			type     : Array,
			required : true
		},
		disableTable : Boolean
	},
	data () {
		return {
			bulkAction : '-1',
			strings    : {
				bulkActions : __('Bulk Actions', td),
				apply       : __('Apply', td)
			}
		}
	},
	watch : {
		bulkOptions (bulkOptions) {
			// Set 'Bulk Actions' in case the action selected doesn't exist in the list.
			if (undefined === bulkOptions.find(row => row.value === this.bulkAction)) {
				this.bulkAction = '-1'
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-wp-bulk-actions {
	select {
		margin-left: 0;
	}
}
</style>