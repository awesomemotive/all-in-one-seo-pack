<template>
	<div>
		<link-assistant
			v-if="shouldShowMain || licenseStore.isUnlicensed"
			:links="links"
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
import Activate from './link-assistant/Activate'
import LinkAssistant from './link-assistant/LinkAssistant'
import Update from './link-assistant/Update'
import Upgrade from './link-assistant/Upgrade'

const licenseStore = useLicenseStore()

const addonSlug = 'aioseo-link-assistant'
const {
	shouldShowActivate,
	shouldShowMain,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug
})

defineProps({
	links : Object
})
</script>

<style lang="scss">
.aioseo-app .aioseo-search-statistics-link-assistant {
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