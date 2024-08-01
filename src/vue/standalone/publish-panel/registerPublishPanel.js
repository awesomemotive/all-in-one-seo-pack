import {
	loadPiniaStores,
	useRootStore
} from '@/vue/stores'

import { shouldShowMetaBox } from '@/vue/utils/metabox'
import { isBlockEditor } from '@/vue/utils/context'

(function (wp) {
	if (
		!isBlockEditor() ||
		!shouldShowMetaBox() ||
		(
			!window.wp?.editor?.PluginDocumentSettingPanel &&
			!window.wp?.editPost?.PluginDocumentSettingPanel
		)
	) {
		return
	}

	const PluginDocumentSettingPanel = window?.editor?.PluginDocumentSettingPanel || window.wp?.editPost?.PluginDocumentSettingPanel
	const PluginPrePublishPanel      = window?.editor?.PluginPrePublishPanel || window.wp?.editPost?.PluginPrePublishPanel
	const PluginPostPublishPanel     = window?.editor?.PluginPostPublishPanel || window.wp?.editPost?.PluginPostPublishPanel
	const registerPlugin             = wp.plugins.registerPlugin

	// We need to load the Pinia here since we are using the store outside an App.
	loadPiniaStores()
	const rootStore = useRootStore()
	const user      = rootStore.aioseo.user
	if (
		!user.capabilities.aioseo_page_analysis &&
		!user.capabilities.aioseo_page_general_settings &&
		!user.capabilities.aioseo_page_advanced_settings
	) {
		return
	}

	registerPlugin('aioseo-publish-panel', {
		render : () => {
			return wp.element.createElement(wp.element.Fragment, {},
				wp.element.createElement(
					PluginDocumentSettingPanel,
					{
						title     : import.meta.env.VITE_SHORT_NAME,
						className : 'aioseo-document-setting aioseo-seo-overview',
						icon      : wp.element.Fragment
					},
					wp.element.createElement(
						'div',
						{},
						wp.element.createElement('div', { id: 'aioseo-document-setting' })
					)
				),
				wp.element.createElement(
					PluginPrePublishPanel,
					{
						title : [
							import.meta.env.VITE_SHORT_NAME,
							':',
							wp.element.createElement('span', { key: 'scoreDescription', className: 'editor-post-publish-panel__link' })
						],
						className   : 'aioseo-pre-publish aioseo-seo-overview',
						initialOpen : true,
						icon        : wp.element.Fragment
					},
					wp.element.createElement(
						'div',
						{},
						wp.element.createElement('div', { id: 'aioseo-pre-publish' })
					)
				),
				wp.element.createElement(
					PluginPostPublishPanel,
					{
						title       : import.meta.env.VITE_SHORT_NAME,
						initialOpen : true,
						icon        : wp.element.Fragment
					},
					wp.element.createElement(
						'div',
						{},
						wp.element.createElement('div', { id: 'aioseo-post-publish' })
					)
				)
			)
		}
	})
})(window.wp)