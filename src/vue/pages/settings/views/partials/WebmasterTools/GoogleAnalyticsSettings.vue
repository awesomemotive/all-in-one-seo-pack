<template>
	<grid-column class="tool-settings tool-settings-google-analytics">
		<div class="mi-alert">
			<core-mi-intro
				v-if="!gaActivated"
				:prefers-em="prefersEm"
				show-button
			/>

			<template v-if="gaActivated">
				<core-alert
					class="aioseo-ga-activated"
					type="blue"
				>
					<div
						v-if="pluginsStore.plugins.miLite.activated || pluginsStore.plugins.miPro.activated"
					>
						{{ strings.miHandlesGa }}
					</div>

					<div
						v-if="pluginsStore.plugins.emLite.activated || pluginsStore.plugins.emPro.activated"
					>
						{{ strings.emHandlesGa }}
					</div>

					<br>

					<base-button
						type="blue"
						size="medium"
						tag="a"
						:href="getGaAdminUrl"
					>
						{{ strings.manageGa }}
					</base-button>
				</core-alert>
			</template>
		</div>

		<template v-if="!gaActivated && showMiPromo">
			<div
				v-for="(setting, index) in filteredSettings"
				:key="index"
			>
				<core-settings-row
					noHorizontalMargin
				>
					<template #name>
						{{ setting.label }}
					</template>

					<template #content>
						<div class="d-flex">
							<template
								v-if="!setting.pro || !licenseStore.isUnlicensed"
							>
								<base-input
									v-if="'input' === setting.type || !setting.type"
									size="small"
									v-model="optionsStore.options.deprecated.webmasterTools.googleAnalytics[setting.option]"
									:placeholder="setting.placeholder"
									:disabled="licenseStore.isUnlicensed && setting.pro"
								/>
							</template>
						</div>

						<p
							class="aioseo-description"
							v-html="setting.description"
						/>

						<br>

						<core-alert
							v-if="setting.showMiPromo && showMiPromo"
							type="blue"
						>
							<div
								v-if="prefersEm"
								v-html="emPromo"
							/>

							<div
								v-else
								v-html="miPromo"
							/>

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
								:type="miInstalled ? 'green' : 'blue'"
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
					</template>
				</core-settings-row>
			</div>
		</template>
	</grid-column>
</template>

<script setup>
import { computed } from 'vue'

import {
	useLicenseStore,
	useOptionsStore,
	usePluginsStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useMiOrEm } from '@/vue/pages/settings/composables/MiOrEm'
import { useWebmasterTools } from '@/vue/composables/WebmasterTools'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreMiIntro from '@/vue/components/common/core/MiIntro'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
import SvgExternal from '@/vue/components/common/svg/External'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const licenseStore = useLicenseStore()
const optionsStore = useOptionsStore()
const pluginsStore = usePluginsStore()

const {
	gaActivated,
	installMi,
	installingPlugin,
	miInstalled,
	prefersEm,
	showMiPromo
} = useMiOrEm()

const { strings : composableStrings } = useWebmasterTools()

const props = defineProps({
	tool : {
		type     : Object,
		required : true
	},
	isConnected : {
		type : Boolean,
		default () {
			return false
		}
	}
})

const strings = merge(composableStrings, {
	miHandlesGa : sprintf(
		// Translators: 1 - The name of one of our partner plugins.
		__('Google Analytics is now handled by %1$s.', td),
		'MonsterInsights'
	),
	emHandlesGa : sprintf(
		// Translators: 1 - The name of one of our partner plugins.
		__('Google Analytics is now handled by %1$s.', td),
		'ExactMetrics'
	),
	manageGa : __('Manage Google Analytics', td)
})

const miPromo = computed(() => {
	return sprintf(
		// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
		__('We recommend using the %1$sFree MonsterInsights%2$s plugin to get the most out of Google Analytics.', td),
		'<strong>',
		'</strong>'
	)
})

const emPromo = computed(() => {
	return sprintf(
		// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
		__('We recommend using the %1$sFree ExactMetrics%2$s plugin to get the most out of Google Analytics.', td),
		'<strong>',
		'</strong>'
	)
})

const filteredSettings = computed(() => {
	return props.tool.settings.filter(setting => shouldDisplaySetting(setting))
})

const getGaAdminUrl = computed(() => {
	let url = pluginsStore.plugins.miLite.adminUrl

	if (pluginsStore.plugins.miPro.activated) {
		url = pluginsStore.plugins.miPro.adminUrl
	}

	if (pluginsStore.plugins.emLite.activated) {
		url = pluginsStore.plugins.emLite.adminUrl
	}

	if (pluginsStore.plugins.emPro.activated) {
		url = pluginsStore.plugins.emPro.adminUrl
	}

	return url
})
const shouldDisplaySetting = (setting) => {
	return !(licenseStore.isUnlicensed && setting.pro)
}
</script>

<style lang="scss">
.tool-settings-google-analytics {
	.mi-alert {
		font-size: 16px;

		.aioseo-card {
			margin: 0;
		}

		.aioseo-ga-activated {
			display: flex;
			align-items: center;

			> div {
				flex: 1;
			}
		}
	}
}
</style>