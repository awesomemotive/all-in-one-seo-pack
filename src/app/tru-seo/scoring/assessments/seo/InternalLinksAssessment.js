import { __ } from '@/vue/plugins/translations'
import merge from 'lodash-es/merge'

import Assessment from '../assessment'
import AssessmentResult from '../../../values/AssessmentResult'

/**
 * @typedef {import("../../../languageProcessing/AbstractResearcher").default } Researcher
 * @typedef {import("../../../languageProcessing/researches/getLinkStatistics").LinkStatistics} LinkStatistics
 * @typedef {import("../../../values/").Paper } Paper
 */

const td = import.meta.env.VITE_TEXTDOMAIN

/**
 * Assessment to check whether the text has internal links and whether they are followed or no-followed.
 */
export default class InternalLinksAssessment extends Assessment {
	/**
	 * Sets the identifier and the config.
	 *
	 * @param {Object} [config] The configuration to use.
	 * @param {Object} [config.parameters] The parameters to use.
	 * @param {number} [config.parameters.recommendedMinimum] The recommended minimum number of internal links in the text.
	 * @param {Object} [config.scores] The scores to use.
	 * @param {number} [config.scores.allInternalFollow] The score to return if all internal links are do-follow.
	 * @param {number} [config.scores.someInternalFollow] The score to return if some but not all internal links are do-follow.
	 * @param {number} [config.scores.noneInternalFollow] The score to return if all internal links are no-follow.
	 * @param {number} [config.scores.noInternal] The score to return if there are no internal links.
	 * @param {string} [config.url] The URL to the relevant KB article.
	 */
	constructor (config = {}) {
		super()

		const defaultConfig = {
			parameters : {
				recommendedMinimum : 1
			},
			scores : {
				allInternalFollow  : 9,
				someInternalFollow : 8,
				noneInternalFollow : 6,
				noInternal         : 3
			}
		}

		this.identifier = 'internalLinks'
		this._config = merge(defaultConfig, config)
	}

	/**
	 * Runs the getLinkStatistics module, based on this returns an assessment result with score.
	 *
	 * @param {Paper} _paper The paper to use for the assessment.
	 * @param {Researcher} researcher The researcher used for calling research.
	 *
	 * @returns {AssessmentResult} The result of the assessment.
	 */
	getResult (_paper, researcher) {
		this.linkStatistics = researcher.getResearch('getLinkStatistics')
		const assessmentResult = new AssessmentResult()

		const calculatedResult = this.calculateResult()
		assessmentResult.setScore(calculatedResult.score)
		assessmentResult.setTitle(calculatedResult.resultTitle)
		assessmentResult.setText(calculatedResult.resultText)

		return assessmentResult
	}

	/**
	 * Returns a score and text based on the linkStatistics object.
	 *
	 * @returns {{score: number, resultTitle: string, resultText: string}} ResultObject with score and text
	 */
	calculateResult () {
		if (0 === this.linkStatistics.internalTotal) {
			return {
				score       : this._config.scores.noInternal,
				resultTitle : __('Internal links', td),
				resultText  : __(
					'Your post has no internal links. Add at least one link to a related post or page on your site. This helps search engines discover related content.',
					td
				)
			}
		}

		if (this.linkStatistics.internalNofollow === this.linkStatistics.internalTotal) {
			return {
				score       : this._config.scores.noneInternalFollow,
				resultTitle : __('Internal links', td),
				resultText  : __(
					'All your internal links are nofollow, which tells search engines not to follow them. Add at least one regular link to pass on link value.',
					td
				)
			}
		}

		if (this.linkStatistics.internalDofollow === this.linkStatistics.internalTotal) {
			return {
				score       : this._config.scores.allInternalFollow,
				resultTitle : __('Internal links', td),
				resultText  : __(
					'You have internal links to other parts of your site.',
					td
				)
			}
		}
		return {
			score       : this._config.scores.someInternalFollow,
			resultTitle : __('Internal links', td),
			resultText  : __(
				'Your internal links include a mix of regular and nofollow links.',
				td
			)
		}
	}
}