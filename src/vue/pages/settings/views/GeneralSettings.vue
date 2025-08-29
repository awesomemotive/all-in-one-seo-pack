<template>
	<div class="aioseo-general-settings">
		<core-getting-started
			v-if="settingsStore.settings.showSetupWizard && allowed('aioseo_setup_wizard') && !rootStore.aioseo.data.isNetworkAdmin"
		/>

		<core-card
			slug="license"
			:header-text="strings.license"
		>
			<template
				v-if="!rootStore.isPro"
				#tooltip
			>
				<div v-html="tooltipText" />

				<br>

				<div
					class="more-tooltip-text"
					v-html="moreToolTipText"
				/>
			</template>

			<settings-license-key />

			<core-settings-row
				:name="strings.setupWizard"
				v-if="!settingsStore.settings.showSetupWizard && allowed('aioseo_setup_wizard') && !rootStore.aioseo.data.isNetworkAdmin"
			>
				<template #content>
					<base-button
						type="blue"
						size="medium"
						tag="a"
						:href="rootStore.aioseo.urls.aio.wizard"
					>
						<svg-rocket /> {{ strings.relaunchSetupWizard }}
					</base-button>

					<p class="aioseo-description">{{ strings.setupWizardText }}</p>
				</template>
			</core-settings-row>
		</core-card>

		<core-card
			slug="domainActivations"
			:header-text="strings.domainActivations"
			v-if="rootStore.aioseo.data.isNetworkAdmin"
		>
			<settings-network-sites-activation
				v-if="!licenseStore.isUnlicensed && license.hasCoreFeature('tools', 'network-tools-site-activation')"
			/>

			<lite-settings-network-sites-activation
				v-if="licenseStore.isUnlicensed || !license.hasCoreFeature('tools', 'network-tools-site-activation')"
			/>
		</core-card>
	</div>
</template>

<script>
import { DISCOUNT_PERCENTAGE } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import CoreCard from '@/vue/components/common/core/Card'
import CoreGettingStarted from '@/vue/components/common/core/GettingStarted'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import LiteSettingsNetworkSitesActivation from '@/vue/components/lite/settings/NetworkSitesActivation'
import SettingsLicenseKey from '@/vue/components/AIOSEO_VERSION/settings/LicenseKey'
import SettingsNetworkSitesActivation from '@/vue/components/AIOSEO_VERSION/settings/NetworkSitesActivation'
import SvgRocket from '@/vue/components/common/svg/Rocket'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore  : useLicenseStore(),
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore()
		}
	},
	components : {
		CoreCard,
		CoreGettingStarted,
		CoreSettingsRow,
		LiteSettingsNetworkSitesActivation,
		SettingsLicenseKey,
		SettingsNetworkSitesActivation,
		SvgRocket
	},
	data () {
		return {
			allowed,
			license,
			strings : {
				license  : __('License', td),
				boldText : sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_NAME,
					__('Free', td)
				),
				purchasedBoldText : sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_NAME,
					'Pro'
				),
				linkText : sprintf(
					// Translators: 1 - "Pro".
					__('upgrading to %1$s', td),
					'Pro'
				),
				moreBoldText : sprintf(
					'<strong>%1$s</strong>',
					// Translators: This refers to a discount (e.g. "As a valued user you receive 50%, automatically applied at checkout!").
					DISCOUNT_PERCENTAGE + ' ' + __('off', td)
				),
				setupWizard         : __('Setup Wizard', td),
				relaunchSetupWizard : __('Relaunch Setup Wizard', td),
				setupWizardText     : sprintf(
					// Translators: 1 - The plugin name ("All in One SEO")
					__('Use our configuration wizard to properly set up %1$s with your WordPress website.', td),
					import.meta.env.VITE_NAME
				),
				domainActivations : __('Domain Activations', td)
			}
		}
	},
	computed : {
		link () {
			return sprintf(
				'<strong><a href="%1$s" target="_blank">%2$s</a></strong>',
				links.getUpsellUrl('general-settings', 'license-box-tooltip', 'liteUpgrade'),
				this.strings.linkText
			)
		},
		tooltipText () {
			return sprintf(
				// Translators: 1 - "upgrading to Pro".
				__('To unlock more features, consider %1$s.', td),
				this.link
			)
		},
		moreToolTipText () {
			return sprintf(
				// Translators: 1 - "50% off".
				__('As a valued user you receive %1$s, automatically applied at checkout!', td),
				this.strings.moreBoldText
			)
		}
	}
}
</script>

<style lang="scss">
.aioseo-general-settings {
	.wizard-actions .aioseo-button {
		text-align: center;
	}

	.more-tooltip-text strong {
		color: $green;
	}

	svg.aioseo-setup-wizard {
		width: 12px;
		height: 12px;
		margin-right: 10px;
	}
}
</style>