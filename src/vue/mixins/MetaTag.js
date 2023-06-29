import {
	useOptionsStore
} from '@/vue/stores'

export const MetaTag = {
	methods : {
		maybeUpdateId (setting) {
			const optionsStore = useOptionsStore()

			// First, let's see if the value is a valid HTML element.
			const meta = this.metaHtml(optionsStore.options.webmasterTools[setting])
			if (meta instanceof HTMLElement && 'META' === meta.nodeName) {
				if (meta.getAttribute('content').length) {
					optionsStore.options.webmasterTools[setting] = meta.getAttribute('content')
				}
			}
		},
		metaHtml (html) {
			const doc = document.createElement('div')
			doc.innerHTML = html
			return doc.firstChild
		}
	}
}