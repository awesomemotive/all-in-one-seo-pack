<template>
	<div
		class="aioseo-redirects-add-redirect-standalone"
		v-if="addons.isActive('aioseo-redirects')"
	>
		<core-modal
			:show="display"
			:classes="[ 'aioseo-redirects' ]"
			@close="display = false"
			allow-overflow
		>
			<template #headerTitle>
				{{ strings.modalHeader }}
			</template>

			<template #body>
				<div class="bd">
					<core-add-redirection
						v-if="!loading"
						:urls="urls"
						:target="urls[0].target ? urls[0].target : '/'"
						:disableSource="true"
						@added-redirect="reload"
					/>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import '@/vue/assets/scss/main.scss'

import {
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import http from '@/vue/utils/http'
import { isEmpty } from 'lodash-es'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreAddRedirection from '@/vue/components/common/core/add-redirection/Index'

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		CoreModal,
		CoreAddRedirection
	},
	data () {
		return {
			addons,
			urls    : [],
			display : false,
			target  : null,
			loading : false,
			strings : {
				modalHeader   : this.$t.__('Add a Redirect', this.$td),
				redirectAdded : this.$t.sprintf(
					// Translators: 1 - A internal link for Redirects, 2 - Open strong tag, 3 - Close strong tag.
					this.$t.__('%2$sYour redirect was added and you may edit it <a href="%1$s" target="_blank">here</a>.%3$s', this.$td),
					this.rootStore.aioseo.urls.aio.redirects,
					'<strong>',
					'</strong>'
				)
			},
			watchClasses : [
				'aioseo-redirects-slug-changed',
				'aioseo-redirects-trashed-post'
			]
		}
	},
	computed : {
		classSelectors () {
			return '.' + this.watchClasses.join(', .')
		}
	},
	methods : {
		reload () {
			this.display = false

			// Gutemberg or normal wp-notice.
			const parent = this.target?.parentElement?.parentElement
			if (parent && (
				parent.classList.contains('components-notice__content') ||
				parent.classList.contains('notice')
			)
			) {
				parent.innerHTML = '<p>' + this.strings.redirectAdded + '</p>'
				return
			}

			// Just replace the link.
			this.target.outerHTML = this.strings.redirectAdded
		},
		loadRedirect (manualUrlsHash) {
			this.loading = true
			http.get(this.$links.restUrl('redirects/manual-redirects/' + manualUrlsHash))
				.then(response => {
					this.urls = response.body.redirects
					this.loading = false
				}).catch((error) => console.error('Redirect modal failed to load the redirect data.', error))
		},
		preloadRedirect () {
			const redirect = document.querySelector(this.classSelectors)
			if (redirect) {
				const manualUrlsHash = this.getElementRedirectHash(redirect)
				if (!manualUrlsHash) {
					return
				}

				// Load the redirect info.
				this.loadRedirect(manualUrlsHash)
			}
		},
		watchClicks () {
			// Watch for clicks on tracked links.
			document.body.onclick = (e) => {
				if (!e.target?.classList) {
					return
				}

				let acceptClick = false
				this.watchClasses.forEach(className => {
					if (e.target.classList.contains(className)) {
						acceptClick = true
					}
				})

				if (!acceptClick) {
					return
				}

				// Prevent the click.
				e.preventDefault()

				// Set the target to use later.
				this.target = e.target

				// Show the modal.
				this.display = true

				// Url not loaded yet?
				if (isEmpty(this.urls)) {
					this.loadRedirect(this.getElementRedirectHash(this.target))
				}
			}
		},
		getElementRedirectHash (elem) {
			// Find the manual url hash.
			const params = new URLSearchParams(elem.href)
			return params.get('aioseo-manual-urls')
		}
	},
	async created () {
		this.preloadRedirect()
		this.watchClicks()

		window.aioseoBus.$on('wp-core-notice-created', () => {
			this.$nextTick(() => {
				this.preloadRedirect()
			})
		})
	}
}
</script>
<style lang="scss">
.aioseo-redirects.aioseo-modal {
	.bd {
		padding: 20px
	}
}
</style>