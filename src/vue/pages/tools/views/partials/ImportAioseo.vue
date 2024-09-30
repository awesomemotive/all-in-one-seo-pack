<template>
	<core-card
		class="aioseo-import-aioseo"
		slug="importAioseoSettings"
		:toggles="false"
		no-slide
		:header-text="strings.importRestoreAioseoSettings"
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

		<core-alert
			v-if="uploadError"
			type="red"
			class="import-alert"
		>
			{{ uploadError }}
		</core-alert>

		<core-alert
			v-if="filename && filename.endsWith('.ini')"
			type="yellow"
			class="import-alert"
		>
			{{strings.v3ImportWarning}}
		</core-alert>

		<core-alert
			v-if="uploadSuccess"
			type="green"
			class="import-alert"
		>
			{{ strings.fileUploadedSuccessfully }}
		</core-alert>

		<div class="file-upload">
			<base-input
				v-model="filename"
				size="medium"
				@focus="triggerFileUpload"
				:placeholder="strings.fileUploadPlaceholder"
				:class="{ 'aioseo-error': uploadError }"
				:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
			/>

			<base-button
				type="black"
				size="medium"
				@click="triggerFileUpload"
				:disabled="rootStore.aioseo.data.isNetworkAdmin && !site"
			>
				{{ strings.chooseAFile }}
			</base-button>
		</div>

		<base-input
			v-model="inputFile"
			type="file"
			@click="reset"
			@change="handleFileUpload"
			ref="file"
		/>

		<div class="aioseo-description">
			{{ strings.fileUploadDescription }}
		</div>

		<base-button
			type="blue"
			size="medium"
			class="import"
			@click="submitFile"
			:disabled="!file || !importValidated"
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

import { debounce } from '@/vue/utils/debounce'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
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
		CoreAlert,
		CoreCard,
		CoreNetworkSiteSelector,
		SvgDownload
	},
	data () {
		return {
			site          : null,
			inputFile     : null,
			filename      : null,
			file          : null,
			uploadError   : false,
			uploadSuccess : false,
			loading       : false,
			strings       : {
				selectSite                  : __('Select Site', td),
				importRestoreAioseoSettings : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('Import / Restore %1$s Settings or Content', td),
					import.meta.env.VITE_SHORT_NAME
				),
				fileUploadPlaceholder    : __('Import from a JSON, CSV or INI file...', td),
				chooseAFile              : __('Choose a File', td),
				fileUploadDescription    : __('Imported will overwrite existing data and will not be merged.', td),
				import                   : __('Import', td),
				jsonFileTypeRequired     : __('A JSON, CSV or INI file is required to import.', td),
				fileUploadedSuccessfully : __('Success! Your settings have been imported.', td),
				fileUploadFailed         : __('There was an error importing your file. Please make sure you are uploading the correct file or it is in the proper format.', td),
				v3ImportWarning          : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('Please note that if you are importing post/term meta from %1$s v3.7.1 or below, this will only be successful if the post/term IDs of this site are identical to those of the source site.', td),
					import.meta.env.VITE_SHORT_NAME
				)
			}
		}
	},
	watch : {
		uploadError (uploadError) {
			if (!uploadError) {
				return
			}
			debounce(() => {
				this.uploadError = false
			}, 5000)
		}
	},
	computed : {
		importValidated () {
			if (this.rootStore.aioseo.data.isNetworkAdmin && !this.site) {
				return false
			}

			if (!this.file.type || !this.file.name) {
				return false
			}

			if ('application/json' !== this.file.type &&
				'text/csv' !== this.file.type &&
				!this.file.name.endsWith('.ini')) {
				return false
			}

			return true
		}
	},
	methods : {
		reset () {
			this.uploadError = false
			this.filename    = null
			this.file        = null
			this.inputFile   = null
		},
		triggerFileUpload () {
			this.reset()
			this.$refs.file.$el.querySelector('input').focus()
			this.$refs.file.$el.querySelector('input').click()
		},
		submitFile () {
			this.loading = true
			this.toolsStore.uploadFile({
				file     : this.file,
				filename : this.filename,
				siteId   : this.site ? this.site.blog_id : null
			})
				.then(response => {
					this.loading = false
					if (!response.body.success) {
						this.uploadError = this.strings.fileUploadFailed
						this.uploadSuccess = false
						return
					}

					this.reset()
					this.uploadSuccess = true

					debounce(() => {
						this.uploadSuccess = false
					}, 5000)
				})
				.catch(() => {
					this.uploadError = this.strings.fileUploadFailed
					this.loading     = false
				})
		},
		handleFileUpload () {
			this.reset()
			this.file = this.$refs.file.$el.querySelector('input').files[0]
			if (this.file) {
				this.filename = this.file.name

				if ('application/json' !== this.file.type &&
					'text/csv' !== this.file.type &&
					!this.file.name.endsWith('.ini')) {
					this.uploadError = this.strings.jsonFileTypeRequired
				}
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-import-aioseo {
	.import-alert {
		margin-bottom: 20px;
	}

	.select-site {
		font-size: 16px;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.file-upload {
		display: flex;
		gap: 8px;

		> .aioseo-input {
			margin-right: 10px;
		}
	}

	.aioseo-button.import {
		margin-top: 20px;
	}
}
</style>