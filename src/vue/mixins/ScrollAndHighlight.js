import { getParams, removeParam } from '@/vue/utils/params'
export const ScrollAndHighlight = {
	mounted () {
		const scroll = getParams()['aioseo-scroll'] || history.state?.scroll
		if (scroll && 'string' === typeof scroll) {
			setTimeout(() => {
				this.$scrollTo(`#${scroll}`, { offset: -130, container: this.scrollContainer || 'body' })
				removeParam('aioseo-scroll')
				delete history.state?.scroll
			}, this.scrollTimeout || 500)
		}

		const highlight = getParams()['aioseo-highlight'] || history.state?.highlight
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
				delete history.state?.highlight
				removeParam('aioseo-highlight')
			}, timeout)
		}
	}
}