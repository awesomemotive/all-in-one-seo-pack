<template>
	<core-settings-row
		:name="strings.licenseKey"
	>
		<template #description>
			{{ strings.licenseKeyDescription }}
		</template>

		<template #content>
			<div v-html="noLicenseNeeded" />

			<div class="license-cta-box">
				<div v-html="tooltipText"></div>

				<span v-html="moreToolTipText" />
			</div>

			<div v-html="alreadyPurchased" />

			<form class="license-key">
				<input type="text" name="username" autocomplete="username" style="display:none;" />

				<base-input
					type="password"
					:placeholder="strings.placeholder"
					:append-icon="licenseKey ? 'circle-check' : null"
					autocomplete="new-password"
					v-model="licenseKey"
				/>

				<base-button
					type="green"
					:disabled="!licenseKey"
					:loading="rootStore.loading"
					@click="processGetConnectUrl"
				>
					{{ strings.connect }}
				</base-button>
			</form>
		</template>
	</core-settings-row>
</template>

<script>
import {
	useConnectStore,
	useRootStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
export default {
	setup () {
		return {
			connectStore : useConnectStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreSettingsRow
	},
	data () {
		return {
			licenseKey : null,
			strings    : {
				boldText : this.$t.sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_SHORT_NAME,
					'Lite'
				),
				purchasedBoldText : this.$t.sprintf(
					'<strong>%1$s %2$s</strong>',
					import.meta.env.VITE_SHORT_NAME,
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
				licenseKey            : this.$t.__('License Key', this.$td),
				licenseKeyDescription : this.$t.__('Your license key provides access to updates and addons.', this.$td),
				placeholder           : this.$t.__('Paste your license key here', this.$td),
				connect               : this.$t.__('Connect', this.$td)
			}
		}
	},
	computed : {
		link () {
			return this.$t.sprintf(
				'<strong><a href="%1$s" target="_blank">%2$s</a></strong>',
				this.$links.utmUrl('general-settings', 'license-box'),
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
		},
		noLicenseNeeded () {
			return this.$t.sprintf(
				// Translators: 1 - The plugin name ("All in One SEO").
				this.$t.__('You\'re using %1$s - no license needed. Enjoy!', this.$td) + ' 🙂',
				this.strings.boldText
			)
		},
		alreadyPurchased () {
			return this.$t.sprintf(
				// Translators: 1 - The plugin name ("All in One SEO").
				this.$t.__('Already purchased? Simply enter your license key below to connect with %1$s!', this.$td),
				this.strings.purchasedBoldText
			)
		}
	},
	methods : {
		processGetConnectUrl () {
			this.rootStore.loading = true
			this.connectStore.getConnectUrl({
				key : this.licenseKey
			})
				.then(response => {
					if (response.body.url) {
						if (!response.body.popup) {
							this.rootStore.loading = false
							return window.open(response.body.url, '_blank')
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
			return this.connectStore.processConnect(payload)
		},
		closedCallback (reload) {
			if (reload) {
				return window.location.reload()
			}

			this.rootStore.loading = false
		}
	}
}
</script>

<style lang="scss">
.aioseo-general-settings {
	.more-tooltip-text strong {
		color: $green;
	}

	.license-cta-box {
		font-size: $font-md;
		line-height: 22px;
		border-radius: 3px;
		background-color: $inline-background;
		padding: 16px;
		max-width: 620px;
		margin: 12px 0;

		a {
			color: $green;
		}

		div {
			font-weight: 600;
		}

		span {
			font-size: 14px;
			font-style: italic;
		}
	}

	.license-key {
		margin-top: 12px;
		display: flex;
		max-width: 620px;

		.aioseo-input-container {
			margin-right: 8px;
		}
	}
}
</style>