<template>
	<div class="aioseo-post-status-options-toggle">
		<div
			class="post-status-options-settings"
		>

			<core-alert
				v-if="0 === postStatuses.length && 'postStatuses' === type"
				type="blue"
			>
				<strong>{{ strings.noPostStatuses }}</strong>
				{{ strings.noPostStatusesDescription }}

			</core-alert>
			<core-alert
				v-if="0 === postStatuses.length && 'taxonomies' === type"
				type="blue"
			>
				<strong>{{ strings.noTaxonomies }}</strong>
				{{ strings.noTaxonomiesDescription }}
			</core-alert>
			<grid-row
				v-if="0 < postStatuses.length"
			>
				<grid-column
					md="6"
					v-for="(status, index) in postStatuses"
					:key="index"
				>
					<base-highlight-toggle
						size="medium"
						:active="isActive(status)"
						:name="status.label"
						type="checkbox"
						:modelValue="getValue(status)"
						@update:modelValue="checked => updateValue(checked, status)"
					>
						{{ status.label }} ({{ status.status }})
					</base-highlight-toggle>
				</grid-column>
			</grid-row>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '$/vue/stores'

import BaseHighlightToggle from '@/vue/components/common/base/HighlightToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		BaseHighlightToggle,
		CoreAlert,
		GridColumn,
		GridRow
	},
	props : {
		type : {
			type     : String,
			required : true
		},
		options : {
			type     : Object,
			required : true
		},
		registeredPostStatuses : Object,
		excluded               : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			strings : {
				label                     : __('Label:', td),
				name                      : __('Status:', td),
				noPostStatuses            : __('No post statuses available.', td),
				noTaxonomies              : __('No taxonomies available.', td),
				noPostStatusesDescription : __('All post statuses are set to noindex or your site does not have any post statuses registered that are supported by this feature.', td),
				noTaxonomiesDescription   : __('All taxonomies are set to noindex or your site does not have any taxonomies registered that are supported by this feature.', td)
			}
		}
	},
	computed : {
		getRegisteredPostStatuses () {
			return this.registeredPostStatuses || this.rootStore.aioseo.postData
		},
		postStatuses () {
			return this.getRegisteredPostStatuses[this.type].filter(postStatus => {
				return !this.excluded.includes(postStatus.status)
			})
		}
	},
	methods : {
		emitInput (value) {
			this.$emit('input', value)
		},
		updateValue (checked, type) {
			if (checked) {
				const included = this.options[this.type].included
				included.push(type.status)
				this.options[this.type].included = included
				return
			}

			const index = this.options[this.type].included.findIndex(t => t === type.status)
			if (-1 !== index) {
				this.options[this.type].included.splice(index, 1)
			}
		},
		getValue (type) {
			return this.options[this.type].included.includes(type.status)
		},
		isActive (type) {
			const index = this.options[this.type].included.findIndex(t => t === type.status)
			if (-1 !== index) {
				return true
			}

			return false
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-status-options-toggle {
	margin-top: 20px;
}
</style>