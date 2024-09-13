import {
	useOptionsStore
} from '@/vue/stores'

const metaHtml = (html) => {
	const doc = document.createElement('div')
	doc.innerHTML = html
	return doc.firstChild
}

export const useMetaTags = () => {
	const maybeUpdateId = (setting) => {
		const optionsStore = useOptionsStore()

		// First, let's see if the value is a valid HTML element.
		const meta = metaHtml(optionsStore.options.webmasterTools[setting])
		if (meta instanceof HTMLElement && 'META' === meta.nodeName) {
			if (meta.getAttribute('content').length) {
				optionsStore.options.webmasterTools[setting] = meta.getAttribute('content')
			}
		}
	}

	return {
		maybeUpdateId
	}
}