<template>
	<div
		class="aioseo-ai-credit-counter"
		:class="{
			'aioseo-ai-credit-counter--metabox': 'metabox' === parentComponentContext,
			'aioseo-ai-credit-counter--settings': isSettingsPage
		}"
	>
		<div class="counter-container-wrapper">
			<svg-ai-sparkles />

			<div class="counter-container">
				<div
					class="counter"
					:class="{
						'counter-sidebar': 'sidebar' === app.root.data.screenContext
					}">
					<span
						class="credit-count"
						:class="{ 'low-credits': isLowCredits }"
					>
						{{ totalRemaining }} <span class="credit-total">/ {{ totalCredits }} {{ creditsText }}</span>
					</span>

					<core-tooltip
						:offset="parsedTooltipOffset"
						:placement="parsedTooltipPlacement"
					>
						<svg-circle-question-mark />

						<template #tooltip>
							<div class="aioseo-ai-credit-counter-tooltip">
								<div
									v-if="hasLicenseCredits"
									class="aioseo-ai-credit-counter-tooltip__section"
								>
									<p class="aioseo-ai-credit-counter-tooltip__heading">{{ planCredits }}</p>

									<div class="aioseo-ai-credit-counter-tooltip__figure">
										<span :class="{ 'aioseo-ai-credit-counter-tooltip__low': isLicenseLow }">{{ licenseRemaining }}</span>
										<span class="aioseo-ai-credit-counter-tooltip__total">/ {{ licenseTotal }}</span>
									</div>

									<p class="aioseo-ai-credit-counter-tooltip__meta">{{ licenseExpiration }}</p>
								</div>

								<div
									v-if="hasTrialCredits"
									class="aioseo-ai-credit-counter-tooltip__section"
								>
									<p class="aioseo-ai-credit-counter-tooltip__heading">{{ strings.trialCredits }}</p>

									<div class="aioseo-ai-credit-counter-tooltip__figure">
										<span :class="{ 'aioseo-ai-credit-counter-tooltip__low': isLowCredits }">{{ totalRemaining }}</span>
										<span class="aioseo-ai-credit-counter-tooltip__total">/ {{ totalCredits }}</span>
									</div>

									<p class="aioseo-ai-credit-counter-tooltip__meta">{{ strings.trialNote }}</p>
								</div>

								<div
									v-if="hasOrderCredits"
									class="aioseo-ai-credit-counter-tooltip__section"
								>
									<p class="aioseo-ai-credit-counter-tooltip__heading">{{ strings.paygCredits }}</p>

									<div class="aioseo-ai-credit-counter-tooltip__figure">
										<span :class="{ 'aioseo-ai-credit-counter-tooltip__low': isOrderLow }">{{ orderRemaining }}</span>
										<span class="aioseo-ai-credit-counter-tooltip__total">/ {{ orderTotal }}</span>
									</div>

									<p class="aioseo-ai-credit-counter-tooltip__meta">{{ orderExpirations }}</p>
								</div>

								<div
									class="aioseo-ai-credit-counter__footer"
									:class="{
										'aioseo-ai-credit-counter__footer--upsell': showProUpsell,
										'aioseo-ai-credit-counter__footer--solo': !hasCreditSections
									}"
								>
									<template v-if="showProUpsell">
										<span class="aioseo-ai-credit-counter__footer-title">{{ strings.needMore }}</span>
										<p class="aioseo-ai-credit-counter__footer-text">{{ strings.upsellBody }}</p>

										<div class="aioseo-ai-credit-counter__footer-actions">
											<base-button
												size="small"
												type="green"
												tag="a"
												:href="upgradeUrl"
												target="_blank"
											>
												{{ strings.upgradeToPro }}
											</base-button>

											<a
												class="aioseo-ai-credit-counter__footer-link"
												v-safe-href="creditsUrl"
												target="_blank"
											>{{ strings.buyBundle }}</a>
										</div>
									</template>

									<template v-else>
										<span class="aioseo-ai-credit-counter__footer-text">{{ strings.needMore }}</span>

										<base-button
											size="small"
											type="gray"
											tag="a"
											:href="creditsUrl"
											target="_blank"
										>
											{{ strings.buyCredits }}
										</base-button>
									</template>
								</div>
							</div>
						</template>
					</core-tooltip>
				</div>
			</div>
		</div>

		<div
			v-if="isSettingsPage"
			class="purchase-credits"
		>
			<p v-safe-html="getCreditsLinks" />
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

import dayjs from '@/vue/utils/dayjs'
import dateFormat from '@/vue/utils/dateFormat'

import BaseButton from '@/vue/components/common/base/Button'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgAiSparkles from '@/vue/components/common/svg/ai/Sparkles'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __, _n, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const app       = getCurrentInstance()
		const rootStore = useRootStore()
		const optionsStore = useOptionsStore()

		const orderExpiration = (order) => {
			const expirationDate = dayjs(order.expires * 1000)
			const expiration     = dateFormat(expirationDate.toDate(), rootStore.aioseo.data.dateFormat)
			const remaining      = parseInt(order.remaining)

			return sprintf(
				// Translators: 1 - Number of credits, 2 - Date of expiration.
				_n('%1$s expires %2$s', '%1$s expire %2$s', remaining, td), remaining.toLocaleString(), expiration
			)
		}

		const licenseExpiration = computed(() => {
			if (rootStore.aioseo.data.isNetworkLicensed) {
				return __('Resets when your license renews. Your license details are in the Network Admin area.', td)
			}
			const expirationDate = dayjs(optionsStore.internalOptions.internal?.license?.expires * 1000)
			const expiration     = dateFormat(expirationDate.toDate(), rootStore.aioseo.data.dateFormat)

			return sprintf(
				// Translators: 1 - Date of expiration.
				__('Resets when your license renews on %1$s', td), expiration
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
			orderExpiration,
			licenseExpiration,
			creditsText
		}
	},
	components : {
		BaseButton,
		CoreTooltip,
		SvgAiSparkles,
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
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'aiCredits')}" target="_blank">${__('purchase a Pay-As-You-Go credit bundle', td)}</a>`
				),
				creditsLinksPro : sprintf(
					// Translators: 1 - Upgrade to higher plan link text. 2 - Purchase a Pay-As-You-Go bundle link text.
					__('To unlock additional credits, %1$s or %2$s.', td),
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'pricing')}" target="_blank">${__('upgrade to higher plan', td)}</a>`,
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'aiCredits')}" target="_blank">${__('purchase a Pay-As-You-Go credit bundle', td)}</a>`
				),
				creditsLinksElite : sprintf(
					// Translators: 1 - Purchase a Pay-As-You-Go bundle link text.
					__('To unlock additional credits, %1$s.', td),
					`<a href="${links.getUpsellUrl('ai-content', 'credit-counter', 'aiCredits')}" target="_blank">${__('purchase a Pay-As-You-Go credit bundle', td)}</a>`
				),
				buyCredits   : __('Buy Credits', td),
				buyBundle    : __('Buy a credit bundle', td),
				needMore     : __('Need more AI credits?', td),
				upgradeToPro : __('Upgrade to Pro', td),
				upsellBody   : __('A Pro license includes a full year\'s allowance that resets at renewal. Bundles are a one-off top-up.', td),
				paygCredits  : __('Pay-As-You-Go', td),
				trialCredits : __('Trial credits', td),
				trialNote    : __('Trial credits aren\'t replenished — a license or a bundle adds more.', td),
				planCredits  : __('License credits', td)
			}
		}
	},
	computed : {
		// Unlicensed users get the license pitch as well as the bundle link, because the
		// license is the recurring sale. Licensed users only need the top-up.
		showProUpsell () {
			return !this.rootStore.isPro || this.licenseStore.isUnlicensed
		},
		creditsUrl () {
			return links.getUpsellUrl('ai-credit-counter', 'tooltip', 'aiCredits')
		},
		upgradeUrl () {
			return links.getUpsellUrl('ai-credit-counter', 'tooltip', 'liteUpgrade')
		},
		isLowCredits () {
			return 20 >= this.optionsStore.aiCreditPercentage
		},
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
				return this.strings.planCredits
			}

			planLevel = planLevel.charAt(0).toUpperCase() + planLevel.slice(1)

			return sprintf(
				// Translators: 1 - Name of the Pro license plan ("Basic, ""Plus", "Pro", "Elite").
				__('%1$s Plan', td), planLevel
			)
		},
		credits () {
			return this.optionsStore.internalOptions.internal.ai.credits
		},
		hasLicenseCredits () {
			return !!this.optionsStore.internalOptions.internal.license && 0 < this.credits.license.total
		},
		hasOrderCredits () {
			return 0 < this.credits.orders.length
		},
		hasCreditSections () {
			return this.hasLicenseCredits || this.hasTrialCredits || this.hasOrderCredits
		},
		// Trial balances live on the top-level counter rather than as license or order
		// credits, so without this the tooltip would be nothing but a footer.
		hasTrialCredits () {
			return this.optionsStore.internalOptions.internal.ai.isTrialAccessToken &&
				!this.hasLicenseCredits &&
				!this.hasOrderCredits
		},
		isLicenseLow () {
			return this.isLow(this.credits.license.remaining, this.credits.license.total)
		},
		isOrderLow () {
			return this.isLow(this.orderRemainingRaw, this.orderTotalRaw)
		},
		totalRemaining () {
			return this.credits.remaining.toLocaleString()
		},
		totalCredits () {
			return this.credits.total.toLocaleString()
		},
		orderExpirations () {
			return this.oldestOrdersFirst.map(order => this.orderExpiration(order)).join(' \u00b7 ')
		},
		orderRemainingRaw () {
			return this.credits.orders.reduce((acc, order) => acc + parseInt(order.remaining), 0)
		},
		orderTotalRaw () {
			return this.credits.orders.reduce((acc, order) => acc + parseInt(order.total), 0)
		},
		oldestOrdersFirst () {
			const orders = this.optionsStore.internalOptions.internal.ai.credits.orders

			return orders.sort((a, b) => a.expires - b.expires)
		},
		orderRemaining () {
			return this.orderRemainingRaw.toLocaleString()
		},
		orderTotal () {
			return this.orderTotalRaw.toLocaleString()
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
	},
	methods : {
		// The old inline check divided localized strings and compared the result against 20
		// as though it were a percentage, so it never fired.
		isLow (remaining, total) {
			return !!total && 20 >= (remaining / total) * 100
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-credit-counter {
	&--metabox {
		--counter-font-size: 14px;
	}

	&--settings {
		--counter-font-size: 14px;
		flex-direction: column !important;
		align-items: flex-start;

		.counter-container-wrapper {
			width: 100%;
		}

		.purchase-credits {
			margin-top: 0;
			margin-left: 0;
		}
	}

	color: $black;
	display: flex;
	flex-direction: row;

	.counter-container-wrapper {
		display: grid;
		grid-template-columns: 32px auto;
		align-items: center;
	}

	svg {
		&.aioseo-ai-sparkles {
			color: $blue;
			width: 20px;
			height: 20px;
			margin-right: 8px;
		}

		&.aioseo-circle-question-mark {
			width: 16px;
			height: 16px;
			color: $placeholder-color;
		}
	}

	div.counter-container {
		align-items: center;
		display: flex;
		flex-wrap: wrap;
		line-height: 22px;
		row-gap: 4px;
		column-gap: 8px;

		.counter {
			font-size: var(--counter-font-size, 12px);
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

			// Nested inside .credit-count, so it has to opt out of both the bold and the red.
			span.credit-total {
				font-weight: 400;
				color: $black2;
			}

			.aioseo-tooltip {
				cursor: pointer;
				margin-left: 8px;
			}
		}
	}

	.purchase-credits {
		font-size: var(--counter-font-size, 12px);

		p {
			margin: 8px 0 0 0;
		}
	}
}

.aioseo-ai-credit-counter__footer {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 10px;
	// Cancels the popper's 20px padding so the footer reads as a bar, not a paragraph.
	margin: 14px -20px -20px;
	padding: 12px 20px;
	border-top: 1px solid $border;
	background: $background;
	font-size: 11.5px;

	&--upsell {
		display: block;
	}

	// Lite has no license, trial or Pay-As-You-Go balance to list, so the footer is the whole
	// tooltip: the separator and the 14px above it would sit against the popper's own padding
	// as an empty band.
	&--solo {
		margin-top: -20px;
		border-top: 0;
	}

	&-title {
		display: block;
		margin-bottom: 3px;
		font-size: 12.5px;
		font-weight: 700;
		color: $black;
	}

	& &-text {
		margin: 0;
		font-size: 11.5px;
		color: $black2;
		line-height: 1.45;
	}

	&-actions {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 12px;
		margin-top: 11px;
	}

	&-link {
		font-size: 12.5px;
		font-weight: 600;
		color: $blue;
		text-decoration: none;

		&:hover {
			text-decoration: underline;
		}
	}
}

// The credit tooltip teleports into a body-level popper portal, so it can't inherit the
// styles scoped to .counter-container. Anchor them on a class that travels with the node.
.aioseo-ai-credit-counter-tooltip {
	&__section + &__section {
		margin-top: 14px;
		padding-top: 14px;
		border-top: 1px solid $border;
	}

	& &__heading {
		margin: 0 0 6px;
		font-size: 12.5px;
		font-weight: 700;
		color: $black;
	}

	&__figure {
		font-size: 13px;
		font-weight: 700;
		color: $black;
		font-variant-numeric: tabular-nums;
	}

	&__total {
		// Vue condenses the whitespace between the two spans, so the gap has to be a margin.
		margin-left: 4px;
		font-weight: 400;
		color: $black2;
	}

	&__low {
		color: $red;
	}

	& &__meta {
		margin: 4px 0 0;
		font-size: 11.5px;
		line-height: 1.45;
		color: $gray3;
	}
}
</style>