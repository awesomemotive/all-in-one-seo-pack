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

		<div
			class="export-settings"
			:class="{'aioseo-settings-row' : canExportPostOptions}"
		>
			<grid-row>
				<grid-column
					class="export-all"
				>
					<base-checkbox
						size="medium"
						v-model="options.all"
						:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
					>
						{{ strings.allSettings }}
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
		</div>

		<div
			v-if="canExportPostOptions"
			class="export-post-types"
		>
			<grid-row>
				<grid-column
					class="export-all"
				>
					<base-checkbox
						size="medium"
						v-model="postOptions.all"
						:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
					>
						{{ strings.allPostTypes }}
					</base-checkbox>
				</grid-column>
				<grid-column
					v-for="(postType, index) in rootStore.aioseo.postData.postTypes"
					:key="index"
					sm="6"
				>
					<base-checkbox
						v-if="!postOptions.all"
						size="medium"
						v-model="postOptions[postType.name]"
						:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
					>
						{{ postType.label }}
					</base-checkbox>

					<base-checkbox
						v-if="'all' !== postType.name && postOptions.all"
						size="medium"
						:modelValue="true"
						disabled
					>
						{{ postType.label }}
					</base-checkbox>
				</grid-column>
			</grid-row>
		</div>

		<base-button
			type="blue"
			size="medium"
			class="import"
			@click="processExportSettings"
			:disabled="!canExport"
			:loading="loading"
		>
			{{ strings.exportSettings }}
		</base-button>
	</core-card>
</template>

<script>
import {
	useRootStore,
	useToolsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { DateTime } from 'luxon'
import { ToolsSettings } from '@/vue/mixins'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreCard from '@/vue/components/common/core/Card'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgUpload from '@/vue/components/common/svg/Upload'
export default {
	setup () {
		return {
			rootStore  : useRootStore(),
			toolsStore : useToolsStore()
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
	mixins : [ ToolsSettings ],
	data () {
		return {
			allowed,
			site        : null,
			options     : {},
			postOptions : {},
			loading     : false,
			strings     : {
				selectSite     : this.$t.__('Select Site', this.$td),
				exportSettings : this.$t.__('Export Settings', this.$td),
				allSettings    : this.$t.__('Export All Settings', this.$td),
				allPostTypes   : this.$t.__('Export All Post Types', this.$td)
			}
		}
	},
	computed : {
		canExport () {
			if (this.rootStore.aioseo.data.isNetworkAdmin && !this.site) {
				return false
			}

			const passed = []
			Object.keys(this.options).forEach(key => {
				passed.push(this.options[key])
			})

			Object.keys(this.postOptions).forEach(key => {
				passed.push(this.postOptions[key])
			})
			return passed.some(a => a)
		},
		canExportPostOptions () {
			return [
				'aioseo_page_general_settings',
				'aioseo_page_advanced_settings',
				'aioseo_page_schema_settings',
				'aioseo_page_social_settings',
				'aioseo_page_local_seo_settings'
			].some(capability => allowed(capability))
		}
	},
	methods : {
		processExportSettings () {
			const settings = []
			if (this.options.all) {
				if (this.$isPro) {
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

			const postOptions = []
			if (this.postOptions.all) {
				this.rootStore.aioseo.postData.postTypes
					.forEach(postType => {
						postOptions.push(postType.name)
					})
			} else {
				Object.keys(this.postOptions).forEach(key => {
					if (this.postOptions[key]) {
						postOptions.push(key)
					}
				})
			}

			const site = this.site ? `${this.site.domain}${this.site.path.replace('/', '-')}` : ''

			this.loading = true
			this.toolsStore.exportSettings({
				settings,
				postOptions,
				siteId : this.site ? this.site.blog_id : null
			})
				.then(response => {
					this.loading     = false
					this.options     = {}
					this.postOptions = {}
					const blob       = new Blob([ JSON.stringify(response.body.settings) ], { type: 'application/json' })
					const link       = document.createElement('a')
					link.href        = URL.createObjectURL(blob)
					link.download    = `aioseo-export-settings-${site}${DateTime.now().toFormat('yyyy-MM-dd')}.json`
					link.click()
					URL.revokeObjectURL(link.href)
				})
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
		margin-top: 24px;
	}

	.export-settings {
		border-bottom-width: 0;

		.export-all {
			padding-bottom: 12px;
			font-weight: $font-bold;
			border-bottom: 1px solid $border;
		}
	}

	.export-post-types {
		border-bottom-width: 0;

		.export-all {
			padding-bottom: 12px;
			font-weight: $font-bold;
			border-bottom: 1px solid $border;
		}
	}
}
</style>