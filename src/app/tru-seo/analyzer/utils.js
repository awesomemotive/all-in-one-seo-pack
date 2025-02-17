import { forEach } from 'lodash-es'

const calculateScore = async element => {
	let scoreCounter    = 0,
		maxScoreCounter = 0,
		scorePercentage = 0

	forEach(element, analysis => {
		if (analysis.title && 0 <= analysis.score) {
			scoreCounter    += analysis.score
			maxScoreCounter += analysis.maxScore
		}
	})
	if (maxScoreCounter) {
		scorePercentage =  Math.round((scoreCounter * 100) / maxScoreCounter)
	}
	return scorePercentage
}

const calculateErrors = async element => {
	let errorCounter = 0

	forEach(element, analysis => {
		if (0 < analysis.error) {
			errorCounter++
		}
	})
	return errorCounter
}

const hasKeyphrases = keyphrases => {
	return hasFocusKeyphrase(keyphrases) || hasAdditionalKeyphrases(keyphrases)
}

const hasFocusKeyphrase = keyphrases => {
	return !!(
		keyphrases &&
		keyphrases.focus &&
		keyphrases.focus.keyphrase
	)
}

const hasAdditionalKeyphrases = keyphrases => {
	return !!(
		keyphrases &&
		keyphrases.additional &&
		keyphrases.additional.length
	)
}

export {
	calculateErrors,
	calculateScore,
	hasAdditionalKeyphrases,
	hasFocusKeyphrase,
	hasKeyphrases
}