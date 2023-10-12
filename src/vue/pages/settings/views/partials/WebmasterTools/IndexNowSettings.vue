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

<script>
import {
	useAddonsStore,
	useIndexNowStore,
	useLicenseStore,
	usePluginsStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { merge } from 'lodash-es'
import { useWebmasterTools } from '@/vue/composables'
import { WebmasterTools } from '@/vue/pages/settings/mixins'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import GridColumn from '@/vue/components/common/grid/Column'
export default {
	setup () {
		const { strings } = useWebmasterTools()

		return {
			addonsStore       : useAddonsStore(),
			indexNowStore     : useIndexNowStore(),
			licenseStore      : useLicenseStore(),
			pluginsStore      : usePluginsStore(),
			composableStrings : strings
		}
	},
	components : {
		CoreAlert,
		CoreSettingsRow,
		GridColumn
	},
	mixins : [ WebmasterTools ],
	data () {
		return {
			indexNow : {
				apiKey : null
			},
			indexNowFailed     : false,
			regeneratingApiKey : false,
			installingPlugin   : false,
			strings            : merge(this.composableStrings, {
				upsell : this.$t.sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					this.$t.__('IndexNow is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('webmaster-tools', this.$constants.GLOBAL_STRINGS.learnMore, 'index-now', true)
				),
				activate            : this.$t.__('Activate IndexNow', this.$td),
				activateDescription : this.$t.__('The IndexNow addon is required to use this feature.', this.$td),
				updateRequired      : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - Pro, 3 - Version Number ("1.0.0"), 4 - Addon name ("Redirects"), 5 - Version Number ("1.0.0").
					this.$t.__('The IndexNow addon requires an update. %1$s %2$s requires a minimum version of %3$s for the %4$s addon. You currently have %5$s installed.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro',
					addons.getAddon('aioseo-index-now').minimumVersion,
					'IndexNow',
					addons.getAddon('aioseo-index-now').installedVersion
				),
				update           : this.$t.__('Update IndexNow', this.$td),
				regenerateApiKey : this.$t.__('Regenerate API Key', this.$td)
			})
		}
	},
	methods : {
		activateIndexNow () {
			this.indexNowFailed   = false
			this.installingPlugin = true
			const addon = addons.getAddon('aioseo-index-now')
			this.pluginsStore.installPlugins([ { plugin: addon.basename } ])
				.then(response => {
					if (response.body.failed.length) {
						this.installingPlugin = false
						this.indexNowFailed   = true
						return
					}

					this.indexNowStore.getApiKey()
						.then(apiKey => {
							this.indexNow.apiKey    = apiKey
							this.installingPlugin   = false
							addon.hasMinimumVersion = true
							addon.isActive          = true
							this.addonsStore.updateAddon(addon)
						})
				})
				.catch(error => {
					console.error(error)
				})
		},
		updateIndexNow () {
			this.indexNowFailed   = false
			this.installingPlugin = true
			const addon = addons.getAddon('aioseo-index-now')
			this.pluginsStore.upgradePlugins([ { plugin: addon.sku } ])
				.then(response => {
					this.installingPlugin = false
					if (response.body.failed.length) {
						this.indexNowFailed = true
						return
					}

					const updatedAddon      = response.body.completed[addon.sku]
					addon.hasMinimumVersion = true
					addon.isActive          = true
					addon.installedVersion  = updatedAddon.installedVersion
					this.updateAddon(addon)
				})
				.catch(error => {
					console.error(error)
				})
		},
		regenerateApiKey () {
			this.regeneratingApiKey = true
			this.indexNowStore.generateApiKey()
				.then(apiKey => {
					this.indexNow.apiKey    = apiKey
					this.regeneratingApiKey = false
				})
		}
	},
	watch : {
		'indexNow.apiKey' (newValue) {
			this.indexNowStore.options.indexNow.apiKey = newValue
		}
	},
	computed : {
		isIndexNowEnabled () {
			return !this.licenseStore.isUnlicensed &&
				addons.isActive('aioseo-index-now') &&
				!addons.requiresUpgrade('aioseo-index-now') &&
				addons.hasMinimumVersion('aioseo-index-now')
		},
		isIndexNowLite () {
			return this.licenseStore.isUnlicensed || addons.requiresUpgrade('aioseo-index-now')
		},
		isIndexNowActivate () {
			return !this.licenseStore.isUnlicensed &&
				!addons.isActive('aioseo-index-now') &&
				addons.canActivate('aioseo-index-now') &&
				!addons.requiresUpgrade('aioseo-index-now') &&
				(
					addons.hasMinimumVersion('aioseo-index-now') ||
					!addons.isInstalled('aioseo-index-now')
				)
		},
		isIndexNowUpdate () {
			return !this.licenseStore.isUnlicensed &&
				addons.isInstalled('aioseo-index-now') &&
				!addons.requiresUpgrade('aioseo-index-now') &&
				!addons.hasMinimumVersion('aioseo-index-now')
		}
	},
	mounted () {
		this.indexNow.apiKey = this.indexNowStore.options.indexNow.apiKey
	}
}
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