<template>
	<div class="google-search-console-alerts">
		<core-alert-actionable
			v-if="!searchStatisticsStore.isConnected && !settingsStore.settings.dismissedAlerts?.searchConsoleNotConnected"
			:title="strings.connectToGoogleToAddSitemaps"
			:text="strings.aioseoCanNowVerify"
			:button="strings.connectToGoogleSearchConsole"
			buttonType="link"
			type="yellow"
			size="small"
			show-close
			@click="redirectToGscSettings"
			@close-alert="() => settingsStore.dismissAlert('searchConsoleNotConnected')"
		/>

		<div v-if="searchStatisticsStore.isConnected && !settingsStore.settings.dismissedAlerts?.searchConsoleSitemapErrors">
			<core-alert-actionable
				v-if="searchStatisticsStore.sitemapsWithErrors.length > 0"
				:title="strings.thereAreSitemapsWithErrors"
				:text="strings.aioseoHasFoundSomeErrorsInSitemaps"
				:button="strings.fixSitemapErrors"
				buttonType="link"
				type="yellow"
				size="small"
				show-close
				@close-alert="() => settingsStore.dismissAlert('searchConsoleSitemapErrors')"
				@click="() => showErrorsModal = true"
			/>

			<sitemaps-with-errors-modal
				:display="showErrorsModal"
				@close="showErrorsModal = false"
			/>
		</div>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore,
	useSettingsStore,
	useOptionsStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useSearchConsole } from '@/vue/composables'
import { getParams } from '@/vue/utils/params'
import CoreAlertActionable from '@/vue/components/common/core/alert/Actionable'
import SitemapsWithErrorsModal from './SitemapsWithErrorsModal'

export default {
	setup () {
		const {
			strings,
			redirectToGscSettings
		} = useSearchConsole()

		return {
			searchStatisticsStore : useSearchStatisticsStore(),
			settingsStore         : useSettingsStore(),
			optionsStore          : useOptionsStore(),
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
				yourSiteIsConnected : this.$t.__('Your site is connected directly to Google Search Console and your sitemaps are in sync.', this.$td)
			})
		}
	},
	mounted () {
		this.showErrorsModal = !!getParams()['open-modal']
	}
}
</script>