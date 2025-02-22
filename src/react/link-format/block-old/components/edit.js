/**
 * External dependencies
 */
import { map } from 'lodash-es'

/**
 * Internal dependencies
 */
import InlineLinkUI from './inline'

const wp = window.wp

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n
const { Component, Fragment } = wp.element
const { select, withSelect } = wp.data
const { BlockControls, RichTextToolbarButton, RichTextShortcut } = wp.blockEditor
const {
	getTextContent,
	applyFormat,
	removeFormat,
	slice
} = wp.richText
const { isURL } = wp.url
const { Toolbar, withSpokenMessages } = wp.components
const { compose, ifCondition } = wp.compose

const name = 'core/link'
const title = __('Add Link', 'all-in-one-seo-pack')
const EMAIL_REGEXP = /^(mailto:)?[a-z0-9._%+-]+@[a-z0-9][a-z0-9.-]*\.[a-z]{2,63}$/i

class Edit extends Component {
	constructor () {
		super(...arguments)

		this.isEmail = this.isEmail.bind(this)
		this.addLink = this.addLink.bind(this)
		this.stopAddingLink = this.stopAddingLink.bind(this)
		this.onRemoveFormat = this.onRemoveFormat.bind(this)
		this.state = {
			addingLink : false
		}
	}

	isEmail (email) {
		return EMAIL_REGEXP.test(email)
	}

	addLink () {
		const { value, onChange } = this.props
		const text = getTextContent(slice(value))

		if (text && isURL(text)) {
			onChange(applyFormat(value, { type: name, attributes: { url: text } }))
		} else if (text && this.isEmail(text)) {
			onChange(applyFormat(value, { type: name, attributes: { url: `mailto:${text}` } }))
		} else {
			this.setState({ addingLink: true })
		}
	}

	stopAddingLink () {
		this.setState({ addingLink: false })
	}

	onRemoveFormat () {
		const { value, onChange, speak } = this.props

		let newValue = value

		map([ 'core/link' ], (linkFormat) => {
			newValue = removeFormat(newValue, linkFormat)
		})

		onChange({ ...newValue })
		speak(__('Link removed.', 'all-in-one-seo-pack'), 'assertive')
	}

	render () {
		const { activeAttributes, onChange } = this.props
		const { isActive, value } = this.props

		return (
			<Fragment>
				<BlockControls>
					<Toolbar className="editorskit-components-toolbar">
						<RichTextShortcut
							type="primary"
							character="k"
							onUse={ this.addLink }
						/>
						<RichTextShortcut
							type="primaryShift"
							character="k"
							onUse={ this.onRemoveFormat }
						/>

						{ isActive && <RichTextToolbarButton
							name="link"
							icon="editor-unlink"
							title={ __('Unlink', 'all-in-one-seo-pack') }
							onClick={ this.onRemoveFormat }
							isActive={ isActive }
							shortcutType="primaryShift"
							shortcutCharacter="k"
						/> }
						{ !isActive && <RichTextToolbarButton
							name="link"
							icon="admin-links"
							title={ title }
							onClick={ this.addLink }
							isActive={ isActive }
							shortcutType="primary"
							shortcutCharacter="k"
						/> }

						<InlineLinkUI
							addingLink={ this.state.addingLink }
							stopAddingLink={ this.stopAddingLink }
							isActive={ isActive }
							activeAttributes={ activeAttributes }
							value={ value }
							onChange={ onChange }
							contentRef={ this.props.contentRef }
						/>

					</Toolbar>
				</BlockControls>
			</Fragment>
		)
	}
}

export default compose(
	withSelect(() => {
		return {
			isDisabled : select('core/edit-post').isFeatureActive('disableEditorsKitLinkFormats')
		}
	}),
	ifCondition((props) => !props.isDisabled),
	withSpokenMessages
)(Edit)