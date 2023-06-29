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
				v-model="$root.$data.showLabel"
			>
				{{ strings.showLabel }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showIcon"
			>
				{{ strings.showIcon }}
			</base-toggle>
		</div>
		<div class="sidebar-row">
			<p class="title">{{strings.customMarker}}</p>

			<core-image-uploader
				class="aioseo-image-uploader--no-icon"
				img-preview-max-width="100px"
				img-preview-max-height="100px"
				base-size="small"
				:description="strings.minimumSize"
				v-model="$root.$data.customMarker"
			/>
		</div>
		<div class="sidebar-row">
			<p class="title">{{strings.mapDisplay}}</p>
		</div>
		<div class="sidebar-row dimensions">
			<div>
				<label>{{ strings.width }}:</label>
				<base-input
					size="small"
					v-model="$root.$data.width"
				/>
			</div>
			<div>
				<div>
					<label>{{ strings.height }}:</label>
					<base-input
						size="small"
						v-model="$root.$data.height"
					/>
				</div>
			</div>
		</div>
		<div class="sidebar-row labels" v-if="$root.$data.showLabel">
			<p class="title">{{ strings.label }}</p>
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
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'

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
		BaseToggle,
		CoreImageUploader
	},
	data () {
		return {
			locationsList : [],
			strings       : {
				selectLocation : this.rootStore.aioseo.localBusiness.postTypeSingleLabel,
				showLabel      : this.$t.__('Show label', this.$td),
				showIcon       : this.$t.__('Show icon', this.$td),
				businessInfo   : this.$t.__('Business Info', this.$td),
				mapDisplay     : this.$t.__('Map Display', this.$td),
				width          : this.$t.__('Width', this.$td),
				height         : this.$t.__('Height', this.$td),
				customMarker   : this.$t.__('Custom Marker', this.$td),
				minimumSize    : this.$t.sprintf(
					// Translators: 1 - Strong tag, 2 - Close strong tag.
					this.$t.__('%1$sThe custom marker should be: 100x100 px.%2$s If the image exceeds those dimensions it could (partially) cover the info popup.', this.$td),
					'<strong>',
					'</strong>'
				),
				label : this.$t.__('Label', this.$td)
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
		if (this.$root.$data.locations) {
			this.$root.$data.locations.forEach(location => {
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

	&.dimensions {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		margin-left: -10px;
		margin-right: -10px;

		> div {
			padding: 0 10px;
		}

		label {
			display: block;
			padding-bottom: 8px;
		}
	}
}
</style>