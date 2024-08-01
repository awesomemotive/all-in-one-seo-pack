import icons from '../icons'
import classnames from 'classnames'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components
const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlinePanelWordCount = props => {
	const textWordCount = __('Word Count', td)
	const wordLength = props.data.result.wordCount
	const wordLengthString = wordLength.toString()
	let classOnLength = '',
	 statusOnLength = '',
	 descOnWordLength = '',
	 wordCountDisplay = ''

	 if (1 === wordLengthString.length) {
		wordCountDisplay = `<span class="character-zero">0</span><span class="character-zero">0</span><span>${wordLength}</span>`
	}

	if (2 === wordLengthString.length) {
		wordCountDisplay = '<span class="character-zero">0</span>'
		for (const char of wordLengthString) {
			wordCountDisplay += `<span>${char}</span>`
		}
	}

	if (3 === wordLengthString.length) {
		for (const char of wordLengthString) {
			wordCountDisplay += `<span>${char}</span>`
		}
	}

	if (4 >= wordLength) {
		classOnLength = 'red'
		statusOnLength = __('Not Enough Words 🙃', td)
		descOnWordLength = __(
			'Your headline doesn’t use enough words. You have more space to add keywords and power words to improve your SEO and get more engagement.',
			td
		)
	} else if (5 <= wordLength && 9 >= wordLength) {
		classOnLength = 'green'
		statusOnLength = __('Good 🙂', td)
		descOnWordLength = __(
			'Your headline has the right amount of words. Headlines are more likely to be clicked on in search results if they have about 6 words.',
			td
		)
	} else if (10 <= wordLength && 11 >= wordLength) {
		classOnLength = 'orange'
		statusOnLength = __(
			'Reduce Word Count 🙂',
			td
		)
	} else {
		classOnLength = 'red'
		statusOnLength = __('Too Many Words 😑', td)
		descOnWordLength = __(
			'Your headline has too many words. Long headlines will get cut off in search results and won’t get as many clicks.',
			td
		)
	}

	return (
		<Fragment>
			<PanelBody
				title={textWordCount}
				className={classnames(
					'aioseo-headline-analyzer-panel-word-count',
					'aioseo-headline-analyzer-panel-has-icon',
					classOnLength
				)}
				icon={'green' === classOnLength ? icons.check : icons.warning}
			>
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						<div className="aioseo-headline-analyzer-word-counter">
							<span className="aioseo-headline-analyzer-status-on-word-length">
								{statusOnLength}
							</span>
							<span
								className={classnames(
									'aioseo-headline-analyzer-word-length',
									classOnLength
								)}
								dangerouslySetInnerHTML={{ __html: wordCountDisplay }}
							>
							</span>
						</div>
						<p>{descOnWordLength}</p>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelWordCount