import isEmpty from 'lodash/isEmpty'
import forEach from 'lodash/forEach'

/**
 * Checks whether a given word precedes a participle directly or indirectly.
 *
 * @param {Array} precedingWords The array of objects with matches and indices.
 * @param {number} participleIndex The index of the participle.
 *
 * @returns {boolean} Returns true if the participle is preceded by a given word, otherwise returns false.
 */
export default function (precedingWords, participleIndex) {
	if (isEmpty(precedingWords)) {
		return false
	}

	var precedingWordsIndices = []
	forEach(precedingWords, function (precedingWord) {
		var precedingWordsIndex = precedingWord.index
		precedingWordsIndices.push(precedingWordsIndex)
	})

	var matches = []
	forEach(precedingWordsIndices, function (precedingWordsIndex) {
		// + 1 because the beginning word boundary is not included in the passive participle match
		if (precedingWordsIndex + 1 < participleIndex) {
			matches.push(precedingWordsIndex)
		}
	})

	if (0 < matches.length) {
		return true
	}
	return false
}