import { html } from '@/vue/standalone/blocks/utils'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	improve      : __('Improve', td),
	selectAction : __('Select action', td),
	summarize    : __('Summarize', td),
	makeLonger   : __('Make longer', td),
	makeShorter  : __('Make shorter', td)
}

const {
	Dropdown,
	ToolbarButton,
	ToolbarGroup,
	MenuGroup,
	MenuItem
} = window.wp?.components || {}

const improveOptions = [
	strings.summarize,
	strings.makeLonger,
	strings.makeShorter
]

export const ImproveSelectorMenu = ({ attributes, onChange }) => {
	return html`
<${ToolbarGroup}>
	<${Dropdown}
		popoverProps=${{ placement: 'bottom-start' }}
		renderToggle=${({ isOpen, onToggle }) => html`
			<${ToolbarButton}
				disabled=${!attributes.content || attributes.isFetching}
				icon="admin-customizer"
				label="${strings.improve}"
				onClick=${onToggle}
				aria-expanded=${isOpen}
			/>
		`}
		renderContent=${({ onClose }) => html`
			<${MenuGroup} label="${strings.selectAction}">
				${improveOptions.map(improve => html`
					<${MenuItem} onClick=${() => { onChange(improve); onClose() }}>
						${improve}
					</${MenuItem}>
				`)}
			</${MenuGroup}>
		`}
	/>
</${ToolbarGroup}>`
}