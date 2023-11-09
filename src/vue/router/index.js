import { createRouter, createWebHashHistory } from 'vue-router'

import {
	loadPiniaStores,
	useRedirectsStore,
	useRootStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

// Creates a `nextMiddleware()` function which not only
// runs the default `next()` callback but also triggers
// the subsequent Middleware function.
const nextFactory = (context, middleware, index) => {
	const subsequentMiddleware = middleware[index]
	// If no subsequent Middleware exists,
	// the default `next()` callback is returned.
	if (!subsequentMiddleware) {
		return context.next
	}

	return () => {
		// Run the subsequent Middleware with a new `nextMiddleware()` callback.
		const nextMiddleware = nextFactory(context, middleware, index + 1)

		subsequentMiddleware({ ...context, next: nextMiddleware })
	}
}

export default (paths, app) => {
	const router = createRouter({
		history : createWebHashHistory(`wp-admin/admin.php?page=aioseo-${window.aioseo.page}`),
		routes  : paths,
		scrollBehavior (to, from, savedPosition) {
			if (savedPosition) {
				return savedPosition
			}
			if (to.hash) {
				return { selector: to.hash }
			}
			return { left: 0, top: 0 }
		}
	})

	router.beforeEach((to, from, next) => {
		const rootStore      = useRootStore()
		const redirectsStore = useRedirectsStore()

		if (!rootStore.loaded) {
			loadPiniaStores(app)
		}

		// Make sure the API is available.
		rootStore.ping()

		const access = to.meta.access

		if (!allowed(access)) {
			return to.meta.home !== from.name ? router.replace({ name: to.meta.home }) : null
		}

		if (to.meta.middleware) {
			const middleware = Array.isArray(to.meta.middleware) ? to.meta.middleware : [ to.meta.middleware ]

			const context = {
				app,
				from,
				next,
				router,
				to
			}
			const nextMiddleware = nextFactory(context, middleware, 1)

			return middleware[0]({ ...context, next: nextMiddleware })
		}

		// Reset state here.
		redirectsStore.resetPageNumbers()

		return next()
	})

	return router
}