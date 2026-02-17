<template>
	<div class="aioseo-buy-or-connect">
		<div
			v-if="isConnected"
			class="aioseo-buy-or-connect__connected"
		>
			<div class="credits-display">
				<credit-counter
					parent-component-context="settings"
					:is-settings-page="true"
				/>
			</div>

			<div class="credits-description">
				{{ strings.creditsDescription }}
				<template v-if="licenseStore.license.isActive">
					<a
						:href="purchaseCreditsUrl"
						target="_blank"
					>{{ strings.purchaseCredits }}</a>
				</template>
				<template v-else>
					<a
						:href="upgradeUrl"
						target="_blank"
					>{{ strings.upgradeToPro }}</a>
					{{ strings.or }}
					<a
						:href="purchaseCreditsUrl"
						target="_blank"
					>{{ strings.buyBundle }}</a>
				</template>
			</div>

			<div class="credits-actions">
				{{ strings.creditsNotUpdating }}
				<a
					href="#"
					:class="{ 'is-refreshing': refreshing }"
					@click.prevent="forceRefresh"
				>{{ refreshing ? strings.refreshing : strings.refresh }}</a>
				<template v-if="!licenseStore.license.isActive">
					<span class="separator">•</span>
					<a
						href="#"
						@click.prevent="showDisconnectModal = true"
					>{{ strings.disconnect }}</a>
				</template>
			</div>

			<disconnect-modal
				v-if="!licenseStore.license.isActive"
				:show-modal="showDisconnectModal"
				@continue="disconnect"
				@cancel="showDisconnectModal = false"
			/>
		</div>

		<div
			v-else
			class="aioseo-buy-or-connect__container"
		>
			<div
				class="aioseo-buy-or-connect__options"
				:class="{ 'buttons-only': buttonsOnly }"
			>
				<div class="aioseo-buy-or-connect__option">
					<template v-if="!buttonsOnly">
						<div class="option-label">{{ strings.newToAi }}</div>
						<div class="option-description">{{ strings.newToAiDescription }}</div>
					</template>
					<base-button
						type="green"
						:size="buttonsOnly ? 'medium' : 'small'"
						tag="a"
						target="_blank"
						@click.native="buyCreditsPopup"
						:loading="buyingCredits"
					>
						{{ strings.getAiCredits }}
					</base-button>
				</div>

				<div class="aioseo-buy-or-connect__option">
					<template v-if="!buttonsOnly">
						<div class="option-label">{{ strings.haveAccount }}</div>
						<div class="option-description">{{ strings.haveAccountDescription }}</div>
						<div class="option-note">
							{{ strings.proNotice }}
						</div>
					</template>
					<base-button
						type="blue"
						:size="buttonsOnly ? 'medium' : 'small'"
						tag="a"
						target="_blank"
						@click.native="connectWithExistingAccountPopup"
						:loading="connectingWithExistingAccount"
					>
						{{ strings.connectAccount }}
					</base-button>
				</div>
			</div>

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
import CreditCounter from './CreditCounter.vue'
import DisconnectModal from './DisconnectModal.vue'

import {
	useAiStore,
	useLicenseStore,
	useRootStore,
	useOptionsStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import links from '@/vue/utils/links'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	buttonsOnly : {
		type    : Boolean,
		default : false
	}
})

const buttonsOnly = props.buttonsOnly

const buyingCredits                 = ref(false)
const connectingWithExistingAccount = ref(false)
const deactivating                  = ref(false)
const refreshing                    = ref(false)
const showDisconnectModal           = ref(false)
const error                         = ref(null)

const aiStore      = useAiStore()
const licenseStore = useLicenseStore()
const rootStore    = useRootStore()
const optionsStore = useOptionsStore()

const isConnected = computed(() => {
	const ai = optionsStore.internalOptions.internal.ai

	// License holders are always connected (pro licenses include AI credits).
	if (licenseStore.license.isActive) {
		return true
	}

	return (ai?.hasAccessToken || ai?.isManuallyConnected) && !ai?.isTrialAccessToken
})

const upgradeUrl = computed(() => {
	return links.getUpsellUrl('ai-content', 'buy-or-connect', 'liteUpgrade')
})

const purchaseCreditsUrl = computed(() => {
	return links.getUpsellUrl('ai-content', 'buy-or-connect', 'aiCredits')
})

const strings = {
	newToAi                : __('No Credits Yet?', td),
	newToAiDescription     : __('Purchase credits to start creating content with AI.', td),
	getAiCredits           : __('Get AI Credits', td),
	haveAccount            : __('Have An AIOSEO Account?', td),
	haveAccountDescription : __('Connect your account to use your credits.', td),
	connectAccount         : __('Connect Account', td),
	proNotice              : __('Note: AIOSEO Pro includes AI credits. Connect here only if you purchased credits separately.', td),
	creditsDescription     : __('Need more credits?', td),
	purchaseCredits        : __('Purchase Credits', td),
	upgradeToPro           : __('Upgrade to Pro', td),
	buyBundle              : __('Purchase Credits', td),
	or                     : __('or', td),
	creditsNotUpdating     : __('Credits not updating?', td),
	refresh                : __('Refresh', td),
	refreshing             : __('Refreshing...', td),
	disconnect             : __('Disconnect', td)
}

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

const forceRefresh = () => {
	if (refreshing.value) {
		return
	}

	refreshing.value = true
	aiStore.getCredits(true)
		.finally(() => {
			refreshing.value = false
		})
}
</script>

<style lang="scss" scoped>
.aioseo-buy-or-connect {
	display: block;

	&__connected {
		.credits-display {
			margin-bottom: 8px;

			:deep(.aioseo-ai-credit-counter) {
				.purchase-credits {
					display: none;
				}
			}
		}

		.credits-description {
			font-size: 14px;
			color: $black2;
			margin-bottom: 8px;

			a {
				color: $blue;
				text-decoration: none;

				&:hover {
					text-decoration: underline;
				}
			}
		}

		.credits-actions {
			font-size: 13px;
			color: $placeholder-color;

			a {
				color: $placeholder-color;
				text-decoration: none;

				&:hover {
					text-decoration: underline;
					color: $black2;
				}

				&.is-refreshing {
					opacity: 0.7;
					pointer-events: none;
					cursor: default;
				}
			}

			.separator {
				margin: 0 6px;
			}
		}
	}

	&__container {
		.license-key-error {
			margin-top: 12px;
		}
	}

	&__options {
		display: flex;
		gap: 24px;

		&.buttons-only {
			justify-content: center;

			.aioseo-buy-or-connect__option:first-child {
				padding-right: 0;
			}
		}

		@media (max-width: 768px) {
			flex-direction: column;
			gap: 16px;
		}
	}

	&__option {
		&:first-child {
			padding-right: 24px;

			@media (max-width: 768px) {
				padding-right: 0;
				padding-bottom: 16px;
				border-right: none;
				border-bottom: 1px solid $border;
			}
		}

		.option-label {
			font-size: 14px;
			font-weight: 600;
			color: $black;
			margin-bottom: 2px;
		}

		.option-description {
			font-size: 14px;
			color: $black2;
			margin-bottom: 8px;
		}

		.option-note {
			font-size: 14px;
			color: $black2;
			margin-bottom: 8px;
			max-width: 350px;
		}
	}
}
</style>