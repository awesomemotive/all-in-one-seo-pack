<template>
	<div class="aioseo-buy-or-connect">
		<div
			v-if="optionsStore.internalOptions.internal.ai?.accessToken && !optionsStore.internalOptions.internal.ai.isTrialAccessToken"
			class="aioseo-buy-or-connect__actions"
		>
			<div class="aioseo-buy-or-connect__connected">
				<svg-circle-check-solid />

				<span>{{ connectedText }}</span>
			</div>

			<base-button
				v-if="!licenseStore.license.isActive"
				type="gray"
				size="medium"
				inline
				tag="button"
				:loading="deactivating"
				@click="showDisconnectModal = true"
			>
				{{ strings.disconnect }}
			</base-button>

			<disconnect-modal
				v-if="!licenseStore.license.isActive"
				:show-modal="showDisconnectModal"
				@continue="disconnect"
				@cancel="showDisconnectModal = false"
			/>
		</div>

		<div
			v-else
			class="aioseo-buy-or-connect__actions"
		>
			<base-button
				type="green"
				size="medium"
				tag="a"
				target="_blank"
				@click.native="buyCreditsPopup"
				:loading="buyingCredits"
			>
				{{ strings.buyCredits }}
			</base-button>

			<div class="actions-divider">
				{{ strings.or }}
			</div>

			<base-button
				type="gray"
				size="medium"
				tag="a"
				target="_blank"
				@click.native="connectWithExistingAccountPopup"
				:loading="connectingWithExistingAccount"
			>
				{{ strings.connectExistingAccount }}
			</base-button>

			<core-alert
				class="license-key-error"
				v-if="error"
				type="red"
				v-html="error"
			/>
		</div>
	</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import BaseButton from '@/vue/components/common/base/Button'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import DisconnectModal from './DisconnectModal.vue'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'

import {
	useAiStore,
	useLicenseStore,
	useRootStore,
	useOptionsStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const buyingCredits                 = ref(false)
const connectingWithExistingAccount = ref(false)
const deactivating                  = ref(false)
const showDisconnectModal           = ref(false)
const error                         = ref(null)

const aiStore      = useAiStore()
const licenseStore = useLicenseStore()
const rootStore    = useRootStore()
const optionsStore = useOptionsStore()

const strings = {
	buyCredits             : __('Buy Credits', td),
	connectExistingAccount : __('Connect to an Existing Account', td),
	or                     : __('OR', td),
	connected              : __('Your account is connected!', td),
	connectedNetwork       : __('Your account is connected at the network level of your WordPress multisite.', td),
	disconnect             : __('Disconnect', td)
}

const connectedText = computed(() => {
	return rootStore.aioseo.data.isNetworkLicensed && !optionsStore.internalOptions.internal.license.level ? strings.connectedNetwork : strings.connected
})

const buyCreditsPopup = () => {
	buyingCredits.value = true

	const url = rootStore.aioseo.urls.marketingSiteUrl + 'pricing-ai-credits?url=' + btoa(rootStore.aioseo.urls.home)
	openPopup(url, 1200, 900)
}

const connectWithExistingAccountPopup = () => {
	connectingWithExistingAccount.value = true

	const url = rootStore.aioseo.urls.marketingSiteUrl + 'account/?ai-credits-connect=1&url=' + btoa(rootStore.aioseo.urls.home)
	openPopup(url, 450, 700)
}

const openPopup = (url, width = 650, height = 800) => {
	popup(
		url,
		__('Connect Your Site with AI Content Generation', td),
		width,
		height,
		true,
		[ 'success' ],
		completedCallback,
		closedCallback
	)
}

const completedCallback = (response) => {
	if (!response.success || !response.accessToken) {
		// If the popup is closed, or declined we don't need to do anything.
		return Promise.resolve()
	}

	// Set these back to true after the popup is closed. It will take a few seconds to get the credits data updated.
	buyingCredits.value                 = true
	connectingWithExistingAccount.value = true

	// Store the access token in the options and get the credits data updated.
	aiStore.storeAccessToken(response.accessToken)

	buyingCredits.value                 = false
	connectingWithExistingAccount.value = false

	return Promise.resolve()
}

const closedCallback = () => {
	// Reset the loading states.
	buyingCredits.value                 = false
	connectingWithExistingAccount.value = false
}

const disconnect = () => {
	deactivating.value = true
	showDisconnectModal.value = false
	aiStore.deactivate()
		.catch(() => {
			error.value = __('An unknown error occurred, please try again later.', td)
		})
		.finally(() => {
			deactivating.value = false
		})
}
</script>

<style lang="scss" scoped>
.aioseo-buy-or-connect {
	display: inline-block;

	&__actions {
		display: flex;
		gap: 15px;
		align-items: center;

		@media (max-width: 768px) {
			flex-direction: column;
			align-items: center;
			justify-content: center;
			align-content: center;
		}

		.license-key-error {
			margin-top: 20px;
		}
	}

	&__connected {
		display: flex;
		align-items: center;
		font-size: 14px;
		padding: 0 18px;
		min-height: 40px;
		border: 1px solid $green;
		border-radius: 4px;
		background-color: #edf8f0;
		font-weight: 700;

		svg {
			width: 22px;
			height: 22px;
			color: $green;
			margin-right: 16px;
		}
	}
}
</style>