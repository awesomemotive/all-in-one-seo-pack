import icons from '../icons'
import classnames from 'classnames'
import HeadlineWordsBlock from './HeadlineWordsBlock'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components

const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlinePanelWordBalance = props => {
	const textPanelTitle = __('Word Balance', td)
	const textGuideline = __(
		'Compare the percentages of your results to the goal for each category and adjust as necessary.',
		td
	)
	const currentScore = props.data.score
	const classOnScore =
		40 > props.data.score
			? 'red'
			: 60 >= props.data.score
				? 'orange'
				: 'green'
	const classOnCommonWords =
		0 === props.data.result.commonWordsPercentage
			? 'red'
			: 0.2 > props.data.result.commonWordsPercentage
				? 'orange'
				: 'green'
	const classOnCommonWordsBg =
		0 === props.data.result.commonWordsPercentage
			? 'red-bg'
			: 0.2 > props.data.result.commonWordsPercentage
				? 'orange-bg'
				: 'green-bg'
	const guideLineOnCommonWords =
		0.2 > props.data.result.commonWordsPercentage
			? __(
				'Your headline would be more likely to get clicks if it had more common words.',
				td
			)
			: __(
				'Headlines with 20-30% common words are more likely to get clicks.',
				td
			  )
	const classOnUnCommonWords =
		0 === props.data.result.uncommonWordsPercentage
			? 'red'
			: 0.1 > props.data.result.uncommonWordsPercentage
				? 'orange'
				: 'green'
	const classOnUnCommonWordsBg =
		0 === props.data.result.uncommonWordsPercentage
			? 'red-bg'
			: 0.1 > props.data.result.uncommonWordsPercentage
				? 'orange-bg'
				: 'green-bg'
	const guideLineOnUnCommonWords =
		0.1 > props.data.result.uncommonWordsPercentage
			? __(
				'Your headline would be more likely to get clicks if it had more uncommon words.',
				td
			)
			: __(
				'Headlines with uncommon words are more likely to get clicks.',
				td
			  )
	const classOnEmotionalWords =
		0 === props.data.result.emotionalWordsPercentage
			? 'red'
			: 0.1 > props.data.result.emotionalWordsPercentage
				? 'orange'
				: 'green'
	const classOnEmotionalWordsBg =
		0 === props.data.result.emotionalWordsPercentage
			? 'red-bg'
			: 0.1 > props.data.result.emotionalWordsPercentage
				? 'orange-bg'
				: 'green-bg'
	const guideLineOnEmotionalWords = __(
		'Emotionally triggered headlines are likely to drive more clicks.',
		td
	)
	const classOnPowerWords =
		0 === props.data.result.powerWords.length ? 'orange' : 'green'
	const classOnPowerWordsBg =
		0 === props.data.result.powerWords.length ? 'orange' : 'green-bg'
	const guideLineOnPowerWords = __(
		'Headlines with power words are more likely to get clicks.',
		td
	)
	const textCommonWords = __('Common Words', td)
	const textTwentyThirty = __('20-30%', td)
	const textUncommonWords = __(
		'Uncommon Words',
		td
	)
	const textTenTwenty = __('10-20%', td)
	const textEmotionalWords = __(
		'Emotional Words',
		td
	)
	const textTenFifteen = __('10-15%', td)
	const textPowerWords = __('Power Words', td)
	const textLeastOne = __('At least one', td)

	let scoreStatus
	switch (true) {
		case 25 > currentScore:
			scoreStatus = <span>{ __('Not Looking Great', td) }</span>
			break
		case 50 > currentScore:
			scoreStatus = <span>{ __('Could Be Better', td) }</span>
			break
		case 60 > currentScore:
			scoreStatus = <span>{ __('Getting There', td) }</span>
			break
		case 75 > currentScore:
			scoreStatus = <span>{ __('Looks Good! 👍👍', td) }</span>
			break
		case 75 < currentScore:
			scoreStatus = <span>{ __('Super! 🔥🔥🔥', td) }</span>
			break
		default:
			scoreStatus = false
	}

	return (
		<Fragment>
			<PanelBody
				title={textPanelTitle}
				className={classnames(
					'aioseo-headline-analyzer-panel-word-balance',
					'aioseo-headline-analyzer-panel-has-icon',
					classOnScore
				)}
				icon={'green' === classOnScore ? icons.check : icons.warning}
			>
				<PanelRow>
					<div className="aioseo-headline-analyzer-words-block">
						<h4>{scoreStatus}</h4>
						<p>{textGuideline}</p>
					</div>
					<HeadlineWordsBlock
						title={textCommonWords}
						value={Math.round(
							props.data.result.commonWordsPercentage * 100
						)}
						goalValue={textTwentyThirty}
						words={props.data.result.commonWords}
						guideLine={guideLineOnCommonWords}
						classOnScore={classOnCommonWords}
						classOnScoreBg={classOnCommonWordsBg}
					/>
					<HeadlineWordsBlock
						title={textUncommonWords}
						value={Math.round(
							props.data.result.uncommonWordsPercentage * 100
						)}
						goalValue={textTenTwenty}
						words={props.data.result.uncommonWords}
						guideLine={guideLineOnUnCommonWords}
						classOnScore={classOnUnCommonWords}
						classOnScoreBg={classOnUnCommonWordsBg}
					/>
					<HeadlineWordsBlock
						title={textEmotionalWords}
						value={Math.round(
							props.data.result.emotionalWordsPercentage * 100
						)}
						goalValue={textTenFifteen}
						words={props.data.result.emotionWords}
						guideLine={guideLineOnEmotionalWords}
						classOnScore={classOnEmotionalWords}
						classOnScoreBg={classOnEmotionalWordsBg}
					/>
					<HeadlineWordsBlock
						title={textPowerWords}
						value={Math.round(
							props.data.result.powerWordsPercentage * 100
						)}
						goalValue={textLeastOne}
						words={props.data.result.powerWords}
						guideLine={guideLineOnPowerWords}
						classOnScore={classOnPowerWords}
						classOnScoreBg={classOnPowerWordsBg}
					/>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelWordBalance