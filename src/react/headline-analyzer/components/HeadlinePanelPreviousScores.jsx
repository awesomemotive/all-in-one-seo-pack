import classnames from 'classnames'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components

const HeadlinePanelPreviousScores = props => {
	const textPanelTitle = __(
		'Previous Scores',
		import.meta.env.VITE_TEXTDOMAIN
	)
	const previousScores =
		'undefined' !== props.analyzer.previousHeadlinesData
			? props.analyzer.previousHeadlinesData
			: []
	const sidebar = document.querySelector('.edit-post-sidebar')
	const activeTab =
		'undefined' !== props.analyzer.activeTab
			? props.analyzer.activeTab
			: 'current-score'
	const scrollPosition = 'current-score' === activeTab ? 390 : 300
	const setNewHeadlineData = index => {
		props.setAnalyzer({
			newHeadlineData : previousScores[index],
			headlineData    : previousScores[index],
			newHeadline     : previousScores[index].sentence,
			isNewData       : true
		})
		if (sidebar) {
			sidebar.scrollTop = scrollPosition
		}
	}

	return (
		<Fragment>
			<PanelBody title={textPanelTitle} className="aioseo-headline-analyzer-panel-previous-scores">
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						<ul className="aioseo-headline-analyzer-previous-scores">
							{previousScores.map((headlineData, index) => {
								if (
									10 > index &&
									('undefined' !== typeof headlineData.sentence ||
										'undefined' !== typeof headlineData.score)
								) {
									const classOnScore =
										40 > headlineData.score
											? 'red-bg'
											: 60 >= headlineData.score
												? 'orange-bg'
												: 'green-bg'
									return (
										<li
											key={index}
											onClick={() =>
												setNewHeadlineData(index)
											}
										>
											<span
												className={classnames(
													'aioseo-headline-analyzer-score',
													classOnScore
												)}
											>
												{headlineData.score}
											</span>
											<span className="aioseo-headline-analyzer-score-text">{headlineData.sentence}</span>
										</li>
									)
								}
								return null
							})}
						</ul>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelPreviousScores