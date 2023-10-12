<template>
	<grid-column class="tool-settings tool-settings-microsoft-clarity">
		<div
			v-for="(setting, index) in tool.settings"
			:key="index"
		>
			<core-settings-row
				noHorizontalMargin
				align-small
			>
				<template #name>
					{{ setting.label }}
				</template>

				<template #content>
					<div class="d-flex">
						<base-input
							size="small"
							@blur="maybeUpdateId(setting.option)"
							v-model="optionsStore.options.webmasterTools[setting.option]"
						/>
					</div>

					<p
						class="aioseo-description"
						v-html="setting.description"
					/>

					<div
						class="aioseo-description"
						v-if="isConnected"
					>
						<a target="_blank" :href="clarityDashboardUrl(optionsStore.options.webmasterTools[setting.option])">{{ strings.dashboard }}</a> |
						<a target="_blank" :href="claritySettingsUrl(optionsStore.options.webmasterTools[setting.option])">{{ strings.settings }}</a>
					</div>

					<br>

					<core-alert
						v-if="!isConnected"
						v-html="strings.description"
					/>

					<core-alert
						v-if="isConnected && !gaActivated && showMiPromo"
						:key="promoKey"
						type="blue"
					>
						<div v-html="strings.miPromo" />
						<br>
						<base-button
							v-if="!pluginsStore.plugins.miLite.canInstall"
							type="blue"
							size="medium"
							tag="a"
							target="_blank"
							:href="pluginsStore.plugins.miLite.wpLink"
						>
							<svg-external /> {{ strings.installMi }}
						</base-button>
						<base-button
							v-if="pluginsStore.plugins.miLite.canInstall"
							:loading="installingPlugin"
							type="blue"
							:disabled="miInstalled"
							size="medium"
							@click="installMi"
						>
							<span
								v-if="miInstalled"
							>{{ strings.miInstalled }}</span>
							<span
								v-if="!miInstalled"
							>{{ strings.installMi }}</span>
						</base-button>
					</core-alert>

					<core-alert
						v-if="isConnected && !gaActivated && !showMiPromo && miInstalled"
						type="blue"
					>
						<div v-html="strings.useMi" />
						<br>
						<base-button
							type="blue"
							size="medium"
							tag="a"
							target="_blank"
							:href="pluginsStore.plugins.miLite.adminUrl"
						>
							<svg-external /> {{ strings.manageGa }}
						</base-button>
					</core-alert>
				</template>
			</core-settings-row>
		</div>
	</grid-column>
</template>

<script>
import {
	useOptionsStore,
	usePluginsStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useWebmasterTools } from '@/vue/composables'
import { MiOrEm, WebmasterTools } from '@/vue/pages/settings/mixins'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
import SvgExternal from '@/vue/components/common/svg/External'
export default {
	setup () {
		const { strings } = useWebmasterTools()

		return {
			optionsStore      : useOptionsStore(),
			pluginsStore      : usePluginsStore(),
			composableStrings : strings
		}
	},
	components : {
		CoreAlert,
		CoreSettingsRow,
		GridColumn,
		SvgExternal
	},
	mixins : [ MiOrEm, WebmasterTools ],
	data () {
		return {
			promoKey : 1,
			strings  : merge(this.composableStrings, {
				dashboard   : this.$t.__('Dashboard', this.$td),
				settings    : this.$t.__('Settings', this.$td),
				description : this.$t.sprintf(
					// Translators: 1 - "Clarity", 2 - Opening HTML link tag, 3 - Closing HTML link tag.
					this.$t.__('If you don\'t already have a project on %1$s, create a project %2$shere%3$s.', this.$td),
					'Clarity',
					'<a target="_blank" href="https://clarity.microsoft.com/projects?snpf=1&utm_source=aioseo&utm_medium=partner&utm_campaign=growth">',
					'</a>'
				),
				useMi    : this.$t.__('Great choice! Get started with MonsterInsights today to see how people find and use your website.', this.$td),
				miPromo  : this.$t.__('Want to get the most out of Clarity? Integrate Clarity with Google Analytics using MonsterInsights today!', this.$td),
				manageGa : this.$t.__('Manage Google Analytics', this.$td)
			})
		}
	},
	watch : {
		// Reactivity is not working well with this prop, so forcing a key change resolves the issue.
		isConnected () {
			this.promoKey++
		}
	},
	methods : {
		clarityDashboardUrl (projectId) {
			return `https://clarity.microsoft.com/projects/view/${projectId}/dashboard?date=Last%203%20days`
		},
		claritySettingsUrl (projectId) {
			return `https://clarity.microsoft.com/projects/view/${projectId}/settings?date=Last%203%20days`
		}
	}
}
</script>

<style lang="scss">
.tool-settings-microsoft-clarity {
	.aioseo-alert {
		display: inline-block;
	}
}
</style>