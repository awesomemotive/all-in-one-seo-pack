import {
	useLicenseStore
} from '@/vue/stores'

export const License = {
	computed : {
		yourLicenseIsText () {
			const licenseStore = useLicenseStore()
			let text = this.$t.__('You have not yet added a valid license key.', this.$td)

			if (licenseStore.license.isExpired) {
				text = this.$t.__('Your license has expired.', this.$td)
			}

			if (licenseStore.license.isDisabled) {
				text = this.$t.__('Your license has been disabled.', this.$td)
			}

			if (licenseStore.license.isInvalid) {
				text = this.$t.__('Your license key is invalid.', this.$td)
			}

			return text
		}
	}
}