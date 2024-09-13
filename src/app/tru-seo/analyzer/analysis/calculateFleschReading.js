import { inRange } from 'lodash-es'
import calculateFlesch from '../researches/helpers/calculateFleschReading'
import getLanguageAvailability from '../researches/helpers/getLanguageAvailability'
import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const availableLanguages = [ 'en', 'nl', 'de', 'it', 'ru', 'fr', 'es', 'pt' ]
const borders = {
	veryEasy        : 90,
	easy            : 80,
	fairlyEasy      : 70,
	okay            : 60,
	fairlyDifficult : 50,
	difficult       : 30,
	veryDifficult   : 0
}
const scores = {
	veryEasy        : 9,
	easy            : 9,
	fairlyEasy      : 9,
	okay            : 9,
	fairlyDifficult : 6,
	difficult       : 3,
	veryDifficult   : 3
}
export default function calculateFleschReading (content, locale) {
	if (!content) {
		return {}
	}

	let fleschReadingResult = calculateFlesch(content, locale)
	const isLanguageAvailable = getLanguageAvailability(locale, availableLanguages)

	if (isLanguageAvailable) {
		if (0 > fleschReadingResult) {
			fleschReadingResult = 0
		}

		if (100 < fleschReadingResult) {
			fleschReadingResult = 100
		}

		let score = 0,
			 feedback = '',
			 note = __('Good job!', td)

		if (fleschReadingResult >= borders.veryEasy) {
			score    = scores.veryEasy
			feedback = __('very easy', td)
		} else if (inRange(fleschReadingResult, borders.easy, borders.veryEasy)) {
			score    = scores.easy
			feedback = 'easy'
		} else if (inRange(fleschReadingResult, borders.fairlyEasy, borders.easy)) {
			score    = scores.fairlyEasy
			feedback = 'fairly easy'
		} else if (inRange(fleschReadingResult, borders.okay, borders.fairlyEasy)) {
			score    = scores.okay
			feedback = 'ok'
		} else if (inRange(fleschReadingResult, borders.fairlyDifficult, borders.okay)) {
			score    = scores.fairlyDifficult
			feedback = 'fairly difficult'
			note     = 'Try to make shorter sentences to improve readability.'
		} else if (inRange(fleschReadingResult, borders.difficult, borders.fairlyDifficult)) {
			score    = scores.difficult
			feedback = 'difficult'
			note     = 'Try to make shorter sentences, using less difficult words to improve readability.'
		} else {
			score    = scores.veryDifficult
			feedback = 'very difficult'
			note     = 'Try to make shorter sentences, using less difficult words to improve readability.'
		}

		if (score >= scores.okay) {
			return {
				title       : __('Flesch reading ease', td),
				description : sprintf(
					// Translators: 1 - Flesch Reading Result Number, 2 - Read difficulty string.
					__('The copy scores %1$s in the test, which is considered %2$s to read.', td),
					fleschReadingResult,
					feedback
				),
				score    : score,
				maxScore : 9,
				error    : 0
			}
		}

		return {
			title       : __('Flesch reading ease', td),
			description : sprintf(
				// Translators: 1 - Flesch Reading Result Number, 2 - Read difficulty string, 3 - Note string.
				__('The copy scores %1$s in the test, which is considered %2$s to read. %3$s', td),
				fleschReadingResult,
				feedback,
				note
			),
			score    : score,
			maxScore : 9,
			error    : 1
		}
	}

	return {
		title       : __('Flesch reading ease N/A', td),
		description : ':-)',
		score       : 0,
		maxScore    : 0,
		error       : 1
	}
}