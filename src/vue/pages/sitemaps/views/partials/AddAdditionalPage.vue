<template>
	<div
		class="additional-pages-input"
		:class="{ 'in-table': inTable }"
	>
		<div
			v-if="!inTable"
			class="page-input-header"
		>
			<div class="page-url">{{ strings.pageUrl }}</div>
			<div class="page-priority">{{ strings.priority }}</div>
			<div class="page-frequency">{{ strings.frequency }}</div>
			<div class="page-last-modified">{{ strings.lastModified }}</div>
		</div>

		<div class="page-row">
			<div class="page-url">
				<base-input
					:modelValue="page.url"
					@keyup="editPage('url', $event.target.value)"
					@input="updateUrl($event.target.value)"
					size="medium"
					:placeholder="strings.placeholder"
					:class="errors.url.invalid && 'aioseo-error' || page.url && errors.url.exists && 'aioseo-warning' || page.url && 'aioseo-active' "
				>
					<template #append-icon>
						<div class="append-icon">
							<template
								v-if="!isLoading"
							>
								<svg-circle-close
									v-if="errors.url.invalid"
								/>

								<svg-circle-check
									v-if="!errors.url.invalid && !errors.url.exists && page.url"
								/>

								<svg-circle-exclamation
									v-if="errors.url.exists && page.url"
								/>
							</template>
						</div>
					</template>
				</base-input>

				<core-alert
					v-if="errors.url.invalid"
					type="red"
					size="small"
				>
					{{ strings.errors.url.invalid }}
				</core-alert>

				<core-alert
					v-if="errors.url.exists"
					type="yellow"
					size="small"
				>
					{{ strings.errors.url.exists }}
				</core-alert>
			</div>

			<div class="page-priority">
				<base-select
					size="medium"
					:modelValue="page.priority"
					@update:modelValue="editPage('priority', $event)"
					:options="PRIORITY_OPTIONS"
				/>
			</div>

			<div class="page-frequency">
				<base-select
					size="medium"
					:modelValue="page.frequency"
					@update:modelValue="editPage('frequency', $event)"
					:options="FREQUENCY_OPTIONS"
				/>
			</div>

			<div class="page-last-modified">
				<base-date-picker
					type="datetime"
					size="large"
					dateFormat="m/d/Y H:i:s"
					:defaultValue="dateStringToLocalJs(page.lastModified)"
					@change="value => editPage('lastModified', dateJsToLocal(value, 'MM/dd/yyyy HH:mm:ss'))"
					:isDisabledDate="isDisabledDate"
				/>
			</div>
		</div>

		<div class="page-input-footer">
			<div v-if="inTable">
				<base-button
					type="blue"
					size="medium"
					@click="updatePage( index )"
					:disabled="errors.url.invalid || errors.url.exists || !page.url"
				>
					{{ strings.saveChanges }}
				</base-button>

				<base-button
					type="gray"
					size="medium"
					@click="$emit('cancel', true)"
				>
					{{ strings.cancel }}
				</base-button>
			</div>

			<div v-if="!inTable">
				<base-button
					type="blue"
					size="small-table"
					@click="addPage"
					:disabled="errors.url.invalid || errors.url.exists || !page.url"
				>
					{{ strings.addPage }}
				</base-button>

				<base-button
					type="black"
					size="small-table"
					@click="showImportModal = true"
				>
					{{ strings.importFromCSV }}
				</base-button>
			</div>
		</div>

		<core-modal
			:show="showImportModal"
			@close="closeImportModal"
			:classes="[ 'aioseo-add-additional-pages-modal' ]"
		>
			<template #headerTitle>
				{{ strings.importAdditionalPages }}
			</template>

			<template #body >
				<div class="aioseo-modal-body import-additional-pages">
					<div class="alert">
						<p v-html="strings.modalDescription" />

						<a
							@click.stop="downloadSampleCSV"
						>
							{{strings.downloadSampleFile}}
						</a>
					</div>

					<img
						:src="getAssetUrl(csvFileImage)"
						:alt="strings.imgAltText"
					/>

					<core-alert
						v-if="errors.upload"
						type="red"
						class="import-error"
					>

					<svg-circle-close />
						{{ errors.upload }}
					</core-alert>

					<div class="file-upload">
						<base-input
							v-model="filename"
							size="medium"
							@focus="triggerFileUpload"
							:placeholder="strings.fileUploadPlaceholder"
							:class="{ 'aioseo-error': errors.upload }"
						/>

						<base-button
							type="black"
							size="medium"
							@click="triggerFileUpload"
						>
							{{ strings.chooseAFile }}
						</base-button>
					</div>

					<base-input
						type="file"
						:value="file"
						@update:modelValue="handleFileUpload"
						ref="file"
					/>

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
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import {
	FREQUENCY_OPTIONS,
	PRIORITY_OPTIONS
} from '@/vue/plugins/constants'
import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import {
	dateJsToLocal,
	dateStringToLocalJs
} from '@/vue/utils/date'

import { DateTime } from 'luxon'
import { __, sprintf } from '@/vue/plugins/translations'
import { getAssetUrl, isUrl, cloneObject } from '@/vue/utils/helpers'
import csvFileImage from '@/vue/assets/images/sitemap/import-from-csv.png'

import BaseDatePicker from '@/vue/components/common/base/DatePicker'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'

const td = import.meta.env.VITE_TEXTDOMAIN
const defaults = {
	page : {
		url          : null,
		priority     : { label: '0.7', value: '0.7' },
		frequency    : { label: __('weekly', import.meta.env.VITE_TEXTDOMAIN), value: 'weekly' },
		lastModified : DateTime.now().toFormat('MM/dd/yyyy')
	}
}

export default {
	emits : [ 'cancel', 'process-page-add-and-update', 'process-page-edit' ],
	setup () {
		return {
			FREQUENCY_OPTIONS,
			PRIORITY_OPTIONS,
			dateJsToLocal,
			dateStringToLocalJs,
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		BaseDatePicker,
		CoreAlert,
		CoreModal,
		SvgCircleCheck,
		SvgCircleClose,
		SvgCircleExclamation
	},
	props : {
		inTable           : Boolean,
		row               : Object,
		index             : Number,
		getPaginatedIndex : Function,
		getParsedPages    : Function,
		rowPage           : {
			type : Object,
			default () {
				return {}
			}
		},
		editedPage : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			csvFileImage,
			priorityOptionsValues  : [],
			frequencyOptionsValues : [],
			filename               : '',
			file                   : '',
			loading                : false,
			showImportModal        : false,
			page                   : cloneObject(defaults.page),
			errors                 : {
				url : {
					invalid : null,
					exists  : null
				},
				upload : false
			},
			isLoading : false,
			strings   : {
				placeholder : sprintf(
					// Translators: 1 - An example URL (e.g. https://aioseo.com/example).
					__('Enter a page URL, e.g. %1$s', td),
					`${this.rootStore.aioseo.urls.home}/new-page`
				),
				pageUrl               : __('Page URL', td),
				priority              : __('Priority', td),
				frequency             : __('Frequency', td),
				lastModified          : __('Last Modified', td),
				addPage               : __('Add Page', td),
				importFromCSV         : __('Import from CSV', td),
				saveChanges           : __('Update Page', td),
				cancel                : __('Cancel', td),
				importAdditionalPages : __('Import Additional Pages', td),
				modalDescription      : sprintf(
					// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
					__('You can import additional page URL\'s to your sitemap using a CSV file. The following 4 columns are required: %1$sPage URL, Priority, Frequency, Date Modified.%2$s', td),
					'<strong>',
					'</strong>'
				),
				downloadSampleFile    : __('Download Sample CSV File', td),
				imgAltText            : __('CSV example file', td),
				fileUploadPlaceholder : __('Import from CSV file...', td),
				chooseAFile           : __('Choose a File', td),
				import                : __('Import', td),
				csvFileTypeRequired   : __('The file that you\'ve currently selected is not a CSV file.', td),
				invalidCSV            : __('Unable to read CSV file. Please check if the file is valid and try again.', td),
				errors                : {
					url : {
						invalid : __('Please enter a valid URL.', td),
						exists  : __('URL already exists.', td)
					}
				}
			},
			sampleCSVData :
				('Page URL,Priority,Frequency,Date Modified\r\nhttps://aioseo.com/pricing/,0.0,weekly,01/30/2022')
		}
	},
	computed : {
		importValidated () {
			return 'text/csv' === this.file.type
		}
	},
	methods : {
		getAssetUrl,
		updateAdditionalPages (pages) {
			this.optionsStore.options.sitemap.general.additionalPages.pages = pages
		},
		addPage () {
			const pages = this.optionsStore.options.sitemap.general.additionalPages.pages
			pages.unshift(JSON.stringify(this.page))

			this.updateAdditionalPages(pages)
			this.page = cloneObject(defaults.page)
			this.errors.url.invalid = null
			this.$emit('process-page-add-and-update')
		},
		editPage (type, value) {
			this.page[type] = value

			if (!isUrl(this.page.url) && this.page.url) {
				this.errors.url.invalid = true
				return
			}

			if (this.pageExists(this.page.url) && !this.inTable) {
				this.errors.url.invalid = false
				this.errors.url.exists = true
				return
			}

			this.errors.url.invalid = false
			this.errors.url.exists = false

			if (this.inTable) {
				this.$emit('process-page-edit', this.page)
			}
		},
		updateUrl (value) {
			this.page.url = ''
			this.page.url = this.decodeUrl(value)
		},
		decodeUrl (url) {
			try {
				return decodeURIComponent(url)
			} catch (error) {
				// If an error occurs during decoding, do nothing
				return url
			}
		},
		updatePage (index) {
			const pages = this.optionsStore.options.sitemap.general.additionalPages.pages
			pages[this.getPaginatedIndex(index)] = JSON.stringify(this.page)

			this.updateAdditionalPages(pages)
			this.$emit('process-page-add-and-update')
			this.$emit('cancel', true)
		},
		pageExists (pageUrl) {
			return this.getParsedPages().some(({ url }) => url === pageUrl)
		},
		reset () {
			this.errors.upload = false
			this.filename      = ''
			this.file          = ''
		},
		triggerFileUpload () {
			this.reset()
			this.$refs.file.$el.querySelector('input').focus()
			this.$refs.file.$el.querySelector('input').click()
		},
		async submitFile () {
			this.loading = true

			try {
				const additionalPages = await this.parseFile()
				const pages           = this.optionsStore.options.sitemap.general.additionalPages.pages

				additionalPages.forEach(page => {
					const preparedPage = this.prepareAdditionalPage(page)

					if (preparedPage) {
						pages.unshift(JSON.stringify(preparedPage))
					}
				})

				this.updateAdditionalPages(pages)
			} catch (e) {
				this.errors.upload = this.strings.invalidCSV
			}

			this.reset()
			this.page            = cloneObject(defaults.page)
			this.showImportModal = false
			this.loading         = false
		},
		prepareAdditionalPage (page) {
			const preparedPage = cloneObject(defaults.page)

			const dateFormats = [
				'MM/dd/yyyy'
				// Add more formats if needed
			]

			page.forEach(prop => {
				try {
					if (isUrl(prop)) {
						const decodedUrl = this.decodeUrl(prop)
						if (!this.pageExists(decodedUrl)) {
							preparedPage.url = decodedUrl
							return
						}
					}
					if (this.priorityOptionsValues.includes(parseFloat(prop))) {
						preparedPage.priority.label = preparedPage.priority.value = prop
						return
					}

					if (this.frequencyOptionsValues.includes(prop.toLowerCase())) {
						preparedPage.frequency.label = preparedPage.frequency.value = prop.toLowerCase()
						return
					}
					// Check for valid date format
					dateFormats.forEach(format => {
						const dateTime = DateTime.fromFormat(prop, format)
						if (dateTime.isValid) {
							preparedPage.lastModified = prop
						}
					})
				} catch {
					// Do nothing.
					// Somehow the script terminates if Date.parse() is called on a non-date string,
					// so we need to catch the error and do nothing. Otherwise the CSV import fails on the first row with the headers.
				}
			})

			return null !== preparedPage.url ? preparedPage : false
		},
		parseFile () {
			const reader = new FileReader()
			reader.readAsText(this.file)

			return new Promise((resolve, reject) => {
				reader.onerror = () => {
					reader.abort()
					reject(new DOMException())
				}

				reader.onload = () => {
					const csvString = reader.result
					const rows      = csvString.split(/[\r\n]/).map(row => row.split(','))

					resolve(rows)
				}
			})
		},
		handleFileUpload () {
			this.file = this.$refs.file.$el.querySelector('input').files[0]
			if (this.file) {
				this.filename = this.file.name

				if ('text/csv' !== this.file.type) {
					this.errors.upload = this.strings.csvFileTypeRequired
				}
			}
		},
		closeImportModal () {
			this.reset()
			this.showImportModal = false
		},
		isDisabledDate (date) {
			return date > DateTime.now()
		},
		downloadSampleCSV () {
			this.showDownloadSample = false
			const data = new Blob([ this.sampleCSVData ], { type: 'text/csv' })
			const url = URL.createObjectURL(data)
			const link = document.createElement('a')
			link.href = url
			link.download = 'aioseo-additional-pages-sample.csv'
			link.click()
		}
	},
	mounted () {
		this.priorityOptionsValues  = PRIORITY_OPTIONS.map(o => o.value)
		this.frequencyOptionsValues = FREQUENCY_OPTIONS.map(o => o.value)

		if (!this.inTable) return
		this.page = this.rowPage !== this.editedPage ? this.rowPage : this.editedPage
	}
}
</script>

<style lang="scss">
.aioseo-additional-pages {
	.additional-pages-input {
		border: 1px solid $input-border;
		border-radius: 3px;
		margin-bottom: var(--aioseo-gutter);

		.append-icon {
			width: 60px;
			justify-content: flex-end;

			svg {
				max-width: 16px;
				margin-right: 5px;

				&.aioseo-circle-check {
					color: $green;
				}

				&.aioseo-circle-close {
					color: $red;
				}
			}
		}

		.page-input-header {
			height: 50px;
			display: flex;
			font-size: 14px;
			color: $black2;
			padding: 16px;
			align-items: center;
			border-bottom: 1px solid $input-border;

			> div {
				flex: 1 0 auto;
			}
		}

		.page-priority,
		.page-frequency {
			max-width: 160px;
		}

		.page-last-modified {
			max-width: 200px;
		}

		.page-row {
			min-height: 70px;
			display: flex;
			padding: 16px;
			background-color: $box-background;

			> div {
				flex: 1 0 auto;
				padding-right: 16px;

				&:last-child {
					padding-right: 0;
				}
			}
		}

		.page-input-footer {
			border-top: 1px solid $input-border;
			padding: 9px 16px;

			button {
				margin-right: 16px;
			}
		}
	}

	.in-table {
		padding:0 24px;
		border: none;

		.page-row {
			background-color: transparent;
			padding: 0;
			min-height: auto;
			margin-bottom: 16px;
			height: 40px;
		}

		.page-input-footer {
			border-top: none;
			text-align: end;
			padding: 0;

			button {
				margin-right: 0;
				margin-left: 16px;
			}
		}
	}

	.aioseo-alert {
		margin-top: 10px;
	}
}

.aioseo-add-additional-pages-modal {
	.aioseo-modal-body.delete {
		margin: 20px 0 50px 0;
	}

	.aioseo-modal-body.import-additional-pages {
		padding: 24px;
		align-items: flex-start;

		> .aioseo-button {
			margin-top: 0;
		}

		> .import-error {
			margin-top: 24px;
			margin-bottom: 0;
		}

		.alert {
			padding: 12px;
			background-color: $yellow;
			border: 1px solid $orange;
			border-radius: 4px;
			margin-bottom: 12px;
			font-size: 14px;

			p {
				margin: 0 0 20px;
			}

			a {
				cursor: pointer;
			}
		}
	}

	.aioseo-alert.medium {
		padding: 7px 16px;
	}

	.import-error {
		margin-bottom: 20px;
		display: inline-flex;
		align-items: center;
		width: 100%;

		> svg {
			margin-inline-end: 10px;
			height: 26px;
			width: 26px;
			filter: invert(28%) sepia(88%) saturate(2504%) hue-rotate(332deg) brightness(88%) contrast(100%);
		}
	}

	.file-upload {
		display: flex;
		margin: 24px 0;
		align-items: baseline;
		width: 100%;
		gap: 8px;

		> .aioseo-input {
			margin-right: 10px;

		}

		button {
			margin-top: 0 !important
		}
	}

	.modal-header {
		border: none;

		button.close {
			top:23px;
			right:20px;
			padding: 0
		}

		svg.aioseo-close {
			filter: brightness(0) saturate(100%) invert(66%) sepia(10%) saturate(292%) hue-rotate(190deg) brightness(85%) contrast(84%);
		}
	}

	.modal-body {
		max-height: 100%;

		img {
			width: 100%;
			border-radius: 5px;
		}
	}
}
</style>