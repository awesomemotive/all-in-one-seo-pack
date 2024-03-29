import { find, forEach, memoize, includes, flattenDeep } from 'lodash-es'

import irregularsEnglishFactory from '../../english/passiveVoice/irregulars'

import irregularsFrenchFactory from '../../french/passiveVoice/irregulars'

import spanishParticiplesFactory from '../../spanish/passiveVoice/participles'
import portugueseParticiplesFactory from '../../portuguese/passiveVoice/participles'
import italianParticiplesFactory from '../../italian/passiveVoice/participles'
import irregularsDutchFactory from '../../dutch/passiveVoice/irregulars'
import polishParticiplesFactory from '../../polish/passiveVoice/participles'
const irregularsEnglish = irregularsEnglishFactory()

const irregularsFrench = irregularsFrenchFactory()

const irregularsRegularFrench = irregularsFrench.irregularsRegular
const irregularsIrregularFrench = irregularsFrench.irregularsIrregular
const irregularsEndingInSFrench = irregularsFrench.irregularsEndingInS
const spanishParticiples = spanishParticiplesFactory()
const portugueseParticiples = portugueseParticiplesFactory()
const italianParticiples = italianParticiplesFactory()
const irregularsDutch = irregularsDutchFactory()
const nlRegex1 = /^(ge|be|ont|ver|her|er)\S+(d|t)($|[ \n\r\t.,'()"+\-;!?:/»«‹›<>])/ig
const nlRegex2 = /^(aan|af|bij|binnen|los|mee|na|neer|om|onder|samen|terug|tegen|toe|uit|vast)(ge)\S+(d|t|n)($|[ \n\r\t.,'()"+\-;!?:/»«‹›<>])/ig
const polishParticiples = polishParticiplesFactory()

// The language-specific participle regexes.
const languageVariables = {
	en : {
		regularParticiplesRegex : /\w+ed($|[ \n\r\t.,'()"+\-;!?:/»«‹›<>])/ig
	},
	fr : {
		regularParticiplesRegex : /\S+(é|ée|és|ées)($|[ \n\r\t.,'()"+\-;!?:/»«‹›<>])/ig
	},
	nl : {
		regularParticipleRegexPattern1 : nlRegex1,
		regularParticipleRegexPattern2 : nlRegex2
	}
}
const languagesWithoutRegularParticiples = [ 'es', 'it', 'pl', 'pt' ]

/**
 * Returns words that have been determined to be a regular participle.
 *
 * @param {string} word The word to check.
 * @param {string} language The language in which to match.
 *
 * @returns {Array} A list with the matches.
 */
const regularParticiples = function (word, language) {
	// In certain languages we don't match participles with a regular regex pattern.
	if (languagesWithoutRegularParticiples.includes(language)) {
		return []
	}

	// Matches word with language-specific participle regexes.
	let matches = []

	Object.keys(languageVariables[language]).forEach(function (regex) {
		const match = word.match(languageVariables[language][regex])
		if (null !== match) {
			matches.push(match)
		}
	})

	matches = flattenDeep(matches)

	return matches
}

/**
 * Returns an array of matches of irregular participles with suffixes.
 *
 * @param {string} word The word to match on.
 * @param {Array} irregulars The list of irregulars to match.
 * @param {string} suffixes The suffixes to match the word with.
 *
 * @returns {Array} A list with matched irregular participles.
 */
const matchFrenchParticipleWithSuffix = function (word, irregulars, suffixes) {
	const matches = []
	forEach(irregulars, function (irregular) {
		const irregularParticiplesRegex = new RegExp('^' + irregular + suffixes + '?$', 'ig')
		const participleMatch = word.match(irregularParticiplesRegex)
		if (participleMatch) {
			matches.push(participleMatch[0])
		}
	})
	return matches
}

/**
 * Returns the matches for a word in the list of irregulars.
 *
 * @param {string} word The word to match in the list.
 * @param {string} language The language for which to match.
 *
 * @returns {Array} A list with the matches.
 */
const irregularParticiples = function (word, language) {
	let matches = []

	switch (language) {
		case 'fr':
			// Match different classes of participles with suffixes.
			matches = matches.concat(matchFrenchParticipleWithSuffix(word, irregularsRegularFrench, '(e|s|es)'))
			matches = matches.concat(matchFrenchParticipleWithSuffix(word, irregularsEndingInSFrench, '(e|es)'))

			// Match irregular participles that don't require adding a suffix.
			find(irregularsIrregularFrench, function (irregularParticiple) {
				if (irregularParticiple === word) {
					matches.push(irregularParticiple)
				}
			})
			break
		case 'es':
			// In Spanish, we only match passives from a word list.
			if (includes(spanishParticiples, word)) {
				matches.push(word)
			}
			break
		case 'it':
			// In Italian, we only match passives from a word list.
			if (includes(italianParticiples, word)) {
				matches.push(word)
			}
			break
		case 'nl':
			if (includes(irregularsDutch, word)) {
				matches.push(word)
			}
			break
		case 'pl':
			// In Polish, we only match passives from a word list.
			if (includes(polishParticiples, word)) {
				matches.push(word)
			}
			break
		case 'pt':
			// In Portuguese, we only match passives from a word list.
			if (includes(portugueseParticiples, word)) {
				matches.push(word)
			}
			break
		case 'en':
		default:
			find(irregularsEnglish, function (irregularParticiple) {
				if (irregularParticiple === word) {
					matches.push(irregularParticiple)
				}
			})
			break
	}
	return matches
}

/**
 * Returns methods to return participles for a language.
 *
 * @returns {Object} Methods to return participles in a language.
 */
export default function () {
	return {
		regularParticiples   : memoize(regularParticiples),
		irregularParticiples : memoize(irregularParticiples)
	}
}