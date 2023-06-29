import translate from './translations'
import emitter from 'tiny-emitter/instance'

import VueClipboard from './vue-clipboard'

import links from '@/vue/utils/links'

import * as constants from './constants'

import VueScrollTo from 'vue-scrollto'

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

	app.use(VueScrollTo, {
		container  : 'body',
		duration   : 1000,
		easing     : 'ease-in-out',
		offset     : 0,
		force      : true,
		cancelable : true,
		onStart    : false,
		onDone     : false,
		onCancel   : false,
		x          : false,
		y          : true
	})

	app.provide('$constants', constants)
	app.provide('$isPro', 'pro' === import.meta.env.VITE_VERSION.toLowerCase())
	app.provide('$td', import.meta.env.VITE_TEXTDOMAIN)
	app.provide('$tdPro', import.meta.env.VITE_TEXTDOMAIN_PRO)
	app.provide('$links', links)
	app.provide('$t', translate)

	app.config.globalProperties.$constants        = constants
	app.config.globalProperties.$isPro            = 'pro' === import.meta.env.VITE_VERSION.toLowerCase()
	app.config.globalProperties.$td               = import.meta.env.VITE_TEXTDOMAIN
	app.config.globalProperties.$tdPro            = import.meta.env.VITE_TEXTDOMAIN_PRO

	// Vue 2 compatibility mode.
	app.$links = app.config.globalProperties.$links = links
	app.$t     = app.config.globalProperties.$t     = translate

	return app
}