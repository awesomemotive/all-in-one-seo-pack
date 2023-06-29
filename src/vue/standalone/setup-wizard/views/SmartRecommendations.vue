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
					v-if="!$isPro"
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
			v-if="showModal"
			no-header
			@close="showModal = false"
		>
			<template #body>
				<div class="aioseo-modal-body">
					<button
						class="close"
						@click.stop="showModal = false"
					>
						<svg-close @click="showModal = false" />
					</button>

					<h3>{{ strings.wouldYouLikeToPurchase }}</h3>

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
						type="yellow"
						v-html="strings.bonusText"
					/>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import {
	useRootStore,
	useSetupWizardStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useWizard } from '@/vue/composables'
import { Wizard } from '@/vue/mixins'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgClose from '@/vue/components/common/svg/Close'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardCloseAndExit from '@/vue/components/common/wizard/CloseAndExit'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'
import WizardSteps from '@/vue/components/common/wizard/Steps'
export default {
	setup () {
		const { strings } = useWizard()

		return {
			rootStore         : useRootStore(),
			setupWizardStore  : useSetupWizardStore(),
			composableStrings : strings
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
		SvgClose,
		WizardBody,
		WizardCloseAndExit,
		WizardContainer,
		WizardHeader,
		WizardSteps
	},
	mixins : [ Wizard ],
	data () {
		return {
			loading      : false,
			stage        : 'smart-recommendations',
			showModal    : false,
			loadingModal : false,
			strings      : merge(this.composableStrings, {
				setupSiteAnalyzer : this.$t.__('Setup Site Analyzer + Smart Recommendations', this.$td),
				description       : this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO").
					this.$t.__('Get helpful suggestions from %1$s on how to optimize your website content, so you can rank higher in search results.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				yourEmailAddress     : this.$t.__('Your Email Address', this.$td),
				yourEmailIsNeeded    : this.$t.__('Your email is needed so you can receive SEO recommendations. This email will also be used to connect your site with our SEO API.', this.$td),
				helpMakeAioseoBetter : this.$t.sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO").
					this.$t.__('Help make %1$s better for everyone', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				yesCountMeIn              : this.$t.__('Yes, count me in', this.$td),
				wouldYouLikeToPurchase    : this.$t.__('Would you like to purchase and install the following features now?', this.$td),
				theseFeaturesAreAvailable : this.$t.__('An upgrade is required to unlock the following features.', this.$td),
				youWontHaveAccess         : this.$t.__('You won\'t have access to this functionality until the extensions have been purchased and installed.', this.$td),
				illDoItLater              : this.$t.__('I\'ll do it later', this.$td),
				purchaseAndInstallNow     : this.$t.__('Purchase and Install Now', this.$td),
				bonusText                 : this.$t.sprintf(
					// Translators: 1 - Opening bold tag, 2 - Closing bold tag, 3 - Opening bold tag, 4 - Percent between 1-100, 5 - Closing bold tag.
					this.$t.__('%1$sBonus:%2$s You can upgrade your plan today and %3$ssave %4$s off%5$s (discount auto-applied).', this.$td),
					'<strong>',
					'</strong>',
					'<strong>',
					this.$constants.DISCOUNT_PERCENTAGE,
					'</strong>'
				),
				usageTrackingTooltip : this.$t.sprintf(
					// Translators: 1 - Opening HTML link and bold tag, 2 - Closing HTML link and bold tag.
					this.$t.__('Complete documentation on usage tracking is available %1$shere%2$s.', this.$td),
					this.$t.sprintf(
						'<strong><a href="%1$s" target="_blank">',
						this.$links.getDocUrl('usageTracking')
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
			this.modalLoading = true
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
		this.setupWizardStore.smartRecommendations.accountInfo = this.rootStore.aioseo.user.data.data.user_email
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

	.aioseo-modal-body {
		max-height: 90vh;
		overflow: auto;
		padding: 20px 50px 50px;
		display: flex;
		flex-direction: column;
		position: relative;

		h3 {
			font-size: 20px;

			&+ .aioseo-description {
				margin-bottom: 20px;
			}
		}

		button.close {
			position: absolute;
			right: 11px;
			top: 11px;
			width: 24px;
			height: 24px;
			background-color: #fff;
			border: none;
			display: flex;
			align-items: center;

			svg.aioseo-close {
				cursor: pointer;
				width: 14px;
				height: 14px;
			}
		}

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
	}
}
</style>