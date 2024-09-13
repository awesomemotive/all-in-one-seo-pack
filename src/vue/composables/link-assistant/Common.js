import { escapeRegex } from '@/vue/utils/regex'

export const useCommon = () => {
	const findTargetBlock = (blocks, phraseHtml) => {
		const escapedOriginalPhraseHtml = escapeRegex(phraseHtml)

		let targetBlockId  = null
		blocks.forEach(block => {
			if (targetBlockId || !block.attributes) {
				return
			}

			if (block.attributes.content && -1 !== block.attributes.content.search(escapedOriginalPhraseHtml)) {
				targetBlockId = block.clientId
				return
			}

			// Check if we can find the link in an inner block.
			if (!block.innerBlocks) {
				return
			}

			const possibleTargetBlockId = findTargetBlock(block.innerBlocks, phraseHtml)
			if (possibleTargetBlockId) {
				targetBlockId = possibleTargetBlockId
			}
		})

		return targetBlockId
	}

	return {
		findTargetBlock
	}
}