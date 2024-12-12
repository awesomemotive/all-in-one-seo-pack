<template>
	<core-card
		v-if="canExportPostOptions"
		class="aioseo-export-content"
		slug="exportPostTypes"
		:toggles="false"
		no-slide
		:header-text="strings.exportContent"
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

		<core-main-tabs
			internal
			:key="tabsKey"
			:tabs="tabOptions"
			:active="activeTab"
			:showSaveButton="false"
			@changed="processChangeTab"
		>
		</core-main-tabs>
		<div class="tabs-content">
			<div
				v-if="'post-types' === activeTab"
			>
				<grid-row>
					<grid-column
						class="col-subtitle col-subtitle--bottom"
					>
						<base-checkbox
							size="medium"
							v-model="postOptions.all"
							:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
						>
							{{ strings.exportAllPostTypes }}
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

			<div
				v-if="canShowTaxonomies && 'taxonomies' === activeTab"
			>
				<grid-row>
					<grid-column
						class="col-subtitle col-subtitle--bottom"
					>
						<base-checkbox
							size="medium"
							v-model="taxonomiesOptions.all"
							:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
						>
							{{ strings.exportAllTaxonomies }}
						</base-checkbox>
					</grid-column>
					<grid-column
						v-for="(taxonomy, index) in rootStore.aioseo.postData.taxonomies"
						:key="index"
						sm="6"
					>
						<base-checkbox
							v-if="!taxonomiesOptions.all"
							size="medium"
							v-model="taxonomiesOptions[taxonomy.name]"
							:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
						>
							{{ taxonomy.label }}
						</base-checkbox>

						<base-checkbox
							v-if="'all' !== taxonomy.name && taxonomiesOptions.all"
							size="medium"
							:modelValue="true"
							disabled
						>
							{{ taxonomy.label }}
						</base-checkbox>
					</grid-column>
				</grid-row>
			</div>

			<cta-export-taxonomies
				v-if="!canShowTaxonomies && 'taxonomies' === activeTab"
			/>
		</div>

		<grid-row
			v-if="
				'post-types' === activeTab ||
				(canShowTaxonomies && 'taxonomies' === activeTab)
			"
		>
			<grid-column
				class="col-subtitle col-subtitle--top"
			>
				{{ strings.exportAs }}
			</grid-column>

			<grid-column>
				<grid-row>
					<base-radio
						v-for="(item, index) in exportTypes"
						:key="index"
						size="medium"
						name="type"
						:modelValue="item.value === exportType.value"
						@update:modelValue="exportType = item"
						:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
						:type="2"
					>
						{{ item.label }}
					</base-radio>
				</grid-row>
			</grid-column>
		</grid-row>

		<core-alert
			class="error-export-post-type"
			v-if="errorContent"
			type="red"
		>
			{{ errorContent }}
		</core-alert>

		<base-button
			v-if="canShowButtonExport"
			type="blue"
			size="medium"
			class="import"
			@click="processExportContent"
			:disabled="isExportContentDisabled"
			:loading="loadingContent"
		>
			{{ strings.exportContent }}
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
import { debounce } from '@/vue/utils/debounce'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import CtaExportTaxonomies from '../lite/CtaExportTaxonomies'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgUpload from '@/vue/components/common/svg/Upload'
import BaseRadio from '@/vue/components/common/base/Radio'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore(),
			toolsStore   : useToolsStore()
		}
	},
	components : {
		BaseCheckbox,
		CoreAlert,
		CoreCard,
		CoreMainTabs,
		CoreNetworkSiteSelector,
		CtaExportTaxonomies,
		GridColumn,
		GridRow,
		SvgUpload,
		BaseRadio
	},
	data () {
		return {
			allowed,
			site              : null,
			options           : {},
			postOptions       : {},
			taxonomiesOptions : {},
			loadingContent    : false,
			errorContent      : null,
			tabsKey           : 0,
			activeTab         : 'post-types',
			strings           : {
				selectSite          : __('Select Site', td),
				exportContent       : __('Export Content', td),
				exportAllPostTypes  : __('Export All Post Types', td),
				exportAllTaxonomies : __('Export All Taxonomies', td),
				exportAs            : __('Export As', td),
				errorExport         : __('We had a problem when exporting data.', td),
				errorExportEmpty    : __('We don\'t have any data to export.', td)
			},
			exportType : {
				label : __('Export as JSON', td),
				value : 'json'
			},
			exportTypes : [
				{
					label : __('JSON', td),
					value : 'json'
				},
				{
					label : __('CSV', td),
					value : 'csv'
				}
			],
			tabOptions : [
				{
					slug : 'post-types',
					name : __('Post Types', td)
				},
				{
					slug : 'taxonomies',
					name : __('Taxonomies', td)
				}
			]
		}
	},
	watch : {
		errorContent (errorContent) {
			if (!errorContent) {
				return
			}
			debounce(() => {
				this.errorContent = false
			}, 5000)
		}
	},
	computed : {
		canExportPostOptions () {
			return [
				'aioseo_page_general_settings',
				'aioseo_page_advanced_settings',
				'aioseo_page_schema_settings',
				'aioseo_page_social_settings',
				'aioseo_page_local_seo_settings'
			].some(capability => allowed(capability))
		},
		isExportContentDisabled () {
			if ('post-types' === this.activeTab) {
				return !this.canExport(this.postOptions)
			}
			if ('taxonomies' === this.activeTab) {
				return !this.canExport(this.taxonomiesOptions)
			}

			return false
		},
		canShowTaxonomies () {
			return this.rootStore.isPro && this.licenseStore.license.isActive
		},
		canShowButtonExport () {
			return 'post-types' === this.activeTab || ('taxonomies' === this.activeTab && this.canShowTaxonomies)
		}
	},
	methods : {
		processChangeTab (newTabValue) {
			this.activeTab = newTabValue
		},
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
		processExportContent () {
			let postOptions       = [],
				taxonomiesOptions = []
			if ('post-types' === this.activeTab) {
				postOptions = this.rootStore.aioseo.postData.postTypes
					.filter(postType => this.postOptions.all || this.postOptions[postType.name])
					.map(postType => postType.name)
			}

			if ('taxonomies' === this.activeTab) {
				taxonomiesOptions = this.rootStore.aioseo.postData.taxonomies
					.filter(tax => this.taxonomiesOptions.all || this.taxonomiesOptions[tax.name])
					.map(tax => tax.name)
			}

			const site = this.site ? `${this.site.domain}${this.site.path.replace('/', '-')}` : ''

			if (!this.exportType) {
				return
			}

			this.loadingContent = true
			this.errorContent   = null
			this.toolsStore.exportContent({
				postOptions,
				taxonomiesOptions,
				typeFile : this.exportType.value,
				siteId   : this.site ? this.site.blog_id : null
			}).then(response => {
				this.loadingContent    = false
				this.postOptions       = 'post-types' === this.activeTab ? {} : this.postOptions
				this.taxonomiesOptions = 'taxonomies' === this.activeTab ? {} : this.taxonomiesOptions

				if (false === response.body.success) {
					this.errorContent = this.strings.errorExport
					return
				}

				if (response.body?.postTypeData) {
					const name = 1 !== postOptions.length ? 'post-types' : postOptions[0]
					this.prepareAfterResponse(name, response.body.postTypeData, site, this.exportType.value)
					return
				}

				if (response.body?.taxonomiesData) {
					const name = 1 !== taxonomiesOptions.length ? 'taxonomies' : taxonomiesOptions[0]
					this.prepareAfterResponse(name, response.body.taxonomiesData, site, this.exportType.value)
					return
				}

				this.errorContent = this.strings.errorExportEmpty
			}).catch(error => {
				console.error('Error fetching data:', error)
			})
		},
		prepareAfterResponse (contentExporting, bodyContent, site, typeFile) {
			const contentType = this.getFileType(typeFile)
			const content     = ('json' === typeFile) ? JSON.stringify(bodyContent) : bodyContent
			const blob        = new Blob([ content ], { type: contentType })

			const link    = document.createElement('a')
			link.href     = URL.createObjectURL(blob)
			link.download = `aioseo-export-${contentExporting}-${site}${DateTime.now().toFormat('yyyy-MM-dd')}.` + typeFile
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
.aioseo-export-content {
	flex: 1;
	font-size: 16px;

	.select-site {
		font-size: 16px;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.aioseo-settings-row {
		margin-top: 20px;
		margin-bottom: 0;
	}

	.col-subtitle {
		font-weight: $font-bold;

		&--top {
			margin-top: 16px;
			padding-top: 12px;
			border-top: 1px solid $border;
		}

		&--bottom {
			padding-bottom: 12px;
			border-bottom: 1px solid $border;
		}
	}

	.aioseo-button.import {
		margin-top: 16px;
	}

	.error-export-post-type {
		margin-top: 16px;
	}

	.tabs-content {
		padding-top: 16px;
	}

	.content {
		padding-top: 0 !important;
	}
}
</style>