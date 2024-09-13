<template>
	<core-alert
		class="aioseo-required-plans"
		v-if="licenseStore.isUnlicensed || showAlert"
		type="blue"
	>
		{{ requiredPlansString }} <strong>{{ getRequiredPlans }}</strong>
	</core-alert>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import license from '@/vue/utils/license'
import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	components : {
		CoreAlert
	},
	props : {
		addon       : String,
		coreFeature : {
			type : Array,
			default () {
				return []
			}
		},
		addonFeature : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			strings : {
				thisFeatureRequires         : __('This feature requires one of the following plans:', td),
				thisFeatureRequiresSingular : __('This feature requires the following plan:', td)
			}
		}
	},
	computed : {
		requiredPlansString () {
			return 1 < this.requiredPlans.length
				? this.strings.thisFeatureRequires
				: this.strings.thisFeatureRequiresSingular
		},
		getRequiredPlans () {
			return this.requiredPlans.join(', ')
		},
		showAlert () {
			return addons.requiresUpgrade(this.addon) && this.requiredPlans.length
		},
		requiredPlans () {
			if (this.coreFeature.length || this.addonFeature.length) {
				const sectionSlug = this.coreFeature[0] || this.addonFeature[0]
				const feature     = this.coreFeature.length
					? ('undefined' !== typeof this.coreFeature[1] ? this.coreFeature[1] : '')
					: ('undefined' !== typeof this.addonFeature[1] ? this.addonFeature[1] : '')
				return license.getPlansForFeature(sectionSlug, feature)
			}

			return addons.currentPlans(this.addon) || []
		}
	}
}
</script>

<style lang="scss">
.aioseo-app,
#aioseo-user-profile-tab {
	.aioseo-cta .aioseo-cta-background .description .aioseo-alert.aioseo-required-plans {
		text-align: center;
	}
}
</style>