import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { getJsonValue } from '@/vue/utils/json'
import { upperFirst } from 'lodash-es'

const levels = {
	individual : 0,
	business   : 1,
	agency     : 2,
	basic      : 3,
	plus       : 4,
	pro        : 5,
	elite      : 6
}

const getFeatures = (type = '') => {
	const optionsStore = useOptionsStore()
	const rootStore    = useRootStore()
	const features     = rootStore.aioseo.data.isNetworkLicensed && !optionsStore.options.general.licenseKey
		? optionsStore.internalNetworkOptions.internal.license?.features || []
		: optionsStore.internalOptions.internal.license?.features || []
	let allFeatures = getJsonValue(features, [])
	if (type) {
		allFeatures = allFeatures[type] || []
	}

	return allFeatures
}

const hasCoreFeature = (sectionSlug, feature) => {
	const features = getFeatures('core')
	for (const section in features) {
		// If we have no specific feature, we're just checking if the section is enabled.
		if (sectionSlug === section && !feature) {
			return true
		}

		if (sectionSlug === section && features[section].includes(feature)) {
			return true
		}
	}

	return false
}

const hasAddonFeature = (slug, feature) => {
	const addonFeatures = getFeatures('addons')
	for (const addon in addonFeatures) {
		if (slug === addon && addonFeatures[addon].includes(feature)) {
			return true
		}
	}

	return false
}

const hasMinimumLevel = (level) => {
	const optionsStore = useOptionsStore()
	const rootStore    = useRootStore()
	const currentLevel = rootStore.aioseo.data.isNetworkLicensed && !optionsStore.options.general.licenseKey
		? optionsStore.internalNetworkOptions.internal.license?.level
		: optionsStore.internalOptions.internal.license?.level

	if (!currentLevel) {
		return false
	}

	return levels[currentLevel] >= levels[level]
}

const getPlansForFeature = (sectionSlug, feature = '') => {
	const plans = []

	// Loop through all the features and find the plans that have access to the feature.
	const rootStore = useRootStore()
	rootStore.aioseo.features.forEach(featureArray => {
		if (featureArray.section !== sectionSlug) {
			return
		}

		if (feature && featureArray.feature !== feature) {
			return
		}

		plans.push(upperFirst(featureArray.license_level))
	})

	const knownPlans = [ 'Basic', 'Plus', 'Pro', 'Elite' ]
	// Ensure that plans are in the correct order.
	plans.sort((a, b) => knownPlans.indexOf(a) - knownPlans.indexOf(b))

	return [ ...new Set(plans) ]
}

export default {
	getFeatures,
	getPlansForFeature,
	hasAddonFeature,
	hasCoreFeature,
	hasMinimumLevel
}