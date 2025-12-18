import DOMPurify from 'dompurify'
import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

const getContent = () => {
	const postEditorStore = usePostEditorStore()
	const processedContent = postEditorStore.currentPost.processedContent

	// The 'iframe' tag needs to be explicitly added as DOMPurify excludes it by default.
	return DOMPurify.sanitize(processedContent, {
		ADD_TAGS           : [ 'iframe' ],
		ALLOWED_ATTR       : [ 'href', 'src', 'alt' ],
		ALLOW_ARIA_ATTR    : false,
		ALLOW_DATA_ATTR    : false,
		ALLOW_UNKNOWN_TAGS : false,
		FORBID_TAGS        : [ 'script', 'style' ],
		FORBID_ATTR        : [ 'style', 'on*' ]
	})
}

const getTitle = () => {
	return window.bricksData?.wp?.post?.title || ''
}

const getPermalink = () => {
	try {
		const url = new URL(window.bricksData?.previewUrl || '')

		return url.origin + url.pathname
	} catch (_error) {
		return ''
	}
}

const getSlug = () => {
	try {
		const url = new URL(window.bricksData?.previewUrl || '')
		const path = url.pathname.replace(/\/$/, '')
		const segments = path.split('/')

		return segments[segments.length - 1]
	} catch (_error) {
		return ''
	}
}

const getExcerpt = () => {
	const tagsStore = useTagsStore()

	// There's no way to edit the excerpt in the Bricks editor, so we're using the tags store.
	return tagsStore.tags.find(tag => 'post_excerpt' === tag.id)?.value || ''
}

const getFeaturedImage = async () => {
	const imageUrl = window.bricksData?.featuredImage?.url || ''

	try {
		// Only return the URL if it's valid.
		new URL(imageUrl)

		return imageUrl
	} catch (_error) {
		// Return empty string for invalid URLs.
		return ''
	}
}

export const getBricksVueGlobalProperties = (property) => {
	return document.querySelector('.brx-body')?.__vue_app__?.config?.globalProperties?.[property] || null
}

export const getEditorData = () => {
	return {
		content       : getContent(),
		title         : getTitle(),
		excerpt       : getExcerpt(),
		slug          : getSlug(),
		permalink     : getPermalink(),
		featuredImage : getFeaturedImage()
	}
}