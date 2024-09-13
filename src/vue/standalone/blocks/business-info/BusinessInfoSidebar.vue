<template>
	<div>
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
				v-model="$root.$data.showLabels"
			>
				{{ strings.showLabels }}
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
			<p class="title">{{strings.businessInfo}}</p>
		</div>

		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showName"
			>
				{{ strings.showName }}
			</base-toggle>
		</div>

		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showAddress"
			>
				{{ strings.address }}
			</base-toggle>
		</div>

		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showPhone"
			>
				{{ strings.phoneNumber }}
			</base-toggle>
		</div>

		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showFax"
			>
				{{ strings.faxNumber }}
			</base-toggle>
		</div>

		<div class="sidebar-row" v-if="$root.$data.showPhone || $root.$data.showFax">
			<base-toggle
				v-model="$root.$data.showCountryCode"
			>
				{{ strings.countryCode }}
			</base-toggle>
		</div>

		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showEmail"
			>
				{{ strings.emailAddress }}
			</base-toggle>
		</div>

		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showVat"
			>
				{{ strings.showVat }}
			</base-toggle>
		</div>

		<div class="sidebar-row">
			<base-toggle
				v-model="$root.$data.showTax"
			>
				{{ strings.showTax }}
			</base-toggle>
		</div>

		<div v-if="$root.$data.showLabels">
			<div class="sidebar-row">
				<p class="title">{{strings.labels}}</p>
			</div>

			<div class="sidebar-row labels">
				<div v-if="$root.$data.showAddress">
					<label>{{ strings.addressLabel }}</label>
					<base-input
						size="small"
						v-model="$root.$data.addressLabel"
					/>
				</div>

				<div v-if="$root.$data.showVat">
					<label>{{ strings.vatIdLabel }}</label>
					<base-input
						size="small"
						v-model="$root.$data.vatIdLabel"
					/>
				</div>

				<div v-if="$root.$data.showTax">
					<label>{{ strings.taxIdLabel }}</label>
					<base-input
						size="small"
						v-model="$root.$data.taxIdLabel"
					/>
				</div>

				<div v-if="$root.$data.showPhone">
					<label>{{ strings.phoneLabel }}</label>
					<base-input
						size="small"
						v-model="$root.$data.phoneLabel"
					/>
				</div>

				<div v-if="$root.$data.showFax">
					<label>{{ strings.faxLabel }}</label>
					<base-input
						size="small"
						v-model="$root.$data.faxLabel"
					/>
				</div>

				<div v-if="$root.$data.showEmail">
					<label>{{ strings.emailLabel }}</label>
					<base-input
						size="small"
						v-model="$root.$data.emailLabel"
					/>
				</div>
			</div>
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

import { __ } from '@/vue/plugins/translations'

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
		BaseToggle
	},
	data () {
		return {
			locationsList : [],
			strings       : {
				selectLocation : this.rootStore.aioseo.localBusiness.postTypeSingleLabel,
				showLabels     : __('Show labels', td),
				showIcons      : __('Show icons', td),
				businessInfo   : __('Business Info', td),
				showName       : __('Name', td),
				address        : __('Address', td),
				phoneNumber    : __('Phone Number', td),
				faxNumber      : __('Fax Number', td),
				emailAddress   : __('Email Address', td),
				showVat        : __('Show VAT ID', td),
				showTax        : __('Show Tax ID', td),
				countryCode    : __('Phone/Fax Country Code', td),
				labels         : __('Labels', td),
				addressLabel   : __('Address', td),
				vatIdLabel     : __('Vat ID', td),
				taxIdLabel     : __('Tax ID', td),
				phoneLabel     : __('Phone', td),
				faxLabel       : __('Fax', td),
				emailLabel     : __('Email', td)
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

	&.labels {

		> div {
			margin-bottom: 16px;
		}

		label {
			display: block;
			margin-bottom: 4px;
		}
	}
}
</style>