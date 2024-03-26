<template>
	<div
		class="aioseo-add-redirection-target-url"
		ref="redirect-target-url"
	>
		<base-input
			v-model="value"
			@keyup="searchChange"
			@focus="showResults = true"
			@update:modelValue="$emit('update:modelValue', value)"
			@blur="$emit('blur', value)"
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

import { debounce } from '@/vue/utils/debounce'

import BaseInput from '@/vue/components/common/base/Input'
import CoreAddRedirectionUrlResults from '@/vue/components/common/core/add-redirection/UrlResults'
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			redirectsStore  : useRedirectsStore(),
			rootStore       : useRootStore()
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
		url      : String,
		errors   : Array,
		warnings : Array
	},
	data () {
		return {
			showResults : false,
			isLoading   : false,
			value       : null,
			results     : []
		}
	},
	watch : {
		value () {
			if (this.value) {
				this.value = this.value.replace(/(https?:\/)(\/)+|(\/)+/g, '$1$2$3')
				if (this.value.startsWith('/')) {
					this.value = this.value.replace(/\s+/g, '')
				}
			}
		},
		url : {
			immediate : true,
			handler () {
				this.value = this.url
			}
		}
	},
	methods : {
		onBlur () {
			setTimeout(() => {
				this.$emit('update:modelValue', this.value)
			}, 150)
		},
		searchChange () {
			if (!this.value) {
				return
			}

			// Don't search if the string starts with known characters.
			if (
				this.value.startsWith('/') ||
				this.value.startsWith('http:') ||
				this.value.startsWith('https:')
			) {
				this.isLoading = false
				return
			}

			this.isLoading = true
			debounce(() => {
				if (!this.value) {
					this.isLoading = false
					return
				}

				this.showResults = true

				this.ajaxSearch(this.value)
					.then(() => (this.isLoading = false))
			}, 500)
		},
		ajaxSearch (query) {
			return this.redirectsStore.getPosts({ query, postId: this.postEditorStore.currentPost.id })
				.then((response) => {
					this.results = response.body.objects
				})
		},
		setUrl (url) {
			this.showResults = false
			this.value       = url.replace(this.rootStore.aioseo.urls.mainSiteUrl, '', url)
			this.$emit('update:modelValue', this.value)
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
	},
	beforeUnmount () {
		document.removeEventListener('click', this.documentClick)
	}
}
</script>

<style lang="scss">
.aioseo-add-redirection-target-url {
	position: relative;
}
</style>