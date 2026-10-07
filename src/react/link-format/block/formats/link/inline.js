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

		// Nothing can be applied before the URL is submitted, and a format built without an
		// href crashes the editor once the block is serialized, so hold every pending value.
		if (undefined === nextValue.url) {
			setNextLinkValue(nextValue)
			return
		}

		// The link is about to be assigned, so the pending values are flushed.
		setNextLinkValue(undefined)

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
			// A picked suggestion supplies its own label; the title attribute is not a label.
			const newText = nextValue.suggestionLabel || newUrl
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

	// From 6.1 on, anchor and place the popover the way core's own link format does. A raw
	// selection range misplaces it once the selection leaves the editable, which core's
	// anchor guards against. Only 6.1/6.2 read `value`.
	const isFloatingPopover = versionCompare(window.aioseo.wpVersion, '6.1', '>=')

	let anchorValue = anchorRef
	if (isFloatingPopover && useAnchor) {
		anchorValue = useAnchor({
			editableContentElement : contentRef.current,
			value,
			settings               : {
				...linkSettings,
				isActive
			}
		}) ?? anchorRef
	}

	// The legacy Popover below 6.1 reads only `anchorRef` and `position`; any other prop lands on its DOM node.
	const popoverProps = isFloatingPopover
		? { anchor: anchorValue, placement: 'bottom', shift: true }
		: { anchorRef, position: 'bottom center' }

	return (
		<Popover
			key={mountingKey}
			{ ...popoverProps }
			focusOnMount={addingLink ? 'firstElement' : false}
			onClose={stopAddingLink}
		>
			<LinkControl
				value={linkValue}
				onChange={onChangeLink}
				forceIsEditingLink={addingLink}
				selectedText={selectedText}
			/>
		</Popover>
	)
}

export default withSpokenMessages(InlineLinkUI)