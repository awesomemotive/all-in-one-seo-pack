/**
 * External dependencies
 */
import { uniqueId } from 'lodash-es'

/**
 * WordPress dependencies
 */
import { useMemo, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { withSpokenMessages, Popover } from '@wordpress/components'
import { prependHTTP } from '@wordpress/url'
import { create, insert, isCollapsed, applyFormat, useAnchor } from '@wordpress/rich-text'
import { versionCompare } from '../../../../../vue/utils/helpers'

/**
 * Internal dependencies
 */
import { LinkControl } from '../../components/index'
import { createLinkFormat, isValidHref } from './utils'
import { link as linkSettings } from './index'

function InlineLinkUI ({
	isActive,
	activeAttributes,
	addingLink,
	value,
	onChange,
	speak,
	stopAddingLink,
	contentRef
}) {
	/**
	 * A unique key is generated when switching between editing and not editing
	 * a link, based on:
	 *
	 * - This component may be rendered _either_ when a link is active _or_
	 *   when adding or editing a link.
	 * - It's only desirable to shift focus into the Popover when explicitly
	 *   adding or editing a link, not when in the inline boundary of a link.
	 * - Focus behavior can only be controlled on a Popover at the time it
	 *   mounts, so a new instance of the component must be mounted to
	 *   programmatically enact the focusOnMount behavior.
	 *
	 * @type {string}
	 */
	const mountingKey = useMemo(uniqueId, [ addingLink ])

	/**
	 * Pending settings to be applied to the next link. When inserting a new
	 * link, toggle values cannot be applied immediately, because there is not
	 * yet a link for them to apply to. Thus, they are maintained in a state
	 * value until the time that the link can be inserted or edited.
	 *
	 * @type {[Object|undefined,Function]}
	 */
	const [ nextLinkValue, setNextLinkValue ] = useState()

	// Set the selected text from the value string.
	const selectedText = value.text.substring(value.start, value.end)

	let anchor = null
	if (useAnchor) {
		anchor = useAnchor({
			editableContentElement : contentRef.current,
			settings               : {
				...linkSettings,
				isActive
			}
		})
	}

	const anchorRef = useMemo(() => {
		const selection = window.getSelection()

		if (!selection.rangeCount) {
			return
		}

		const range = selection.getRangeAt(0)

		if (addingLink && !isActive) {
			return range
		}

		let element = range.startContainer

		// If the caret is right before the element, select the next element.
		element = element.nextElementSibling || element

		while (element.nodeType !== window.Node.ELEMENT_NODE) {
			element = element.parentNode
		}

		return element.closest('a')
	}, [ addingLink, value.start, value.end ])

	const linkValue = {
		url           : activeAttributes.url,
		opensInNewTab : '_blank' === activeAttributes.target,
		nofollow      : activeAttributes.rel ? activeAttributes.rel.includes('nofollow') : false,
		sponsored     : activeAttributes.rel ? activeAttributes.rel.includes('sponsored') : false,
		ugc           : activeAttributes.rel ? activeAttributes.rel.includes('ugc') : false,
		title         : activeAttributes.title || '',
		isAddingLink  : !activeAttributes.url,
		...nextLinkValue
	}

	function onChangeLink (nextValue) {
		// Merge with values from state, both for the purpose of assigning the
		// next state value, and for use in constructing the new link format if
		// the link is ready to be applied.
		nextValue = {
			...nextLinkValue,
			...nextValue
		}

		// LinkControl calls `onChange` immediately upon the toggling a setting.
		const didToggleSetting =
			(
				linkValue.opensInNewTab !== nextValue.opensInNewTab ||
				linkValue.sponsored !== nextValue.sponsored ||
				linkValue.nofollow !== nextValue.nofollow ||
				linkValue.ugc !== nextValue.ugc
			) &&
			linkValue.url === nextValue.url

		// If change handler was called as a result of a settings change during
		// link insertion, it must be held in state until the link is ready to
		// be applied.
		const didToggleSettingForNewLink =
			didToggleSetting && nextValue.url === undefined

		// If link will be assigned, the state value can be considered flushed.
		// Otherwise, persist the pending changes.
		setNextLinkValue(didToggleSettingForNewLink ? nextValue : undefined)

		if (didToggleSettingForNewLink) {
			return
		}

		const newUrl = prependHTTP(nextValue.url)
		const format = createLinkFormat({
			url              : newUrl,
			opensInNewWindow : nextValue.opensInNewTab,
			nofollow         : nextValue.nofollow,
			sponsored        : nextValue.sponsored,
			ugc              : nextValue.ugc,
			title            : nextValue.title
		})

		if (isCollapsed(value) && !isActive) {
			const newText = nextValue.title || newUrl
			const toInsert = applyFormat(
				create({ text: newText }),
				format,
				0,
				newText.length
			)
			onChange(insert(value, toInsert))
		} else {
			onChange(applyFormat(value, format))
		}

		// Focus should only be shifted back to the formatted segment when the
		// URL is submitted.
		if (!didToggleSetting) {
			stopAddingLink()
		}

		if (!isValidHref(newUrl)) {
			speak(
				__(
					'Warning: the link has been inserted but may have errors. Please test it.',
					'all-in-one-seo-pack'
				),
				'assertive'
			)
		} else if (isActive) {
			speak(__('Link edited.', 'all-in-one-seo-pack'), 'assertive')
		} else {
			speak(__('Link inserted.', 'all-in-one-seo-pack'), 'assertive')
		}
	}

	// eslint-disable-next-line one-var
	let anchorValue
	if (versionCompare(window.aioseo.wpVersion, '6.1', '>=')) {
		anchorValue = anchorRef
	}
	if (versionCompare(window.aioseo.wpVersion, '6.6', '>=')) {
		anchorValue = anchor
	}

	return (
		<Popover
			key={ mountingKey }
			anchor={ anchorValue }
			focusOnMount={ addingLink ? 'firstElement' : false }
			onClose={ stopAddingLink }
		>
			<LinkControl
				value={ linkValue }
				onChange={ onChangeLink }
				forceIsEditingLink={ addingLink }
				selectedText={ selectedText }
			/>
		</Popover>
	)
}

export default withSpokenMessages(InlineLinkUI)