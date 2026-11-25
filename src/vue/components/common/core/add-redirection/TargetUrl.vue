<template>
	<div
		class="aioseo-add-redirection-target-url"
		ref="redirect-target-url"
	>
		<base-input
			v-model="value"
			@keyup="searchChange"
			@focus="showResults = true"
			@update:modelValue="$emit('update:modelValue', cleanUrl(decodeUrl(value), true))"
			@blur="onBlur"
			@input="inputEventDecodeUrl($event.target.value)"
			size="medium"
			placeholder="/target-page/"
			:class="{
				'aioseo-error'  : errors.length,
				'aioseo-active' : !errors.length && !warnings.length && url,
				'aioseo-warning' : warnings.length
			}"
		>

			<template #append-icon>
				<div class="append-icon">
					<template
						v-if="!isLoading"
					>
						<svg-circle-close
							v-if="errors.length"
						/>
						<svg-circle-check
							v-if="!errors.length && !warnings.length && url"
						/>
						<svg-circle-exclamation
							v-if="warnings.length"
						/>
					</template>

					<core-loader
						v-if="isLoading"
						dark
					/>
				</div>
			</template>
		</base-input>

		<core-add-redirection-url-results
			v-if="showResults && results.length"
			:results="results"
			:url="value"
			@set-url="setUrl"
		/>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useRedirectsStore,
	useRootStore
} from '@/vue/stores'

import { useUrl } from '@/vue/composables/Url'

import BaseInput from '@/vue/components/common/base/Input'
import CoreAddRedirectionUrlResults from '@/vue/components/common/core/add-redirection/UrlResults'
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'

// Mirrors `Request::$fileExtensions` on the PHP side. Keep ccTLDs (`pl`, `md`, `py`...) out; `zip`/`mov` stay as files.
const FILE_EXTENSIONS = [
	'htm', 'html', 'shtml', 'xhtml', 'php', 'asp', 'aspx', 'jsp', 'cgi', 'pdf', 'doc', 'docx', 'xls', 'xlsx',
	'ppt', 'pptx', 'odt', 'txt', 'csv', 'rtf', 'xml', 'json', 'rss', 'css', 'js', 'mjs', 'jpg', 'jpeg',
	'png', 'gif', 'webp', 'avif', 'svg', 'ico', 'bmp', 'tif', 'tiff', 'wav', 'ogg', 'webm', 'avi', 'mov',
	'wmv', 'flv', 'zip', 'rar', 'gz', 'tar', 'exe', 'dmg', 'apk', 'woff', 'ttf', 'eot', 'otf', 'ics', 'vcf', 'yml', 'yaml', 'toml', 'ini',
	'log', 'sql', 'tsv', 'ods', 'odp', 'epub', 'heic', 'heif', 'psd', 'eps', 'mkv', 'ogv', 'oga', 'opus', 'flac', 'aac', 'wma', 'srt', 'vtt',
	'wasm', 'webmanifest'
]

// Mirrors `Request::isProtocolRelativeHost()` on the PHP side.
const isProtocolRelativeHost = rawSegment => {
	// A fully-qualified host may end with a dot (`example.com.`).
	const segment = rawSegment.replace(/\.$/, '')

	if (segment.includes(':') || 'localhost' === segment.toLowerCase() || /^\d{1,3}(\.\d{1,3}){3}$/.test(segment)) {
		return true
	}

	const labels = segment.split('.')
	if (2 > labels.length) {
		return false
	}

	const tld = labels[labels.length - 1].toLowerCase()

	return /^(?:[a-z\u0080-\uffff]{2,}|xn--[a-z0-9-]+)$/.test(tld) && !FILE_EXTENSIONS.includes(tld)
}

export default {
	setup () {
		const {
			decodeUrl
		} = useUrl()

		return {
			postEditorStore : usePostEditorStore(),
			redirectsStore  : useRedirectsStore(),
			rootStore       : useRootStore(),
			decodeUrl
		}
	},
	components : {
		BaseInput,
		CoreAddRedirectionUrlResults,
		CoreLoader,
		SvgCircleCheck,
		SvgCircleClose,
		SvgCircleExclamation
	},
	props : {
		url           : String,
		errors        : Array,
		warnings      : Array,
		disableSearch : Boolean
	},
	data () {
		return {
			showResults : false,
			isLoading   : false,
			value       : null,
			results     : [],
			searchTimer : null
		}
	},
	watch : {
		url : {
			immediate : true,
			handler () {
				// The parent echoing back what was just typed (cleaned) must not rewrite the field mid-typing,
				// or the caret jumps to the end. The field itself is cleaned on blur.
				if (this.url === this.value || this.url === this.cleanUrl(this.value, true)) {
					return
				}

				this.value = this.cleanUrl(this.url)

				// A stored `//path/` is shown cleaned, so hand the cleaned value back too or Save
				// re-submits the raw one.
				if (this.value !== this.url) {
					this.$emit('update:modelValue', this.value)
				}
			}
		}
	},
	methods : {
		// The field keeps the raw typed value until blur, so emits clean it themselves or the parent keeps the raw one.
		cleanUrl (url, typing = false) {
			if (!url) {
				return url
			}

			// `//host/path` is a valid protocol-relative URL, so the leading slashes are only
			// collapsed when the first segment can't be a host. While it is still being typed
			// (`//`, `//cdn`), nothing ends it yet, so it is left alone until blur.
			// Mirrors `Request::collapseLeadingSlashes()` on the PHP side.
			const leading          = /^\/{2,}([^/?#]*)/.exec(url)
			const pending          = typing && !!leading && leading[0].length === url.length
			const protocolRelative = !!leading && (pending || isProtocolRelativeHost(leading[1]))

			let cleaned = protocolRelative ? url.replace(/^\/+/, '') : url

			// Keep the `//` of a leading scheme (ftp://, sftp://), not just http(s)://.
			cleaned = cleaned.replace(/(^[a-z][a-z0-9+.-]*:\/|https?:\/)(\/)+|(\/)+/gi, '$1$2$3')

			if (protocolRelative) {
				cleaned = `//${cleaned}`
			}

			// Whitespace is left alone: it's encoded server-side on save. Encoding it here would
			// ping-pong against the decoded value we emit to the parent.
			return cleaned
		},
		onBlur () {
			const value = this.cleanUrl(this.value)
			if (value !== this.value) {
				this.value = value
				this.$emit('update:modelValue', value)
			}
		},
		searchChange () {
			if (this.disableSearch) {
				return
			}

			if (!this.value) {
				this.isLoading = false
				this.showResults = false
				this.results = []
				return
			}

			// Don't search if the string starts with known characters.
			if (
				this.value.startsWith('/') ||
				this.value.startsWith('http:') ||
				this.value.startsWith('https:')
			) {
				this.isLoading = false
				this.showResults = false
				this.results = []
				return
			}
			this.isLoading = true
			if (this.searchTimer) {
				clearTimeout(this.searchTimer)
			}

			this.searchTimer = setTimeout(() => {
				if (!this.value) {
					this.isLoading   = false
					this.showResults = false
					this.results     = []
					return
				}

				this.showResults = true

				this.ajaxSearch(this.value)
					.finally(() => (this.isLoading = false))
			}, 500)
		},
		async ajaxSearch (query) {
			const response = await this.redirectsStore.getPosts({ query, postId: this.postEditorStore.currentPost.id })
			this.results   = response.body.objects
		},
		setUrl (url) {
			this.showResults = false
			this.value       = this.cleanUrl(url.replace(this.rootStore.aioseo.urls.home, '', url))
			this.$emit('update:modelValue', this.value)
		},
		inputEventDecodeUrl (value) {
			this.value = ''
			this.value = this.decodeUrl(value)
		},
		documentClick (event) {
			if (!this.showResults) {
				return
			}

			const target  = event && event.target ? event.target : null
			const element = this.$refs['redirect-target-url']

			if (element && element !== target && !element.contains(target)) {
				this.showResults = false
			}
		}
	},
	mounted () {
		document.addEventListener('click', this.documentClick)

		const modalWrapper = document.querySelector('#aioseo-modal-portal .modal-wrapper')
		if (modalWrapper) {
			modalWrapper.addEventListener('click', this.documentClick)
		}

		const mainSettingsCont = document.querySelector('#main-settings-cont')
		if (mainSettingsCont) {
			mainSettingsCont.addEventListener('click', this.documentClick)
		}
	},
	beforeUnmount () {
		document.removeEventListener('click', this.documentClick)

		const modalWrapper = document.querySelector('#aioseo-modal-portal .modal-wrapper')
		if (modalWrapper) {
			modalWrapper.removeEventListener('click', this.documentClick)
		}

		const mainSettingsCont = document.querySelector('#main-settings-cont')
		if (mainSettingsCont) {
			mainSettingsCont.removeEventListener('click', this.documentClick)
		}
	}
}
</script>

<style lang="scss">
.aioseo-add-redirection-target-url {
	position: relative;
}
</style>