<template>
	<grid-column class="tool-settings tool-settings-index-now">
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
					<div class="aioseo-index-now-key">
						<base-input
							size="small"
							v-model="indexNow.apiKey"
							:disabled="!isIndexNowEnabled"
						/>

						<base-button
							v-if="isIndexNowEnabled"
							type="blue"
							size="medium"
							@click="regenerateApiKey"
							:loading="regeneratingApiKey"
						>
							{{ strings.regenerateApiKey }}
						</base-button>
					</div>

					<p
						v-if="isIndexNowEnabled"
						class="aioseo-description"
						v-html="setting.description"
					/>

					<core-alert
						class="inline-upsell"
						:class="{ 'is-pro': !isIndexNowLite && (isIndexNowActivate || isIndexNowUpdate) }"
						v-if="isIndexNowLite || isIndexNowActivate || isIndexNowUpdate"
						type="blue"
					>
						<div
							v-if="isIndexNowLite"
							v-html="strings.upsell"
						/>

						<template
							v-if="isIndexNowActivate"
						>
							<div>
								{{ strings.activateDescription }}
							</div>

							<base-button
								:loading="installingPlugin"
								type="blue"
								size="medium"
								@click="activateIndexNow"
							>
								{{ strings.activate }}
							</base-button>
						</template>

						<template
							v-if="isIndexNowUpdate"
						>
							<div>
								{{ strings.updateRequired }}
							</div>

							<base-button
								:loading="installingPlugin"
								type="blue"
								size="medium"
								@click="updateIndexNow"
							>
								{{ strings.update }}
							</base-button>
						</template>
					</core-alert>
				</template>
			</core-settings-row>
		</div>
	</grid-column>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import {
	useAddonsStore,
	useIndexNowStore,
	useLicenseStore,
	usePluginsStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import links from '@/vue/utils/links'
import { merge } from 'lodash-es'

import { useWebmasterTools } from '@/vue/composables/WebmasterTools'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const { strings : composableStrings } = useWebmasterTools()

const addonsStore = useAddonsStore()
const indexNowStore = useIndexNowStore()
const licenseStore = useLicenseStore()
const pluginsStore = usePluginsStore()

const indexNow = ref({
	apiKey : null
})
const indexNowFailed     = ref(false)
const regeneratingApiKey = ref(false)
const installingPlugin   = ref(false)

const strings = merge(composableStrings, {
	upsell : sprintf(
		// Translators: 1 - "PRO", 2 - "Learn more".
		__('IndexNow is a %1$s feature. %2$s', td),
		'PRO',
		links.getUpsellLink('webmaster-tools', GLOBAL_STRINGS.learnMore, 'index-now', true)
	),
	activate            : __('Activate IndexNow', td),
	activateDescription : __('The IndexNow addon is required to use this feature.', td),
	updateRequired      : sprintf(
		// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Pro, 3 - Version Number ("1.0.0"), 4 - Addon name ("Redirects"), 5 - Version Number ("1.0.0").
		__('The IndexNow addon requires an update. %1$s %2$s requires a minimum version of %3$s for the %4$s addon. You currently have %5$s installed.', td),
		import.meta.env.VITE_SHORT_NAME,
		'Pro',
		addons.getAddon('aioseo-index-now').minimumVersion,
		'IndexNow',
		addons.getAddon('aioseo-index-now').installedVersion
	),
	update           : __('Update IndexNow', td),
	regenerateApiKey : __('Regenerate API Key', td)
})

defineProps({
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

const activateIndexNow = () => {
	indexNowFailed.value   = false
	installingPlugin.value = true
	const addon = addons.getAddon('aioseo-index-now')
	pluginsStore.installPlugins([ { plugin: addon.basename } ])
		.then(response => {
			if (response.body.failed.length) {
				installingPlugin.value = false
				indexNowFailed.value   = true
				return
			}

			indexNowStore.getApiKey()
				.then(apiKey => {
					indexNow.value.apiKey   = apiKey
					installingPlugin.value  = false
					addon.hasMinimumVersion = true
					addon.isActive          = true
					addonsStore.updateAddon(addon)
				})
		})
		.catch(error => {
			console.error(error)
		})
}

const updateIndexNow = () => {
	indexNowFailed.value   = false
	installingPlugin.value = true
	const addon = addons.getAddon('aioseo-index-now')
	pluginsStore.upgradePlugins([ { plugin: addon.sku } ])
		.then(response => {
			installingPlugin.value = false
			if (response.body.failed.length) {
				indexNowFailed.value = true
				return
			}

			const updatedAddon      = response.body.completed[addon.sku]
			addon.hasMinimumVersion = true
			addon.isActive          = true
			addon.installedVersion  = updatedAddon.installedVersion
			addonsStore.updateAddon(addon)
		})
		.catch(error => {
			console.error(error)
		})
}

const regenerateApiKey = () => {
	regeneratingApiKey.value = true
	indexNowStore.generateApiKey()
		.then(apiKey => {
			indexNow.value.apiKey    = apiKey
			regeneratingApiKey.value = false
		})
}

watch(() => indexNow.value.apiKey, (newValue) => {
	indexNowStore.options.indexNow.apiKey = newValue
})

const isIndexNowEnabled = computed(() => {
	return !licenseStore.isUnlicensed &&
		addons.isActive('aioseo-index-now') &&
		!addons.requiresUpgrade('aioseo-index-now') &&
		addons.hasMinimumVersion('aioseo-index-now')
})

const isIndexNowLite = computed(() => {
	return licenseStore.isUnlicensed || addons.requiresUpgrade('aioseo-index-now')
})

const isIndexNowActivate = computed(() => {
	return !licenseStore.isUnlicensed &&
		!addons.isActive('aioseo-index-now') &&
		addons.canActivate('aioseo-index-now') &&
		!addons.requiresUpgrade('aioseo-index-now') &&
		(
			addons.hasMinimumVersion('aioseo-index-now') ||
			!addons.isInstalled('aioseo-index-now')
		)
})

const isIndexNowUpdate = computed(() => {
	return !licenseStore.isUnlicensed &&
		addons.isInstalled('aioseo-index-now') &&
		!addons.requiresUpgrade('aioseo-index-now') &&
		!addons.hasMinimumVersion('aioseo-index-now')
})

onMounted(() => {
	indexNow.value.apiKey = indexNowStore.options.indexNow.apiKey
})
</script>

<style lang="scss">
.tool-settings-index-now {
	.inline-upsell {
		display: inline-flex;
		margin-top: 12px;
		gap: 10px;

		&.is-pro {
			min-width: 100%;
			max-width: 100%;
		}
	}

	.aioseo-index-now-key {
		display: flex;
	}
}
</style>