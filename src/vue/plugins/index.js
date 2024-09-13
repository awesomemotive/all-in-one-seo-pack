import emitter from 'tiny-emitter/instance'

import VueClipboard from './vue-clipboard'

import './emoji'

window.aioseo    = window.aioseo || {}
window.aioseoBus = window.aioseoBus || {
	$on   : (...args) => emitter.on(...args),
	$once : (...args) => emitter.once(...args),
	$off  : (...args) => emitter.off(...args),
	$emit : (...args) => emitter.emit(...args)
}

if (import.meta.env.PROD) {
	window.__aioseoDynamicImportPreload__ = filename => {
		return `${window.aioseo.urls.publicPath || '/'}dist/${import.meta.env.VITE_VERSION}/assets/${filename}`
	}
}

export default app => {
	app.use(VueClipboard)

	return app
}