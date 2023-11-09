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

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { AddonConditions } from '@/vue/mixins/AddonConditions'
import Activate from './link-assistant/Activate'
import LinkAssistant from './link-assistant/LinkAssistant'
import Update from './link-assistant/Update'
import Upgrade from './link-assistant/Upgrade'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	mixins     : [ AddonConditions ],
	components : {
		Activate,
		LinkAssistant,
		Update,
		Upgrade
	},
	props : {
		links : Object
	},
	data () {
		return {
			addons,
			addonSlug : 'aioseo-link-assistant'
		}
	}
}
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