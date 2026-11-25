import merge from 'lodash-es/merge'

import Assessment from '../assessment'
import Mark from '../../../values/Mark'
import AssessmentResult from '../../../values/AssessmentResult'

import { getLanguagesWithSpellChecker } from '@/app/tru-seo/helpers'
import { __, sprintf } from '@/vue/plugins/translations'
import { escapeHtml } from '@/vue/utils/strings'

const td = import.meta.env.VITE_TEXTDOMAIN

/**
 * Represents the assessment that checks for spelling errors in the text.
 *
 * @since 5.0.0
 */
export default class SpellingCheckerAssessment extends Assessment {
	/**
	 * Sets the identifier and the config.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} config                         The configuration to use.
	 * @param {Object} [config.scores]                The score values.
	 * @param {number} [config.scores.noErrors]       Score when no errors (default 9).
	 * @param {number} [config.scores.fewErrors]      Score for few errors (default 6).
	 * @param {number} [config.scores.manyErrors]     Score for many errors (default 3).
	 * @param {Object} [config.thresholds]            Error count thresholds.
	 * @param {number} [config.thresholds.fewErrors]  Max errors for "few" (default 3).
	 * @param {number} [config.thresholds.manyErrors] Min errors for "many" (default 6).
	 * @returns {void}
	 */
	constructor (config = {}) {
		super()

		const defaultConfig = {
			scores : {
				noErrors   : 9,
				fewErrors  : 6,
				manyErrors : 3
			},
			thresholds : {
				fewErrors  : 3,
				manyErrors : 6
			}
		}

		this.identifier = 'spellingChecker'
		this._config = merge(defaultConfig, config)
	}

	/**
	 * Scores the text based on spelling errors found.
	 *
	 * @since 5.0.0
	 *
	 * @param {Paper}      _paper     The paper to use for the assessment.
	 * @param {Researcher} researcher The researcher used for calling research.
	 * @returns {AssessmentResult}     The assessment result.
	 */
	getResult (_paper, researcher) {
		this._spellingErrors = researcher.getResearch('getSpellingErrors')

		const assessmentResult = new AssessmentResult()
		assessmentResult.setTitle(__('Spelling mistakes', td))

		// If the research returned no words (dictionary not loaded or empty text), show a neutral result.
		if (0 === this._spellingErrors.totalWords) {
			assessmentResult.setScore(0)
			assessmentResult.setHasMarks(false)

			if (this._spellingErrors.notInstalled) {
				const url = this._spellingErrors.settingsUrl
				assessmentResult.setText(sprintf(
					// Translators: 1 - link open tag, 2 - link close tag.
					__('Spell-check isn\'t set up for this language yet. %1$sDownload the dictionary%2$s to enable it.', td),
					url ? `<a href="${url}">` : '',
					url ? '</a>' : ''
				))
			} else {
				assessmentResult.setText(__('Spell-check isn\'t available for this language yet.', td))
			}

			return assessmentResult
		}

		const calculatedScore = this.calculateResult()
		assessmentResult.setScore(calculatedScore.score)
		assessmentResult.setText(calculatedScore.resultText)
		assessmentResult.setHasMarks(0 < this._spellingErrors.misspelledCount)

		return assessmentResult
	}

	/**
	 * Calculates the spelling assessment result.
	 *
	 * @since 5.0.0
	 *
	 * @returns {Object} Object containing the score and result text.
	 */
	calculateResult () {
		const { misspelledCount } = this._spellingErrors

		if (0 === misspelledCount) {
			return {
				score      : this._config.scores.noErrors,
				resultText : __('No spelling errors found.', td)
			}
		}

		if (this._config.thresholds.fewErrors >= misspelledCount) {
			return {
				score      : this._config.scores.fewErrors,
				resultText : sprintf(
					// Translators: 1 - Number of misspelled words.
					__('Found %1$d possible spelling error. Hover over the highlighted words to see suggestions.', td),
					misspelledCount
				)
			}
		}

		return {
			score      : this._config.scores.manyErrors,
			resultText : sprintf(
				// Translators: 1 - Number of misspelled words.
				__('Found %1$d possible spelling errors. Hover over the highlighted words to see suggestions — typos can hurt how readers trust your content.', td),
				misspelledCount
			)
		}
	}

	/**
	 * Returns marks for each misspelled word.
	 *
	 * @since 5.0.0
	 *
	 * @param {Paper}      _paper     The paper to use.
	 * @param {Researcher} researcher The researcher.
	 * @returns {Array<Mark>}         A list of marks for misspelled words.
	 */
	getMarks (_paper, researcher) {
		const { errors } = researcher.getResearch('getSpellingErrors')

		return errors.map(error => new Mark({
			original : error.word,
			marked   : `<truseomark class='truseo-text-mark'>${escapeHtml(error.word)}</truseomark>`,
			position : {
				startOffset : error.start,
				endOffset   : error.end
			}
		}))
	}

	/**
	 * Checks whether the spelling assessment is applicable for the paper's locale.
	 *
	 * Only applicable when a Hunspell dictionary exists for the language.
	 *
	 * @since 5.0.0
	 *
	 * @param {Paper} paper The paper to check.
	 * @returns {boolean}   True if the language has spell checker support.
	 */
	isApplicable (paper) {
		const locale = paper.getLocale()
		const language = locale.split('_')[0]

		return getLanguagesWithSpellChecker().includes(language)
	}
}