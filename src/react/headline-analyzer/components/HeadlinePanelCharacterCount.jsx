import icons from '../icons'
import classnames from 'classnames'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components
const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlinePanelCharacterCount = props => {
	const textPanelTitle = __(
		'Character Count',
		td
	)
	const characterLength = props.data.result.length
	const characterLengthString = characterLength.toString()
	let classOnLength = '', statusOnLength = '', descOnCharLength = '', characterDisplay = ''

	if (1 === characterLengthString.length) {
		characterDisplay = `<span class="character-zero">0</span><span class="character-zero">0</span><span>${characterLength}</span>`
	}

	if (2 === characterLengthString.length) {
		characterDisplay = '<span class="character-zero">0</span>'
		for (const char of characterLengthString) {
			characterDisplay += `<span>${char}</span>`
		}
	}

	if (3 === characterLength.toString().length) {
		for (const char of characterLengthString) {
			characterDisplay += `<span>${char}</span>`
		}
	}

	if (19 >= characterLength) {
		classOnLength = 'red'
	} else if (20 <= characterLength && 34 >= characterLength) {
		classOnLength = 'orange'
	} else if (35 <= characterLength && 66 >= characterLength) {
		classOnLength = 'green'
	} else if (67 <= characterLength && 79 >= characterLength) {
		classOnLength = 'orange'
	} else if (80 <= characterLength) {
		classOnLength = 'red'
	}

	if (34 >= characterLength) {
		statusOnLength = __('Too Short 🙃', td)
		descOnCharLength = __(
			'You have space to add more keywords and power words to boost your rankings and click-through rate.',
			td
		)
	} else if (35 <= characterLength && 66 >= characterLength) {
		statusOnLength = __('Good 🙂', td)
		descOnCharLength = __(
			'Headlines that are about 55 characters long will display fully in search results and tend to get more clicks.',
			td
		)
	} else if (67 <= characterLength) {
		statusOnLength = __('Too Long 😑', td)
		descOnCharLength = __(
			'At this length, it will get cut off in search results. Try reducing it to about 55 characters.',
			td
		)
	}

	return (
		<Fragment>
			<PanelBody
				title={textPanelTitle}
				className={classnames(
					'aioseo-headline-analyzer-panel-character-count',
					'aioseo-headline-analyzer-panel-has-icon',
					classOnLength
				)}
				icon={'green' === classOnLength ? icons.check : icons.warning}
			>
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						<div className="aioseo-headline-analyzer-character-count-container">
							<span className="aioseo-headline-analyzer-status-on-character-length">
								{statusOnLength}
							</span>
							<span
								className={classnames(
									'aioseo-headline-analyzer-character-length',
									classOnLength
								)}
								dangerouslySetInnerHTML={{ __html: characterDisplay }}
							></span>
						</div>
						<p>{descOnCharLength}</p>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelCharacterCount