import { __ } from '@/vue/plugins/translations'
import { useAiContent } from '@/vue/composables/AiContent'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	tone : __('Tone', td)
}

const { SelectControl } = window.wp.components

/**
 * Renders the Tone Selector panel for the block inspector.
 *
 * @param {Object}   props          Component props.
 * @param {string}   props.value    The current tone value.
 * @param {Function} props.onChange Callback invoked when the tone is changed.
 * @returns {Object}                HTML template for the inspector panel.
 */
export const ToneSelectorPanel = ({ value, onChange }) => {
	const { toneOptions } = useAiContent()

	return (
		<SelectControl
			label={strings.tone}
			value={value}
			options={toneOptions}
			onChange={onChange}
			__next40pxDefaultSize={true}
			__nextHasNoMarginBottom={true}
		/>
	)
}