<template>
	<core-card
		class="aioseo-export-settings"
		slug="exportSettings"
		:toggles="false"
		no-slide
		:header-text="strings.exportSettings"
	>
		<template #header-icon>
			<svg-upload />
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
		<grid-row>
			<grid-column
				class="export-all"
			>
				<base-checkbox
					size="medium"
					v-model="options.all"
					:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
				>
					{{ strings.exportAllSettings }}
				</base-checkbox>
			</grid-column>
			<grid-column
				v-for="(setting, index) in toolsSettings"
				:key="index"
				sm="6"
			>
				<base-checkbox
					v-if="!options.all"
					size="medium"
					v-model="options[setting.value]"
					:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
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
		<base-button
			type="blue"
			size="medium"
			class="import"
			@click="processExportSettings"
			:disabled="!canExport(options)"
			:loading="loadingSettings"
		>
			{{ strings.exportSettings }}
		</base-button>
	</core-card>
</template>

<script>
import {
	useLicenseStore,
	useRootStore,
	useToolsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { DateTime } from 'luxon'
import { useToolsSettings } from '@/vue/composables/ToolsSettings'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreCard from '@/vue/components/common/core/Card'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgUpload from '@/vue/components/common/svg/Upload'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { toolsSettings } = useToolsSettings()

		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore(),
			toolsSettings,
			toolsStore   : useToolsStore()
		}
	},
	components : {
		BaseCheckbox,
		CoreCard,
		CoreNetworkSiteSelector,
		GridColumn,
		GridRow,
		SvgUpload
	},
	data () {
		return {
			allowed,
			site            : null,
			options         : {},
			loadingSettings : false,
			strings         : {
				selectSite        : __('Select Site', td),
				exportSettings    : __('Export Settings', td),
				exportAllSettings : __('Export All Settings', td),
				errorExport       : __('We had a problem when exporting data.', td)
			}
		}
	},
	methods : {
		canExport (options) {
			if (this.rootStore.aioseo.data.isNetworkAdmin && !this.site) {
				return false
			}

			const passed = []
			Object.keys(options).forEach(key => {
				passed.push(options[key])
			})
			return passed.some(a => a)
		},
		processExportSettings () {
			const settings = []
			if (this.options.all) {
				if (this.rootStore.isPro) {
					// Includes the license key.
					settings.push('general')
				}
				settings.push('internal')
				this.toolsSettings
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

			const site = this.site ? `${this.site.domain}${this.site.path.replace('/', '-')}` : ''
			this.loadingSettings = true
			this.toolsStore.exportSettings({
				settings,
				siteId : this.site ? this.site.blog_id : null
			})
				.then(response => {
					this.loadingSettings = false
					this.options         = {}
					this.prepareAfterResponse('settings', response.body.settings, site, 'json')
				})
		},
		prepareAfterResponse (contentExporting, bodyContent, site, typeFile) {
			const contentType = this.getFileType(typeFile)
			const content     = ('json' === typeFile) ? JSON.stringify(bodyContent) : bodyContent
			const blob        = new Blob([ content ], { type: contentType })

			const link       = document.createElement('a')
			link.href        = URL.createObjectURL(blob)
			link.download    = `aioseo-export-${contentExporting}-${site}${DateTime.now().toFormat('yyyy-MM-dd')}.` + typeFile
			link.click()
			URL.revokeObjectURL(link.href)
		},
		getFileType (type) {
			if ('csv' === type) {
				return 'text/csv'
			}
			if ('json' === type) {
				return 'application/json'
			}
			return false
		}
	}
}
</script>

<style lang="scss">
.aioseo-export-settings {
	flex: 1;
	font-size: 16px;

	.select-site {
		font-size: 16px;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.aioseo-button.import {
		margin-top: 16px;
	}

	.export-all {
		padding-bottom: 12px;
		font-weight: $font-bold;
		border-bottom: 1px solid $border;
	}
}
</style>