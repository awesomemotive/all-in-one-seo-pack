<template>
	<div
		class="aioseo-redirect-source-url"
		ref="redirect-source-url"
	>
		<base-input
			:modelValue="decodeUrl(url.url)"
			@update:modelValue="value => updateSourceUrl(decodeUrl(value))"
			@keyup="searchChange"
			@focus="showResults = true"
			:disabled="log404 || disableSource"
			size="medium"
			placeholder="/source-page/"
			:class="{
				'aioseo-error'  : url.errors.length,
				'aioseo-active' : !url.errors.length && !url.warnings.length && url.url,
				'aioseo-warning' : url.warnings.length
			}"
		>
			<template #append-icon>
				<div class="append-icon">
					<template
						v-if="!isLoading"
					>
						<svg-circle-close
							v-if="url.errors.length"
						/>
						<svg-circle-check
							v-if="!url.errors.length && !url.warnings.length && url.url"
						/>
						<svg-circle-exclamation
							v-if="url.warnings.length"
						/>
						<svg-gear
							:class="{ active: urlOptionsActive }"
							@click.native="showOptions = !showOptions"
						/>
						<svg-trash
							v-if="allowDelete"
							@click.native="$emit('remove-url')"
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
			v-if="!url.regex && showResults && results.length"
			:results="results"
			:url="url.url"
			@set-url="setUrl"
		/>

		<slot name="source-url-description" v-if="!log404" />

		<transition-slide
			:active="showOptions"
			class="source-url-options"
		>
			<base-checkbox
				size="medium"
				:modelValue="url.ignoreSlash"
				@update:modelValue="value => updateOption('ignoreSlash', value)"
			>
				{{ strings.ignoreSlash }}
			</base-checkbox>
			<base-checkbox
				size="medium"
				:modelValue="url.ignoreCase"
				@update:modelValue="value => updateOption('ignoreCase', value)"
			>
				{{ strings.ignoreCase }}
			</base-checkbox>
			<base-checkbox
				v-if="!log404 && !disableSource"
				size="medium"
				:modelValue="url.regex"
				@update:modelValue="value => updateOption('regex', value)"
			>
				{{ strings.regex }}
			</base-checkbox>
		</transition-slide>

		<transition-slide
			:active="!!url.errors.length"
		>
			<core-alert
				v-for="(error, index) in url.errors"
				:key="index"
				class="source-url-error"
				type="red"
				size="small"
				v-html="error"
			/>
		</transition-slide>

		<transition-slide
			:active="!!url.warnings.length"
		>
			<core-alert
				v-for="(warning, index) in url.warnings"
				:key="index"
				class="source-url-warning"
				type="yellow"
				size="small"
				v-html="warning"
			/>
		</transition-slide>
	</div>
</template>

<script>
import {
	useRedirectsStore,
	useRootStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'
import { sanitizeString } from '@/vue/utils/strings'
import { makeUrlRelative } from '@/vue/utils/urls'

import { useRedirect } from '@/vue/composables/redirects/Redirect'
import { useUrl } from '@/vue/composables/Url'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseInput from '@/vue/components/common/base/Input'
import CoreAddRedirectionUrlResults from '@/vue/components/common/core/add-redirection/UrlResults'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgGear from '@/vue/components/common/svg/Gear'
import SvgTrash from '@/vue/components/common/svg/Trash'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN
export default {
	emits : [ 'updated-url', 'remove-url', 'updated-option' ],
	setup () {
		const {
			validateRedirect
		} = useRedirect()

		const {
			decodeUrl
		} = useUrl()

		return {
			redirectsStore : useRedirectsStore(),
			rootStore      : useRootStore(),
			validateRedirect,
			decodeUrl
		}
	},
	components : {
		BaseCheckbox,
		BaseInput,
		CoreAddRedirectionUrlResults,
		CoreAlert,
		CoreLoader,
		SvgCircleCheck,
		SvgCircleClose,
		SvgCircleExclamation,
		SvgGear,
		SvgTrash,
		TransitionSlide
	},
	props : {
		url : {
			type : Object,
			default () {
				return {
					id          : null,
					url         : null,
					regex       : false,
					ignoreSlash : true,
					ignoreCase  : true,
					errors      : [],
					warnings    : []
				}
			}
		},
		allowDelete   : Boolean,
		targetUrl     : String,
		log404        : Boolean,
		disableSource : Boolean,
		disableSearch : Boolean
	},
	data () {
		return {
			showResults : false,
			isLoading   : false,
			showOptions : false,
			strings     : {
				ignoreSlash : __('Ignore Slash', td),
				ignoreCase  : __('Ignore Case', td),
				regex       : __('Regex', td)
			},
			results : []
		}
	},
	watch : {
		targetUrl () {
			this.updateSourceUrl(this.url.url)
		}
	},
	computed : {
		maybeRegex () {
			if (null !== this.url.url.match(/[*\\()[\]^$]/)) {
				return true
			}

			if (-1 !== this.url.url.indexOf('.?')) {
				return true
			}

			return false
		},
		iffyUrl () {
			if (!this.url.url || this.disableSource) {
				return []
			}

			const warnings = []
			if ('http' !== this.url.url.substr(0, 4) && '/' !== this.url.url.substr(0, 1) && 0 < this.url.url.length && !this.url.regex) {
				warnings.push(sprintf(
					// Translators: 1 - Adds a html tag with an option like: <code>^/</code>
					__('The source URL should probably start with a %1$s', td),
					'<code>/</code>'
				))
			}

			if (-1 !== this.url.url.indexOf('#')) {
				warnings.push(__('Anchor values are not sent to the server and cannot be redirected.', td))
			}

			if (!this.log404 && this.maybeRegex && !this.url.regex) {
				warnings.push(sprintf(
					// Translators: 1 - Adds a html tag with an option like: <code>Regex</code>
					__('Remember to enable the %1$s option if this is a regular expression.', td),
					'<code>Regex</code>'
				))
			}

			if (this.url.regex) {
				if (-1 === this.url.url.indexOf('^') && -1 === this.url.url.indexOf('$')) {
					warnings.push(sprintf(
						// Translators: 1 - Adds a html tag with an option like: <code>^</code>, 2 - Adds a html tag with an option like: <code>^</code>.
						__('To prevent a greedy regular expression you can use %1$s to anchor it to the start of the URL. For example: %2$s', td),
						'<code>^/</code>', '<code>^/' + sanitizeString(this.url.url.replace(/^\//, '')) + '</code>'
					))
				}

				if (0 < this.url.url.indexOf('^')) {
					warnings.push(sprintf(
						// Translators: 1 - Adds a html tag with an option like: <code>^</code>, 2 - Adds a html tag with an option like: <code>^</code>.
						__('The caret %1$s should be at the start. For example: %2$s', td),
						'<code>^/</code>',
						'<code>^/' + sanitizeString(this.url.url.replace('^', '').replace(/^\//, '')) + '</code>'
					))
				}

				if (0 === this.url.url.indexOf('^') && -1 === this.url.url.indexOf('^/')) {
					warnings.push(sprintf(
						// Translators: 1 - Adds a html tag with an option like: <code>^/</code>
						__('The source URL should probably start with a %1$s', td),
						'<code>^/</code>'
					))
				}

				if (this.url.url.length - 1 !== this.url.url.indexOf('$') && -1 !== this.url.url.indexOf('$')) {
					warnings.push(sprintf(
						// Translators: 1 - The dollar symbol, 2 - Dollar symbol example.
						__('The dollar symbol %1$s should be at the end. For example: %2$s', td),
						'<code>$</code>',
						'<code>' + sanitizeString(this.url.url.replace(/\$/g, '')) + '$</code>'
					))
				}
			}

			// Warning if a URL with a common file extension
			if (null !== this.url.url.match(/(\.html|\.htm|\.php|\.pdf|\.jpg)$/)) {
				warnings.push(__('Some servers may be configured to serve file resources directly, preventing a redirect occurring.', td))
			}

			return warnings
		},
		urlOptionsActive () {
			return this.url.regex || this.showOptions
		}
	},
	methods : {
		updateSourceUrl (value) {
			if (!this.disableSource && value) {
				// First, let's format the URL for duplicate slashes.
				if (value) {
					value = value.replace(/(https?:\/)(\/)+|(\/)+/g, '$1$2$3')
				}

				// Remove white space from the url if it's not a regex.
				if (!this.url.regex && value.startsWith('/')) {
					value = value.replace(/\s+/g, '')
				}

				// Remove the home url.
				value = makeUrlRelative(value, this.rootStore.aioseo.urls.home)
			}

			this.url.url = value
			this.url.errors = this.validateRedirect(this)
			this.url.warnings = this.iffyUrl
			this.$emit('updated-url', this.url)
		},
		updateOption (option, value) {
			this.url[option] = value
			this.updateSourceUrl(this.url.url)

			this.$emit('updated-option', this.url)
		},
		searchChange () {
			if (this.disableSearch) {
				return
			}

			if (!this.url.url || this.url.regex) {
				this.isLoading = false
				this.showResults = false
				this.results = []
				return
			}

			// Don't search if the string starts with known characters.
			if (
				this.url.url.startsWith('/') ||
				this.url.url.startsWith('^') ||
				this.url.url.startsWith('http:') ||
				this.url.url.startsWith('https:')
			) {
				this.isLoading = false
				this.showResults = false
				this.results = []
				return
			}

			this.isLoading = true
			debounce(() => {
				if (!this.url.url) {
					this.isLoading = false
					this.showResults = false
					this.results = []
					return
				}

				this.showResults = true

				this.ajaxSearch(this.url.url)
					.then(() => (this.isLoading = false))
			}, 500)
		},
		ajaxSearch (query) {
			return this.redirectsStore.getPosts({ query })
				.then((response) => {
					this.results = response.body.objects
				})
		},
		setUrl (url) {
			this.showResults = false
			this.updateOption('url', url.replace(this.rootStore.aioseo.urls.home, '', url))
		},
		documentClick (event) {
			if (!this.showResults) {
				return
			}

			const target  = event && event.target ? event.target : null
			const element = this.$refs['redirect-source-url']

			if (element && element !== target && !element.contains(target)) {
				this.showResults = false
			}
		}
	},
	mounted () {
		if (this.url.showOptions) {
			this.showOptions = true
			this.updateSourceUrl(this.url.url)
		}

		document.addEventListener('click', this.documentClick)
	},
	beforeUnmount () {
		document.removeEventListener('click', this.documentClick)
	}
}
</script>

<style lang="scss">
.aioseo-redirect-source-url {
	position: relative;

	.aioseo-input {
		input {
			padding-right: 76px !important;
		}

		.append-icon {
			width: 60px;
			justify-content: flex-end;

			svg {
				max-width: 16px;
				margin-right: 8px;

				&:last-of-type {
					margin-right: 0;
				}

				&.aioseo-gear {
					color: $placeholder-color;
					cursor: pointer;

					&:hover,
					&.active {
						color: $blue;
					}
				}

				&.aioseo-circle-check {
					color: $green;
				}

				&.aioseo-circle-close {
					color: $red;
				}

				&.aioseo-trash {
					color: $placeholder-color;
					cursor: pointer;

					&:hover,
					&.active {
						color: $red;
					}
				}
			}
		}
	}

	.source-url-warning,
	.source-url-error {
		margin-bottom: 10px;
	}

	.source-url-options {
		display: flex;
		flex-wrap: wrap;
		gap: 8px 30px;

		&[aria-expanded="true"] {
			margin: 12px 0;
		}
	}
}
</style>