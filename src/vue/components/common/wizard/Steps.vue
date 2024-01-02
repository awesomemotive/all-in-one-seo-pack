<template>
	<div class="aioseo-wizard-steps">
		{{ getSteps }}
	</div>
</template>

<script setup>
import { computed } from 'vue'
import {
	useSetupWizardStore
} from '@/vue/stores'
import { __, sprintf } from '@wordpress/i18n'

const td               = import.meta.env.VITE_TEXTDOMAIN
const setupWizardStore = useSetupWizardStore()
const getSteps         = computed(() => {
	return sprintf(
		// Translators: 1 - The current step count. 2 - The total step count.
		__('Step %1$s of %2$s', td),
		setupWizardStore.getCurrentStageCount,
		setupWizardStore.getTotalStageCount
	)
})
</script>

<style lang="scss">
.aioseo-wizard-steps {
	color: $placeholder-color;
	font-size: 16px;
	font-weight: $font-bold;
	line-height: 24px;
	margin-bottom: 16px;
}
</style>