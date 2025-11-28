<template>
	<div>
		<redirects
			v-if="!licenseStore.isUnlicensed && rootStore.isPro"
			:redirects="redirects"
		/>

		<upgrade
			v-if="licenseStore.isUnlicensed || !rootStore.isPro"
		/>
	</div>
</template>

<script setup>
import {
	useRootStore,
	useLicenseStore
} from '@/vue/stores'

import Redirects from './redirects/Redirects'
import Upgrade from './redirects/Upgrade'

const rootStore     = useRootStore()
const licenseStore  = useLicenseStore()

defineProps({
	redirects : Object
})
</script>

<style lang="scss">
.aioseo-app .aioseo-search-statistics-redirects {
	font-size: 14px;

	.aioseo-cta {
		.aioseo-cta-background {
			.header-text {
				font-size: 15px;
			}

			.description {
				font-size: 14px;
			}
		}
	}
}
</style>