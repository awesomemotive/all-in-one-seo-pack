import { defineStore } from 'pinia'

export const useSensitiveOptionsStore = defineStore('SensitiveOptionsStore', {
	state : () => ({
		hasLicenseKey                   : false,
		hasNetworkLicenseKey            : false,
		hasConnectLicenseKey            : false,
		hasAiAccessToken                : false,
		hasSemrushAccessToken           : false,
		hasSemrushRefreshToken          : false,
		hasSearchStatisticsProfileKey   : false,
		hasSearchStatisticsProfileToken : false,
		hasSearchStatisticsTrustToken   : false,
		hasSiteAnalysisConnectToken     : false,
		hasConnectKey                   : false,
		hasConnectToken                 : false,
		hasSeoAlertsSlackWebhookUrl     : false
	})
})