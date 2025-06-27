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
		{
			value : 'friendly',
			label : __('Friendly', td)
		},
		{
			value : 'formal',
			label : __('Formal', td)
		},
		{
			value : 'humorous',
			label : __('Humorous', td)
		},
		{
			value : 'informal',
			label : __('Informal', td)
		},
		{
			value : 'informative',
			label : __('Informative', td)
		},
		{
			value : 'neutral',
			label : __('Neutral', td)
		},
		{
			value : 'persuasive',
			label : __('Persuasive', td)
		},
		{
			value : 'professional',
			label : __('Professional', td)
		}
	]

	const audienceOptions = [
		{
			value : 'academic',
			label : __('Academic', td)
		},
		{
			value : 'business',
			label : __('Business', td)
		},
		{
			value : 'educational',
			label : __('Educational', td)
		},
		{
			value : 'general',
			label : __('General', td)
		},
		{
			value : 'technical',
			label : __('Technical', td)
		}
	]

	const strings = {
		credits          : __('Credits', td),
		alertDescription : sprintf(
			// Translators: 1 - Link to the AI Content settings page.
			__('Choose the tone and target audience for your content. You can manage your default settings under <a href="%1$s" target="_blank" rel="noopener noreferrer">General Settings > AI Content</a>.', td),
			rootStore.aioseo.urls.aio.settings + '/#ai-content'
		),
		audience : __('Audience', td),
		tone     : __('Tone', td)
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

	return {
		audienceOptions,
		loadingText,
		strings,
		toneOptions,
		hasEnoughCredits
	}
}