import indicesProcessing from '../../stringProcessing/indices'
import stripSpaces from '../../stringProcessing/stripSpaces.js'
import { normalizeSingle as normalizeSingleQuotes } from '../../stringProcessing/quotes.js'
import arrayToRegex from '../../stringProcessing/createRegexFromArray.js'
import getWordIndices from './getIndicesWithRegex.js'
import includesIndex from '../../stringProcessing/includesIndex'
import followsIndex from '../../stringProcessing/followsIndex'

import { isUndefined, includes, map, forEach, filter } from 'lodash-es'

// English-specific variables and imports.
import SentencePartEnglish from '../../english/passiveVoice/SentencePart'

import auxiliariesEnglishFactory from '../../english/passiveVoice/auxiliaries.js'
import stopwordsEnglishFactory from '../../english/passiveVoice/stopwords.js'

// French-specific variables and imports.
import SentencePartFrench from '../../french/passiveVoice/SentencePart'

import auxiliariesFrenchFactory from '../../french/passiveVoice/auxiliaries.js'
import stopwordsFrenchFactory from '../../french/passiveVoice/stopwords.js'

// Spanish-specific variables and imports.
import SentencePartSpanish from '../../spanish/passiveVoice/SentencePart'

import auxiliariesSpanishFactory from '../../spanish/passiveVoice/auxiliaries.js'
import stopwordsSpanishFactory from '../../spanish/passiveVoice/stopwords.js'

// Portuguese-specific variables and imports.
import SentencePartPortuguese from '../../portuguese/passiveVoice/SentencePart'
import auxiliariesPortugueseFactory from '../../portuguese/passiveVoice/auxiliaries.js'
import stopwordsPortugueseFactory from '../../portuguese/passiveVoice/stopwords.js'

// Italian-specific variables and imports.
import SentencePartItalian from '../../italian/passiveVoice/SentencePart'
import auxiliariesItalianFactory from '../../italian/passiveVoice/auxiliaries.js'
import stopwordsItalianFactory from '../../italian/passiveVoice/stopwords.js'
const getIndicesOfList = indicesProcessing.getIndicesByWordList
const filterIndices = indicesProcessing.filterIndices
const sortIndices = indicesProcessing.sortIndices
const auxiliariesEnglish = auxiliariesEnglishFactory().all

const stopwordsEnglish = stopwordsEnglishFactory()
const stopCharacterRegexEnglish = /([:,]|('ll)|('ve))(?=[ \n\r\t'"+\-»«‹›<>])/ig
const verbEndingInIngRegex = /\w+ing(?=$|[ \n\r\t.,'()"+\-;!?:/»«‹›<>])/ig
const ingExclusionArray = [ 'king', 'cling', 'ring', 'being', 'thing', 'something', 'anything' ]
const auxiliariesFrench = auxiliariesFrenchFactory()
const stopwordsFrench = stopwordsFrenchFactory()
const stopCharacterRegexFrench = /(,)(?=[ \n\r\t'"+\-»«‹›<>])/ig
const followingAuxiliaryExceptionWordsFrench = [ 'le', 'la', 'les', 'une', 'l\'un', 'l\'une' ]
const reflexivePronounsFrench = [ 'se', 'me', 'te', 's\'y', 'm\'y', 't\'y', 'nous nous', 'vous vous' ]
const directPrecedenceExceptionRegexFrench = arrayToRegex(reflexivePronounsFrench)
const elisionAuxiliaryExceptionWords = [ 'c\'', 's\'', 'peut-' ]
const elisionAuxiliaryExceptionRegex = arrayToRegex(elisionAuxiliaryExceptionWords, true)
const auxiliariesSpanish = auxiliariesSpanishFactory()
const stopwordsSpanish = stopwordsSpanishFactory()
const followingAuxiliaryExceptionWordsSpanish = [ 'el', 'la', 'los', 'las', 'una' ]
const auxiliariesPortuguese = auxiliariesPortugueseFactory()
const stopwordsPortuguese = stopwordsPortugueseFactory()
const followingAuxiliaryExceptionWordsPortuguese = [ 'o', 'a', 'os', 'as', 'um', 'ums', 'uma', 'umas' ]
const auxiliariesItalian = auxiliariesItalianFactory()
const stopwordsItalian = stopwordsItalianFactory()
const followingAuxiliaryExceptionWordsItalian = [ 'il', 'i', 'la', 'le', 'lo', 'gli', 'uno', 'una' ]
const reflexivePronounsItalian = [ 'mi', 'ti', 'si', 'ci', 'vi' ]
const directPrecedenceExceptionRegexItalian = arrayToRegex(reflexivePronounsItalian)

/*
 * Variables applying to multiple languages
 * This regex applies to Spanish, Italian and Portuguese.
 */
const stopCharacterRegexOthers = /([:,])(?=[ \n\r\t'"+\-»«‹›<>])/ig

// The language-specific variables used to split sentences into sentence parts.
const languageVariables = {
	en : {
		stopwords          : stopwordsEnglish,
		auxiliaryRegex     : arrayToRegex(auxiliariesEnglish),
		SentencePart       : SentencePartEnglish,
		auxiliaries        : auxiliariesEnglish,
		stopCharacterRegex : stopCharacterRegexEnglish
	},
	fr : {
		stopwords                        : stopwordsFrench,
		auxiliaryRegex                   : arrayToRegex(auxiliariesFrench),
		SentencePart                     : SentencePartFrench,
		auxiliaries                      : auxiliariesFrench,
		stopCharacterRegex               : stopCharacterRegexFrench,
		followingAuxiliaryExceptionRegex : arrayToRegex(followingAuxiliaryExceptionWordsFrench),
		directPrecedenceExceptionRegex   : directPrecedenceExceptionRegexFrench
	},
	es : {
		stopwords                        : stopwordsSpanish,
		auxiliaryRegex                   : arrayToRegex(auxiliariesSpanish),
		SentencePart                     : SentencePartSpanish,
		auxiliaries                      : auxiliariesSpanish,
		stopCharacterRegex               : stopCharacterRegexOthers,
		followingAuxiliaryExceptionRegex : arrayToRegex(followingAuxiliaryExceptionWordsSpanish)
	},
	pt : {
		stopwords                        : stopwordsPortuguese,
		auxiliaryRegex                   : arrayToRegex(auxiliariesPortuguese),
		SentencePart                     : SentencePartPortuguese,
		auxiliaries                      : auxiliariesPortuguese,
		stopCharacterRegex               : stopCharacterRegexOthers,
		followingAuxiliaryExceptionRegex : arrayToRegex(followingAuxiliaryExceptionWordsPortuguese)
	},
	it : {
		stopwords                        : stopwordsItalian,
		auxiliaryRegex                   : arrayToRegex(auxiliariesItalian),
		SentencePart                     : SentencePartItalian,
		auxiliaries                      : auxiliariesItalian,
		stopCharacterRegex               : stopCharacterRegexOthers,
		followingAuxiliaryExceptionRegex : arrayToRegex(followingAuxiliaryExceptionWordsItalian),
		directPrecedenceExceptionRegex   : directPrecedenceExceptionRegexItalian
	}
}

/**
 * Gets active verbs (ending in ing) to determine sentence breakers in English.
 *
 * @param {string} sentence The sentence to get the active verbs from.
 * @returns {Array} The array with valid matches.
 */
const getVerbsEndingInIng = function (sentence) {
	// Matches the sentences with words ending in ing.
	const matches = sentence.match(verbEndingInIngRegex) || []
	// Filters out words ending in -ing that aren't verbs.
	return filter(matches, function (match) {
		return !includes(ingExclusionArray, stripSpaces(match))
	})
}

/**
 * Gets stop characters to determine sentence breakers.
 *
 * @param {string} sentence The sentence to get the stop characters from.
 * @param {string} language The language for which to get the stop characters.
 * @returns {Array} The array with stop characters.
 */
const getStopCharacters = function (sentence, language) {
	const stopCharacterRegex = languageVariables[language].stopCharacterRegex
	let match
	const matches = []

	stopCharacterRegex.lastIndex = 0

	while (null !== (match = stopCharacterRegex.exec(sentence))) {
		matches.push(
			{
				index : match.index,
				match : match[0]
			}
		)
	}
	return matches
}

/**
 * Filters auxiliaries preceded by a reflexive pronoun.
 *
 * @param {string} text The text part in which to check.
 * @param {Array} auxiliaryMatches The auxiliary matches for which to check.
 * @param {string} language The language for which to check auxiliary precedence exceptions.
 *
 * @returns {Array} The filtered list of auxiliary indices.
 */
const auxiliaryPrecedenceExceptionFilter = function (text, auxiliaryMatches, language) {
	const directPrecedenceExceptionMatches = getWordIndices(text, languageVariables[language].directPrecedenceExceptionRegex)

	forEach(auxiliaryMatches, function (auxiliaryMatch) {
		if (includesIndex(directPrecedenceExceptionMatches, auxiliaryMatch.index)) {
			auxiliaryMatches = auxiliaryMatches.filter(function (auxiliaryObject) {
				return auxiliaryObject.index !== auxiliaryMatch.index
			})
		}
	})

	return auxiliaryMatches
}

/**
 * Filters auxiliaries followed by a word on the followingAuxiliaryExceptionWords list.
 *
 * @param {string} text The text part in which to check.
 * @param {Array} auxiliaryMatches The auxiliary matches for which to check.
 * @param {string} language The language for which to filter the auxiliaries.
 * @returns {Array} The filtered list of auxiliary indices.
 */
const followingAuxiliaryExceptionFilter = function (text, auxiliaryMatches, language) {
	const followingAuxiliaryExceptionRegex = languageVariables[language].followingAuxiliaryExceptionRegex
	const followingAuxiliaryExceptionMatches = getWordIndices(text, followingAuxiliaryExceptionRegex)

	forEach(auxiliaryMatches, function (auxiliaryMatch) {
		if (followsIndex(followingAuxiliaryExceptionMatches, auxiliaryMatch)) {
			auxiliaryMatches = auxiliaryMatches.filter(function (auxiliaryObject) {
				return auxiliaryObject.index !== auxiliaryMatch.index
			})
		}
	})

	return auxiliaryMatches
}

/**
 * Filters auxiliaries preceded by an elided word (e.g., s') on the elisionAuxiliaryExceptionWords list.
 *
 * @param {string} text The text part in which to check.
 * @param {Array} auxiliaryMatches The auxiliary matches for which to check.
 * @returns {Array} The filtered list of auxiliary indices.
 */
const elisionAuxiliaryExceptionFilter = function (text, auxiliaryMatches) {
	const elisionAuxiliaryExceptionMatches = getWordIndices(text, elisionAuxiliaryExceptionRegex)

	forEach(auxiliaryMatches, function (auxiliaryMatch) {
		if (includesIndex(elisionAuxiliaryExceptionMatches, auxiliaryMatch.index, false)) {
			auxiliaryMatches = auxiliaryMatches.filter(function (auxiliaryObject) {
				return auxiliaryObject.index !== auxiliaryMatch.index
			})
		}
	})

	return auxiliaryMatches
}

/**
 * Gets the indexes of sentence breakers (auxiliaries, stopwords and stop characters;
 * in English also active verbs) to determine sentence parts.
 * Indices are filtered because there could be duplicate matches, like "even though" and "though".
 * In addition, 'having' will be matched both as a -ing verb as well as an auxiliary.
 *
 * @param {string} sentence The sentence to check for indices of sentence breakers.
 * @param {string} language The language for which to match the sentence breakers.
 * @returns {Array} The array with valid indices to use for determining sentence parts.
 */
const getSentenceBreakers = function (sentence, language) {
	sentence = sentence.toLocaleLowerCase()
	const stopwords = languageVariables[language].stopwords
	const auxiliaries = languageVariables[language].auxiliaries
	let auxiliaryIndices = getIndicesOfList(auxiliaries, sentence),
		indices
	const stopwordIndices = getIndicesOfList(stopwords, sentence)
	const stopCharacterIndices = getStopCharacters(sentence, language)
	const ingVerbs = getVerbsEndingInIng(sentence)
	const ingVerbsIndices = getIndicesOfList(ingVerbs, sentence)

	// Concat all indices arrays, filter them and sort them.
	switch (language) {
		case 'fr':
			// Filters auxiliaries matched in the sentence based on a precedence exception filter.
			auxiliaryIndices = auxiliaryPrecedenceExceptionFilter(sentence, auxiliaryIndices, 'fr')
			// Filters auxiliaries matched in the sentence based on a elision exception filter.
			auxiliaryIndices = elisionAuxiliaryExceptionFilter(sentence, auxiliaryIndices)

			indices = [].concat(auxiliaryIndices, stopwordIndices, stopCharacterIndices)
			break
		case 'es':
			indices = [].concat(auxiliaryIndices, stopwordIndices, stopCharacterIndices)
			break
		case 'pt':
			indices = [].concat(auxiliaryIndices, stopwordIndices, stopCharacterIndices)
			break
		case 'it':
			// Filters auxiliaries matched in the sentence based on a precedence exception filter.
			auxiliaryIndices = auxiliaryPrecedenceExceptionFilter(sentence, auxiliaryIndices, 'it')
			indices = [].concat(auxiliaryIndices, stopwordIndices, stopCharacterIndices)
			break
		case 'en':
		default:
			indices = [].concat(auxiliaryIndices, stopwordIndices, ingVerbsIndices, stopCharacterIndices)
			break
	}
	indices = filterIndices(indices)
	return sortIndices(indices)
}

/**
 * Gets the auxiliaries from a sentence.
 *
 * @param {string} sentencePart The part of the sentence to match for auxiliaries.
 * @param {string} language The language for which to match the auxiliaries.
 * @returns {Array} All formatted matches from the sentence part.
 */
const getAuxiliaryMatches = function (sentencePart, language) {
	const auxiliaryRegex = languageVariables[language].auxiliaryRegex
	const auxiliaryMatches = sentencePart.match(auxiliaryRegex) || []
	// An array with the matched auxiliaries and their indices.
	let auxiliaryMatchIndices = getIndicesOfList(auxiliaryMatches, sentencePart)
	// An array with the matched auxiliary verbs (without indices).
	const auxiliaryMatchWords = []

	switch (language) {
		case 'fr':
		case 'es':
		case 'pt':
		case 'it':
			if (('fr' ===  language) || ('it' === language)) {
				// Filters auxiliaries matched in the sentence part based on a precedence exception filter.
				auxiliaryMatchIndices = auxiliaryPrecedenceExceptionFilter(sentencePart, auxiliaryMatchIndices, language)
			}
			// Filters auxiliaries matched in the sentence part based on a exception filter for words following the auxiliary.
			auxiliaryMatchIndices = followingAuxiliaryExceptionFilter(sentencePart, auxiliaryMatchIndices, language)

			forEach(auxiliaryMatchIndices, function (auxiliaryMatchIndex) {
				auxiliaryMatchWords.push(auxiliaryMatchIndex.match)
			})

			return map(auxiliaryMatchWords, function (auxiliaryMatch) {
				return stripSpaces(auxiliaryMatch)
			})
		case 'en':
		default:
			return map(auxiliaryMatches, function (auxiliaryMatch) {
				return stripSpaces(auxiliaryMatch)
			})
	}
}

/**
 * Gets the sentence parts from a sentence by determining sentence breakers.
 *
 * @param {string} sentence The sentence to split up in sentence parts.
 * @param {string} language The language for which to get the sentence parts.
 * @returns {Array} The array with all parts of a sentence that have an auxiliary.
 */
const getSentenceParts = function (sentence, language) {
	const sentenceParts = []
	const auxiliaryRegex = languageVariables[language].auxiliaryRegex
	const SentencePart = languageVariables[language].SentencePart

	sentence = normalizeSingleQuotes(sentence)

	// First check if there is an auxiliary in the sentence.
	if (null === sentence.match(auxiliaryRegex)) {
		return sentenceParts
	}

	const indices = getSentenceBreakers(sentence, language)
	// Get the words after the found auxiliary.
	for (let i = 0; i < indices.length; i++) {
		let endIndex = sentence.length
		if (!isUndefined(indices[i + 1])) {
			endIndex = indices[i + 1].index
		}

		// Cut the sentence from the current index to the endIndex (start of next breaker, of end of sentence).
		const sentencePart = stripSpaces(sentence.substr(indices[i].index, endIndex - indices[i].index))

		const auxiliaryMatches = getAuxiliaryMatches(sentencePart, language)
		// If a sentence part doesn't have an auxiliary, we don't need it, so it can be filtered out.
		if (0 !== auxiliaryMatches.length) {
			sentenceParts.push(new SentencePart(sentencePart, auxiliaryMatches))
		}
	}
	return sentenceParts
}

/**
 * Split the sentence in sentence parts based on auxiliaries.
 *
 * @param {string} sentence The sentence to split in parts.
 * @param {string} language The language for which to get the sentence parts.
 * @returns {Array} A list with sentence parts.
 */
export default function (sentence, language) {
	return getSentenceParts(sentence, language)
}