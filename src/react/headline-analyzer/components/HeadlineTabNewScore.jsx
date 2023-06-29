import { http, restUrl } from './../functions'
import classnames from 'classnames'
const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow, TextControl, Button } = window.wp.components
const td = import.meta.env.VITE_TEXTDOMAIN

const HeadlineTabNewScore = props => {
	const textPanelTitle = __(
		'Try New Headline',
		td
	)
	const textNewHeadlineInputLabel = __(
		'Enter a different headline than your post title to see how it compares.',
		td
	)
	const textAnalyze = __('Analyze Headline', td)
	const newHeadline =
		'undefined' !== typeof props.analyzer.newHeadline
			? props.analyzer.newHeadline
			: ''
	const previousScores =
		'undefined' !== typeof props.analyzer.previousHeadlinesData
			? props.analyzer.previousHeadlinesData
			: []
	const isDisabled = !newHeadline

	const fetchNewHeadlineData = value => {
		const headline = value.trim()

		if (!headline) {
			return
		}

		http(window.aioseo.nonce).post(restUrl('analyze-headline'))
			.send({
				headline            : headline,
				shouldStoreHeadline : false
			})
			.then(response => {
				const headlineResult = JSON.parse(response.body[Object.keys(response.body)[0]])
				props.setAnalyzer({
					newHeadlineData       : headlineResult,
					headlineData          : headlineResult,
					previousHeadlinesData : [
						props.analyzer.headlineData,
						...previousScores
					],
					isNewData : true
				})
			})
			.catch(error => {
				props.setAnalyzer({
					isNewData : false
				})
				console.log(error)
			})
	}

	return (
		<Fragment>
			<PanelBody title={textPanelTitle} className={classnames(
				'aioseo-headline-analyzer-panel-tab-new-score-form'
			)}>
				<PanelRow>
					<div className="aioseo-headline-analyzer-new-tab">
						<div className="aioseo-headline-analyzer-new-score-form-block">
							<form
								onSubmit={e => {
									e.preventDefault()
									fetchNewHeadlineData(newHeadline)
								}}
							>
								<TextControl
									label={textNewHeadlineInputLabel}
									value={newHeadline}
									onChange={newValue => {
										if (' ' !== newValue) {
											props.setAnalyzer({
												newHeadline : newValue
											})
										}
									}}
									className="aioseo-headline-analyzer-input-field"
								/>
								<Button
									className={
										classnames('aioseo-headline-analyzer-button')
									}
									onClick={() => {
										fetchNewHeadlineData(newHeadline)
									}}
									disabled={isDisabled}
								>
									{textAnalyze}
								</Button>
							</form>
						</div>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlineTabNewScore