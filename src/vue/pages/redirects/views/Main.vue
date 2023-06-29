<template>
	<core-main
		:page-name="strings.pageName"
		:show-save-button="showSaveButton"
		:exclude-tabs="excludeTabs"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import {
	useRedirectsStore
} from '@/vue/stores'

import { RequiresActivation, RequiresUpdate } from '@/vue/mixins'
import addons from '@/vue/utils/addons'
import CoreMain from '@/vue/components/common/core/main/Index'
import FullSiteRedirect from './AIOSEO_VERSION/FullSiteRedirect'
import ImportExport from './AIOSEO_VERSION/ImportExport'
import Logs from './AIOSEO_VERSION/Logs'
import Logs404 from './AIOSEO_VERSION/Logs404'
import Redirects from './Redirects'
import Settings from './AIOSEO_VERSION/Settings'
export default {
	setup () {
		return {
			redirectsStore : useRedirectsStore()
		}
	},
	components : {
		CoreMain,
		FullSiteRedirect,
		ImportExport,
		Logs,
		Logs404,
		Redirects,
		Settings
	},
	mixins : [ RequiresActivation, RequiresUpdate ],
	data () {
		return {
			strings : {
				pageName : this.$t.__('Redirects', this.$td)
			}
		}
	},
	computed : {
		showSaveButton () {
			return 'redirects' !== this.$route.name &&
				'groups' !== this.$route.name &&
				'logs-404' !== this.$route.name &&
				'logs' !== this.$route.name &&
				'import-export' !== this.$route.name
		},
		excludeTabs () {
			const exclude = !addons.isActive('aioseo-redirects')
				? this.getExcludedActivationTabs('aioseo-redirects')
				: this.getExcludedUpdateTabs('aioseo-redirects')

			if (!this.redirectsStore.options?.logs?.log404?.enabled) {
				exclude.push('logs-404')
			}

			if (!this.redirectsStore.options?.logs?.redirects?.enabled || 'server' === this.redirectsStore.options?.main?.method) {
				exclude.push('logs')
			}

			return exclude
		}
	}
}
</script>

<style lang="scss">
.aioseo-redirects {
	position: relative;
}
</style>