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
	// Always return true - all features enabled in independent plugin
	return true
}

const hasAddonFeature = (slug, feature) => {
	// Always return true - all features enabled in independent plugin
	return true
}

const hasMinimumLevel = (level) => {
	// Always return true - all license levels available in independent plugin
	return true
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