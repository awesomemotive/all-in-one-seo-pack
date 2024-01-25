<template>
	<div class="aioseo-included-objects-toggle">
		<div
			class="included-objects-settings"
		>
			<base-toggle
				size="medium"
				v-if="type === 'post_types'"
				v-model="$root.$data.post_types_all"
			>
				{{ strings.includeAllPostTypes }}
			</base-toggle>

			<base-toggle
				size="medium"
				v-if="type === 'taxonomies'"
				v-model="$root.$data.taxonomies_all"
			>
				{{ strings.includeAllTaxonomies }}
			</base-toggle>

			<grid-row
				class="aioseo-included-list"
				v-if="0 < objects.length && !includeAllSetting"
			>
				<grid-column
					md="6"
					v-for="(object, index) in objects"
					:key="index"
				>
					<base-highlight-toggle
						size="medium"
						:active="isActive(object)"
						:name="object.name"
						type="checkbox"
						:modelValue="getValue(object)"
						@update:modelValue="checked => updateValue(checked, object)"
					>
						<core-tooltip>
							<span
								class="icon dashicons"
								:class="getPostIconClass(object.icon)"
							/>

							<template #tooltip>
								<div class="aioseo-description">
									{{ strings.label }} <strong>{{ object.label }}</strong><br>
									{{ strings.name }} <strong>{{ object.name }}</strong>
								</div>
							</template>
						</core-tooltip>
						{{ object.label }}
					</base-highlight-toggle>
				</grid-column>
			</grid-row>
		</div>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import BaseHighlightToggle from '@/vue/components/common/base/HighlightToggle'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import PostTypesMixin from '@/vue/mixins/PostTypes'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		BaseHighlightToggle,
		CoreTooltip,
		GridColumn,
		GridRow
	},
	mixins : [ PostTypesMixin ],
	props  : {
		type : {
			type     : String,
			required : true
		},
		excluded : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			strings : {
				label                : this.$t.__('Label:', this.$td),
				name                 : this.$t.__('Slug:', this.$td),
				includeAllPostTypes  : this.$t.__('Include All Post Types', this.$td),
				includeAllTaxonomies : this.$t.__('Include All Taxonomies', this.$td)
			}
		}
	},
	computed : {
		includeAllSetting () {
			const key = this.type + '_all'
			return this.$root.$data[key]
		},
		objects () {
			const objectName = 'taxonomies' === this.type ? 'taxonomies' : 'postTypes'
			return this.rootStore.aioseo.postData[objectName].filter(object => {
				return !this.excluded.includes(object.name)
			})
		}
	},
	methods : {
		getState () {
			return JSON.parse(this.$root.$data[this.type])
		},
		setState (value) {
			this.$root.$data[this.type] = JSON.stringify(value)
		},
		getValue (object) {
			return this.getState().includes(object.name)
		},
		updateValue (checked, type) {
			const state = this.getState()
			if (checked) {
				state.push(type.name)
				this.setState(state)
				return
			}

			const index = state.findIndex(t => t === type.name)
			if (-1 !== index) {
				state.splice(index, 1)
				this.setState(state)
			}
		},
		isActive (object) {
			const index = this.getState().findIndex(t => t === object.name)
			if (-1 !== index) {
				return true
			}
			return false
		}
	}
}
</script>

<style lang="scss">
.aioseo-sidebar-row .aioseo-included-objects-toggle {
	margin-top: 5px;

	.aioseo-included-list {
		margin-top: 8px;
	}
}
</style>