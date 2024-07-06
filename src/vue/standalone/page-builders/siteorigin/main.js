import initWatcher from './watcher'
import initLiveEditor from './live-editor'

/**
 * Init the SiteOrigin integration.
 *
 * @param {Object} builderView The builder view.
 * @returns {void}
 */
const init = (builderView) => {
	// Initialize the live editor.
	initLiveEditor(builderView)

	// Initialize the editor data watcher.
	initWatcher(builderView)
}

let preload = false
const { soPanelsBuilderView } = window
if (undefined !== soPanelsBuilderView) {
	const builderView = Array.isArray(soPanelsBuilderView)
		? soPanelsBuilderView[0]
		: soPanelsBuilderView

	setTimeout(() => {
		init(builderView)
	})

	preload = true
}

(function ($) {
	if (preload) {
		return
	}

	$(document).on('panels_setup', (_e, builderView) => {
		setTimeout(() => { init(builderView) })
	})
})(window.jQuery)