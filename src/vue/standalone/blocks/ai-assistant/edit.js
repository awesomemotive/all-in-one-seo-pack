import { h, createApp } from 'vue'

import {
	loadPiniaStores,
	useOptionsStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
import { useAiContent } from '@/vue/composables/AiContent'
import { observeElement } from '@/vue/utils/helpers'
import { html, maybeDeleteBlockVueApp } from '@/vue/standalone/blocks/utils'

import {
	TranslateSelectorMenu,
	ImproveSelectorMenu,
	ToneSelectorPanel,
	AudienceSelectorPanel
} from './components'

import VueBlock from './vue/Block'
import VueSidebar from './vue/Sidebar'

const td = import.meta.env.VITE_TEXTDOMAIN

const {
	BlockControls,
	InspectorControls
} = window.wp.blockEditor

const { PanelBody } = window.wp.components
const { useEffect } = window.wp.element

const aiAssistantApps = []
const strings         = {
	notFanOfOptions : __('Not a fan of these options? You can set your own tone/audience directly in your prompt.', td)
}

export default function edit (props) {
	const optionsStore = useOptionsStore()

	const { setAttributes, attributes, clientId, className, isSelected, toggleSelection } = props
	const { toneOptions, audienceOptions } = useAiContent()

	const blockAppId   = `aioseo-block-${clientId}`
	const sidebarAppId = `aioseo-sidebar-${clientId}`
	const $block       = document.getElementById(blockAppId)

	const $blockParent = document.querySelector('.block-editor')
	if (isSelected || $block) {
		observeElement({
			id      : blockAppId,
			parent  : $blockParent,
			subtree : true,
			loop    : false,
			done    : (node) => {
				if (node.firstChild) {
					return
				}

				maybeDeleteBlockVueApp(blockAppId, aiAssistantApps)

				const app = createApp({
					name  : 'Blocks/AiAssistant',
					data  : () => attributes,
					watch : {
						'$data.userPrompt' : {
							handler : function (value) {
								setAttributes({ userPrompt: value })
							}
						},
						'$data.isFetching' : {
							handler : function (value) {
								setAttributes({ isFetching: value })
							}
						},
						'$data.content' : {
							handler : function (value) {
								setAttributes({ content: value })
							}
						},
						'$data.messages' : {
							handler : function (value) {
								setAttributes({ messages: value })
							}
						}
					},
					render : () => h(VueBlock)
				})

				loadPiniaStores(app)

				app.mount(node)

				aiAssistantApps.push({
					id  : blockAppId,
					app : app
				 })
			}
		})
	}

	const sidebarApp = aiAssistantApps.find(v => sidebarAppId === v.id)
	const $sidebarParent = document.querySelector('.interface-interface-skeleton__sidebar')
	if (
		($block && $sidebarParent) &&
		(!sidebarApp || !$sidebarParent.contains(sidebarApp.app._container))
	) {
		observeElement({
			id      : sidebarAppId,
			parent  : $sidebarParent,
			subtree : true,
			loop    : false,
			done    : (node) => {
				maybeDeleteBlockVueApp(sidebarAppId, aiAssistantApps)

				const app = createApp({
					name   : 'Blocks/AiAssistant/Sidebar',
					render : () => h(VueSidebar)
				})

				loadPiniaStores(app)

				app.mount(node)

				aiAssistantApps.push({
					id  : sidebarAppId,
					app : app
				})
			}
		})
	}

	const generalSidebarName = window.wp.data.useSelect(
		select => select('core/edit-post').getActiveGeneralSidebarName()
	)
	if ('edit-post/block' === generalSidebarName) {
		'function' !== typeof toggleSelection || toggleSelection(true)
	}

	useEffect(() => {
		// Runs when the block is removed.
		return () => {
			maybeDeleteBlockVueApp(blockAppId, aiAssistantApps)
			maybeDeleteBlockVueApp(sidebarAppId, aiAssistantApps)
		}
	}, [])

	const handleToneChange = (newTone) => {
		setAttributes({ tone: newTone.toLowerCase() })
	}

	const handleAudienceChange = (newAudience) => {
		setAttributes({ audience: newAudience.toLowerCase() })
	}

	const handleTranslateChange = (newTranslate) => {
		window.aioseoBus.$emit('aiAssistantTranslateChange', {
			clientId,
			translate : newTranslate
		})
	}

	const handleImproveChange = (newImprove) => {
		window.aioseoBus.$emit('aiAssistantImproveChange', {
			clientId,
			improve : newImprove
		})
	}

	const defaultTone = toneOptions.find(t => t.value === optionsStore.options.aiContent.tone) || toneOptions[0]
	if (!attributes.tone) {
		setAttributes({ tone: defaultTone.value })
	}

	const defaultAudience = audienceOptions.find(a => a.value === optionsStore.options.aiContent.audience) || audienceOptions[0]
	if (!attributes.audience) {
		setAttributes({ audience: defaultAudience.value })
	}

	return html`
	<${BlockControls}>
		<${TranslateSelectorMenu}
			attributes=${attributes}
			onChange=${handleTranslateChange}
		/>

		<${ImproveSelectorMenu}
			attributes=${attributes}
			onChange=${handleImproveChange}
		/>
	</${BlockControls}>

	<${InspectorControls}>
		<${PanelBody} initialOpen=${true}>
			<${ToneSelectorPanel}
				value=${attributes.tone}
				onChange=${handleToneChange}
			/>

			<${AudienceSelectorPanel}
				value=${attributes.audience}
				onChange=${handleAudienceChange}
			/>

			<div>${strings.notFanOfOptions}</div>
		</${PanelBody}>

		<${PanelBody} initialOpen=${true}>
			<div
				id=${sidebarAppId}
				class="aioseo-app"
			 />
		</${PanelBody}>
	</${InspectorControls}>

	<div className="${className}">
		<div id=${blockAppId} />
	</div>`
}