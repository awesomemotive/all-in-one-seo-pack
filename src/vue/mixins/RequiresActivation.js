import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { RequiresActivation as RequiresActivationMiddleware } from '@/vue/router/middleware'

export const RequiresActivation = {
	methods : {
		getExcludedActivationTabs (addon) {
			const licenseStore = useLicenseStore()
			if (!licenseStore.isUnlicensed && addons.isActive(addon) && !addons.requiresUpgrade(addon)) {
				return []
			}

			const routes = []
			this.$router.options.routes.forEach(route => {
				if (!route.meta || !route.meta.middleware) {
					return
				}

				const middleware = Array.isArray(route.meta.middleware) ? route.meta.middleware : [ route.meta.middleware ]
				if (middleware.some(m => m === RequiresActivationMiddleware)) {
					routes.push(route.name)
				}
			})
			return routes
		}
	}
}