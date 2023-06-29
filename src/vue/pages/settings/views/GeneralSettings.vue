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
				v-if="!$isPro"
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
				license  : this.$t.__('License', this.$td),
				boldText : this.$t.sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_NAME,
					this.$t.__('Free', this.$td)
				),
				purchasedBoldText : this.$t.sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_NAME,
					'Pro'
				),
				linkText : this.$t.sprintf(
					// Translators: 1 - "Pro".
					this.$t.__('upgrading to %1$s', this.$td),
					'Pro'
				),
				moreBoldText : this.$t.sprintf(
					'<strong>%1$s</strong>',
					// Translators: This refers to a discount (e.g. "As a valued user you receive 50%, automatically applied at checkout!").
					this.$constants.DISCOUNT_PERCENTAGE + ' ' + this.$t.__('off', this.$td)
				),
				setupWizard         : this.$t.__('Setup Wizard', this.$td),
				relaunchSetupWizard : this.$t.__('Relaunch Setup Wizard', this.$td),
				setupWizardText     : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO")
					this.$t.__('Use our configuration wizard to properly set up %1$s with your WordPress website.', this.$td),
					import.meta.env.VITE_NAME
				),
				domainActivations : this.$t.__('Domain Activations', this.$td)
			}
		}
	},
	computed : {
		link () {
			return this.$t.sprintf(
				'<strong><a href="%1$s" target="_blank">%2$s</a></strong>',
				this.$links.utmUrl('general-settings', 'license-box-tooltip'),
				this.strings.linkText
			)
		},
		tooltipText () {
			return this.$t.sprintf(
				// Translators: 1 - "upgrading to Pro".
				this.$t.__('To unlock more features, consider %1$s.', this.$td),
				this.link
			)
		},
		moreToolTipText () {
			return this.$t.sprintf(
				// Translators: 1 - "50% off".
				this.$t.__('As a valued user you receive %1$s, automatically applied at checkout!', this.$td),
				this.strings.moreBoldText
			)
		}
	}
}
</script>

<style lang="scss">
.aioseo-general-settings {

	.aioseo-card {

		.settings-content {

			.license-key + .aioseo-description {
				display: inline-block;
				margin: 8px 4px 0 0;
			}

			.deactivate-license {
				display: inline-block;
				font-weight: $font-bold;
				margin-top: 8px;
			}
		}
	}

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