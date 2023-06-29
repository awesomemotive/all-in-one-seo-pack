import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

export default function RequiresActivation ({ next, router, to }) {
	const licenseStore = useLicenseStore()
	if (licenseStore.isUnlicensed || !addons.isActive(to.meta.middlewareData.addon)) {
		next()
		return router.push({ name: to.meta.middlewareData.routeName })
			.catch(() => {})
	}

	return next()
}