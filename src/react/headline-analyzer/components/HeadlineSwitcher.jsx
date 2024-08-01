import classnames from 'classnames'
import HeadlineTabCurrentScore from './HeadlineTabCurrentScore'
import HeadlineTabNewScore from './HeadlineTabNewScore'
import HeadlinePanelNewScore from './HeadlinePanelNewScore'

const { __ } = window.wp.i18n
const { Fragment, useState, useEffect } = window.wp.element

const HeadlineSwitcher = props => {
	const textCurrentScore = __(
		'Current Score',
		import.meta.env.VITE_TEXTDOMAIN
	)
	const textNewHeadline = __(
		'Try New Headline',
		import.meta.env.VITE_TEXTDOMAIN
	)
	const [ activeTab, setActiveTab ] = useState('current-score')
	const activeCurrentScore = () => setActiveTab('current-score')
	const activeNewHeadline = () => setActiveTab('new-headline')
	const isNewData =
		'undefined' !== typeof props.analyzer.isNewData
			? props.analyzer.isNewData
			: false

	useEffect(() => {
		props.setAnalyzer({
			activeTab : activeTab
		})
	}, [ activeTab ])

	return (
		<Fragment>
			<div className="aioseo-inline-buttons">
				<button
					onClick={activeCurrentScore}
					className={classnames('aioseo-switcher-button', {
						active : 'current-score' === activeTab
					})}
				>
					{textCurrentScore}
				</button>
				<button
					onClick={activeNewHeadline}
					className={classnames('aioseo-switcher-button', {
						active : 'new-headline' === activeTab
					})}
				>
					{textNewHeadline}
				</button>
			</div>
			{'new-headline' === activeTab
				? (
					<>
						<HeadlineTabNewScore
							analyzer={props.analyzer}
							setAnalyzer={props.setAnalyzer}
						/>
					</>
				)
				: (
					<HeadlineTabCurrentScore analyzer={props.analyzer} />
				)}
			{isNewData
				? (
					<HeadlinePanelNewScore analyzer={props.analyzer} />
				)
				: (
					''
				)}
		</Fragment>
	)
}

export default HeadlineSwitcher