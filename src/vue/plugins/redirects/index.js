import {
	useRedirectsStore,
	useRootStore
} from '@/vue/stores'

import { isBlockEditor, isWooCommerceProduct } from '@/vue/utils/context'
import { getPostEditedSlug } from '@/vue/plugins/tru-seo/components/postSlug'
import { getPostStatus } from '@/vue/plugins/tru-seo/components/postStatus'
import { debounce } from 'lodash-es'

export default class RedirectsSlugMonitor {
	previousPostSlug
	previousPostStatus
	updatingRedirects = false

	constructor () {
		if (!window.aioseo.currentPost && !window.aioseo.redirects) {
			return
		}

		const rootStore = useRootStore()

		if (!rootStore.aioseo.redirectsWatcherSet) {
			this.initWatchers()
			rootStore.aioseo.redirectsWatcherSet = true
		}
	}

	initWatchers () {
		if (isWooCommerceProduct()) {
			return
		}

		if (isBlockEditor()) {
			const interval = window.setInterval(() => {
				const post = window.wp.data.select('core/editor').getCurrentPost()
				if (post.id) {
					window.clearInterval(interval)
					this.previousPostSlug = getPostEditedSlug()
					this.previousPostStatus = getPostStatus()
					this.watchBlockEditor()
				}
			}, 50)
		}
	}

	watchBlockEditor () {
		window.wp.data.subscribe(() => {
			if (!this.updatingRedirects) {
				this.update()
			}
		})
	}

	update = debounce(() => {
		const postSlug = getPostEditedSlug()
		const postStatus = getPostStatus()

		// If the slug or status is still the same, we don't need to update the redirects.
		if (this.previousPostSlug === postSlug && this.previousPostStatus === postStatus) {
			return
		}

		this.previousPostSlug = postSlug
		this.previousPostStatus = postStatus
		this.updatingRedirects = true

		const redirectsStore = useRedirectsStore()
		redirectsStore.getPostRedirects({})
			.finally(() => {
				this.updatingRedirects = false
			})
	}, 2500)
}