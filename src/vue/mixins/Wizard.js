import {
	useLicenseStore,
	useSetupWizardStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

export const Wizard = {
	computed : {
		features () {
			return [ ...this.$constants.WIZARD_FEATURES ]
		},
		getSelectedUpsellFeatures () {
			const setupWizardStore = useSetupWizardStore()
			if (!setupWizardStore.features) {
				return []
			}

			return setupWizardStore.features
				.filter(feature => {
					return this.needsUpsell(this.features.find(f => f.value === feature))
				})
				.map(feature => this.features.find(f => f.value === feature))
		}
	},
	methods : {
		needsUpsell (feature) {
			if (!feature.pro) {
				return false
			}

			const licenseStore = useLicenseStore()
			if (licenseStore.isUnlicensed) {
				return true
			}

			return feature.upgrade && addons.requiresUpgrade(feature.upgrade)
		}
	},
	mounted () {
		const setupWizardStore        = useSetupWizardStore()
		setupWizardStore.currentStage = this.stage
	}
}