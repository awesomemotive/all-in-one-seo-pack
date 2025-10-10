import { html } from '@/vue/standalone/blocks/utils'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	selectLanguage : __('Select language', td),
	translate      : __('Translate', td),
	arabic         : __('Arabic', td),
	chinese        : __('Chinese', td),
	dutch          : __('Dutch', td),
	english        : __('English', td),
	french         : __('French', td),
	german         : __('German', td),
	hindi          : __('Hindi', td),
	italian        : __('Italian', td),
	japanese       : __('Japanese', td),
	korean         : __('Korean', td),
	portuguese     : __('Portuguese', td),
	spanish        : __('Spanish', td),
	turkish        : __('Turkish', td)
}

const {
	Dropdown,
	ToolbarButton,
	ToolbarGroup,
	MenuGroup,
	MenuItem
} = window.wp.components

const languageOptions = [
	strings.arabic,
	strings.chinese,
	strings.dutch,
	strings.english,
	strings.french,
	strings.german,
	strings.hindi,
	strings.italian,
	strings.japanese,
	strings.korean,
	strings.portuguese,
	strings.spanish,
	strings.turkish
]

export const TranslateSelectorMenu = ({ attributes, onChange }) => {
	return html`
<${ToolbarGroup}>
	<${Dropdown}
		popoverProps=${{ placement: 'bottom-start' }}
		renderToggle=${({ isOpen, onToggle }) => html`
			<${ToolbarButton}
				disabled=${!attributes.content || attributes.isFetching}
				icon="admin-site-alt3"
				label="${strings.translate}"
				onClick=${onToggle}
				aria-expanded=${isOpen}
			/>
		`}
		renderContent=${({ onClose }) => html`
			<${MenuGroup} label="${strings.selectLanguage}">
				${languageOptions.map(language => html`
					<${MenuItem} onClick=${() => { onChange(language); onClose() }}>
						${language}
					</${MenuItem}>
				`)}
			</${MenuGroup}>
		`}
	/>
</${ToolbarGroup}>`
}