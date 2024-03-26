<template>
	<div class="aioseo-redirects-blur">
		<core-card
			v-if="!noCoreCard"
			slug="addNewRedirection"
			:header-text="strings.addNewRedirection"
			:noSlide="true"
		>
			<core-blur>
				<core-add-redirection
					:type="REDIRECT_TYPES[0].value"
					:query="REDIRECT_QUERY_PARAMS[0].value"
					:slash="true"
					:case="true"
				/>
			</core-blur>
		</core-card>

		<core-blur
			v-if="noCoreCard"
		>
			<core-add-redirection
				:type="REDIRECT_TYPES[0].value"
				:query="REDIRECT_QUERY_PARAMS[0].value"
				:slash="true"
				:case="true"
			/>
		</core-blur>

		<core-blur>
			<core-wp-table
				:filters="[]"
				:totals="{
					total : 0,
					pages : 0,
					page  : 1
				}"
				:columns="columns"
				:rows="[]"
				:search-label="strings.searchUrls"
				:bulk-options="bulkOptions"
				:additional-filters="additionalFilters"
			/>
		</core-blur>
	</div>
</template>

<script>
import { GLOBAL_STRINGS, REDIRECT_TYPES, REDIRECT_QUERY_PARAMS, REDIRECT_GROUPS } from '@/vue/plugins/constants'

import CoreAddRedirection from '@/vue/components/common/core/add-redirection/Index'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
export default {
	components : {
		CoreAddRedirection,
		CoreBlur,
		CoreCard,
		CoreWpTable
	},
	props : {
		noCoreCard : Boolean
	},
	data () {
		return {
			REDIRECT_TYPES,
			REDIRECT_QUERY_PARAMS,
			strings : {
				addNewRedirection : this.$t.__('Add New Redirection', this.$td),
				searchUrls        : this.$t.__('Search URLs', this.$td)
			},
			bulkOptions : [
				{ label: '', value: '' }
			]
		}
	},
	computed : {
		columns () {
			const columns = [
				{
					slug  : 'source_url',
					label : this.$t.__('Source URL', this.$td)
				},
				{
					slug  : 'target_url',
					label : this.$t.__('Target URL', this.$td)
				},
				{
					slug  : 'hits',
					label : this.$t.__('Hits', this.$td),
					width : '97px'
				},
				{
					slug  : 'type',
					label : this.$t.__('Type', this.$td),
					width : '100px'
				},
				{
					slug  : 'group',
					label : this.$t.__('Group', this.$td),
					width : '150px'
				},
				{
					slug  : 'enabled',
					label : GLOBAL_STRINGS.enabled,
					width : '80px'
				}
			]

			return columns
		},
		additionalFilters () {
			return [
				{
					label   : this.$t.__('Filter by Group', this.$td),
					name    : 'group',
					options : [ { label: this.$t.__('All Groups', this.$td), value: 'all' } ].concat(REDIRECT_GROUPS)
				}
			]
		}
	}
}
</script>