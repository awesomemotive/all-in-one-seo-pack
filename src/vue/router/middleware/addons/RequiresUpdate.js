import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

export default function RequiresUpdate ({ next, router, to }) {
	const licenseStore = useLicenseStore()
	if (licenseStore.isUnlicensed || !addons.hasMinimumVersion(to.meta.middlewareData.addon)) {
		next()
		return router.push({ name: to.meta.middlewareData.routeName })
			.catch(() => {})
	}

	return next()
}