import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { __, sprintf } from '@wordpress/i18n'
const td = import.meta.env.VITE_TEXTDOMAIN

export const useAiContent = () => {
	const rootStore    = useRootStore()
	const optionsStore = useOptionsStore()

	const toneOptions = [
		{ value: 'analytical', label: __('Analytical', td) },
		{ value: 'concise', label: __('Concise', td) },
		{ value: 'empathetic', label: __('Empathetic', td) },
		{ value: 'friendly', label: __('Friendly', td) },
		{ value: 'formal', label: __('Formal', td) },
		{ value: 'humorous', label: __('Humorous', td) },
		{ value: 'informal', label: __('Informal', td) },
		{ value: 'informative', label: __('Informative', td) },
		{ value: 'motivational', label: __('Motivational', td) },
		{ value: 'neutral', label: __('Neutral', td) },
		{ value: 'persuasive', label: __('Persuasive', td) },
		{ value: 'playful', label: __('Playful', td) },
		{ value: 'professional', label: __('Professional', td) },
		{ value: 'serious', label: __('Serious', td) },
		{ value: 'storytelling', label: __('Storytelling', td) },
		{ value: 'urgent', label: __('Urgent', td) }
	]

	const audienceOptions = [
		{ value: 'academic', label: __('Academic', td) },
		{ value: 'beginners-novices', label: __('Beginners & Novices', td) },
		{ value: 'business', label: __('Business', td) },
		{ value: 'creative-artistic', label: __('Creative & Artistic', td) },
		{ value: 'educational', label: __('Educational', td) },
		{ value: 'general', label: __('General', td) },
		{ value: 'parents-families', label: __('Parents & Families', td) },
		{ value: 'technical', label: __('Technical', td) }
	]

	const imageQualityOptions = [
		{ label: __('High', td), value: 'high' },
		{ label: __('Medium', td), value: 'medium' },
		{ label: __('Low', td), value: 'low' }
	]

	const imageStyleOptions = [
		{ label: __('Auto', td), value: 'auto' },
		{ label: __('3D Render', td), value: '3d-render' },
		{ label: __('Cinematic', td), value: 'cinematic' },
		{ label: __('Creative', td), value: 'creative' },
		{ label: __('Illustration', td), value: 'illustration' },
		{ label: __('Minimalist', td), value: 'minimalist' },
		{ label: __('Stock Photo', td), value: 'stock-photo' },
		{ label: __('Vibrant', td), value: 'vibrant' }
	]

	const imageAspectRatioOptions = [
		{ label: __('Landscape (3:2)', td), value: 'landscape' },
		{ label: __('Portrait (2:3)', td), value: 'portrait' },
		{ label: __('Square (1:1)', td), value: 'square' }
	]

	const getAspectRatioFromDimensions = (width, height) => {
		if (!width || !height) {
			return null
		}

		const ratio = width / height
		if (1 < ratio) {
			return 'landscape'
		}
		if (1 > ratio) {
			return 'portrait'
		}
		return 'square'
	}

	const strings = {
		credits          : __('Credits', td),
		alertDescription : sprintf(
			// Translators: 1 - Link to the AI Content settings page.
			__('You can manage your default settings under <a href="%1$s" target="_blank" rel="noopener noreferrer">General Settings > AI Content</a>.', td),
			rootStore.aioseo.urls.aio.settings + '/#ai-content'
		),
		audience         : __('Audience', td),
		tone             : __('Tone', td),
		imageQuality     : __('Quality', td),
		imageStyle       : __('Style', td),
		imageAspectRatio : __('Aspect Ratio', td),
		somethingWrong   : __('We ran into an error. Please try again or contact support if it persists.', td)
	}

	const hasEnoughCredits = (amountOfCredits) => {
		return optionsStore.internalOptions.internal.ai.credits.remaining >= amountOfCredits
	}

	const loadingText = (title) => {
		return sprintf(
			// Translators: 1 - The title of the content.
			__('Loading %1$s Content', td),
			title
		)
	}

	const getFeatureCost = (featureName) => {
		const costPerFeature = optionsStore.internalOptions?.internal?.ai?.costPerFeature || {}

		// Return cost from API or fallback to a hardcoded value of 10.
		return costPerFeature[featureName] || 10
	}

	return {
		audienceOptions,
		getAspectRatioFromDimensions,
		getFeatureCost,
		hasEnoughCredits,
		imageAspectRatioOptions,
		imageQualityOptions,
		imageStyleOptions,
		loadingText,
		strings,
		toneOptions
	}
}