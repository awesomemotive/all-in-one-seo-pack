<template>
	<div class="aioseo-location-map-sidebar">
		<div class="sidebar-row" v-if="locationsList.length && !isLocationPostType()">
			<p class="title">{{ strings.selectLocation }}</p>
			<base-select
				size="medium"
				:options="locationsList"
				:modelValue="getLocationOptions($root.$data.locationId)"
				@update:modelValue="values => $root.$data.locationId = values.value"
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

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

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
				showLabel      : __('Show label', td),
				showIcon       : __('Show icon', td),
				businessInfo   : __('Business Info', td),
				mapDisplay     : __('Map Display', td),
				width          : __('Width', td),
				height         : __('Height', td),
				customMarker   : __('Custom Marker', td),
				minimumSize    : sprintf(
					// Translators: 1 - Strong tag, 2 - Close strong tag.
					__('%1$sThe custom marker should be: 100x100 px.%2$s If the image exceeds those dimensions it could (partially) cover the info popup.', td),
					'<strong>',
					'</strong>'
				),
				label : __('Label', td)
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