import safeHref from './safe-href'
import safeHtml from './safe-html'

/**
 * Registers our custom directives on a Vue app.
 *
 * Every app has to call this. It is invoked for you by loadPlugins(), so only the entry points
 * that do not use loadPlugins() need to call it directly - the same way they already call
 * loadPinia(). An unregistered directive is a dev-only warning in Vue, so a missed app would
 * render blank content in production rather than failing loudly.
 *
 * @since 5.0.6
 *
 * @param  {Object} app The Vue app.
 * @return {Object}     The Vue app.
 */
export default app => {
	app.directive('safe-href', safeHref)
	app.directive('safe-html', safeHtml)

	return app
}

export { safeHref, safeHtml }