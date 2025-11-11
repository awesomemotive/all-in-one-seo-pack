import { createApp } from 'vue'

import {
	loadPiniaStores,
	useRootStore,
	useTableOfContentsStore
} from '@/vue/stores'

import App from './vue/App'
import SelectMode from './vue/SelectMode.vue'

import { Sidebar } from './vue/Sidebar'

import { deepCopy, cleanHtml } from '@/vue/standalone/blocks/utils'
import { observeElement } from '@/vue/utils/helpers'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'

import { flattenHeadings, MODES } from './helpers'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const wp                      = window.wp
const { useState, useEffect } = wp.element
const { InspectorControls }   = wp.blockEditor
const { PanelBody }           = wp.components
const { useSelect }           = wp.data
const blockEditorStore        = wp.blockEditor.store
const { isTyping }            = wp.data.select(blockEditorStore) || { isTyping: () => null }

// Constants
const BLOCK_TYPES = {
	HEADING : 'core/heading',
	FAQ     : 'aioseo/faq',
	TOC     : 'aioseo/table-of-contents'
}

const appInstances   = new Map()
let hasInitialized = [],
	mainApp          = null

// Utility Functions
const getHeadingContent = (headingAttributes) => {
	return headingAttributes.question || // Support FAQ block headings.
				 headingAttributes.content?.text || // Heading block content WP 6.5 and up.
				 headingAttributes.content || '' // Heading block content WP 6.4 and below.
}

const isValidHeading = (headingContent, tableOfContentsIndex, headingIndex) => {
	return 'string' === typeof headingContent &&
				 '' !== headingContent &&
				 tableOfContentsIndex <= headingIndex
}

const processHeading = (headingAttributes, headingIndex, tableOfContentsIndex, existingHeadings = [], blockClientId) => {
	const hasAnchor = 'string' === typeof headingAttributes?.anchor && '' !== headingAttributes.anchor
	const headingLevel = headingAttributes.level || headingAttributes.tagName.replace('h', '')

	if ('div' === headingLevel) {
		return null
	}

	let headingContent = getHeadingContent(headingAttributes)

	if (!isValidHeading(headingContent, tableOfContentsIndex, headingIndex)) {
		return null
	}

	headingContent = cleanHtml(headingContent.replace(/(<br *\/?>)+/g, ' '), true)

	if (!hasAnchor && !isTyping()) {
		const rootStore = useRootStore()
		headingAttributes.anchor = rootStore.aioseo.data.blocks.toc.hashPrefix + cleanForSlug(`${headingContent}-${headingIndex}`)
	}

	// Find matching existing heading to preserve order and visibility
	const existingHeading = existingHeadings.find(h =>
		h.content === headingContent &&
		h.level === Number(headingLevel) &&
		h.anchor === (hasAnchor ? headingAttributes.anchor : '')
	)

	return {
		id          : headingIndex,
		content     : headingContent,
		level       : Number(headingLevel),
		anchor      : hasAnchor ? headingAttributes.anchor : '',
		editedOrder : existingHeading?.editedOrder ?? headingIndex,
		hidden      : existingHeading?.hidden || false,
		blockClientId
	}
}

const getBlockClientIds = (clientId, allBlockClientIds, tableOfContentsIndex, selectedBlockName, getBlockName) => {
	const currentBlock = window.wp.data.select(blockEditorStore).getBlockAttributes(clientId)

	// If not in synced mode or not a TOC block, just return headings after this block
	if ('synced' !== currentBlock?.mode || BLOCK_TYPES.TOC !== selectedBlockName) {
		return allBlockClientIds.slice(tableOfContentsIndex).map((blockId) => blockId)
	}

	// Find the next TOC block
	const nextTocIndex = findNextTocIndex(allBlockClientIds, tableOfContentsIndex, getBlockName)

	// If no next TOC block found, return all headings after this block
	if (-1 === nextTocIndex) {
		return allBlockClientIds.slice(tableOfContentsIndex).map((blockId) => blockId)
	}

	// Return headings between this TOC block and the next one
	return allBlockClientIds
		.slice(tableOfContentsIndex, nextTocIndex)
		.map((blockId) => blockId)
}

const findNextTocIndex = (allBlockClientIds, tableOfContentsIndex, getBlockName) => {
	// Start looking from the block after the current TOC
	const nextBlocks = allBlockClientIds.slice(tableOfContentsIndex + 1)
	const nextIndex = nextBlocks.findIndex((blockId) => BLOCK_TYPES.TOC === getBlockName(blockId))

	// If found, return the actual index in the full array
	return -1 === nextIndex ? -1 : tableOfContentsIndex + 1 + nextIndex
}

export const edit = ({ setAttributes, attributes, clientId, className, isSelected }) => {
	const { headings = [] } = attributes
	// State to track headings locally
	const [ localHeadings, setLocalHeadings ] = useState(attributes?.headings || [])
	const tableOfContentsStore = useTableOfContentsStore()

	// Subscribe to block removal
	const unsubscribe = window.wp.data.subscribe(() => {
		const block = window.wp.data.select('core/block-editor').getBlock(clientId)
		if (!block) {
			// Cleanup app instances when block is removed
			const appInstance = appInstances.get(clientId)
			if (appInstance?.mainApp) {
				appInstance.mainApp.unmount()
			}

			if (appInstance?.selectedModeApp) {
				appInstance.selectedModeApp.unmount()
			}

			appInstances.delete(clientId)

			tableOfContentsStore.removeClientId(clientId)
			hasInitialized = hasInitialized.filter((id) => id !== clientId)
			unsubscribe()
		}
	})

	// If we have headings, mount our Vue menu.
	const targetElementIdForModeSelection = `aioseo-mode-selection-${clientId}`

	// Get post content from the editor
	const postContent = useSelect((select) => {
		const { getEditedPostAttribute } = select('core/editor')

		return getEditedPostAttribute('content')
	}, [])

	const getContentHeadings = () => {
		const {
			getBlockAttributes,
			getBlockName,
			getBlocks
		} = wp.data.select('core/block-editor')
		// Build a consistent flat list of all block client IDs (including nested blocks)
		const topLevelBlocks = getBlocks()
		const allBlockClientIds = []

		// Recursively collect all block client IDs including nested ones
		const collectClientIds = (blocks) => {
			blocks.forEach(block => {
				allBlockClientIds.push(block.clientId)
				if (block.innerBlocks && 0 < block.innerBlocks.length) {
					collectClientIds(block.innerBlocks)
				}
			})
		}
		collectClientIds(topLevelBlocks)

		// Find this TOC's position in the flattened list
		const tableOfContentsIndex = allBlockClientIds.indexOf(clientId)

		// Get relevant block client IDs based on mode
		const relevantBlockClientIds = getBlockClientIds(
			clientId,
			allBlockClientIds,
			tableOfContentsIndex,
			getBlockName(clientId),
			getBlockName
		)

		const result = []
		relevantBlockClientIds.forEach((blockClientId) => {
			const blockName = getBlockName(blockClientId)

			if (BLOCK_TYPES.HEADING !== blockName && BLOCK_TYPES.FAQ !== blockName) {
				return []
			}

			const headingAttributes = getBlockAttributes(blockClientId)
			const headingIndex = allBlockClientIds.indexOf(blockClientId)

			const heading = processHeading(headingAttributes, headingIndex, tableOfContentsIndex, localHeadings, blockClientId)
			if (heading) {
				result.push(heading)
			}
		})

		return result
	}

	// Recursive function to sort headings and their subheadings by editedOrder
	const sortHeadingsRecursively = (headings) => {
		if (!headings || !Array.isArray(headings)) {
			return headings
		}

		// Sort the current level of headings
		const sortedHeadings = [ ...headings ].sort((a, b) => a.editedOrder - b.editedOrder)

		// Recursively sort subheadings for each heading
		return sortedHeadings.map(heading => ({
			...heading,
			headings : sortHeadingsRecursively(heading.headings)
		}))
	}

	// Sort headings by content (ascending) for comparison
	const sortByContent = (headings) => {
		if (!headings || !Array.isArray(headings)) return []

		return [ ...headings ].sort((a, b) => {
		// Primary sort: content (case-insensitive)
			const contentCompare = a.content.toLowerCase().localeCompare(b.content.toLowerCase())
			if (0 !== contentCompare) return contentCompare

			// Secondary sort: level
			if (a.level !== b.level) return a.level - b.level

			// Tertiary sort: anchor
			return (a.anchor || '').localeCompare(b.anchor || '')
		})
	}

	// Parse headings from post content
	const parseHeadings = () => {
		const existingHeadings = flattenHeadings(deepCopy(headings || []))
		const contentHeadings  = getContentHeadings()

		const newHeadings = contentHeadings.map((block, index) => {
			const existingHeading = existingHeadings.find(h =>
				h.content === block.content &&
				h.level === Number(block.level) &&
				h.anchor === block.anchor
			)

			return {
				content       : block.content,
				level         : block.level,
				anchor        : block.anchor,
				blockClientId : block.blockClientId,
				hidden        : existingHeading?.hidden || false,
				editedContent : existingHeading?.editedContent ?? '',
				editedOrder   : existingHeading?.editedOrder ?? index + 1,
				headings      : existingHeading?.headings || []
			}
		})

		// Organize subheadings under their parent headings
		const structuredHeadings = []
		const stack = []

		newHeadings.forEach((heading) => {
			// Find the appropriate parent in the stack
			while (0 < stack.length && stack[stack.length - 1].level >= heading.level) {
				stack.pop()
			}

			const newHeading = { ...heading, headings: [] }

			if (0 === stack.length) {
				// This is a top-level heading
				structuredHeadings.push(newHeading)
			} else {
				// This is a subheading - add it to the parent's headings array
				stack[stack.length - 1].headings.push(newHeading)
			}

			// Add this heading to the stack for potential future subheadings
			stack.push(newHeading)
		})

		if (attributes?.reOrdered) {
			// Use recursive sorting instead of just sorting top-level headings
			return sortHeadingsRecursively(structuredHeadings)
		}

		return structuredHeadings
	}

	const updateHeadings = () => {
		const newHeadings = parseHeadings()

		if (0 === newHeadings.length) {
			return
		}

		const normalizedNewHeadings = sortByContent(newHeadings)?.map(h => ({
			content       : h.content,
			level         : h.level,
			anchor        : h.anchor,
			blockClientId : h.blockClientId,
			hidden        : h.hidden,
			editedContent : h.editedContent
		}))

		const normalizedHeadings = sortByContent(headings)?.map(h => ({
			content       : h.content,
			level         : h.level,
			anchor        : h.anchor,
			blockClientId : h.blockClientId,
			hidden        : h.hidden,
			editedContent : h.editedContent
		}))

		// Only update if headings have changed
		if (JSON.stringify(normalizedNewHeadings) !== JSON.stringify(normalizedHeadings)) {
			setLocalHeadings(newHeadings)
			setAttributes({ headings: newHeadings })
			window.aioseoBus.$emit('updateToc' + clientId)
		}
	}

	const renderVueComponents = () => {
		const targetElementId = `aioseo-${clientId}`
		const targetElement = document.getElementById(targetElementId)

		// The hasInitialized value is unique to this instance. Check if it's new.
		// When a page is loaded with an existing block, check that the DOM has loaded the editors block HTML.
		// When a block is first added, the DOM check would have already come back false. But isSelected will be true.
		if (
			(!hasInitialized.includes(clientId) || (targetElement && !targetElement.firstChild)) &&
				(isSelected || document.querySelector(`[data-block="${clientId}"]`))
		) {
			if (!hasInitialized.includes(clientId)) {
				hasInitialized.push(clientId)
			}

			// Initialize the block state with any existing attributes that were saved previously or simply the defaults.
			tableOfContentsStore.addClientId(clientId)

			observeElement({
				id      : targetElementId,
				parent  : document.querySelector('.block-editor'),
				subtree : true,
				done    : function (el) {
					const existingInstance = appInstances.get(clientId)
					if (existingInstance) {
						existingInstance.unmount()
					}

					mainApp = createApp({
						...App,
						name  : 'Blocks/TableOfContents',
						props : [ 'clientId' ]
					}, { clientId })

					// Use the pinia store.
					loadPiniaStores(mainApp)

					mainApp.mount(el)

					appInstances.set(clientId, mainApp)
				}
			})
		}
	}

	renderVueComponents()

	useEffect(() => {
		if (null === attributes?.mode && 1 === tableOfContentsStore.clientIds.length) {
			setAttributes({ mode: MODES.STANDALONE })
		}

		updateHeadings()
	}, [ postContent, attributes ])

	useEffect(() => {
		renderVueComponents()
	}, [ attributes, clientId ])

	// Show mode selection for second TOC block.
	if (!attributes?.mode) {
		observeElement({
			id      : targetElementIdForModeSelection,
			parent  : document.querySelector('.block-editor'),
			subtree : true,
			done    : function (el) {
				const existingInstance = appInstances.get(clientId)
				if (existingInstance) {
					existingInstance.unmount()
				}

				const selectedModeApp = createApp({
					...SelectMode,
					name  : 'Blocks/TableOfContents',
					props : [ 'clientId' ]
				}, {
					clientId
				})
				selectedModeApp.mount(el)

				appInstances.set(clientId, selectedModeApp)
			}
		})

		return (
			<div className={className}>
				<div id={`aioseo-mode-selection-${clientId}`}></div>
			</div>
		)
	}

	return (
		<div className={className}>
			<div id={`aioseo-${clientId}`}></div>
			<InspectorControls>
				<PanelBody title={__('Table of Contents Settings', td)}>
					<Sidebar
						attributes={attributes}
						setAttributes={setAttributes}
						clientId={clientId}
					/>
				</PanelBody>
			</InspectorControls>
		</div>
	)
}