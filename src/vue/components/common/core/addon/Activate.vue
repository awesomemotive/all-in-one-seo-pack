<template>
	<cta
		:cta-button-visible="addons.userCanInstallOrActivate(addonSlug)"
		:cta-button-visible-warning="strings.permissionWarning"
		:cta-link="`${rootStore?.aioseo?.urls.aio.featureManager}&aioseo-activate=${addonSlug}`"
		cta-button-action
		:cta-button-loading="activationLoading"
		@cta-button-click="activateAddon"
		same-tab
		:button-text="ctaButtonText"
		:learn-more-link="learnMoreLink"
		:feature-list="featureList"
		:align-top="alignTop"
		hide-bonus
	>
		<template #header-text>
			{{ ctaHeader }}
		</template>
		<template #description>
			<core-alert
				v-if="failed"
				type="red"
			>
				{{ strings.activateError }}
			</core-alert>
			{{ ctaDescription }}
		</template>

		<template #learn-more-text>
			{{ learnMoreText }}
		</template>
	</cta>
</template>

<script>
import {
	useAddonsStore,
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import Cta from '@/vue/components/common/cta/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'addon-activated' ],
	setup () {
		return {
			addonsStore  : useAddonsStore(),
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreAlert,
		Cta
	},
	props : {
		addonSlug : {
			type     : String,
			required : true
		},
		featureList : {
			type    : Array,
			default : () => []
		},
		postActivationPromises : {
			type    : Array,
			default : () => []
		},
		ctaButtonText            : String,
		ctaHeader                : String,
		ctaDescription           : String,
		learnMoreText            : String,
		learnMoreLink            : String,
		alignTop                 : Boolean,
		preventGlobalAddonUpdate : Boolean
	},
	data () {
		return {
			addons,
			strings : {
				activateError     : __('An error occurred while activating the addon. Please upload it manually or contact support for more information.', td),
				permissionWarning : __('You currently don\'t have permission to activate this addon. Please ask a site administrator to activate first.', td)
			},
			failed            : false,
			activationLoading : false
		}
	},
	methods : {
		activateAddon () {
			this.failed            = false
			this.activationLoading = true
			const addon            = addons.getAddon(this.addonSlug)
			this.pluginsStore.installPlugins([ { plugin: addon.basename } ])
				.then(response => {
					if (response.body.failed.length) {
						this.activationLoading = false
						this.failed            = true
						return
					}

					const promises = this.postActivationPromises.map(promise => promise())

					Promise.all(promises)
						.then(() => {
							if (this.preventGlobalAddonUpdate) {
								this.$emit('addon-activated', addon)

								return
							}

							this.activationLoading  = false
							addon.hasMinimumVersion = true
							addon.isActive          = true
							this.addonsStore.updateAddon(addon)

							this.$emit('addon-activated', addon)
						})
				})
				.catch(() => {
					this.activationLoading = false
				})
		}
	}
}
</script>