import {
	useLicenseStore,
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgClose from '@/vue/components/common/svg/Close'

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

export const WizardUsageTracking = {
	components : {
		CoreModal,
		SvgClose
	},
	data () {
		return {
			loading   : false,
			showModal : false
		}
	},
	methods : {
		processOptIn () {
			const setupWizardStore = useSetupWizardStore()

			this.setupWizardStore.smartRecommendations.usageTracking = true
			this.loading                                             = true

			setupWizardStore.saveWizard('smartRecommendations')
				.then(() => {
					const rootStore      = useRootStore()
					window.location.href = rootStore.aioseo.urls.aio.dashboard
				})
		}
	}
}