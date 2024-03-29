// Lodash imports.
import { filter, flatMap, isEmpty, negate, memoize } from 'lodash-es'

// Internal dependencies.
import { getBlocks } from '../helpers/html.js'
import { unifyNonBreakingSpace as unifyWhitespace } from './unifyWhitespace.js'
import SentenceTokenizer from './SentenceTokenizer'

// Character classes.
const newLines = '\n\r|\n|\r'

// Regular expressions.
const newLineRegex = new RegExp(newLines)

/**
 * Returns the sentences from a certain block.
 *
 * @param {string} block The HTML inside a HTML block.
 * @returns {Array<string>} The list of sentences in the block.
 */
function getSentencesFromBlock (block) {
	const sentenceTokenizer = new SentenceTokenizer()
	const { tokenizer, tokens } = sentenceTokenizer.createTokenizer()
	sentenceTokenizer.tokenize(tokenizer, block)

	return 0 === tokens.length ? [] : sentenceTokenizer.getSentencesFromTokens(tokens)
}

const getSentencesFromBlockCached = memoize(getSentencesFromBlock)

/**
 * Returns sentences in a string.
 *
 * @param {string} text The string to count sentences in.
 * @returns {Array} Sentences found in the text.
 */
export default function (text) {
	text = unifyWhitespace(text)
	let blocks = getBlocks(text)

	// Split each block on newlines.
	blocks = flatMap(blocks, function (block) {
		return block.split(newLineRegex)
	})

	const sentences = flatMap(blocks, getSentencesFromBlockCached)

	return filter(sentences, negate(isEmpty))
}