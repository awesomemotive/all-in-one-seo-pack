import classnames from 'classnames'
import HeadlinePieChart from './HeadlinePieChart'
import { veryGoodScore, forBetterResults } from '../constants'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components
const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlineNewScorePanel = props => {
	const postTitle = props.analyzer.currentHeadlineData.sentence
	const textPanelTitle = __('New Score', td)
	const textCurrentScore = __(
		'Current Score',
		td
	)
	const newTitle =
		'undefined' !== typeof props.analyzer.newHeadlineData
			? props.analyzer.newHeadlineData.sentence
			: ''
	const newScore =
		'undefined' !== typeof props.analyzer.newHeadlineData
			? props.analyzer.newHeadlineData.score
			: ''
	const currentScore =
		'undefined' !== typeof props.analyzer.currentHeadlineData.score
			? props.analyzer.currentHeadlineData.score
			: ''
	const classOnNewScore = 40 > newScore ? 'red' : 60 >= newScore ? 'orange' : 'green'
	const barColor = 'red' === classOnNewScore ? '#df2a4a' : 'orange' === classOnNewScore ? '#F2994A' : '#00aa63'
	const classOnCurrentScoreBg =
		40 > currentScore
			? 'red-bg'
			: 60 >= currentScore
				? 'orange-bg'
				: 'green-bg'
	const scoreDifference = Math.abs(newScore - currentScore)

	let statusOnScore
	switch (true) {
		case 25 > newScore:
			statusOnScore = <span>{ __('Not Looking Great', td) }</span>
			break
		case 50 > newScore:
			statusOnScore = <span>{ __('Could Be Better', td) }</span>
			break
		case 60 > newScore:
			statusOnScore = <span>{ __('Getting There', td) }</span>
			break
		case 75 > newScore:
			statusOnScore = <span>{ __('Looks Good! 👍👍', td) }</span>
			break
		case 75 < newScore:
			statusOnScore = <span>{ __('Super! 🔥🔥🔥', td) }</span>
			break
		default:
			statusOnScore = false
	}

	return (
		<Fragment>
			<PanelBody title={textPanelTitle} className="aioseo-headline-analyzer-panel-tab-new-score">
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						<div className="aioseo-headline-analyzer-new-score-panel">
							<p>{veryGoodScore} {forBetterResults}</p>
							<h4>“{newTitle}”</h4>
							<div className="aioseo-headline-analyzer-pie-chart-container">
								<span
									className={classnames(
										'aioseo-headline-analyzer-new-score',
										classOnNewScore
									)}
								>
									{newScore}
								</span>
								<HeadlinePieChart
									barScore={newScore}
									color={barColor}
								/>
								<span
									className={classnames(
										'aioseo-headline-analyzer-score-difference',
										classOnNewScore
									)}
								>
									{newScore > currentScore
										? '+ '
										: newScore === currentScore
											? ''
											: '- '}
									{scoreDifference}
								</span>
								{statusOnScore && <div className={classnames(
									'aioseo-headline-analyzer-score-status',
									classOnNewScore
								)}>{statusOnScore}</div>}
							</div>
							<div className="current-score">
								<span
									className={classnames(
										'aioseo-headline-analyzer-score',
										classOnCurrentScoreBg
									)}
								>
									{currentScore}
								</span>
								<div className="aioseo-headline-analyzer-current-score-content">
									<h5>{textCurrentScore}</h5>
									<p>{postTitle}</p>
								</div>
							</div>
						</div>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlineNewScorePanel