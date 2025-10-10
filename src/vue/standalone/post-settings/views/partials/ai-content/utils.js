import { isBlockEditor, isClassicEditor } from '@/vue/utils/context'
import {
	usePostEditorStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export const aiFeatures = {
	socialPosts : {
		slug    : 'social-posts',
		svg     : 'repurpose-content',
		strings : {
			name         : __('Social Posts', td),
			description  : __('Generate posts you can easily share on social media so you can reach a broader audience.', td),
			buttonSubmit : __('Generate Social Posts', td)
		},
		excludedPostTypes : []
	},
	faqs : {
		slug    : 'faqs',
		svg     : 'faq',
		strings : {
			name         : __('FAQs', td),
			description  : __('Generate helpful FAQs based on your content to enhance user engagement and boost SEO.', td),
			buttonSubmit : __('Generate FAQs', td)
		},
		excludedPostTypes : []
	},
	keyPoints : {
		slug    : 'key-points',
		svg     : 'key-points',
		strings : {
			name         : __('Key Points', td),
			description  : __('Extract and summarize the key points from your content to provide quick insights and improve readability.', td),
			buttonSubmit : __('Generate Key Points', td)
		},
		excludedPostTypes : []
	},
	metaTitle : {
		slug    : 'meta-title',
		svg     : 'meta-title',
		strings : {
			name         : __('SEO Titles', td),
			description  : __('Generate a compelling SEO title for your post to improve click-through rates and search engine visibility.', td),
			buttonSubmit : __('Generate SEO Titles', td)
		},
		excludedPostTypes : []
	},
	metaDescription : {
		slug    : 'meta-description',
		svg     : 'meta-description',
		strings : {
			name         : __('Meta Descriptions', td),
			description  : __('Stand out in search results with a meta description that sparks curiosity and drives clicks to your content.', td),
			buttonSubmit : __('Generate Meta Descriptions', td)
		},
		excludedPostTypes : []
	},
	imageGenerator : {
		slug    : 'image-generator',
		svg     : 'image-generator',
		strings : {
			name         : __('Image Generator', td),
			description  : __('Generate AI-powered images from text prompts to visually enhance your content and capture attention.', td),
			buttonSubmit : __('Generate Image', td)
		},
		excludedPostTypes : []
	}
}

export const getAiFeatures = () => {
	const postEditorStore = usePostEditorStore()
	if (!postEditorStore?.currentPost?.postType) {
		return Object.values(aiFeatures)
	}

	return Object.values(aiFeatures).filter(feature => !feature.excludedPostTypes.includes(postEditorStore.currentPost.postType))
}

export const copyContent = (content) => {
	if (isBlockEditor()) {
		return content
	}

	if (isClassicEditor()) {
		// Fallback for older browsers or non-secure contexts
		function listener (e) {
			e.clipboardData.setData('text/html', content)
			e.clipboardData.setData('text/plain', content)
			e.preventDefault()
		}

		document.addEventListener('copy', listener)
		document.execCommand('copy')
		document.removeEventListener('copy', listener)
	}

	return content
}