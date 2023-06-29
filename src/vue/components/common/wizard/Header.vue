<template>
	<div class="aioseo-wizard-header">
		<div class="logo">
			<svg-aioseo-logo />
		</div>

		<wizard-progress
			:steps="steps"
		/>
	</div>
</template>

<script>
import {
	useSetupWizardStore
} from '@/vue/stores'

import SvgAioseoLogo from '@/vue/components/common/svg/aioseo/Logo'
import WizardProgress from '@/vue/components/common/wizard/Progress'
export default {
	setup () {
		return {
			setupWizardStore : useSetupWizardStore()
		}
	},
	components : {
		SvgAioseoLogo,
		WizardProgress
	},
	computed : {
		steps () {
			const steps = []
			for (let i = 0; i < this.setupWizardStore.getTotalStageCount; i++) {
				if (i < this.setupWizardStore.getCurrentStageCount) {
					steps.push({ active: true })
				} else {
					steps.push({ active: false })
				}
			}

			return steps
		}
	}
}
</script>

<style lang="scss">
.aioseo-wizard-header {
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;

	svg.aioseo-logo {
		margin: 60px 0 40px;
		width: 100%;
		max-width: 140px;
		height: auto;
		margin-right: 10px;
	}
}
</style>