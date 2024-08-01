import { typeLinkText } from '../constants'

const { __ } = window.wp.i18n
const { Fragment } = window.wp.element
const { PanelBody, PanelRow } = window.wp.components

const HeadlinePanelType = (props) => {
	const headlineTypes = props.data.result.headlineTypes.join(', ')
	const panelTitle = __('Headline Type', import.meta.env.VITE_TEXTDOMAIN)
	const typePanelTitle = (
		<span className="aioseo-headline-analyzer-panel-types-title">
			{panelTitle}
			<span>{headlineTypes}</span>
		</span>
	)

	return (
		<Fragment>
			<PanelBody
				title={typePanelTitle}
				className="aioseo-headline-analyzer-panel-types"
			>
				<PanelRow>
					<div className="aioseo-headline-analyzer-panel-first-block">
						<h4>{headlineTypes}</h4>
						<p dangerouslySetInnerHTML={{ __html: typeLinkText }}></p>
					</div>
				</PanelRow>
			</PanelBody>
		</Fragment>
	)
}

export default HeadlinePanelType