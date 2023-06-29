<template>
	<div>
		<div class="sidebar-row" v-if="locationsList.length && !isLocationPostType()">
			<p class="title">{{ strings.selectLocation }}</p>
			<base-select
				size="medium"
				:options="locationsList"
				:modelValue="getLocationOptions(this.$root.$data.locationId)"
				@update:modelValue="values => this.$root.$data.locationId = values.value"
				track-by="value"
			/>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showTitle"
			>
				{{ strings.showTitle }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showIcons"
			>
				{{ strings.showIcons }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showMonday"
			>
				{{ strings.Monday }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showTuesday"
			>
				{{ strings.Tuesday }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showWednesday"
			>
				{{ strings.Wednesday }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showThursday"
			>
				{{ strings.Thursday }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showFriday"
			>
				{{ strings.Friday }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showSaturday"
			>
				{{ strings.Saturday }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showSunday"
			>
				{{ strings.Sunday }}
			</base-toggle>
		</div>
		<div class="sidebar-row labels" v-if="$root.$data.showTitle">
			<label>{{ strings.label }}</label>
			<base-input
				size="small"
				v-model="$root.$data.label"
			/>
		</div>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import BaseInput from '@/vue/components/common/base/Input'
import BaseSelect from '@/vue/components/common/base/Select'
import BaseToggle from '@/vue/components/common/base/Toggle'

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore()
		}
	},
	components : {
		BaseInput,
		BaseSelect,
		BaseToggle
	},
	data () {
		return {
			locationsList : [],
			strings       : {
				selectLocation : this.rootStore.aioseo.localBusiness.postTypeSingleLabel,
				showTitle      : this.$t.__('Show Title', this.$td),
				showIcons      : this.$t.__('Show Icons', this.$td),
				Monday         : this.$t.__('Monday', this.$td),
				Tuesday        : this.$t.__('Tuesday', this.$td),
				Wednesday      : this.$t.__('Wednesday', this.$td),
				Thursday       : this.$t.__('Thursday', this.$td),
				Friday         : this.$t.__('Friday', this.$td),
				Saturday       : this.$t.__('Saturday', this.$td),
				Sunday         : this.$t.__('Sunday', this.$td),
				label          : this.$t.__('Label', this.$td)
			}
		}
	},
	methods : {
		getLocationOptions (option) {
			let selected = this.locationsList.find(u => u.value === option)

			// Default to the first location in the list.
			if (!selected && !this.isLocationPostType()) {
				selected = this.locationsList.find(u => !!u)
				if (selected) {
					this.$root.$data.locationId = selected.value
				}
			}

			return selected
		},
		isLocationPostType () {
			return this.postEditorStore.currentPost.postType === this.rootStore.aioseo.localBusiness.postTypeName
		}
	},
	created () {
		if (this.$root._data.locations) {
			this.$root._data.locations.forEach(location => {
				this.locationsList.push({
					value : location.id,
					label : location.title.rendered
				})
			})
		}
	}
}
</script>

<style lang="scss" scoped>
.sidebar-row {
	margin-bottom: 16px;
	.title {
		font-weight: $font-bold;
	}

	&.labels {

		label {
			display: block;
			margin-bottom: 4px;
		}
	}
}
</style>