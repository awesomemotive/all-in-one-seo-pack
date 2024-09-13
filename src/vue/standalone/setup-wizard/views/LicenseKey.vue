<template>
	<div class="aioseo-wizard-license-key">
		<wizard-header />
		<wizard-container>
			<wizard-body>
				<wizard-steps />

				<div class="header">
					{{ strings.enterYourLicenseKey }}
				</div>

				<div
					v-if="!rootStore.isPro"
					class="description"
					v-html="noLicenseNeeded"
				/>

				<div class="license-cta-box">
					<div v-html="tooltipText" />

					<grid-row>
						<grid-column
							sm="6"
							v-for="(feature, index) in getSelectedUpsellFeatures"
							:key="index"
						>
							<svg-checkmark />
							{{ feature.name }}
						</grid-column>
					</grid-row>
				</div>

				<div
					v-if="!rootStore.isPro"
					v-html="alreadyPurchased"
				/>

				<form class="license-key">
					<input type="text" name="username" autocomplete="username" style="display:none;" />
					<base-input
						type="password"
						:placeholder="strings.placeholder"
						:append-icon="localLicenseKey ? 'circle-check' : null"
						autocomplete="new-password"
						v-model="localLicenseKey"
					/>
					<base-button
						type="green"
						:disabled="!localLicenseKey"
						:loading="loading"
						@click="processConnectOrActivate"
					>
						{{ strings.connect }}
					</base-button>
				</form>

				<core-alert
					class="license-key-error"
					v-if="error"
					type="red"
					v-html="error"
				/>

				<template #footer>
					<div class="go-back">
						<router-link :to="setupWizardStore.getPrevLink" class="no-underline">&larr;</router-link>
						&nbsp;
						<router-link :to="setupWizardStore.getPrevLink">{{ strings.goBack }}</router-link>
					</div>
					<div class="spacer"></div>
					<base-button
						type="gray"
						@click="skipStep"
					>{{ strings.skipThisStep }}</base-button>
				</template>
			</wizard-body>

			<wizard-close-and-exit />
		</wizard-container>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useConnectStore,
	useLicenseStore,
	useOptionsStore,
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import { merge } from 'lodash-es'

import { useWizard } from '@/vue/composables/Wizard'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCheckmark from '@/vue/components/common/svg/Checkmark'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getSelectedUpsellFeatures,
			strings
		} = useWizard({
			stage : 'license-key'
		})

		return {
			composableStrings : strings,
			connectStore      : useConnectStore(),
			getSelectedUpsellFeatures,
			licenseStore      : useLicenseStore(),
			optionsStore      : useOptionsStore(),
			rootStore         : useRootStore(),
			setupWizardStore  : useSetupWizardStore()
		}
	},
	components : {
		CoreAlert,
		GridColumn,
		GridRow,
		SvgCheckmark,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	data () {
		return {
			error           : null,
			loading         : false,
			localLicenseKey : null,
			strings         : merge(this.composableStrings, {
				enterYourLicenseKey : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('Enter your %1$s License Key', td),
					import.meta.env.VITE_SHORT_NAME
				),
				boldText : sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_SHORT_NAME,
					'Lite'
				),
				purchasedBoldText : sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				linkText : sprintf(
					// Translators: 1 - "Pro".
					__('upgrade to %1$s', td),
					'Pro'
				),
				placeholder : __('Paste your license key here', td),
				connect     : __('Connect', td)
			})
		}
	},
	watch : {
		localLicenseKey (newVal) {
			this.setupWizardStore.licenseKey = newVal
		}
	},
	computed : {
		noLicenseNeeded () {
			return sprintf(
				// Translators: 1 - The plugin name ("All in One SEO").
				__('You\'re using %1$s - no license needed. Enjoy!', td) + ' 🙂',
				this.strings.boldText
			)
		},
		link () {
			return sprintf(
				'<strong><a href="%1$s" target="_blank">%2$s</a></strong>', links.utmUrl('general-settings', 'license-box'),
				this.strings.linkText
			)
		},
		tooltipText () {
			return this.rootStore.isPro
				? __('To unlock the selected features, please enter your license key below.', td)
				: sprintf(
					// Translators: 1 - "upgrade to Pro".
					__('To unlock the selected features, please %1$s and enter your license key below.', td),
					this.link
				)
		},
		alreadyPurchased () {
			return sprintf(
				// Translators: 1 - The plugin name ("All in One SEO").
				__('Already purchased? Simply enter your license key below to connect with %1$s!', td),
				this.strings.purchasedBoldText
			)
		}
	},
	methods : {
		processConnectOrActivate () {
			if (this.rootStore.isPro) {
				return this.processActivateLicense()
			}

			this.processGetConnectUrl()
		},
		processActivateLicense () {
			this.error   = null
			this.loading = true
			this.rootStore.loading = true
			this.licenseStore.activate(this.localLicenseKey)
				.then(() => {
					this.optionsStore.internalOptions.internal.license.expired = false
					this.setupWizardStore.saveWizard('license-key')
						.then(() => {
							this.$router.push(this.setupWizardStore.getNextLink)
						})
				})
				.catch(error => {
					this.loading         = false
					this.localLicenseKey = null
					this.rootStore.loading = false
					if (!error || !error.response || !error.response.body || !error.response.body.error || !error.response.body.licenseData) {
						this.error = __('An unknown error occurred, please try again later.', td)
						return
					}

					const data = error.response.body.licenseData
					if (data.invalid) {
						this.error = __('The license key provided is invalid. Please use a different key to continue receiving automatic updates.', td)
					} else if (data.disabled) {
						this.error = __('The license key provided is disabled. Please use a different key to continue receiving automatic updates.', td)
					} else if (data.expired) {
						this.error = this.licenseKeyExpired
					} else if (data.activationsError) {
						this.error = __('This license key has reached the maximum number of activations. Please deactivate it from another site or purchase a new license to continue receiving automatic updates.', td)
					} else if (data.connectionError || data.requestError) {
						this.error = __('There was an error connecting to the licensing API. Please try again later.', td)
					}
				})
		},
		processGetConnectUrl () {
			this.loading = true
			this.rootStore.loading = true
			this.connectStore.getConnectUrl({
				key    : this.localLicenseKey,
				wizard : true
			})
				.then(response => {
					if (response.body.url) {
						if (!response.body.popup) {
							this.loading = false
							this.rootStore.loading = false
							return window.open(response.body.url)
						}

						this.openPopup(response.body.url)
					}
				})
		},
		openPopup (url) {
			popup(
				url,
				'_self',
				600,
				630,
				true,
				[ 'file', 'token' ],
				this.completedCallback,
				this.closedCallback
			)
		},
		completedCallback (payload) {
			payload.wizard = true
			return this.connectStore.processConnect(payload)
		},
		closedCallback (reload) {
			if (reload) {
				return window.location.reload()
			}

			this.loading = false
			this.rootStore.loading = false
		},
		skipStep () {
			this.setupWizardStore.saveWizard()
			this.$router.push(this.setupWizardStore.getNextLink)
		}
	},
	mounted () {
		this.localLicenseKey = this.setupWizardStore.licenseKey
	}
}
</script>

<style lang="scss">
.aioseo-wizard-license-key {
	font-size: 16px;
	color: $black;

	.header {
		font-size: 24px;
		color: $black;
		font-weight: 600;
	}

	.description {
		margin-top: 32px;
		font-size: 16px;
		color: $black2;
		margin-bottom: 20px;
	}

	.aioseo-settings-row {
		&:last-child {
			border-bottom: none;
			margin-bottom: 0;
		}

		&.feature-grid {
			.settings-name {
				.name {
					font-size: 18px;
				}
			}

			.aioseo-col {
				display: flex;
				align-items: center;
			}
		}
	}

	.go-back {
		a {
			color: $black2;
			font-size: 14px;
		}
	}

	.license-cta-box {
		font-size: $font-md;
		line-height: 22px;
		border-radius: 3px;
		background-color: $inline-background;
		padding: 20px;
		max-width: 620px;
		margin: 12px 0;

		a {
			color: $green;
		}

		> div:first-child {
			font-weight: 600;
			line-height: 1.4;
		}

		.aioseo-row {
			margin-top: 12px;

			.aioseo-col {
				display: flex;
				align-items: center;

				svg {
					width: 16px;
					height: 16px;
					color: $blue;
					margin-right: 10px;
				}
			}
		}
	}

	.license-key {
		margin-top: 12px;
		display: flex;
		gap: 8px;
		max-width: 620px;

		.aioseo-input {
			margin-right: 8px;
		}
	}

	.license-key-error {
		margin-top: 20px;
	}
}
</style>