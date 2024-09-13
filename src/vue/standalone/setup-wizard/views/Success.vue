<template>
	<div class="aioseo-wizard-success">
		<wizard-header />
		<wizard-container>
			<wizard-body>
				<div class="header">
					{{ strings.congratulations }}
				</div>

				<div class="description">
					{{ strings.heresWhatToDoNext }}
				</div>

				<div class="actions">
					<div class="no-border">
						<div class="icon">
							<svg-people-outline />
						</div>
						<div class="content">
							<div>{{ strings.joinOurCommunity }}</div>
						</div>
					</div>

					<div>
						<div class="icon" />

						<div class="content">
							<base-button
								class="social-button facebook"
								tag="a"
								:href="links.utmUrl('wizard-success', null, 'https://aioseo.com/plugin/facebook/')"
								target="_blank"
								size="small"
							><svg-facebook /> {{ strings.joinOnFacebook }}</base-button>

							<base-button
								class="social-button twitter"
								tag="a"
								:href="links.utmUrl('wizard-success', null, 'https://aioseo.com/plugin/twitter/')"
								target="_blank"
								size="small"
							><svg-icon-twitter /> {{ strings.followOnTwitter }}</base-button>

							<base-button
								class="social-button youtube"
								tag="a"
								:href="links.utmUrl('wizard-success', null, 'https://aioseo.com/plugin/youtube/')"
								target="_blank"
								size="small"
							><svg-youtube /> {{ strings.followOnYouTube }}</base-button>
						</div>
					</div>
					<div>
						<div class="icon">
							<svg-book />
						</div>
						<div class="content">
							<div><a :href="links.getDocUrl('ultimateGuide')" target="_blank">{{ strings.readOurStepByStep }}</a></div>
						</div>
					</div>

					<div>
						<div class="icon">
							<svg-video-camera />
						</div>
						<div class="content">
							<div><a :href="links.utmUrl('wizard-success', null, 'https://aioseo.com/plugin/guided-tour/')" target="_blank">{{ strings.watchOurGuidedTour }}</a></div>
						</div>
					</div>
				</div>

				<template v-if="licenseStore.isUnlicensed" #cta>
					<div class="content">
						<div class="cta-header">
							{{ strings.upgradeToProToUnlock }}
						</div>

						<div class="cta-description">
							{{ strings.ctaDescription }}
						</div>

						<grid-row class="feature-list">
							<grid-column
								md="6"
								v-for="(feature, index) in UPSELL_FEATURE_LIST"
								:key="index"
							>
								<svg-circle-check />
								{{ feature }}
							</grid-column>
						</grid-row>

						<base-button
							type="green"
							tag="a"
							:href="links.utmUrl('onboarding-wizard', 'success')"
							target="_blank"
						>
							{{ strings.ctaButton }}
						</base-button>

						<core-alert
							class="bonus-alert"
							type="yellow"
						>
							🎁 <span v-html="strings.bonusText" />
						</core-alert>
					</div>
				</template>

				<template #footer>
					<base-button
						type="gray"
						tag="a"
						:href="rootStore.aioseo.urls.aio.settings + '#/webmaster-tools'"
					>{{ strings.setupWebmasterTools }}</base-button>
					<div class="spacer"></div>
					<base-button
						type="gray"
						tag="a"
						:href="rootStore.aioseo.urls.aio.settings + '#/advanced'"
					>{{ strings.seeAdvancedSettings }}</base-button>
					<div class="spacer"></div>
					<base-button
						type="blue"
						tag="a"
						:href="rootStore.aioseo.urls.aio.dashboard"
					>{{ strings.finishSetup }}</base-button>
				</template>
			</wizard-body>

		</wizard-container>
	</div>
</template>

<script>
import {
	DISCOUNT_PERCENTAGE,
	UPSELL_FEATURE_LIST
} from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import { merge } from 'lodash-es'

import { useWizard } from '@/vue/composables/Wizard'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgBook from '@/vue/components/common/svg/Book'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgFacebook from '@/vue/components/common/svg/facebook/Index'
import SvgIconTwitter from '@/vue/components/common/svg/icon/TwitterWithoutCircle'
import SvgPeopleOutline from '@/vue/components/common/svg/PeopleOutline'
import SvgVideoCamera from '@/vue/components/common/svg/VideoCamera'
import SvgYoutube from '@/vue/components/common/svg/Youtube'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { strings } = useWizard({
			stage : 'success'
		})

		return {
			licenseStore      : useLicenseStore(),
			rootStore         : useRootStore(),
			composableStrings : strings,
			UPSELL_FEATURE_LIST,
			links
		}
	},
	components : {
		CoreAlert,
		GridColumn,
		GridRow,
		SvgBook,
		SvgCircleCheck,
		SvgFacebook,
		SvgIconTwitter,
		SvgPeopleOutline,
		SvgVideoCamera,
		SvgYoutube,
		WizardBody,
		WizardContainer,
		WizardHeader
	},
	data () {
		return {
			loading : false,
			strings : merge(this.composableStrings, {
				congratulations    : __('Congratulations, your site is now SEO ready!', td),
				finishSetup        : __('Finish Setup and Go to the Dashboard', td),
				heresWhatToDoNext  : __('Here\'s what to do next:', td),
				joinOurCommunity   : __('Join our Community', td),
				joinOnFacebook     : __('Join on Facebook', td),
				followOnTwitter    : __('Follow on X (Twitter)', td),
				followOnYouTube    : __('Follow on YouTube', td),
				readOurStepByStep  : __('Read our Step By Step Guide to Improve your SEO Rankings', td),
				watchOurGuidedTour : sprintf(
					// Translators: 1 - Plugin short name ("AIOSEO").
					__('Watch our Guided Tour of %1$s', td),
					import.meta.env.VITE_SHORT_NAME
				),
				seeAdvancedSettings : __('See Advanced Settings', td),
				setupWebmasterTools : __('Setup Webmaster Tools', td),
				bonusText           : sprintf(
					// Translators: 1 - Opening bold tag, 2 - Closing bold tag, 3 - "Pro", 4 - Opening bold tag, 5 - A discount percentage (e.g. "50%"), 6 - Closing bold tag.
					__('%1$sBonus:%2$s You can upgrade to the %3$s plan today and %4$ssave %5$s off%6$s (discount auto-applied).', td),
					'<strong>',
					'</strong>',
					'Pro',
					'<strong>',
					DISCOUNT_PERCENTAGE,
					'</strong>'
				),
				upgradeToProToUnlock : __('Upgrade to Pro to Unlock Powerful SEO Features', td),
				ctaDescription       : sprintf(
					// Translators: 1 - Plugin name ("All in One SEO"), 2 - The number of active users, 3 - Plugin short name ("AIOSEO").
					__('%1$s is the best WordPress SEO plugin. Join over %2$s Professionals who are already using %3$s to improve their website search rankings.', td),
					import.meta.env.VITE_NAME,
					'3,000,000+',
					import.meta.env.VITE_SHORT_NAME
				),
				ctaButton : sprintf(
					// Translators: 1 - "Pro".
					__('Upgrade to %1$s Today', td),
					'Pro'
				)
			})
		}
	},
	computed : {
		featureList () {
			return [
				this.strings.videoNewsSitemaps,
				this.strings.imageSeoOptimization,
				this.strings.localBusinessSeo,
				this.strings.advancedWooCommerce,
				this.strings.customTaxonomies,
				this.strings.andMore
			]
		}
	}
}
</script>

<style lang="scss">
.aioseo-wizard-success {
	color: $black;
	padding-bottom: 40px;

	.actions {
		> div {
			display: flex;
			align-items: center;

			&:not(.no-border) {
				border-bottom: 1px solid $border;
				padding-bottom: 24px;
				margin-bottom: 24px;
			}

			.icon {
				margin-right: 16px;
				min-width: 24px;

				svg {
					width: 24px;
					height: 24px;
				}
			}

			.content {
				font-weight: 700;
				font-size: 14px;
				color: $black;
				flex: 1;

				.social-button {
					color: #fff;
					margin: 10px 10px 0 0;

					svg {
						width: 14px;
						height: 14px;
						margin-right: 10px;

						&.aioseo-icon-twitter {
							width: 14px;
							height: 14px;
							color: black;
						}
					}

					&.facebook {
						background-color: #4064AC;
					}

					&.twitter {
						background-color: $black;
					}

					&.youtube {
						background-color: #D63E22;
					}
				}
			}
		}

		> div:last-child {
			border-bottom: none;
			padding-bottom: 0;
			margin-bottom: 0;
		}
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

	.cta {
		margin: 0 20px 40px;
		padding: 20px;
		border: 1px solid $border;
		text-align: center;

		.content {
			flex-direction: column;
			display: flex;
			align-items: center;
			justify-content: center;

			.cta-header {
				font-size: 24px;
				font-weight: 700;
				line-height: 1.2;
				margin-bottom: 24px;
				margin-top: 20px;
			}

			.cta-description {
				line-height: 1.4;
				max-width: 630px;
				margin-bottom: 55px;
			}

			.aioseo-alert {
				width: 100%;
			}

			.feature-list {
				color: $black;
				font-size: 16px;
				width: 100%;
				max-width: 600px;
				margin-bottom: 50px;

				.aioseo-col {
					display: flex;
					align-items: flex-start;
					margin: 2px 0;

					svg.aioseo-circle-check {
						color: $green;
						width: 18px;
						min-width: 18px;
						min-height: 18px;
						margin-right: 10px;
					}
				}
			}
		}

		.bonus-alert {
			margin: 24px auto;
			max-width: 650px;
		}
	}
}
</style>