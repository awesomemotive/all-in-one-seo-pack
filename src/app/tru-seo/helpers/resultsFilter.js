// Assessments that only mean anything with a keyword set. They render under the keyword rows on
// the Keywords tab, so consumers building the basic/readability lists exclude them from there.
export const KEYWORD_ASSESSMENT_IDS = [
	'introductionKeyword',
	'keyphraseLength',
	'keyphraseDensity',
	'keyphraseDistribution',
	'keywordCannibalization',
	'metaDescriptionKeyword',
	'subheadingsKeyword',
	'textCompetingLinks',
	'imageKeyphrase',
	'keyphraseInSEOTitle',
	'keyphraseInTermName',
	'slugKeyword',
	'functionWordsInKeyphrase'
]

export function getAllResultsGrouped (results, excludeIds = new Set()) {
	return {
		basic       : getBasicResults(results, excludeIds),
		readability : getReadabilityResults(results, excludeIds),
		spelling    : getSpellingResults(results, excludeIds)
	}
}

export function getBasicResults (results, excludeIds = new Set()) {
	const identifiers = [
		'keyphraseInSEOTitle',
		'titleWidth',
		'functionWordsInKeyphrase',
		'imageAltTags',
		'images',
		'internalLinks',
		'introductionKeyword',
		'keyphraseDistribution',
		'imageKeyphrase',
		'keyphraseLength',
		'keyphraseDensity',
		'keywordCannibalization',
		'metaDescriptionKeyword',
		'metaDescriptionLength',
		'externalLinks',
		'productIdentifier',
		'productSKU',
		'singleH1',
		'subheadingsKeyword',
		'textCompetingLinks',
		'textLength',
		'slugKeyword'
	]

	return getResultsByIdentifiers(identifiers, results, excludeIds)
}

export function getReadabilityResults (results, excludeIds = new Set()) {
	const identifiers = [
		'textParagraphTooLong',
		'passiveVoice',
		'sentenceBeginnings',
		'textSentenceLength',
		'subheadingsTooLong',
		'textAlignment',
		'textPresence',
		// Leaves out 'wordComplexity', so a result stored before the check was unregistered isn't shown.
		'textTransitionWords'
	]

	return getResultsByIdentifiers(identifiers, results, excludeIds)
}

export function getSpellingResults (results, excludeIds = new Set()) {
	return getResultsByIdentifiers([ 'spellingChecker' ], results, excludeIds)
}

export function getFocusKeywordResults (results) {
	return getResultsByIdentifiers(KEYWORD_ASSESSMENT_IDS, results)
}

// Whether an analysis-items object has at least one check worth expanding.
// getFocusKeywordResults returns {} when no focus-keyword checks ran, and the
// persisted shape keeps only { score } per identifier (title/text are stripped on
// save) — both are truthy objects that render nothing. A caret gated on `!!items`
// would open onto an empty panel, so gate on this instead. Mirrors
// MetaboxAnalysisDetail, which needs a title to render a row.
export function hasAnalysisItems (items) {
	return !!items && Object.values(items).some(item => item && item.title)
}

export function getResultsByIdentifiers (identifiers, results, excludeIds = new Set()) {
	const result = {}

	// Check if results is a Map or a plain object
	const isMap = results instanceof Map

	if (isMap) {
		identifiers.forEach(identifier => {
			if (excludeIds.has(identifier)) {
				return
			}
			if (results.has(identifier)) {
				result[identifier] = results.get(identifier)
			}
		})
	} else if ('object' === typeof results) {
		identifiers.forEach(identifier => {
			if (excludeIds.has(identifier)) {
				return
			}
			if (results[identifier]) {
				result[identifier] = results[identifier]
			}
		})
	}

	return result
}