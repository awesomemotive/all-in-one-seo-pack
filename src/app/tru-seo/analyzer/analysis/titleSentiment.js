import Sentiment from 'sentiment'
import { sentimentWords } from '../researches/config/sentimentWords'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noSentimentWords       : 5,
	titleHasSentimentWords : 9
}

function titleSentiment (title) {
	if (!title) {
		return {}
	}

	const sentiment = new Sentiment()
	const sentimentScore = sentiment.analyze(title, sentimentWords).score

	if (0 !== sentimentScore) {
		return {
			title       : __('SEO Title Sentiment Words', td),
			description : __('Your SEO title has a positive or a negative sentiment.', td),
			score       : scores.titleHasSentimentWords,
			maxScore    : scores.titleHasSentimentWords,
			error       : 0
		}
	}

	return {
		title       : __('SEO Title Sentiment Words', td),
		description : __('Your SEO title doesn\'t contain a sentiment word. Headlines with a strong emotional sentiment (positive or negative) tend to receive more clicks.', td),
		score       : scores.noSentimentWords,
		maxScore    : scores.titleHasSentimentWords,
		error       : 1
	}
}

export default titleSentiment