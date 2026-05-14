import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	promptTemplates : __('Prompt Templates', td),
	selectTemplate  : __('Select a template', td),
	generateFaq     : __('Generate FAQ', td),
	generateHowTo   : __('Generate How-To', td)
}

const {
	Dropdown,
	ToolbarButton,
	ToolbarGroup,
	MenuGroup,
	MenuItem
} = window.wp?.components || {}

const templateOptions = [
	strings.generateFaq,
	strings.generateHowTo
]

export const PromptTemplateSelectorMenu = ({ attributes, onChange }) => {
	return (
		<ToolbarGroup>
			<Dropdown
				popoverProps={{ placement: 'bottom-start' }}
				renderToggle={({ isOpen, onToggle }) => (
					<ToolbarButton
						disabled={attributes.isFetching}
						icon="lightbulb"
						label={strings.promptTemplates}
						onClick={onToggle}
						aria-expanded={isOpen}
					/>
				)}
				renderContent={({ onClose }) => (
					<MenuGroup label={strings.selectTemplate}>
						{templateOptions.map(template => (
							<MenuItem
								key={template}
								onClick={() => { onChange(template); onClose() }}
							>
								{template}
							</MenuItem>
						))}
					</MenuGroup>
				)}
			/>
		</ToolbarGroup>
	)
}