import {
	useTableOfContentsStore
} from '@/vue/stores'

import { html } from '@/vue/standalone/blocks/utils'
import { __ } from '@/vue/plugins/translations'

const { InspectorControls }        = window.wp.blockEditor
const { PanelBody, SelectControl } = window.wp.components
const td                           = import.meta.env.VITE_TEXTDOMAIN

export const sidebarControls = (props) => {
	const { setAttributes, attributes: { listStyle } } = props

	const tableOfContentsStore = useTableOfContentsStore()

	return html`
	<${InspectorControls}>
		<${PanelBody} title=${__('Table of Contents Settings', td)}>
			<${SelectControl}
				label=${__('List Style', td)}
				options=${[
					{ label: 'Bullets', value: 'ul' },
					{ label: 'Numbers', value: 'ol' }
				]}
				value=${listStyle}
				__next40pxDefaultSize=${true}
				__nextHasNoMarginBottom=${true}
				onChange=${(value) => {
					tableOfContentsStore.listStyle = value
					setAttributes({ listStyle: value })
				}}
			/>
		</${PanelBody}>
	</${InspectorControls}>
`
}