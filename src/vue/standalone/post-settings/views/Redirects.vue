<template>
	<div class="aioseo-tab-content">
		<Redirects
			v-if="!licenseStore.isUnlicensed && addons.isActive('aioseo-redirects') && !addons.requiresUpgrade('aioseo-redirects')"
			:parentComponentContext="parentComponentContext"
		/>

		<RedirectsLite
			v-if="licenseStore.isUnlicensed || addons.requiresUpgrade('aioseo-redirects')"
			:parentComponentContext="parentComponentContext"
		/>

		<RedirectsActivate
			v-if="!licenseStore.isUnlicensed && !addons.isActive('aioseo-redirects') && addons.canActivate('aioseo-redirects') && !addons.requiresUpgrade('aioseo-redirects')"
			:parentComponentContext="parentComponentContext"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import Redirects from './AIOSEO_VERSION/partials-redirects/Redirects'
import RedirectsActivate from './AIOSEO_VERSION/partials-redirects/RedirectsActivate'
import RedirectsLite from './lite/partials-redirects/Redirects'

export default {
	setup () {
		return {
			addons,
			licenseStore : useLicenseStore()
		}
	},
	components : {
		Redirects,
		RedirectsActivate,
		RedirectsLite
	},
	props : {
		parentComponentContext : String
	}
}
</script>