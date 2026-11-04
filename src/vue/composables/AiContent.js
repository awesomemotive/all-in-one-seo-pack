import {
	useAiStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { getPostEditedContent } from '@/vue/utils/postData/postContent'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'

import { __, sprintf } from '@wordpress/i18n'
const td = import.meta.env.VITE_TEXTDOMAIN

// Keys must match the licensing server's `costPerFeature` map — a key it doesn't
// publish can never be overridden and silently freezes at the value below.
export const defaultFeatureCosts = {
	titles             : 10,
	descriptions       : 10,
	socialPosts        : 10,
	keyPoints          : 10,
	faqs               : 10,
	schemas            : 25,
	aiAssistant        : 50,
	imageAltText       : 10,
	imageGenerator     : 250,
	truseoSuggest      : 5,
	truseoSpelling     : 5,
	truseoOptimizePost : 25
}

// The Image Generator is priced as a nested map of model and quality tiers rather than a
// single figure, so collapse whatever shape the API sent to its numeric leaves.
const flattenCosts = value => {
	if (null === value || 'object' !== typeof value) {
		// Number('') is 0, and an unpublished price is not a free one.
		return [ '' === value ? NaN : Number(value) ]
	}

	return Object.values(value).flatMap(flattenCosts)
}

/**
 * Returns the credit cost of an AI feature, preferring the price the licensing
 * server published over the local default.
 *
 * @param {string} featureName The `costPerFeature` key.
 * @returns {number} The cost in credits.
 */
export const getFeatureCost = (featureName) => {
	const optionsStore   = useOptionsStore()
	const costPerFeature = optionsStore.internalOptions?.internal?.ai?.costPerFeature || {}

	// Prices arrive as strings and callers add them together, so always hand back a
	// number. A published 0 is meaningful — the feature is bundled into another charge.
	// Features priced per model advertise their cheapest tier.
	const costs = flattenCosts(costPerFeature[featureName]).filter(Number.isFinite)

	return costs.length ? Math.min(...costs) : (defaultFeatureCosts[featureName] ?? 10)
}

export const useAiContent = () => {
	const aiStore      = useAiStore()
	const rootStore    = useRootStore()
	const optionsStore = useOptionsStore()

	const minContentLength = aiStore.options?.minContentLength || 200

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

	const imageModelOptions = [
		{
			label           : __('Nano Banana 2', td),
			value           : 'gemini-3.1-flash-image',
			description     : __('Google\'s state-of-the-art image model. Excels at photorealism, accurate in-image text, and creative edits.', td),
			badge           : { text: __('New', td), color: 'green' },
			supportsQuality : false,
			flatCost        : 1000
		},
		{
			label           : __('GPT Image 2', td),
			value           : 'gpt-image-2',
			description     : __('OpenAI\'s state-of-the-art model with precise text rendering and high-fidelity output.', td),
			badge           : { text: __('Updated', td), color: 'blue' },
			supportsQuality : true
		}
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

	const getPostContentLength = () => {
		return getPostEditedContent()
			.replace(/<\/?[a-z][^>]*?>/gi, '')
			.replace(/<!--[\s\S]*?-->/g, '')
			.trim()
			.length
	}

	const hasEnoughContent = () => getPostContentLength() >= minContentLength

	const rephraseCost = 5

	const getRephraseCost = () => {
		const costPerFeature = optionsStore.internalOptions?.internal?.ai?.costPerFeature || {}

		return Number(costPerFeature.rephrase || rephraseCost)
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

	const strings = {
		credits    : __('Credits', td),
		disclaimer : sprintf(
			// Translators: %1$s - Link to the AI Content disclaimer documentation.
			__('AI-generated content could be inaccurate or biased. %1$s', td),
			links.getDocLink(GLOBAL_STRINGS.learnMore, 'aiDisclaimer', true)
		),
		alertDescription : sprintf(
			// Translators: 1 - Link to the AI Content settings page.
			__('You can manage your default settings under <a href="%1$s" target="_blank" rel="noopener noreferrer">AI Suite > AI Content</a>.', td),
			rootStore.aioseo.urls.aio.aiSuite + '#/ai-content'
		),
		audience         : __('Audience', td),
		tone             : __('Tone', td),
		imageQuality     : __('Quality', td),
		imageStyle       : __('Style', td),
		imageAspectRatio : __('Aspect Ratio', td),
		imageModel       : __('Image Model', td),
		noContentWarning : sprintf(
			// Translators: 1 - Minimum number of characters.
			__('Your post is too short to generate AI content. Please add more content. For the best results, we recommend adding at least %1$d characters.', td),
			minContentLength
		),
		rephrase            : __('Regenerate', td),
		aiDisabled          : __('AI features have been disabled on this site.', td),
		seedImageForbidden  : __('You don\'t have permission to edit the selected image with AI. It may be attached to a post you can\'t access.', td),
		somethingWrong      : __('We ran into an error. Please try again or contact support if it persists.', td),
		viewPreviousResults : __('View Previous Results', td)
	}

	return {
		audienceOptions,
		getAspectRatioFromDimensions,
		getFeatureCost,
		getPostContentLength,
		getRephraseCost,
		hasEnoughContent,
		hasEnoughCredits,
		imageAspectRatioOptions,
		imageModelOptions,
		imageQualityOptions,
		imageStyleOptions,
		loadingText,
		minContentLength,
		strings,
		toneOptions
	}
}