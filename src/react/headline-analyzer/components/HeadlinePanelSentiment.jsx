import icons from '../icons'
import classnames from 'classnames'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components
const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlinePanelSentiment = props => {
	const textNeutralSentiment = __(
		'Your headline has a neutral sentiment.',
		td
	)
	const textNeutralSentimentGuideline = __(
		'Headlines that are strongly positive or negative tend to get more engagement than neutral ones.',
		td
	)
	const textPositiveSentiment = __(
		'Your headline has a positive sentiment.',
		td
	)
	const textPositiveSentimentGuideline = __(
		'Positive headlines tend to get better engagement than neutral or negative ones.',
		td
	)
	const textNegativeSentiment = __(
		'Your headline has a negative sentiment.',
		td
	)
	const textNegativeSentimentGuideline = __(
		'Negative headlines are attention-grabbing and tend to perform better than neutral ones.',
		td
	)
	const textPanelTitle = __('Sentiment', td)
	const sentiment =
		'neu' === props.data.result.sentiment
			? __('Neutral', td)
			: 'pos' === props.data.result.sentiment
				? __('Positive', td)
				: __('Negative', td)
	const sentimentIcon =
		'neu' === props.data.result.sentiment
			? icons.neutral
			: 'pos' === props.data.result.sentiment
				? icons.smile
				: icons.negative
	const classOnSentiment =
		'neu' === props.data.result.sentiment
			? 'orange'
			: 'pos' === props.data.result.sentiment
				? 'green'
				: 'red'

	return (
		<Fragment>
			<PanelBody
				title={textPanelTitle}
				className={classnames(
					'aioseo-headline-analyzer-panel-sentiment',
					'aioseo-headline-analyzer-panel-has-icon',
					classOnSentiment
				)}
				icon={sentimentIcon}
			>
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						<h4>{sentiment}</h4>
						{'neu' === props.data.result.sentiment
							? (
								<p>
									<strong>{textNeutralSentiment}</strong><br />{textNeutralSentimentGuideline}
								</p>
							)
							: (
								''
							)}
						{'pos' === props.data.result.sentiment
							? (
								<p>
									<strong>{textPositiveSentiment}</strong><br />{textPositiveSentimentGuideline}
								</p>
							)
							: (
								''
							)}
						{'neg' === props.data.result.sentiment
							? (
								<p>
									<strong>{textNegativeSentiment}</strong><br /> {textNegativeSentimentGuideline}
								</p>
							)
							: (
								''
							)}
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelSentiment