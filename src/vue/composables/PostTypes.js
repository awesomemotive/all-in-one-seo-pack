import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const usePostTypes = () => {
	const viewPost = (singular) => {
		return sprintf(
			// Translators: 1 - The singular label for the current post type.
			__('View %1$s', td),
			singular || __('Post', td)
		)
	}

	const editPost = (singular) => {
		return sprintf(
			// Translators: 1 - A noun for something that's being edited ("Post", "Page", "Article", "Product", etc.).
			__('Edit %1$s', td),
			singular || __('Post', td)
		)
	}

	const getPostIconClass = (icon) => {
		const defaultIcon = 'dashicons-admin-post'

		// If the icon's name starts with 'dashicons-awb-', then it's a custom icon for Avada that
		// we are not able to import.
		if (icon?.startsWith('dashicons-awb-')) {
			return defaultIcon
		}

		return icon || defaultIcon
	}

	return {
		editPost,
		getPostIconClass,
		viewPost
	}
}