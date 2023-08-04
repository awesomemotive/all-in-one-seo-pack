// @TODO: We may possibly add these back in, so let's leave for now.
import calculateFleschReading from './calculateFleschReading'
import consecutiveSentences from './consecutiveSentences'
import contentHasAssets from './contentHasAssets'
import isExternalLink from './isExternalLink'
import isInternalLink from './isInternalLink'
import keyphraseInBeginningTitle from './keyphraseBeginningTitle'
import keyphraseInContent from './keyphraseInContent'
import keyphraseInDescription from './keyphraseInDescription'
import keyphraseInImageAlt from './keyphraseInImageAlt'
import keyphraseInIntroduction from './keyphraseInIntroduction'
import keyphraseInSubHeadings from './keyphraseInSubHeadings'
import keyphraseInTitle from './keyphraseInTitle'
import keyphraseInURL from './keyphraseInURL'
import keyphraseLength from './keyphraseLength'
import keywordDensity from './keywordDensity'
import lengthContent from './lengthContent'
import metadescriptionLength from './metadescriptionLength'
import paragraphLength from './paragraphLength'
import passiveVoice from './passiveVoice'
import sentenceLength from './sentenceLength'
import subheadingsDistribution from './subheadingsDistribution'
import titleLength from './titleLength'
import transitionWords from './transitionWords'
// import titleHasNumber from './titleHasNumber'
// import titleHasPowerWords from './titleHasPowerWords'
// import titleSentiment from './titleSentiment'
// import isExternalLinkNoFollow from './isExternalLinkNoFollow'

export const analyzers = {
	// isExternalLinkNoFollow
	// titleHasNumber,
	// titleHasPowerWords,
	// titleSentiment
	calculateFleschReading,
	consecutiveSentences,
	contentHasAssets,
	isExternalLink,
	isInternalLink,
	keyphraseInBeginningTitle,
	keyphraseInContent,
	keyphraseInDescription,
	keyphraseInImageAlt,
	keyphraseInIntroduction,
	keyphraseInSubHeadings,
	keyphraseInTitle,
	keyphraseInURL,
	keyphraseLength,
	keywordDensity,
	lengthContent,
	metadescriptionLength,
	paragraphLength,
	passiveVoice,
	sentenceLength,
	subheadingsDistribution,
	titleLength,
	transitionWords
}