import { __ } from '@/vue/plugins/translations'
import merge from 'lodash-es/merge'

import Assessment from '../assessment'
import AssessmentResult from '../../../values/AssessmentResult'

/**
 * @typedef {import("../../../languageProcessing/AbstractResearcher").default } Researcher
 * @typedef {import("../../../values/").Paper } Paper
 */

const td = import.meta.env.VITE_TEXTDOMAIN

/**
 * Assessment to check whether there are links which use the keyphrase or its synonym as their anchor text.
 * Links pointing at this page and links that cannot pass anchor text signal (nofollow, sponsored, ugc) are not counted.
 */
export default class TextCompetingLinksAssessment extends Assessment {
	/**
	 * Sets the identifier and the config.
	 *
	 * @param {Object} [config] 								The configuration to use.
	 * @param {number} [config.parameters] 						The evaluation parameters used by the assessment.
	 * @param {number} [config.parameters.recommendedMaximum] 	The recommended maximum number of links using the keyphrase as their anchor text.
	 * @param {Object} [config.scores] 							The scores to use.
	 * @param {string} [config.scores.good] 					The score to return if there are no links using the keyphrase as their anchor text.
	 * @param {string} [config.scores.bad] 						The score to return if there are links using the keyphrase as their anchor text.
	 *
	 */
	constructor (config = {}) {
		super()

		const defaultConfig = {
			parameters : {
				recommendedMaximum : 0
			},
			scores : {
				good : 9,
				bad  : 2
			}
		}

		this.identifier = 'textCompetingLinks'
		this._config = merge(defaultConfig, config)
	}

	/**
	 * Runs the linkCount module, based on this returns an assessment result with score.
	 *
	 * @param {Paper}       _paper      The paper to use for the assessment.
	 * @param {Researcher}  researcher  The researcher used for calling research.
	 *
	 * @returns {Object} The AssessmentResult.
	 */
	getResult (_paper, researcher) {
		const assessmentResult = new AssessmentResult()

		this.totalAnchorsWithKeyphrase = researcher.getResearch('getAnchorsWithKeyphrase').anchorsWithKeyphraseCount

		const calculatedResult = this.calculateResult()

		assessmentResult.setScore(calculatedResult.score)
		assessmentResult.setTitle(calculatedResult.resultTitle)
		assessmentResult.setText(calculatedResult.resultText)
		assessmentResult.setHasMarks(false)

		return assessmentResult
	}

	/**
	 * Returns a result based on the number of links which use the keyphrase or its synonym as their anchor text.
	 *
	 * @returns {{score: number, resultTitle: string, resultText: string}} ResultObject with score and text.
	 */
	calculateResult () {
		if (this.totalAnchorsWithKeyphrase === this._config.parameters.recommendedMaximum) {
			return {
				score       : this._config.scores.good,
				resultTitle : __('Competing links', td),
				resultText  : __(
					'No links in your content use your keyword as link text.',
					td
				)
			}
		}
		if (this.totalAnchorsWithKeyphrase > this._config.parameters.recommendedMaximum) {
			return {
				score       : this._config.scores.bad,
				resultTitle : __('Competing links', td),
				resultText  : __(
					'One of your links uses your keyword as link text. That link can compete with this post in search — change the link text to something else.',
					td
				)
			}
		}
	}
}