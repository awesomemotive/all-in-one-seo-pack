<template>
	<div class="aioseo-wizard-smart-recommendations">
		<wizard-header />
		<wizard-container>
			<wizard-body>
				<wizard-steps />

				<div class="header">
					{{ strings.setupSiteAnalyzer }}
				</div>

				<div class="description">
					{{ strings.description }}
				</div>

				<div class="aioseo-settings-row no-border small-padding">
					<div class="settings-name">
						<div class="name small-margin">
							{{ strings.yourEmailAddress }}
						</div>
					</div>

					<base-input
						size="medium"
						v-model="setupWizardStore.smartRecommendations.accountInfo"
					/>

					<div class="aioseo-description">
						{{ strings.yourEmailIsNeeded }}
					</div>
				</div>

				<div
					v-if="!rootStore.isPro"
					class="aioseo-settings-row no-border no-margin small-padding"
				>
					<div class="settings-name">
						<div class="name small-margin">
							{{ strings.helpMakeAioseoBetter }}
							<core-tooltip>
								<svg-circle-question-mark />

								<template #tooltip>
									<div v-html="strings.usageTrackingTooltip" />
								</template>
							</core-tooltip>
						</div>
					</div>

					<base-toggle
						v-model="setupWizardStore.smartRecommendations.usageTracking"
					>
						{{ strings.yesCountMeIn }}
					</base-toggle>
				</div>

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
					<base-button
						type="blue"
						:loading="loading"
						@click="saveAndContinue"
					>{{ strings.saveAndContinue }} &rarr;</base-button>
				</template>
			</wizard-body>

			<wizard-close-and-exit />
		</wizard-container>

		<core-modal
			:show="showModal"
			@close="showModal = false"
			:classes="[ 'aioseo-smart-recommendations-modal' ]"
		>
			<template #headerTitle>
				{{ strings.wouldYouLikeToPurchase }}
			</template>

			<template #body>
				<div class="aioseo-modal-body">
					<div
						class="available-features"
						v-html="strings.theseFeaturesAreAvailable"
					/>

					<div
						v-for="(feature, index) in getSelectedUpsellFeatures"
						:key="index"
						class="aioseo-settings-row feature-grid small-padding medium-margin"
					>
						<grid-row>
							<grid-column xs="11">
								<div class="settings-name">
									<div class="name small-margin">
										{{ feature.name }}
										<core-pro-badge v-if="needsUpsell(feature)" />
									</div>
									<div class="aioseo-description-text">
										{{ feature.description }}
									</div>
								</div>
							</grid-column>
							<grid-column xs="1">
								<base-checkbox
									round
									class="no-clicks"
									type="green"
									:modelValue="true"
									@click.native="preventUncheck"
								/>
							</grid-column>
						</grid-row>
					</div>

					<div class="available-features no-access"
						v-html="strings.youWontHaveAccess"
					/>

					<div class="actions">
						<div class="spacer"></div>
						<div class="go-back">
							<router-link :to="setupWizardStore.getNextLink">{{ strings.illDoItLater }}</router-link>
						</div>
						<base-button
							type="green"
							:loading="loadingModal"
							@click="purchase"
						>{{ strings.purchaseAndInstallNow }}</base-button>
					</div>

					<core-alert
						class="bonus-alert"
						type="yellow"
					>
						🎁 <span v-html="strings.bonusText" />
					</core-alert>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import { DISCOUNT_PERCENTAGE } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useWizard } from '@/vue/composables/Wizard'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
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
			needsUpsell,
			features,
			strings
		} = useWizard({
			stage : 'smart-recommendations'
		})

		return {
			composableStrings : strings,
			features,
			getSelectedUpsellFeatures,
			needsUpsell,
			rootStore         : useRootStore(),
			setupWizardStore  : useSetupWizardStore()
		}
	},
	components : {
		BaseCheckbox,
		CoreAlert,
		CoreModal,
		CoreProBadge,
		CoreTooltip,
		GridColumn,
		GridRow,
		SvgCircleQuestionMark,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	data () {
		return {
			loading      : false,
			showModal    : false,
			loadingModal : false,
			strings      : merge(this.composableStrings, {
				setupSiteAnalyzer : __('Setup Site Analyzer + Smart Recommendations', td),
				description       : sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO").
					__('Get helpful suggestions from %1$s on how to optimize your website content, so you can rank higher in search results.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				yourEmailAddress     : __('Your Email Address', td),
				yourEmailIsNeeded    : __('Your email is needed so you can receive SEO recommendations. This email will also be used to connect your site with our SEO API.', td),
				helpMakeAioseoBetter : sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO").
					__('Help make %1$s better for everyone', td),
					import.meta.env.VITE_SHORT_NAME
				),
				yesCountMeIn              : __('Yes, count me in', td),
				wouldYouLikeToPurchase    : __('Would you like to purchase and install the following features now?', td),
				theseFeaturesAreAvailable : __('An upgrade is required to unlock the following features.', td),
				youWontHaveAccess         : __('You won\'t have access to this functionality until the extensions have been purchased and installed.', td),
				illDoItLater              : __('I\'ll do it later', td),
				purchaseAndInstallNow     : __('Purchase and Install Now', td),
				bonusText                 : sprintf(
					// Translators: 1 - Opening bold tag, 2 - Closing bold tag, 3 - "Pro", 4 - Opening bold tag, 5 - A discount percentage (e.g. "50%"), 6 - Closing bold tag.
					__('%1$sBonus:%2$s You can upgrade to the %3$s plan today and %4$ssave %5$s off%6$s (discount auto-applied).', td),
					'<strong>',
					'</strong>',
					'Pro',
					'<strong>',
					DISCOUNT_PERCENTAGE,
					'</strong>'
				),
				usageTrackingTooltip : sprintf(
					// Translators: 1 - Opening HTML link and bold tag, 2 - Closing HTML link and bold tag.
					__('Complete documentation on usage tracking is available %1$shere%2$s.', td),
					sprintf(
						'<strong><a href="%1$s" target="_blank">',
						links.getDocUrl('usageTracking')
					),
					'</a></strong>'
				)
			})
		}
	},
	computed : {
		selectedFeaturesNeedsUpsell () {
			let upsell = false
			this.setupWizardStore.features.forEach(feature => {
				if (this.needsUpsell(this.features.find(f => f.value === feature))) {
					upsell = true
				}
			})

			return upsell
		}
	},
	methods : {
		purchase () {
			const redirect = `&license-redirect=${btoa(this.rootStore.aioseo.urls.aio.wizard)}#/license-key`
			window.open('https://aioseo.com/pricing/?features[]=' + this.getSelectedUpsellFeatures.map(f => f.value).join('&features[]=') + redirect)
			this.$router.push(this.setupWizardStore.getNextLink)
		},
		saveAndContinue () {
			this.loading = true
			this.setupWizardStore.saveWizard('smartRecommendations')
				.then(() => {
					if (!this.selectedFeaturesNeedsUpsell) {
						return this.$router.push(this.setupWizardStore.getNextLink)
					}

					this.showModal = true
					this.loading   = false
				})
		},
		skipStep () {
			this.setupWizardStore.saveWizard()
			this.$router.push(this.setupWizardStore.getNextLink)
		},
		preventUncheck (event) {
			event.preventDefault()
			event.stopPropagation()
		}
	},
	mounted () {
		this.setupWizardStore.smartRecommendations.accountInfo = this.rootStore.aioseo.user.emailAddress
	}
}
</script>

<style lang="scss">
.aioseo-wizard-smart-recommendations {
	.header {
		font-size: 24px;
		color: $black;
		font-weight: 600;
	}

	.description {
		margin-top: 20px;
		font-size: 16px;
		color: $black2;
		margin-bottom: 40px;
		line-height: 1.4;
	}

	.available-features {
		font-size: 16px;
		color: $black;
		margin: 20px 0;

		&.no-access {
			color: $black2;
		}
	}

	.aioseo-settings-row {
		font-size: 16px;
		color: $black;

		.settings-name {
			margin-bottom: 10px;
		}
	}

	.aioseo-tooltip {
		svg.aioseo-circle-question-mark {
			width: 17px;
			height: 17px;
			color: $placeholder-color;
			cursor: pointer;
		}
	}

	.go-back {
		a {
			color: $black2;
			font-size: 14px;
		}
	}
}

.aioseo-smart-recommendations-modal {
	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		flex-direction: column;
		position: relative;

		.actions {
			margin-top: 20px;
			display: flex;
			align-items: center;

			> * {
				margin-right: 20px;

				&:last-child {
					margin-right: 0;
				}
			}

			.spacer {
				flex: 1 0 auto;
			}
		}

		.aioseo-alert {
			margin-top: 30px;
			font-size: 14px;
			text-align: center;
		}

		.bonus-alert {
			margin: 24px auto;
			max-width: 650px;
		}
	}
}
</style>