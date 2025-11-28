import {
	useAiAssistantStore
} from '@/vue/stores'

import aiAssistantIcon from '@/vue/standalone/blocks/ai-assistant/icon'

import { __ } from '@/vue/plugins/translations'

import { Button } from '@wordpress/components'
import { createElement, createRoot, flushSync } from '@wordpress/element'

const td      = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	placeholder : __('Type / to choose a block or // to use AI Assistant', td)
}

let $cachedAiAssistantButton = null

const { addFilter } = window.wp?.hooks || {}
if (addFilter) {
	addFilter(
		'blocks.registerBlockType',
		'aioseo/paragraph-placeholder',
		(settings, name) => {
			if ('core/paragraph' !== name) {
				return settings
			}

			const aiAssistantStore = useAiAssistantStore()
			if (!aiAssistantStore.hasPermission) {
				return settings
			}

			return {
				...settings,
				attributes : {
					...settings.attributes,
					placeholder : {
						type    : 'string',
						default : strings.placeholder
					}
				}
			}
		}
	)
}

/**
 * Update the empty editor placeholder (when no blocks exist yet).
 *
 * @returns {void}
 */
export const extendEmptyEditorPlaceholder = () => {
	const { dispatch } = window.wp?.data || {}
	if (!dispatch) {
		return
	}

	setTimeout(() => {
		dispatch('core/editor').updateEditorSettings({
			bodyPlaceholder : strings.placeholder
		})
	})
}

/**
 * Check for "//" shortcut and insert AI Assistant block if detected.
 *
 * @returns {void}
 */
export const checkAiAssistantShortcut = () => {
	const { select, dispatch } = window.wp?.data || {}
	const { createBlock }      = window.wp?.blocks || {}
	if (!select || !dispatch || !createBlock) {
		return
	}

	const selectedBlock = select('core/block-editor').getSelectedBlock()
	if ('core/paragraph' !== selectedBlock?.name) {
		return
	}

	const content = selectedBlock.attributes?.content || ''

	// Only trigger if the content is "//" and the cursor is at the end (position 2).
	if ('//' === content.trim()) {
		const selectionEnd = select('core/block-editor')?.getSelectionEnd()
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

			const { select, dispatch } = window.wp?.data || {}
			const { createBlock }      = window.wp?.blocks || {}
			if (!select || !dispatch || !createBlock) {
				return
			}

			const aiAssistantBlock = createBlock('aioseo/ai-assistant')
			const selectedBlock    = select('core/block-editor').getSelectedBlock()

			if (
				'core/paragraph' === selectedBlock?.name &&
				!selectedBlock?.attributes?.content?.trim()
			) {
				dispatch('core/block-editor').replaceBlock(
					selectedBlock.clientId,
					aiAssistantBlock
				)
			} else {
				const insertionPoint = select('core/block-editor').getBlockInsertionPoint() || {}

				dispatch('core/block-editor').insertBlock(
					aiAssistantBlock,
					insertionPoint.index,
					insertionPoint.rootClientId
				)
			}
		})

		$tempContainer.remove()
	}

	setTimeout(() => {
		const $editor = document.getElementById('editor')
		if (!$editor) {
			return
		}

		const $addBlockButton = $editor.querySelector('.block-editor-inserter__toggle')
		if (
			!$addBlockButton ||
			$addBlockButton.closest('.is-vertical') ||
			$editor.querySelector('.aioseo-ai-assistant-inserter-btn')
		) {
			return
		}

		$addBlockButton.after($cachedAiAssistantButton)
	})
}