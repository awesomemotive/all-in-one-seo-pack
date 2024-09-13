import { computed } from 'vue'

import {
	useLicenseStore
} from '@/vue/stores'

import license from '@/vue/utils/license'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useLicense = () => {
	const licenseStore = useLicenseStore()

	const yourLicenseIsText = computed(() => {
		let text = __('You have not yet added a valid license key.', td)

		if (licenseStore.license.isExpired) {
			text = __('Your license has expired.', td)
		}

		if (licenseStore.license.isDisabled) {
			text = __('Your license has been disabled.', td)
		}

		if (licenseStore.license.isInvalid) {
			text = __('Your license key is invalid.', td)
		}

		return text
	})

	const shouldShowLite = computed(() => {
		return licenseStore.isUnlicensed
	})

	const shouldShowMain = (sectionSlug, feature) => {
		return !licenseStore.isUnlicensed && license.hasCoreFeature(sectionSlug, feature)
	}

	const shouldShowUpgrade = (sectionSlug, feature) => {
		return !licenseStore.isUnlicensed && !license.hasCoreFeature(sectionSlug, feature)
	}

	return {
		shouldShowLite,
		shouldShowMain,
		shouldShowUpgrade,
		yourLicenseIsText
	}
}