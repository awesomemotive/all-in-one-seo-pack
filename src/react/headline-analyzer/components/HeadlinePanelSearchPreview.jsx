const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components
const { select } = window.wp.data

const HeadlinePanelSearchPreview = props => {
	const panelTitle = __('Search Preview', import.meta.env.VITE_TEXTDOMAIN)
	const descText = __(
		'Here is how your headline will look like in Google search results page.',
		import.meta.env.VITE_TEXTDOMAIN
	)
	const postUrl = select('core/editor').getPermalink()
	const postUrlAttribute = { href: postUrl }

	return (
		<Fragment>
			<PanelBody
				title={panelTitle}
				className="aioseo-headline-analyzer-panel-search-preview"
			>
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						<div className="aioseo-headline-analyzer-search-prevew-wrap">
							<p className="aioseo-headline-analyzer-post-url">
								<a {...postUrlAttribute} target="_blank">
									{postUrl}
								</a>
							</p>
							<h4>{props.data.sentence}</h4>
							<p>{descText}</p>
						</div>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelSearchPreview