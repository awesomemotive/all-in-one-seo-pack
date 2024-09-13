<template>
	<core-alert
		class="aioseo-input-error aioseo-search-statistics-authentication-alert"
		type="red"
		v-if="invalidAuthentication"
	>
		<strong>{{ error }}</strong>
	</core-alert>
</template>

<script>
import {
	useOptionsStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			optionsStore          : useOptionsStore(),
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreAlert
	},
	data () {
		return {
			error : __('Your connection with Google Search Console has expired or is invalid. Please check that your site is verified in Google Search Console and try to reconnect. If the problem persists, please contact our support team.', td)
		}
	},
	computed : {
		invalidAuthentication () {
			return this.searchStatisticsStore.unverifiedSite || 'object' !== typeof this.optionsStore.internalOptions.internal?.searchStatistics?.profile
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-authentication-alert {
	margin-bottom: 20px;
}
</style>