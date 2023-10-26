import emitter from 'tiny-emitter/instance'
import { isBlockEditor, isClassicEditor, isClassicNoEditor } from '@/vue/utils/context'

let prevValue

export default () => {
	if (isBlockEditor()) {
		const { subscribe, select } = window.wp.data
		const coreEditor            = select('core/editor')
		subscribe(() => {
			const featuredImage = coreEditor.getEditedPostAttribute('featured_media')
			if (!prevValue || prevValue !== featuredImage) {
				emitter.emit('updateFeaturedImage', featuredImage)
				prevValue = featuredImage
			}
		})
	}

	if (isClassicEditor() || isClassicNoEditor()) {
		const MutationObserver = window.MutationObserver || window.WebKitMutationObserver
		const observer         = new MutationObserver(() => {
			const featuredImage = document.getElementById('_thumbnail_id')?.value
			if (!prevValue || prevValue !== featuredImage) {
				emitter.emit('updateFeaturedImage', featuredImage)
				prevValue = featuredImage
			}
		})

		// It's important we check first if the element exists because the observer will otherwise throw a fatal error.
		const featuredImageEditor = document.getElementById('postimagediv')
		if (featuredImageEditor) {
			observer.observe(featuredImageEditor, { subtree: true, childList: true })
		}
	}
}