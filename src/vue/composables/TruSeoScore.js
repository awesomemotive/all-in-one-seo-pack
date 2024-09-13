import { __, _n, sprintf } from '@/vue/plugins/translations'

import TruSeo from '@/vue/plugins/tru-seo'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useTruSeoScore = () => {
	const strings = {
		weveGotWorkToDo : sprintf(
			// Translators: 1 - HTML Line break tag.
			__('We\'ve got some%1$swork to do!', td),
			'<br>'
		),
		needsImprovement : sprintf(
			// Translators: 1 - HTML Line break tag.
			__('Needs%1$sImprovement!', td),
			'<br>'
		),
		veryGood  : __('Very Good!', td),
		excellent : __('Excellent!', td),
		allGood   : __('All Good!', td)
	}

	const getErrorDisplay = (amountOfErrors) => {
		if (0 < amountOfErrors) {
			return sprintf(
				// Translators: 1 - The amount of errors.
				_n('%1$s Error', '%1$s Errors', amountOfErrors, td),
				amountOfErrors
			)
		}
		return strings.allGood
	}

	const getErrorClass = (errors) => {
		if (5 < errors) {
			return 'red'
		}
		if (0 < errors) {
			return 'orange'
		}
		return 'green'
	}

	const getScoreClass = (score) => {
		return 80 < score ? 'green' : 50 < score ? 'orange' : 1 < score ? 'red' : 'none'
	}

	const runAnalysis = (postId) => {
		const truSeo = new TruSeo()
		truSeo.runAnalysis(postId)
	}

	return {
		getErrorClass,
		getErrorDisplay,
		getScoreClass,
		runAnalysis,
		strings
	}
}