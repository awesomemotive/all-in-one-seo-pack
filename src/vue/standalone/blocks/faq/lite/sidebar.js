import { html } from '@/vue/standalone/blocks/utils'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'

const {
	RichText,
	InnerBlocks
} = window.wp.blockEditor
const {
	PanelBody,
	SelectControl,
	ToggleControl
} = window.wp.components
const InspectorControls =
	window.wp.blockEditor.InspectorControls ||
	window.wp.editor.InspectorControls

const { __ } = window.wp.i18n
const td = import.meta.env.VITE_TEXTDOMAIN

const TEMPLATE = [
	[
		'core/paragraph', { placeholder: __('Write an answer...', td) }
	]
]
const ALLOWED_BLOCKS = [
	'core/paragraph',
	'core/heading',
	'core/list',
	'core/image',
	'core/media-text'
]

export const render = (className, attributes, setSchemaBlockAttributes) => {
	const {
		hidden,
		question,
		tagName
	} = attributes

	const upgradeLink = links.getUpsellLink(
		'faq-block',
		// Translators: The full string is "Click here to get AIOSEO Pro".
		GLOBAL_STRINGS.learnMore,
		'sidebar',
		true
	)

	return html`
		<div data-schema-only="${hidden}" className="${className}">
			<${InspectorControls}>
				<${PanelBody} title=${__('FAQ Options', td)}>
					<${SelectControl}
						label=${__('Title Wrapper', td)}
						options=${[
							{ label: 'DIV', value: 'div' },
							{ label: 'H1', value: 'h1' },
							{ label: 'H2', value: 'h2' },
							{ label: 'H3', value: 'h3' },
							{ label: 'H4', value: 'h4' }
						]}
						value=${tagName}
						onChange=${value => setSchemaBlockAttributes({ tagName: value })}
					/>

					<p className="aioseo-visibility-label">${__('Visibility', td)}</p>
					<${ToggleControl}
						className="aioseo-faq-visibility"
						label=${__('Display Block', td)}
						help=${__('You can choose to hide this block on the front-end of your site so that visitors won\'t see it but search engines will still see it.', td)}
						checked=${!hidden}
						onChange=${() => setSchemaBlockAttributes({ hidden: !hidden })}
						disabled
					/>
					<div className="aioseo-alert aioseo-alert inline-upsell blue medium">
						<div dangerouslySetInnerHTML=${{ __html: __('FAQ schema is a Pro feature.', td) + ' ' + upgradeLink }}></div>
					</div>
				</${PanelBody}>
			</${InspectorControls}>
			<${RichText}
				tagName=${tagName}
				placeholder=${__('Write a question...', td)}
				className='aioseo-faq-block-question'
				value=${question}
				onChange=${value => setSchemaBlockAttributes({ question: value })}
			/>
			<div className="aioseo-faq-block-answer">
				<${InnerBlocks}
					template=${TEMPLATE}
					allowedBlocks=${ALLOWED_BLOCKS}
				/>
			</div>
		</div>
	`
}