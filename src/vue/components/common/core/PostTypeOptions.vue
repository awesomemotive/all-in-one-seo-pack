<template>
	<div class="aioseo-post-type-options-toggle">
		<div
			class="post-type-options-settings"
		>

			<core-alert
				v-if="0 === postTypes.length && 'postTypes' === type"
				type="blue"
			>
				<strong>{{ strings.noPostTypes }}</strong>
				{{ strings.noPostTypesDescription }}

			</core-alert>
			<core-alert
				v-if="0 === postTypes.length && 'taxonomies' === type"
				type="blue"
			>
				<strong>{{ strings.noTaxonomies }}</strong>
				{{ strings.noTaxonomiesDescription }}
			</core-alert>
			<grid-row
				v-if="0 < postTypes.length"
			>
				<grid-column
					md="6"
					v-for="(type, index) in postTypes"
					:key="index"
				>
					<base-highlight-toggle
						size="medium"
						:active="isActive(type)"
						:name="type.name"
						type="checkbox"
						:modelValue="getValue(type)"
						@update:modelValue="checked => updateValue(checked, type)"
					>
						<core-tooltip>
							<span
								class="icon dashicons"
								:class="getPostIconClass(type.icon)"
							/>

							<template #tooltip>
								<div class="aioseo-description">
									{{ strings.label }} <strong>{{ type.label }}</strong><br>
									{{ strings.name }} <strong>{{ type.name }}</strong>
								</div>
							</template>
						</core-tooltip>
						{{ type.label }}
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

import { usePostTypes } from '@/vue/composables/PostTypes'

import BaseHighlightToggle from '@/vue/components/common/base/HighlightToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getPostIconClass
		} = usePostTypes()

		return {
			getPostIconClass,
			rootStore : useRootStore()
		}
	},
	components : {
		BaseHighlightToggle,
		CoreAlert,
		CoreTooltip,
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
		registeredPostTypes : Object,
		excluded            : {
			type : Array,
			default () {
				return []
			}
		},
		supports : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			strings : {
				label                   : __('Label:', td),
				name                    : __('Slug:', td),
				noPostTypes             : __('No post types available.', td),
				noTaxonomies            : __('No taxonomies available.', td),
				noPostTypesDescription  : __('All post types are set to noindex or your site does not have any post types registered that are supported by this feature.', td),
				noTaxonomiesDescription : __('All taxonomies are set to noindex or your site does not have any taxonomies registered that are supported by this feature.', td)
			}
		}
	},
	computed : {
		getRegisteredPostTypes () {
			return this.registeredPostTypes || this.rootStore.aioseo.postData
		},
		postTypes () {
			return this.getRegisteredPostTypes[this.type].filter(postType => {
				let filtered = true
				if (this.supports.length && postType.supports.length) {
					filtered = this.supports.every(support => postType.supports.includes(support))
				}

				return filtered && !this.excluded.includes(postType.name)
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
				included.push(type.name)
				this.options[this.type].included = included
				return
			}

			const index = this.options[this.type].included.findIndex(t => t === type.name)
			if (-1 !== index) {
				this.options[this.type].included.splice(index, 1)
			}
		},
		getValue (type) {
			return this.options[this.type].included.includes(type.name)
		},
		isActive (type) {
			const index = this.options[this.type].included.findIndex(t => t === type.name)
			if (-1 !== index) {
				return true
			}

			return false
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-type-options-toggle {
	margin-top: 16px;

	+ div.aioseo-description {
		margin-top: 16px;
	}
}
</style>