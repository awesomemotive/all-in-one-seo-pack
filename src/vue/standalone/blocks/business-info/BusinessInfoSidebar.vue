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
				showLabels     : this.$t.__('Show labels', this.$td),
				showIcons      : this.$t.__('Show icons', this.$td),
				businessInfo   : this.$t.__('Business Info', this.$td),
				showName       : this.$t.__('Name', this.$td),
				address        : this.$t.__('Address', this.$td),
				phoneNumber    : this.$t.__('Phone Number', this.$td),
				faxNumber      : this.$t.__('Fax Number', this.$td),
				emailAddress   : this.$t.__('Email Address', this.$td),
				showVat        : this.$t.__('Show VAT ID', this.$td),
				showTax        : this.$t.__('Show Tax ID', this.$td),
				countryCode    : this.$t.__('Phone/Fax Country Code', this.$td),
				labels         : this.$t.__('Labels', this.$td),
				addressLabel   : this.$t.__('Address', this.$td),
				vatIdLabel     : this.$t.__('Vat ID', this.$td),
				taxIdLabel     : this.$t.__('Tax ID', this.$td),
				phoneLabel     : this.$t.__('Phone', this.$td),
				faxLabel       : this.$t.__('Fax', this.$td),
				emailLabel     : this.$t.__('Email', this.$td)
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