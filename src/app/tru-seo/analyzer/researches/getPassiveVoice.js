import Sentence from '../values/Sentence.js'
import getLanguage from './helpers/getLanguage.js'
import getSentences from './stringProcessing/getSentences.js'
import { stripFullTags as stripHTMLTags } from './stringProcessing/stripHTMLTags.js'

import { forEach } from 'lodash-es'

import isPassiveSentence from './passiveVoice/morphological/determinePassiveSentence.js'
import isPassiveSentencePart from './passiveVoice/periphrastic/determinePassiveSentencePart.js'
import getPeriphrasticSentencePartsDefault from './passiveVoice/periphrastic/getSentenceParts.js'
import getPeriphrasticSentencePartsSplitOnStopwords from './passiveVoice/periphrastic/getSentencePartsSplitOnStopwords.js'

const morphologicalLanguages = [ 'ru', 'tr', 'sv', 'id', 'ar' ]
const periphrasticLanguages = [ 'en', 'de', 'nl', 'fr', 'es', 'it', 'pt', 'cn', 'pl' ]

/* Languages that employ both morphological and periphrastic passive voice marking have not been implemented yet.
 * const morphologicalAndPeriphrasticLanguages = [ "da", "nb" ];
 */

/**
 * Looks for morphological passive voice.
 *
 * @param {Array} sentences Sentences extracted from the text.
 * @param {string} language Language of the text.
 * @returns {Object} The found passive sentences.
 */
const getMorphologicalPassives = function (sentences, language) {
	const passiveSentences = []

	forEach(sentences, function (sentence) {
		const strippedSentence = stripHTMLTags(sentence.getSentenceText()).toLocaleLowerCase()

		sentence.setPassive(isPassiveSentence(strippedSentence, language))

		if (true === sentence.isPassive()) {
			passiveSentences.push(sentence.getSentenceText())
		}
	})
	return {
		passiveSentences
	}
}

/**
 * Looks for periphrastic passive voice.
 *
 * @param {Array} sentences Sentences extracted from the text.
 * @param {string} language Language of the text.
 * @returns {Object} The found passive sentences.
 */
const getPeriphrasticPassives = function (sentences, language) {
	const passiveSentences = []

	forEach(sentences, function (sentence) {
		const strippedSentence = stripHTMLTags(sentence.getSentenceText()).toLocaleLowerCase()

		// The functionality based on sentencePart objects should be rewritten using array indices of stopwords and auxiliaries.
		let sentenceParts = [],
			passive       = false

		if ('de' === language || 'nl' === language || 'pl' === language) {
			sentenceParts = getPeriphrasticSentencePartsSplitOnStopwords(strippedSentence, language)
		} else {
			sentenceParts = getPeriphrasticSentencePartsDefault(strippedSentence, language)
		}

		forEach(sentenceParts, function (sentencePart) {
			sentencePart.setPassive(isPassiveSentencePart(sentencePart.getSentencePartText(), sentencePart.getAuxiliaries(), language))
			passive = passive || sentencePart.isPassive()
		})
		if (passive) {
			passiveSentences.push(sentence.getSentenceText())
		}
	})
	return {
		passiveSentences
	}
}

/**
 * Determines the number of passive sentences in the text.
 *
 * @param {string} text The text to lookup.
 * @param {string} locale The locale to use.
 * @returns {Object} The total number of sentences in the text and the found passive sentences.
 */
export default function (text, locale) {
	const language = getLanguage(locale)
	const sentences = getSentences(text)
		.map(function (sentence) {
			return new Sentence(sentence)
		})
	const totalNumberSentences = sentences.length

	if (morphologicalLanguages.includes(language)) {
		return {
			total    : totalNumberSentences,
			passives : getMorphologicalPassives(sentences, language).passiveSentences
		}
	}
	if (periphrasticLanguages.includes(language)) {
		return {
			total    : totalNumberSentences,
			passives : getPeriphrasticPassives(sentences, language).passiveSentences
		}
	}

	return false
}