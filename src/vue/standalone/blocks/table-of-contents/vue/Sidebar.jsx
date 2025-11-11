import { __ } from '@/vue/plugins/translations'

const wp = window.wp
const {
	CheckboxControl,
	SelectControl,
	TextControl
} = wp.components

const td = import.meta.env.VITE_TEXTDOMAIN

export const Sidebar = (props) => {
	const {
		setAttributes,
		attributes: { listStyle, collapsibleType, collapsedTitle, mode, expandedTitle },
		clientId
	} = props

	return (
		<>
			<SelectControl
				label={__('List Style', td)}
				options={[
					{ label: 'Bullets', value: 'ul' },
					{ label: 'Numbers', value: 'ol' }
				]}
				value={listStyle}
				onChange={(value) => {
					setAttributes({ listStyle: value })
					window.aioseoBus.$emit('updateToc' + clientId)
				}}
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>

			<SelectControl
				label={__('Collapsible', td)}
				options={[
					{ label: 'Off', value: 'off' },
					{ label: 'On - Open by default', value: 'open' },
					{ label: 'On - Closed by default', value: 'closed' }
				]}
				value={collapsibleType}
				onChange={(value) => {
					setAttributes({ collapsibleType: value })
					window.aioseoBus.$emit('updateToc' + clientId)
				}}
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>

			{'off' !== collapsibleType && (
				<TextControl
					label={__('Collapsed Title', td)}
					value={collapsedTitle}
					onChange={(value) => {
						setAttributes({ collapsedTitle: value })
						window.aioseoBus.$emit('updateToc' + clientId)
					}}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			)}

			{'off' !== collapsibleType && (
				<TextControl
					label={__('Expanded Title', td)}
					value={expandedTitle}
					onChange={(value) => {
						setAttributes({ expandedTitle: value })
						window.aioseoBus.$emit('updateToc' + clientId)
					}}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			)}

			<CheckboxControl
				label={__('Synced Table of Contents', td)}
				help={__('Syncing table of contents enables you to build one unified table of contents for documents with multiple sections, even using separate ToC blocks.', td)}
				checked={'synced' === mode}
				onChange={() => {
					setAttributes({ mode: 'synced' === mode ? 'standalone' : 'synced' })
					window.aioseoBus.$emit('updateToc' + clientId)
				}}
				__nextHasNoMarginBottom
			/>
		</>
	)
}