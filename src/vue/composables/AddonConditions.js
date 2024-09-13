import { computed } from 'vue'

import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import Activate from '@/vue/components/common/core/addon/Activate'
import Update from '@/vue/components/common/core/addon/Update'

export const useAddonConditions = ({ addonSlug }) => {
	const licenseStore = useLicenseStore()

	const shouldShowMain = computed(() => {
		return !licenseStore.isUnlicensed &&
			addons.isActive(addonSlug) &&
			!addons.requiresUpgrade(addonSlug) &&
			addons.hasMinimumVersion(addonSlug)
	})

	const shouldShowUpdate = computed(() => {
		return !licenseStore.isUnlicensed &&
			addons.isInstalled(addonSlug) &&
			!addons.requiresUpgrade(addonSlug) &&
			!addons.hasMinimumVersion(addonSlug)
	})

	const shouldShowActivate = computed(() => {
		return !licenseStore.isUnlicensed &&
			!addons.isActive(addonSlug) &&
			addons.canActivate(addonSlug) &&
			!addons.requiresUpgrade(addonSlug) &&
			(
				addons.hasMinimumVersion(addonSlug) ||
				!addons.isInstalled(addonSlug)
			)
	})

	const shouldShowLite = computed(() => {
		return licenseStore.isUnlicensed || addons.requiresUpgrade(addonSlug)
	})

	const ctaComponent = computed(() => {
		if (shouldShowUpdate.value) {
			return Update
		}

		return Activate
	})

	return {
		ctaComponent,
		shouldShowActivate,
		shouldShowLite,
		shouldShowMain,
		shouldShowUpdate
	}
}