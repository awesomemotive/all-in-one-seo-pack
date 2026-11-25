// Import each assessment from its own module rather than through a barrel, so the worker bundle
// only pulls in the four it registers.
import SpellingCheckerAssessment from '@/app/tru-seo/scoring/assessments/readability/SpellingCheckerAssessment'
import TextAlignmentAssessment from '@/app/tru-seo/scoring/assessments/readability/TextAlignmentAssessment'
import KeyphraseDistributionAssessment from '@/app/tru-seo/scoring/assessments/seo/KeyphraseDistributionAssessment'
import KeywordCannibalizationAssessment from '@/app/tru-seo/scoring/assessments/seo/KeywordCannibalizationAssessment'
import { researches as languageProcessingResearches } from '@/app/tru-seo/languageProcessing'
import getSpellingErrors from '@/app/tru-seo/researches/getSpellingErrors'

const { getLongCenterAlignedTexts, keyphraseDistribution: keyPhraseDistribution } = languageProcessingResearches
const pluginName = 'AIOSEO'

export default function (worker) {
	// Word complexity is not registered: it needs word-frequency data we don't ship, so it would
	// always pass and inflate every readability score.

	const keyphraseDistributionAssessment = new KeyphraseDistributionAssessment()
	worker.registerResearch('keyphraseDistribution', keyPhraseDistribution)
	worker.registerAssessment('keyphraseDistributionAssessment', keyphraseDistributionAssessment, pluginName, 'seo')

	// Spell Checker — dictionary loaded via module singleton in spellChecker.js.
	worker.registerResearch('getSpellingErrors', getSpellingErrors)
	worker.registerAssessment('spellingChecker', new SpellingCheckerAssessment(), pluginName, 'readability')
	worker.registerAssessment('spellingChecker', new SpellingCheckerAssessment({
		scores : {
			fewErrors : 3
		}
	}), pluginName, 'cornerstoneReadability')

	// Text alignment — the assessment is inapplicable unless its research is registered, so both
	// must be registered together or it silently never runs.
	worker.registerResearch('getLongCenterAlignedTexts', getLongCenterAlignedTexts)
	worker.registerAssessment('textAlignment', new TextAlignmentAssessment(), pluginName, 'readability')
	worker.registerAssessment('textAlignment', new TextAlignmentAssessment(), pluginName, 'cornerstoneReadability')

	// Keyword Cannibalization
	const keywordCannibalizationAssessment = new KeywordCannibalizationAssessment()
	worker.registerAssessment('keywordCannibalization', keywordCannibalizationAssessment, pluginName, 'seo')
}