import classnames from 'classnames'

const { __ } = window.wp.i18n

const HeadlineWordsBlock = props => {
	const textGoal = __('Goal: ', import.meta.env.VITE_TEXTDOMAIN)
	const progressBarStyle = {
		width : props.value + '%'
	}

	return (
		<div className="aioseo-headline-analyzer-words-block">
			<h5>{props.title}</h5>
			<div className="aioseo-headline-analyzer-words-block-data">
				<span
					className={classnames(
						'aioseo-headline-analyzer-words-block-percentage',
						props.classOnScore
					)}
				>
					{props.value}%
				</span>
				<span className={classnames(
					'aioseo-headline-analyzer-words-block-percentage-right-column',
					props.classOnScore
				)}>
					<span className="aioseo-headline-analyzer-words-block-goal">
						{textGoal} {props.goalValue}
					</span>
					<span className="aioseo-headline-analyzer-words-block-progressbar">
						<span className="aioseo-headline-analyzer-progressbar-bg"></span>
						<span
							className={classnames(
								'aioseo-headline-analyzer-progressbar-part',
								props.classOnScoreBg
							)}
							style={progressBarStyle}
						></span>
					</span>
				</span>
			</div>
			<ul className="aioseo-headline-analyzer-words-tag-list">
				{0 < props.words.length
					? props.words.map((word, index) => {
						return <li key={index}>{word}</li>
					  })
					: ''}
			</ul>
			<p className="aioseo-headline-analyzer-words-guideline">
				{props.guideLine}
			</p>
		</div>
	)
}

export default HeadlineWordsBlock