import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import Activate from '@/vue/components/common/core/addon/Activate'
import Update from '@/vue/components/common/core/addon/Update'

export const AddonConditions = {
	computed : {
		ctaComponent () {
			if (this.shouldShowUpdate) {
				return Update
			}

			return Activate
		},
		shouldShowMain () {
			const licenseStore = useLicenseStore()
			return !licenseStore.isUnlicensed &&
				addons.isActive(this.addonSlug) &&
				!addons.requiresUpgrade(this.addonSlug) &&
				addons.hasMinimumVersion(this.addonSlug)
		},
		shouldShowActivate () {
			const licenseStore = useLicenseStore()
			return !licenseStore.isUnlicensed &&
				!addons.isActive(this.addonSlug) &&
				addons.canActivate(this.addonSlug) &&
				!addons.requiresUpgrade(this.addonSlug) &&
				(
					addons.hasMinimumVersion(this.addonSlug) ||
					!addons.isInstalled(this.addonSlug)
				)
		},
		shouldShowUpdate () {
			const licenseStore = useLicenseStore()
			return !licenseStore.isUnlicensed &&
				addons.isInstalled(this.addonSlug) &&
				!addons.requiresUpgrade(this.addonSlug) &&
				!addons.hasMinimumVersion(this.addonSlug)
		},
		shouldShowLite () {
			const licenseStore = useLicenseStore()
			return licenseStore.isUnlicensed || addons.requiresUpgrade(this.addonSlug)
		}
	}
}