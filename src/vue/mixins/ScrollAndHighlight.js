import {
	useRootStore
} from '@/vue/stores'

import { getParams, removeParam } from '@/vue/utils/params'
export const ScrollAndHighlight = {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	data () {
		return {
			running : false
		}
	},
	watch : {
		'rootStore.navigate.scroll' () {
			if (this.running) {
				return
			}

			this.running = true
			this.scrollAndHighlight()
		},
		'rootStore.navigate.highlight' () {
			if (this.running) {
				return
			}

			this.running = true
			this.scrollAndHighlight()
		}
	},
	methods : {
		scrollAndHighlight () {
			const rootStore = useRootStore()
			const scroll    = getParams()['aioseo-scroll'] || history.state?.scroll || rootStore.navigate.scroll
			if (scroll && 'string' === typeof scroll) {
				setTimeout(() => {
					this.$scrollTo(`#${scroll}`, { offset: -130, container: this.scrollContainer || 'body' })
					removeParam('aioseo-scroll')
					delete history.state?.scroll
					rootStore.navigate.scroll = null
					this.running = false
				}, this.scrollTimeout || 500)
			}

			const highlight = getParams()['aioseo-highlight'] || history.state?.highlight || rootStore.navigate.highlight
			if (highlight && 'string' === typeof highlight) {
				const timeout = scroll ? this.scrollAndHighlightTimeout || 1500 : this.highlightTimeout || 500
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
					this.running = false
				}, timeout)
			}
		}
	},
	mounted () {
		this.scrollAndHighlight()
	}
}