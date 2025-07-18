import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export const getBreadcrumbPreview = (postType) => { // eslint-disable-line no-unused-vars
	return [
		__('Category Parent', td),
		__('Article Title', td)
	]
}