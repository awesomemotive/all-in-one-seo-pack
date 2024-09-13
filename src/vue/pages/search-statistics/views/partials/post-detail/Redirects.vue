<template>
	<div>
		<redirects
			v-if="shouldShowMain || licenseStore.isUnlicensed"
			:redirects="redirects"
		/>

		<activate
			v-if="shouldShowActivate"
		/>

		<update
			v-if="shouldShowUpdate"
		/>

		<upgrade
			v-if="addons.requiresUpgrade(addonSlug)"
		/>
	</div>
</template>

<script setup>
import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { useAddonConditions } from '@/vue/composables/AddonConditions'
import Activate from './redirects/Activate'
import Redirects from './redirects/Redirects'
import Update from './redirects/Update'
import Upgrade from './redirects/Upgrade'

const licenseStore = useLicenseStore()

const addonSlug = 'aioseo-redirects'
const {
	shouldShowActivate,
	shouldShowMain,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug
})

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