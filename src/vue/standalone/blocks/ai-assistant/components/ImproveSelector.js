import { html } from '@/vue/standalone/blocks/utils'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	improve          : __('Improve', td),
	contentLength    : __('Content Length', td),
	contentStructure : __('Content Structure', td),
	contentType      : __('Content Type', td),
	contentTone      : __('Content Tone', td)
}

const {
	Dropdown,
	ToolbarButton,
	ToolbarGroup,
	MenuGroup,
	MenuItem
} = window.wp?.components || {}

const improveOptions = {
	contentLength : [
		{ value: 'summarize', label: __('Summarize', td) },
		{ value: 'makeLonger', label: __('Make longer', td) },
		{ value: 'makeShorter', label: __('Make shorter', td) }
	],
	contentStructure : [
		{ value: 'expandOnKeyPoints', label: __('Expand on key points', td) },
		{ value: 'reorganizeForClarity', label: __('Reorganize for clarity / bullet points', td) },
		{ value: 'addExamples', label: __('Add examples / case studies', td) },
		{ value: 'removeFiller', label: __('Remove filler / make concise', td) }
	],
	contentType : [
		{ value: 'rewriteAsBlog', label: __('Rewrite as blog', td) },
		{ value: 'rewriteAsListicle', label: __('Rewrite as listicle', td) },
		{ value: 'rewriteAsStory', label: __('Rewrite as story / narrative', td) },
		{ value: 'rewriteForSeo', label: __('Rewrite for SEO (keyword-focused)', td) }
	],
	contentTone : [
		{ value: 'makeMoreFormal', label: __('Make more formal', td) },
		{ value: 'makeLessFormal', label: __('Make less formal', td) },
		{ value: 'makeMoreHumorous', label: __('Make more humorous', td) },
		{ value: 'makeMoreSerious', label: __('Make more serious', td) },
		{ value: 'makeMoreProfessional', label: __('Make more professional', td) },
		{ value: 'makeMoreCasual', label: __('Make more casual', td) }
	]
}

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
			<${MenuGroup} label="${strings.contentLength}">
				${improveOptions.contentLength.map(improve => html`
					<${MenuItem} onClick=${() => { onChange(improve.value); onClose() }}>
						${improve.label}
					</${MenuItem}>
				`)}
			</${MenuGroup}>

			<${MenuGroup} label="${strings.contentStructure}">
				${improveOptions.contentStructure.map(improve => html`
					<${MenuItem} onClick=${() => { onChange(improve.value); onClose() }}>
						${improve.label}
					</${MenuItem}>
				`)}
			</${MenuGroup}>

			<${MenuGroup} label="${strings.contentType}">
				${improveOptions.contentType.map(improve => html`
					<${MenuItem} onClick=${() => { onChange(improve.value); onClose() }}>
						${improve.label}
					</${MenuItem}>
				`)}
			</${MenuGroup}>

			<${MenuGroup} label="${strings.contentTone}">
				${improveOptions.contentTone.map(improve => html`
					<${MenuItem} onClick=${() => { onChange(improve.value); onClose() }}>
						${improve.label}
					</${MenuItem}>
				`)}
			</${MenuGroup}>
		`}
	/>
</${ToolbarGroup}>`
}