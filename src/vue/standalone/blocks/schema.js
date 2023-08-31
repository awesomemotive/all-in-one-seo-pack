import {
	usePostEditorStore
} from '@/vue/stores'

import { debounce } from 'lodash-es'
import { flattenBlocks } from '@/vue/utils/helpers'

if ('post' === window.aioseo.currentPost?.context) {
	let blockList = []
	window.wp.data.subscribe(() => {
		checkBlocksLength()
	})

	const checkBlocksLength = debounce(() => {
		const newBlockList = window.wp.data.select('core/block-editor').getBlocks()

		if (newBlockList.length < blockList.length) {
			updateBlockGraphs()
		}

		blockList = newBlockList
	}, 200)

	window.aioseoBus.$on('schemaBlockUpdated', () => { updateBlockGraphs() })

	const updateBlockGraphs = debounce(() => {
		// First, grab all schema supported blocks from the editor.
		let blocks = window.wp.data.select('core/block-editor').getBlocks()
		blocks     = flattenBlocks(blocks) // Extract inner blocks.
		blocks     = blocks.filter(block => block?.attributes?.schemaBlockId)

		// Now, map their type to their attributes so that we can identify their type in the backend.
		blocks = blocks.map((block) => {
			block.attributes.type = block.name
			return block
		})

		const postEditorStore = usePostEditorStore()

		// Then, grab the existing stored block graphs and loop over them to see if any of them were removed.
		const blockGraphs = postEditorStore.currentPost.schema?.blockGraphs || []
		blockGraphs.forEach((blockGraph, blockGraphIndex) => {
			const blockIndex = blocks.findIndex(block => block?.attributes?.schemaBlockId === blockGraph?.schemaBlockId)

			// If no block was found, then it must have been removed.
			if (-1 === blockIndex) {
				blockGraphs.splice(blockGraphIndex, 1)
				return
			}

			const blockAttributes = { ...blocks[blockIndex].attributes }

			// Any block attribute that we aren't watching could get updated to its previous value when we set our blockGraphs.
			// These are known attributes that we don't need.
			const attsToOmit = [
				'backgroundColor',
				'textColor',
				'fontSize',
				'style'
			]

			// Remove the irrelevant attributes before setting our blockGraphs.
			attsToOmit.forEach(att => {
				delete blockAttributes[att]
			})

			// If a block was found, let's update it.
			blockGraphs[blockGraphIndex] = blockAttributes
		})

		// Now, we also need to loop over the new blocks to see if there are any new ones that need to be added.
		blocks.forEach((block) => {
			const blockGraphIndex = blockGraphs.findIndex(blockGraph => blockGraph?.schemaBlockId === block?.attributes?.schemaBlockId)
			if (-1 === blockGraphIndex && block?.attributes) {
				blockGraphs.push(block.attributes)
			}
		})

		postEditorStore.currentPost.schema.blockGraphs = blockGraphs
	}, 200)
}