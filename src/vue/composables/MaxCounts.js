import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useMaxCounts = () => {
	const maxRecommendedCount = (current, max) => {
		const error = current > max ? ' class="error"' : ''
		return sprintf(
			// Translators: 1 - A number, 2 - A number.
			__('%1$s out of %2$s max recommended characters.', td),
			`<strong${error}>${current}</strong>`,
			`<strong>${max}</strong>`
		)
	}

	return {
		maxRecommendedCount
	}
}