<template>
	<component :is="$route.name" />
</template>

<script>
import {
	useLicenseStore,
	useOptionsStore,
	useSetupWizardStore
} from '@/vue/stores'

import AdditionalInformation from './AdditionalInformation'
import Category from './Category'
import Features from './Features'
import Import from './Import'
import LicenseKey from './LicenseKey'
import SearchAppearance from './SearchAppearance'
import SmartRecommendations from './SmartRecommendations'
import Success from './Success'
import Welcome from './Welcome'

export default {
	setup () {
		return {
			licenseStore     : useLicenseStore(),
			optionsStore     : useOptionsStore(),
			setupWizardStore : useSetupWizardStore()
		}
	},
	components : {
		AdditionalInformation,
		Category,
		Features,
		Import,
		LicenseKey,
		SearchAppearance,
		SmartRecommendations,
		Success,
		Welcome
	},
	methods : {
		deleteStage (stage) {
			const index = this.setupWizardStore.stages.findIndex(s => stage === s)
			if (-1 !== index) {
				// Delete the stage from stages.
				this.setupWizardStore.stages.splice(index, 1)
			}
		}
	},
	mounted () {
		if (this.optionsStore.internalOptions.internal.wizard) {
			const wizard = JSON.parse(this.optionsStore.internalOptions.internal.wizard)
			delete wizard.currentStage
			delete wizard.stages
			delete wizard.licenseKey

			this.setupWizardStore.loadState(wizard)
		}

		if (!this.setupWizardStore.shouldShowImportStep) {
			this.deleteStage('import')
		}

		if (!this.licenseStore.isUnlicensed) {
			this.deleteStage('license-key')
		}

		if (this.$isPro) {
			this.deleteStage('smart-recommendations')
		}
	}
}
</script>