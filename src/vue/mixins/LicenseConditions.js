import {
	useLicenseStore
} from '@/vue/stores'

import license from '@/vue/utils/license'

export const LicenseConditions = {
	computed : {
		shouldShowLite () {
			const licenseStore = useLicenseStore()
			return licenseStore.isUnlicensed
		}
	},
	methods : {
		shouldShowMain (sectionSlug, feature) {
			const licenseStore = useLicenseStore()
			return !licenseStore.isUnlicensed && license.hasCoreFeature(sectionSlug, feature)
		},
		shouldShowUpgrade (sectionSlug, feature) {
			const licenseStore = useLicenseStore()
			return !licenseStore.isUnlicensed && !license.hasCoreFeature(sectionSlug, feature)
		}
	}
}