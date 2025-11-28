<template>
	<div class="aioseo-tools-database-tools">
		<core-card
			slug="databaseTools"
			:header-text="strings.resetRestoreSettings"
		>
			<core-settings-row
				v-if="rootStore.aioseo.data.isNetworkAdmin"
				:name="strings.selectSite"
			>
				<template #content>
					<base-select
						size="medium"
						v-model="site"
						:options="sites"
					/>
				</template>
			</core-settings-row>

			<core-reset-settings
				:site="selectedSite"
			/>
		</core-card>
	</div>
</template>

<script>
import {
	useOptionsStore,
	useRootStore,
	useRedirectsStore,
	useToolsStore
} from '@/vue/stores'

import { useNetwork } from '@/vue/composables/Network'
import license from '@/vue/utils/license'

import CoreCard from '@/vue/components/common/core/Card'
import CoreResetSettings from '@/vue/components/common/core/ResetSettings'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getSites,
			getUniqueSiteId
		} = useNetwork()

		return {
			license,
			optionsStore   : useOptionsStore(),
			rootStore      : useRootStore(),
			redirectsStore : useRedirectsStore(),
			toolsStore     : useToolsStore(),
			getSites,
			getUniqueSiteId
		}
	},
	components : {
		CoreCard,
		CoreResetSettings,
		CoreSettingsRow
	},
	data () {
		return {
			site         : null,
			selectedSite : null,
			strings      : {
				selectSite           : __('Select Site', td),
				resetRestoreSettings : __('Reset / Restore Settings', td)
			}
		}
	},
	watch : {
		site (newVal) {
			this.selectedSite = this.rootStore.aioseo.data.network.sites.sites.find(s => this.getUniqueSiteId(s) === newVal.value)
		}
	},
	computed : {
		canReset () {
			const passed = []
			Object.keys(this.options).forEach(key => {
				passed.push(this.options[key])
			})
			return !passed.some(a => a)
		},
		sites () {
			return this.getSites
				.filter(s => !s.parentDomain)
				.map(s => {
					return {
						value : this.getUniqueSiteId(s),
						label : `${s.domain}${s.path}`
					}
				})
		}
	}
}
</script>