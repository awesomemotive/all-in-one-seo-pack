import { isBlockEditor } from '@/vue/utils/context'

if (isBlockEditor() && window.wp) {
	/**
	 * WordPress dependencies
	 */
	const { createElement }    = window.wp.element
	const { registerPlugin }   = window.wp.plugins
	const PluginPostStatusInfo = window.wp?.editor?.PluginPostStatusInfo || window.wp?.editPost?.PluginPostStatusInfo

	registerPlugin('aioseo-limit-modified-date', {
		render : () => {
			return createElement(PluginPostStatusInfo, {}, createElement('div', { id: 'aioseo-limit-modified-date' }))
		}
	})
}