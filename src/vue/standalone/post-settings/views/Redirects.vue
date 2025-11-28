<template>
	<div class="aioseo-tab-content">
		<Redirects
			v-if="!licenseStore.isUnlicensed && rootStore.isPro"
			:parentComponentContext="parentComponentContext"
		/>

		<RedirectsLite
			v-if="licenseStore.isUnlicensed || !rootStore.isPro"
			:parentComponentContext="parentComponentContext"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import Redirects from './AIOSEO_VERSION/partials-redirects/Redirects'
import RedirectsLite from './lite/partials-redirects/Redirects'

export default {
	setup () {
		return {
			rootStore    : useRootStore(),
			licenseStore : useLicenseStore()
		}
	},
	components : {
		Redirects,
		RedirectsLite
	},
	props : {
		parentComponentContext : String
	}
}
</script>