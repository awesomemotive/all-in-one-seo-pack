import { html } from '@/vue/standalone/blocks/utils'
import { __ } from '@/vue/plugins/translations'
import { useAiContent } from '@/vue/composables/AiContent'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	audience : __('Audience', td)
}

const { SelectControl } = window.wp.components

/**
 * Renders the Audience Selector panel for the block inspector.
 *
 * @param {Object}   props          Component props.
 * @param {string}   props.value    The current audience value.
 * @param {Function} props.onChange Callback invoked when the audience is changed.
 * @returns {Object}                HTML template for the inspector panel.
 */
export const AudienceSelectorPanel = ({ value, onChange }) => {
	const { audienceOptions } = useAiContent()

	return html`
		<${SelectControl}
			label="${strings.audience}"
			value=${value}
			options=${audienceOptions}
			onChange=${onChange}
			__next40pxDefaultSize=${true}
			__nextHasNoMarginBottom=${true}
		/>`
}