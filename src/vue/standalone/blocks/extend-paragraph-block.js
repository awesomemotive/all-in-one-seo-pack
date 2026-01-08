import aiAssistantIcon from '@/vue/standalone/blocks/ai-assistant/icon'

import { __ } from '@/vue/plugins/translations'

import { Button } from '@wordpress/components'
import { createElement, createRoot, flushSync } from '@wordpress/element'
import { createHigherOrderComponent } from '@wordpress/compose'

const td      = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	placeholder : __('Type / to choose a block or // to use AI Assistant', td)
}

let $cachedAiAssistantButton = null

/**
 * Check if a block is a root-level paragraph block.
 *
 * @param {Object} block       The block object to check.
 * @param {Object} blockEditor The block editor selector object.
 *
 * @returns {boolean} True if the block is a root-level paragraph block.
 */
const isRootLevelParagraphBlock = (block, blockEditor) => {
	return (
		block &&
		'core/paragraph' === block.name &&
		!blockEditor.getBlockRootClientId(block.clientId)
	)
}

/**
 * Extend the paragraph block placeholder for root-level blocks only.
 * Uses a HOC to modify the placeholder prop without changing stored attributes.
 *
 * @param {Object} options                  Options object.
 * @param {Object} options.aiAssistantStore The AI Assistant store instance.
 *
 * @returns {void}
 */
export const extendParagraphPlaceholder = ({ aiAssistantStore }) => {
	if (!aiAssistantStore.extend.paragraphPlaceholder) {
		return
	}

	const { addFilter } = window.wp?.hooks || {}
	const { select }    = window.wp?.data || {}
	if (!addFilter || !select) {
		return
	}

	addFilter(
		'editor.BlockEdit',
		'aioseo/paragraph-placeholder',
		createHigherOrderComponent(
			(BlockEdit) => (props) => {
				const blockEditor = select('core/block-editor')

				// Only modify root-level paragraph blocks when AI Assistant is available.
				if (!aiAssistantStore.isBlockAvailable || !isRootLevelParagraphBlock(props, blockEditor)) {
					return createElement(BlockEdit, props)
				}

				return createElement(BlockEdit, {
					...props,
					attributes : {
						...props.attributes,
						placeholder : props.attributes.placeholder || strings.placeholder
					}
				})
			},
			'aioseoExtendParagraphPlaceholder'
		)
	)
}

/**
 * Check for "//" shortcut and insert AI Assistant block if detected.
 *
 * @param {Object} options                  Options object.
 * @param {Object} options.aiAssistantStore The AI Assistant store instance.
 *
 * @returns {void}
 */
export const checkAiAssistantShortcut = ({ aiAssistantStore }) => {
	if (!aiAssistantStore.extend.paragraphPlaceholder) {
		return
	}

	const { select, dispatch } = window.wp?.data || {}
	const { createBlock }      = window.wp?.blocks || {}
	if (!select || !dispatch || !createBlock) {
		return
	}

	const blockEditor   = select('core/block-editor')
	const selectedBlock = blockEditor.getSelectedBlock()

	// Only trigger for root-level paragraph blocks.
	if (!isRootLevelParagraphBlock(selectedBlock, blockEditor)) {
		return
	}

	const content = selectedBlock.attributes?.content || ''

	// Only trigger if the content is "//" and the cursor is at the end (position 2).
	if ('//' === content.trim()) {
		const selectionEnd = blockEditor.getSelectionEnd()
		if (2 === selectionEnd?.offset) {
			const aiAssistantBlock = createBlock('aioseo/ai-assistant')

			// Replace the paragraph block with the AI Assistant block.
			dispatch('core/block-editor').replaceBlock(
				selectedBlock.clientId,
				aiAssistantBlock
			)
		}
	}
}

/**
 * Inject a custom AI Assistant button next to the "Add Block" button.
 *
 * @returns {void}
 */
export const extendBlockEditorInserterButton = ({ aiAssistantStore }) => {
	if (!aiAssistantStore.extend.blockEditorInserterButton) {
		return
	}

	if (!$cachedAiAssistantButton) {
		const $tempContainer = document.createElement('div')
		const root           = createRoot($tempContainer)

		flushSync(() => {
			root.render(
				createElement(Button, {
					className             : 'aioseo-ai-assistant-inserter-btn',
					icon                  : aiAssistantIcon,
					iconSize              : '18',
					variant               : 'secondary',
					size                  : 'small',
					__next40pxDefaultSize : true,
					title                 : __('Insert AI Assistant Block', td),
					style                 : {
						marginLeft      : '8px',
						verticalAlign   : 'top',
						padding         : '0',
						minWidth        : 'auto',
						width           : '24px',
						backgroundColor : '#fff',
						display         : 'inline-flex'
					}
				})
			)
		})

		$cachedAiAssistantButton = $tempContainer.firstChild?.cloneNode(true)

		$cachedAiAssistantButton.addEventListener('click', e => {
			e.preventDefault()
			e.stopPropagation()

			const wpSelect      = window.wp?.data?.select
			const wpDispatch    = window.wp?.data?.dispatch
			const { createBlock } = window.wp?.blocks || {}
			if (!wpSelect || !wpDispatch || !createBlock) {
				return
			}

			const aiAssistantBlock = createBlock('aioseo/ai-assistant')
			const wpBlockEditor    = wpSelect('core/block-editor')
			const currentBlock     = wpBlockEditor.getSelectedBlock()

			if (
				isRootLevelParagraphBlock(currentBlock, wpBlockEditor) &&
				!currentBlock?.attributes?.content?.trim()
			) {
				wpDispatch('core/block-editor').replaceBlock(
					currentBlock.clientId,
					aiAssistantBlock
				)
			} else {
				const insertionPoint = wpBlockEditor.getBlockInsertionPoint() || {}

				wpDispatch('core/block-editor').insertBlock(
					aiAssistantBlock,
					insertionPoint.index,
					insertionPoint.rootClientId
				)
			}
		})

		$tempContainer.remove()
	}

	const { select } = window.wp?.data || {}
	if (!select) {
		return
	}

	const $editor = document.getElementById('editor')
	if (!$editor) {
		return
	}

	const blockEditor     = select('core/block-editor')
	const selectedBlock   = blockEditor.getSelectedBlock()
	const $existingButton = $editor.querySelector('.aioseo-ai-assistant-inserter-btn')

	// Remove the button immediately if the selected block is not a root-level paragraph block.
	if (!isRootLevelParagraphBlock(selectedBlock, blockEditor)) {
		$existingButton?.remove()
		return
	}

	setTimeout(() => {
		// Don't add the button if it already exists.
		if ($editor.querySelector('.aioseo-ai-assistant-inserter-btn')) {
			return
		}

		const $addBlockButton = $editor.querySelector('.block-editor-inserter__toggle')
		if (!$addBlockButton || $addBlockButton.closest('.is-vertical')) {
			return
		}

		$addBlockButton.after($cachedAiAssistantButton)
	})
}