<template>
	<core-card
		id="aioseo-import-others"
		class="aioseo-import-others"
		slug="importOtherPlugins"
		:toggles="false"
		no-slide
		:header-text="strings.importSettingsFromOtherPlugins"
	>
		<template #header-icon>
			<svg-download />
		</template>

		<div
			class="aioseo-settings-row"
			v-if="rootStore.aioseo.data.isNetworkAdmin"
		>
			<div class="select-site">
				{{ strings.selectSite }}
			</div>

			<core-network-site-selector
				@selected-site="site = $event"
			/>
		</div>

		<div class="aioseo-section-description">
			{{ strings.importOthersDescription }}
		</div>

		<core-alert
			class="import-success"
			v-if="importSuccess"
			type="green"
		>
			{{ importSuccessful }}
		</core-alert>

		<core-alert
			class="import-error"
			v-if="importError"
			type="red"
		>
			{{ importErrorMessage }}
		</core-alert>

		<base-select
			size="medium"
			v-model="plugin"
			:options="plugins"
			:placeholder="strings.selectPlugin"
			:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
		>
			<template #option="{ option }">
				<div class="import-plugin-label">
					<span class="plugin-label">{{ option.label }}</span>
					<span
						v-if="option.$isDisabled"
						class="plugin-status"
					>{{ strings.notInstalled }}</span>
				</div>
			</template>
		</base-select>

		<div
			v-if="plugin"
			class="import-settings"
		>
			<grid-row
				v-if="plugin.canImport"
			>
				<grid-column>
					<base-checkbox
						size="medium"
						v-model="options.all"
					>
						{{ strings.allSettings }}
					</base-checkbox>
				</grid-column>
				<grid-column
					v-for="(setting, index) in settings"
					:key="index"
					sm="6"
				>
					<base-checkbox
						v-if="!options.all"
						size="medium"
						v-model="options[setting.value]"
					>
						{{ setting.label }}
					</base-checkbox>

					<base-checkbox
						v-if="'all' !== setting.value && options.all"
						size="medium"
						:modelValue="true"
						disabled
					>
						{{ setting.label }}
					</base-checkbox>
				</grid-column>
			</grid-row>

			<core-alert
				v-if="!plugin.canImport"
				type="red"
			>
				{{ invalidVersion(plugin) }}
			</core-alert>
		</div>

		<base-button
			type="blue"
			size="medium"
			class="import"
			@click="processImportPlugin"
			:disabled="!plugin || !canImport"
			:loading="loading"
		>
			{{ strings.import }}
		</base-button>
	</core-card>
</template>

<script>
import {
	useRootStore,
	useToolsStore
} from '@/vue/stores'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgDownload from '@/vue/components/common/svg/Download'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore  : useRootStore(),
			toolsStore : useToolsStore()
		}
	},
	components : {
		BaseCheckbox,
		CoreAlert,
		CoreCard,
		CoreNetworkSiteSelector,
		GridColumn,
		GridRow,
		SvgDownload
	},
	data () {
		return {
			site          : null,
			importSuccess : false,
			importError   : false,
			options       : {},
			plugin        : null,
			loading       : false,
			strings       : {
				selectSite                     : __('Select Site', td),
				importSettingsFromOtherPlugins : __('Import Settings From Other Plugins', td),
				importOthersDescription        : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('Choose a plugin to import SEO data directly into %1$s.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				selectPlugin : __('Select a plugin...', td),
				import       : __('Import', td),
				allSettings  : __('All Settings', td),
				notInstalled : __('not installed', td)
			}
		}
	},
	watch : {
		plugin () {
			this.importSuccess = false
			this.importError   = false
			this.options       = {}
		}
	},
	computed : {
		settings () {
			const settings = [
				{ value: 'settings', label: __('SEO Settings', td) },
				{ value: 'postMeta', label: __('Post Meta', td) }
			]

			if (this.rootStore.isPro) {
				settings.push({ value: 'termMeta', label: __('Term Meta', td) })
			}

			return settings
		},
		plugins () {
			const plugins = []
			this.rootStore.aioseo.importers.forEach(plugin => {
				plugins.push({
					value       : plugin.slug,
					label       : plugin.name,
					canImport   : plugin.canImport,
					version     : plugin.version,
					$isDisabled : !plugin.installed
				})
			})

			return plugins
		},
		canImport () {
			if (this.rootStore.aioseo.data.isNetworkAdmin && !this.site) {
				return false
			}

			const passed = []
			Object.keys(this.options).forEach(key => {
				passed.push(this.options[key])
			})
			return passed.some(a => a)
		},
		importSuccessful () {
			return sprintf(
				// Translators: 1 - The name of the plugin being imported (e.g "Yoast SEO").
				__('%1$s was successfully imported!', td),
				this.plugin.label
			)
		},
		importErrorMessage () {
			return sprintf(
				// Translators: 1 - The name of the plugin being imported (e.g "Yoast SEO").
				__('An error occurred while importing %1$s. Please try again.', td),
				this.plugin.label
			)
		}
	},
	methods : {
		processImportPlugin () {
			this.importSuccess = false
			this.importError   = false
			this.loading       = true
			const settings     = []
			if (this.options.all) {
				this.settings
					.filter(setting => 'all' !== setting.value)
					.forEach(setting => {
						settings.push(setting.value)
					})
			} else {
				Object.keys(this.options).forEach(key => {
					if (this.options[key]) {
						settings.push(key)
					}
				})
			}

			this.toolsStore.importPlugins({
				plugins : [ {
					plugin : this.plugin.value,
					settings
				} ],
				siteId : this.site ? this.site.blog_id : null
			})
				.then(() => {
					this.loading       = false
					this.importSuccess = true
					this.options       = {}
				})
				.catch(() => {
					this.loading     = false
					this.importError = true
					this.options     = {}
				})
		},
		invalidVersion (plugin) {
			return sprintf(
				// Translators: 1 - The name of the plugin (e.g. "Yoast SEO"), 2 - The version of the plugin (e.g. "10.2.3").
				__('We do not support importing from the currently installed version of %1$s (%2$s). Please upgrade to the latest version and try again.', td),
				plugin.label,
				plugin.version
			)
		}
	}
}
</script>

<style lang="scss">
.aioseo-import-others {
	font-size: 16px;

	.import-success,
	.import-error {
		margin-bottom: 20px;
	}

	.select-site {
		font-size: 16px;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.aioseo-button.import {
		margin-top: 16px;
	}

	.import-settings {
		margin-top: 20px;
	}

	.import-plugin-label {
		display: flex;

		span:first-child {
			flex: 1;
		}
	}
}
</style>