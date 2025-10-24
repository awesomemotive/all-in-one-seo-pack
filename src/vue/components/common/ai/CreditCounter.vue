<template>
	<div
		class="aioseo-ai-credit-counter"
		:class="{
			'aioseo-ai-credit-counter--metabox': 'metabox' === parentComponentContext
		}"
	>
		<div class="counter-container-wrapper">
			<svg-ai-credits />

			<div class="counter-container">
				<div
					class="counter"
					:class="{
						'counter-sidebar': 'sidebar' === app.root.data.screenContext
					}">
					<span
						:class="
							{
								'credit-count': true,
								'low-credits': optionsStore.aiCreditPercentage <= 20
							}
						"
					>
						{{ optionsStore.internalOptions.internal.ai.credits.remaining.toLocaleString() }} / {{ optionsStore.internalOptions.internal.ai.credits.total.toLocaleString() }} {{ creditsText }}
					</span>

					<core-tooltip
						v-if="
							!optionsStore.internalOptions.internal.ai.isTrialAccessToken &&
							(optionsStore.internalOptions.internal.ai.credits.orders.length > 0 ||
							optionsStore.internalOptions.internal.ai.credits.license.total > 0)
						"
						:offset="parsedTooltipOffset"
						:placement="parsedTooltipPlacement"
					>
						<svg-circle-question-mark />

						<template #tooltip>
							<div
								v-if="
									optionsStore.internalOptions.internal.license &&
									optionsStore.internalOptions.internal.ai.credits.license.total > 0
								"
								class="aioseo-ai-credit-counter-license"
							>
								<span class="credit-heading">{{ planCredits }}</span>

								<div class="counter">
									<svg-ai-credits />

									<span
										:class="{
											'credit-count': true,
											'low-credits': (licenseRemaining/licenseTotal)> 20
										}"
									>
										{{ licenseRemaining }} / {{ licenseTotal }}
									</span>
								</div>

								<span class="license-expiration">
									{{ licenseExpiration }}
								</span>
							</div>

							<hr v-if="
								optionsStore.internalOptions.internal.license &&
								optionsStore.internalOptions.internal.ai.credits.license.total > 0 &&
								optionsStore.internalOptions.internal.ai.credits.orders.length > 0
							"
							/>

							<div
								v-if="optionsStore.internalOptions.internal.ai.credits.orders.length > 0"
								class="aioseo-ai-credit-counter-orders"
							>
								<span class="credit-heading">{{ strings.paygCredits }}</span>

								<div class="counter">
									<svg-ai-credits />

									<span
										:class="{
											'credit-count': true,
											'low-credits': (orderRemaining/orderTotal)> 20
										}"
									>
										{{ orderRemaining }} / {{ orderTotal }}
									</span>
								</div>

								<div class="credit-orders">
									<span
										v-for="(order, index) in oldestOrdersFirst"
										:key="index"
										class="credit-order"
									>
										{{ orderExpiration(order) }}
									</span>
								</div>
							</div>
						</template>
					</core-tooltip>
				</div>

				<div
					v-if="!isSettingsPage"
					class="purchase-credits"
				>
					<base-button
						v-if="optionsStore.aiCreditPercentage <= 20"
						:class="{ 'aioseo-ai-content-settings-button': isSettingsPage }"
						:size="isSettingsPage ? 'medium' : 'small'"
						type="green"
						tag="a"
						:href="links.getUpsellUrl('ai-credit-counter', '', 'aiCredits')"
						target="_blank"
					>
						{{ strings.buyCredits }}
					</base-button>

					<a
						v-else
						class="buy-credits"
						:href="links.getUpsellUrl('ai-credit-counter', '', 'aiCredits')"
						target="__blank"
					>
						{{ strings.buyCredits }}
					</a>
				</div>
			</div>
		</div>

		<div
			v-if="isSettingsPage"
			class="purchase-credits"
		>
			<p v-html="getCreditsLinks" />
		</div>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { getCurrentInstance, computed } from 'vue'
import links from '@/vue/utils/links'

import { DateTime } from 'luxon'
import dateFormat from '@/vue/utils/dateFormat'

import BaseButton from '@/vue/components/common/base/Button'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgAiCredits from '@/vue/components/common/svg/ai/AiCredits'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const app       = getCurrentInstance()
		const rootStore = useRootStore()
		const optionsStore = useOptionsStore()

		const orderExpiration = (order) => {
			const expirationDate = DateTime.fromMillis(order.expires * 1000)
			const expiration = dateFormat(expirationDate.toJSDate(), rootStore.aioseo.data.dateFormat)

			return sprintf(
				// Translators: 1 - Number of credits, 2 - Date of expiration.
				__('%1$s credits will expire on %2$s.', td), parseInt(order.remaining).toLocaleString(), expiration
			)
		}

		const licenseExpiration = computed(() => {
			if (rootStore.aioseo.data.isNetworkLicensed) {
				return __('These credits will reset when your license renews. Your license details can be found in the Network Admin area.', td)
			}
			const expirationDate = DateTime.fromMillis(optionsStore.internalOptions.internal?.license?.expires * 1000)
			const expiration     = dateFormat(expirationDate.toJSDate(), rootStore.aioseo.data.dateFormat)

			return sprintf(
				// Translators: 1 - Date of expiration.
				__('These credits will reset when your license renews on %1$s.', td), expiration
			)
		})

		const creditsText = computed(() => {
			if (optionsStore.internalOptions.internal.ai.isTrialAccessToken) {
				return __('Trial Credits', td)
			}

			return __('Credits', td)
		})

		return {
			app,
			licenseStore : useLicenseStore(),
			optionsStore : optionsStore,
			rootStore    : rootStore,
			links,
			DateTime,
			orderExpiration,
			licenseExpiration,
			creditsText
		}
	},
	components : {
		BaseButton,
		CoreTooltip,
		SvgAiCredits,
		SvgCircleQuestionMark
	},
	props : {
		parentComponentContext : {
			type    : String,
			default : 'metabox'
		},
		isSettingsPage : {
			type    : Boolean,
			default : false
		},
		tooltipOffset    : String,
		tooltipPlacement : String
	},
	data () {
		return {
			strings : {
				creditsLinksLite : sprintf(
					// Translators: 1 - Upgrade to Pro link text. 2 - Purchase a Pay-As-You-Go bundle link text.
					__('To unlock additional credits, %1$s or %2$s.', td),
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'liteUpgrade')}" target="_blank">${__('upgrade to Pro', td)}</a>`,
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'aiCredits')}" target="_blank">${__('purchase a Pay-As-You-Go bundle', td)}</a>`
				),
				creditsLinksPro : sprintf(
					// Translators: 1 - Upgrade to higher plan link text. 2 - Purchase a Pay-As-You-Go bundle link text.
					__('To unlock additional credits, %1$s or %2$s.', td),
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'proUpgrade')}" target="_blank">${__('upgrade to higher plan', td)}</a>`,
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'aiCredits')}" target="_blank">${__('purchase a Pay-As-You-Go bundle', td)}</a>`
				),
				creditsLinksElite : sprintf(
					// Translators: 1 - Purchase a Pay-As-You-Go bundle link text.
					__('To unlock additional credits, %1$s.', td),
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'aiCredits')}" target="_blank">${__('purchase a Pay-As-You-Go bundle', td)}</a>`
				),
				buyCredits  : __('Buy Credits', td),
				paygCredits : __('PAYG AI Credits', td)
			}
		}
	},
	computed : {
		parsedTooltipOffset () {
			return this.tooltipOffset || ('sidebar' === this.app.root.data.screenContext && 'metabox' === this.parentComponentContext ? '10px,0' : '50px,0')
		},
		parsedTooltipPlacement () {
			return this.tooltipPlacement || ('sidebar' === this.app.root.data.screenContext && 'metabox' === this.parentComponentContext ? 'left' : 'right')
		},
		planLevel () {
			if (this.licenseStore.isUnlicensed) {
				return ''
			}

			return this.optionsStore.internalOptions.internal.license.level
		},
		planCredits () {
			let planLevel = this.planLevel
			if (this.rootStore.aioseo.data.isNetworkLicensed) {
				planLevel = 'Elite'
			}

			if (!planLevel) {
				return ''
			}

			planLevel = planLevel.charAt(0).toUpperCase() + planLevel.slice(1)

			return sprintf(
				// Translators: 1 - Name of the Pro license plan ("Basic, ""Plus", "Pro", "Elite").
				__('%1$s Plan AI Credits', td), planLevel
			)
		},
		oldestOrdersFirst () {
			const orders = this.optionsStore.internalOptions.internal.ai.credits.orders

			return orders.sort((a, b) => a.expires - b.expires)
		},
		orderRemaining () {
			return this.optionsStore.internalOptions.internal.ai.credits.orders.reduce((acc, order) => acc + parseInt(order.remaining), 0).toLocaleString()
		},
		orderTotal () {
			return this.optionsStore.internalOptions.internal.ai.credits.orders.reduce((acc, order) => acc + parseInt(order.total), 0).toLocaleString()
		},
		licenseRemaining () {
			return this.optionsStore.internalOptions.internal.ai.credits.license.remaining.toLocaleString()
		},
		licenseTotal () {
			return this.optionsStore.internalOptions.internal.ai.credits.license.total.toLocaleString()
		},
		getCreditsLinks () {
			if (this.rootStore.aioseo.data.isNetworkLicensed && !this.optionsStore.internalOptions.internal.license.level) {
				return this.strings.creditsLinksElite
			}

			if (!this.rootStore.isPro || !this.licenseStore.license.isActive) {
				return this.strings.creditsLinksLite
			}

			return 'elite' === this.optionsStore.internalOptions.internal.license?.level?.toLowerCase() ? this.strings.creditsLinksElite : this.strings.creditsLinksPro
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-credit-counter {
	&--metabox {
		--counter-font-size: 14px;
	}

	display: flex;
	flex-direction: row;

	.counter-container-wrapper {
		display: grid;
		grid-template-columns: 32px auto;
		align-items: center;
	}

	svg {
		&.aioseo-ai-credits {
			width: 24px;
			height: 24px;
			margin-right: 8px;
			align-self: start;
		}

		&.aioseo-circle-question-mark {
			width: 16px;
			height: 16px;
			color: $placeholder-color;
		}
	}

	div.counter-container {
		display: flex;
		flex-wrap: wrap;
		line-height: 22px;

		.counter {
			font-size: var(--counter-font-size, 12px);
			margin-right: 8px;
			display: inline-grid;
			align-items: center;
			grid-template-columns: auto auto;

			&-sidebar .aioseo-tooltip {
				.popper {
					top: 70px !important;
					transform: unset !important;
				}
			}

			.low-credits {
				color: $red;
			}

			span.credit-count {
				font-weight: 700;
			}

			.aioseo-tooltip {
				cursor: pointer;
				margin-left: 8px;

				hr {
					border-color: $gray;
					margin: 12px 0;
				}

				.credit-heading {
					display: block;
					font-weight: bold;
					margin-bottom: 8px;
				}

				.credit-orders {
					flex-direction: column;
					gap: 8px;

					.credit-order {
						display: block;
						line-height: 22px;
						margin: 6px 0;

						&:first-of-type {
							margin-top: 8px;
						}
						&:last-of-type {
							margin-bottom: 8px;
						}
					}
				}

				.license-expiration {
					display: block;
					margin-top: 8px;
				}
			}
		}
	}

	.purchase-credits {
		font-size: var(--counter-font-size, 12px);

		p {
			margin: 8px 0 0 0;
		}

		a {
			margin-top: 8px;

			&.buy-credits {
				font-style: normal;
				font-weight: 700;
				text-decoration: none;
			}
		}
	}
}

.aioseo-modal .aioseo-ai-credit-counter {
	.counter-container-wrapper {
		align-items: center;

		a {
			margin-top: 0;
		}
	}
}
</style>