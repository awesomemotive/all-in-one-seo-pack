const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components
const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlinePanelStartEndWords = props => {
	const textPanelTitle = __(
		'Beginning & Ending Words',
		td
	)
	const words = props.data.result.originalExplodedHeadline
	const guideLineText = __(
		'Most readers only look at the first and last 3 words of a headline before deciding whether to click.',
		td
	)
	let beginningWords = '',
	 endingWords = ''

	if (6 <= words.length) {
		beginningWords = words.slice(0, 3).join(' ')
		endingWords = words.slice(-3).join(' ')
	} else if (3 < words.length && 5 >= words.length) {
		beginningWords = words.slice(0, 3).join(' ')
		endingWords = words.slice(3).join(' ')
	} else {
		beginningWords = words.slice(0, 3).join(' ')
	}

	return (
		<Fragment>
			<PanelBody
				title={textPanelTitle}
				className="aioseo-headline-analyzer-panel-beginning-ending-words"
			>
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						{beginningWords
							? (
								<>
									<ul className="aioseo-headline-analyzer-word-begining-title">
										<li>{ __('Beginning Words', td) }</li>
									</ul>
									<div className="aioseo-headline-analyzer-words beginning">
										<span>{beginningWords}</span>
									</div>
								</>
							)
							: (
								''
							)}
						{endingWords
							? (
								<>
									<ul className="aioseo-headline-analyzer-word-ending-title">
										<li>{ __('Ending Words', td) }</li>
									</ul>
									<div className="aioseo-headline-analyzer-words ending">
										<span>{endingWords}</span>
									</div>
								</>
							)
							: (
								''
							)}
						<p className="aioseo-headline-analyzer-words-guideline">
							{guideLineText}
						</p>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelStartEndWords