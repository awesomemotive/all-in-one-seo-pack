import {
	useLicenseStore
} from '@/vue/stores'

import license from '@/vue/utils/license'

export default function RequiresUpgrade ({ next, router, to }) {
	const licenseFeature = to.meta.licenseFeature || to.meta.middlewareData.licenseFeature || ''
	const licenseStore   = useLicenseStore()

	if (licenseStore.isUnlicensed || !license.hasCoreFeature(licenseFeature[0], licenseFeature[1])) {
		next()
		return router.push({ name: to.meta.middlewareData.routeName })
			.catch(() => {})
	}

	return next()
}