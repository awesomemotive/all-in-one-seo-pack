import { __ } from '@/vue/plugins/translations'
import merge from 'lodash-es/merge'
import { isEmpty } from '@/app/tru-seo/helpers'

import Assessment from '../assessment'
import AssessmentResult from '../../../values/AssessmentResult'

const td = import.meta.env.VITE_TEXTDOMAIN

/**
 * @typedef {import("../../../languageProcessing/AbstractResearcher").default } Researcher
 * @typedef {import("../../../languageProcessing/researches/getLinkStatistics").LinkStatistics} LinkStatistics
 * @typedef {import("../../../values/").Paper } Paper
 */

/**
 * Assessment for calculating the outbound links in the text.
 */
export default class OutboundLinksAssessment extends Assessment {
	/**
	 * Sets the identifier and the config.
	 *
	 * @param {Object} [config] The configuration to use.
	 */
	constructor (config = {}) {
		super()

		const defaultConfig = {
			scores : {
				noLinks        : 3,
				allNofollowed  : 6,
				someNoFollowed : 8,
				allFollowed    : 9
			}
		}

		this.identifier = 'externalLinks'
		this._config = merge(defaultConfig, config)
	}

	/**
	 * Runs the getLinkStatistics module, based on this returns an assessment result with score.
	 *
	 * @param {Paper}       _paper      Unused; kept for the Assessor interface signature.
	 * @param {Researcher}  researcher  The researcher used for calling research.
	 *
	 * @returns {AssessmentResult} The assessment result.
	 */
	getResult (_paper, researcher) {
		const linkStatistics = researcher.getResearch('getLinkStatistics')
		const assessmentResult = new AssessmentResult()

		if (!isEmpty(linkStatistics)) {
			assessmentResult.setScore(this.calculateScore(linkStatistics))
			assessmentResult.setTitle(__('Outbound links', td))
			assessmentResult.setText(this.translateScore(linkStatistics))
		}

		return assessmentResult
	}

	/**
	 * Returns a score based on the linkStatistics object.
	 *
	 * @param {LinkStatistics} linkStatistics The object with all link statistics.
	 *
	 * @returns {number} The calculated score.
	 */
	calculateScore (linkStatistics) {
		if (0 === linkStatistics.externalTotal) {
			return this._config.scores.noLinks
		}

		if (linkStatistics.externalNofollow === linkStatistics.externalTotal) {
			return this._config.scores.allNofollowed
		}

		if (linkStatistics.externalDofollow < linkStatistics.externalTotal) {
			return this._config.scores.someNoFollowed
		}

		if (linkStatistics.externalDofollow === linkStatistics.externalTotal) {
			return this._config.scores.allFollowed
		}

		return 0
	}

	/**
	 * Translates the score to a message the user can understand.
	 *
	 * @param {LinkStatistics}  linkStatistics  The object with all link statistics.
	 *
	 * @returns {string} The translated string.
	 */
	translateScore (linkStatistics) {
		if (0 === linkStatistics.externalTotal) {
			return __(
				'Your post has no external links. Linking to a few credible sources adds context and trust.',
				td
			)
		}

		if (linkStatistics.externalNofollow === linkStatistics.externalTotal) {
			return __(
				'All your external links are nofollow. Add at least one regular link to a trusted source.',
				td
			)
		}

		if (linkStatistics.externalDofollow === linkStatistics.externalTotal) {
			return __(
				'You\'re linking out to other sites.',
				td
			)
		}

		if (linkStatistics.externalDofollow < linkStatistics.externalTotal) {
			return __(
				'Your external links include a mix of regular and nofollow links.',
				td
			)
		}

		return ''
	}
}