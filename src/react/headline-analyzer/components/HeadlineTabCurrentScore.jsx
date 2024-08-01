import classnames from 'classnames'
import HeadlinePieChart from './HeadlinePieChart'
import { veryGoodScore, forBetterResults } from '../constants'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components
const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlineTabCurrentScore = props => {
	const postTitle = props.analyzer.currentHeadlineData.sentence
	const textScore = __('Score', td)
	const currentScore = props.analyzer.currentHeadlineData.score
	const classOnScore = 40 > currentScore ? 'red' : 70 >= currentScore ? 'orange' : 'green'
	const barColor =
		'red' === classOnScore
			? '#df2a4a'
			: 'orange' === classOnScore
				? '#F2994A'
				: '#00aa63'

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
			<PanelBody className="aioseo-headline-analyzer-panel-score" title={textScore}>
				<PanelRow>
					<div className="aioseo-headline-analyzer-current-score-tab aioseo-headline-analyzer-panel-first-block">
						<h4 className="aioseo-headline-analyzer-current-title">
							“{postTitle}”
						</h4>
						<div className="aioseo-headline-analyzer-pie-chart-container">
							<div
								className={classnames(
									'aioseo-headline-analyzer-current-score',
									classOnScore
								)}
							>
								{currentScore}<span className="aioseo-headline-analyzer-total-out-of-score">/ 100</span>
							</div>
							{scoreStatus && <div className={classnames('aioseo-headline-analyzer-score-status', classOnScore)}>{scoreStatus}</div>}
							<HeadlinePieChart
								barScore={currentScore}
								color={barColor}
							/>
						</div>
						<p>{veryGoodScore} {forBetterResults}</p>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlineTabCurrentScore