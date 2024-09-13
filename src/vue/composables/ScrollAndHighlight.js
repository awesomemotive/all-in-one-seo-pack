import { ref, watch, onMounted } from 'vue'

import {
	useRootStore
} from '@/vue/stores'

import { useScrollTo } from '@/vue/composables/ScrollTo'

import {
	getParams,
	removeParam
} from '@/vue/utils/params'

export const useScrollAndHighlight = (params = {}) => {
	const {
		scrollTimeout             = 500,
		highlightTimeout          = 500,
		scrollAndHighlightTimeout = 1500
	} = params

	const running   = ref(false)
	const rootStore = useRootStore()

	// Watch for scroll changes.
	watch(() => rootStore.navigate.scroll, () => {
		if (running.value) {
			return
		}

		running.value = true
		scrollAndHighlight()
	})

	// Watch for highlight changes.
	watch(() => rootStore.navigate.highlight, () => {
		if (running.value) {
			return
		}

		running.value = true
		scrollAndHighlight()
	})

	// Scroll and highlight.
	const scrollAndHighlight = () => {
		const scroll = getParams()['aioseo-scroll'] || history.state?.scroll || rootStore.navigate.scroll
		if (scroll && 'string' === typeof scroll) {
			setTimeout(() => {
				useScrollTo().scrollTo(scroll, {
					mods : {
						scrollMarginClass : 'aioseo-scroll-and-highlight-margin',
						timeout           : scrollTimeout
					}
				})

				removeParam('aioseo-scroll')
				delete history.state?.scroll
				rootStore.navigate.scroll = null
				running.value = false
			}, scrollTimeout)
		}

		const highlight = getParams()['aioseo-highlight'] || history.state?.highlight || rootStore.navigate.highlight
		if (highlight && 'string' === typeof highlight) {
			const timeout = scroll ? scrollAndHighlightTimeout : highlightTimeout
			setTimeout(() => {
				const elements = document.querySelectorAll(`#${highlight.replace(/,/g, ', #').replace(/%2C/ig, ', #')}`)
				if (elements.length) {
					elements.forEach(element => {
						element.classList.add('aioseo-row-highlight')
						setTimeout(() => {
							element.classList.remove('aioseo-row-highlight')
						}, 1500)
					})
				}
				removeParam('aioseo-highlight')
				delete history.state?.highlight
				rootStore.navigate.highlight = null
				running.value = false
			}, timeout)
		}
	}

	// Run on mounted.
	onMounted(scrollAndHighlight)
}