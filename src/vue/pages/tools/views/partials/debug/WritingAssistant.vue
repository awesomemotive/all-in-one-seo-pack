<template>
	<div class="aioseo-writing-assistant">
		<base-button
			type="blue"
			size="medium"
			@click="resetSeoboostLogins"
			:loading="loading"
			:disabled="loading"
		>
			{{ strings.resetLogins }}
		</base-button>
	</div>
</template>

<script setup>
import { __ } from '@/vue/plugins/translations'
import { useToolsStore } from '@/vue/stores'

const toolsStore = useToolsStore()
const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	resetLogins : __('Reset SEOBoost Logins', td)
}

let loading = false

const resetSeoboostLogins = () => {
	if (confirm(__('Are you sure you want to reset SEOBoost logins?', td))) {
		loading = true
		// Reset SEOBoost logins.
		toolsStore.doTask({
			action : 'aioseo-reset-seoboost-logins'
		}).finally(() => {
			alert(__('SEOBoost logins have been reset.', td))
			loading = false
		})
	}
}
</script>