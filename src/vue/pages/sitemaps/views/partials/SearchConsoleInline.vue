<template>
	<div class="google-search-console-alerts-inline">
		<template v-if="showIsConnected">
			<core-alert-actionable
				:text="strings.yourSiteIsConnected"
				type="green"
				size="small"
			/>

			<core-alert-actionable
				v-if="searchStatisticsStore.sitemapsWithErrors.length > 0 && settingsStore.settings.dismissedAlerts?.searchConsoleSitemapErrors"
				:text="strings.aioseoHasFoundSomeErrorsInSitemaps"
				:button="strings.fixSitemapErrors"
				buttonType="link"
				type="yellow"
				size="small"
				@click="() => showErrorsModal = true"
			/>

			<sitemaps-with-errors-modal
				:display="showErrorsModal"
				@close="showErrorsModal = false"
				:sitemaps="searchStatisticsStore.sitemapsWithErrors"
			/>
		</template>

		<core-alert-actionable
			v-else-if="settingsStore.settings.dismissedAlerts?.searchConsoleNotConnected"
			:text="strings.connectToGoogleToAddSitemaps"
			:button="strings.connectToGoogleSearchConsole"
			buttonType="link"
			type="yellow"
			size="small"
			@click="redirectToGscSettings"
		/>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useSearchStatisticsStore,
	useSettingsStore,
	useRootStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useGoogleSearchConsole } from '@/vue/composables/GoogleSearchConsole'
import CoreAlertActionable from '@/vue/components/common/core/alert/Actionable'
import SitemapsWithErrorsModal from './SitemapsWithErrorsModal'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			strings,
			redirectToGscSettings
		} = useGoogleSearchConsole()

		return {
			optionsStore          : useOptionsStore(),
			searchStatisticsStore : useSearchStatisticsStore(),
			settingsStore         : useSettingsStore(),
			rootStore             : useRootStore(),
			composableStrings     : strings,
			redirectToGscSettings
		}
	},
	components : {
		CoreAlertActionable,
		SitemapsWithErrorsModal
	},
	data () {
		return {
			showErrorsModal : false,
			strings         : merge(this.composableStrings, {
				yourSiteIsConnected : __('Your site is connected directly to Google Search Console and your sitemaps are in sync.', td)
			})
		}
	},
	computed : {
		showIsConnected () {
			return this.searchStatisticsStore.isConnected && this.optionsStore.internalOptions.internal.searchStatistics.site.verified
		}
	}
}
</script>

<style lang="scss">
.google-search-console-alerts-inline:not(:empty) {
	margin-top: 16px;

	.aioseo-alert:not(:last-child) {
		margin-bottom: 8px;
	}
}
</style>