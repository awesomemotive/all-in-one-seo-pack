import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const wp = window.wp
const {
	PanelBody,
	SelectControl,
	ToggleControl
} = wp.components

export const Sidebar = ({ attributes, setSchemaBlockAttributes }) => {
	const {
		hidden,
		tagName
	} = attributes

	const upgradeLink = links.getUpsellLink(
		'faq-block',
		null,
		// Translators: The full string is "Click here to get AIOSEO Pro".
		GLOBAL_STRINGS.learnMore,
		'sidebar',
		true
	)

	return (
		<PanelBody title={__('FAQ Options', td)}>
			<SelectControl
				label={__('Title Wrapper', td)}
				options={[
					{ label: 'DIV', value: 'div' },
					{ label: 'H1', value: 'h1' },
					{ label: 'H2', value: 'h2' },
					{ label: 'H3', value: 'h3' },
					{ label: 'H4', value: 'h4' }
				]}
				value={tagName}
				onChange={value => setSchemaBlockAttributes({ tagName: value })}
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>

			<p className="aioseo-visibility-label">{__('Visibility', td)}</p>
			<ToggleControl
				className="aioseo-faq-visibility"
				label={__('Display Block', td)}
				help={__('You can choose to hide this block on the front-end of your site so that visitors won\'t see it but search engines will still see it.', td)}
				checked={!hidden}
				onChange={() => setSchemaBlockAttributes({ hidden: !hidden })}
				__nextHasNoMarginBottom
			/>
			<div className="aioseo-alert aioseo-alert inline-upsell blue medium">
				<div dangerouslySetInnerHTML={{ __html: __('FAQ schema is a Pro feature.', td) + ' ' + upgradeLink }}></div>
			</div>
		</PanelBody>
	)
}