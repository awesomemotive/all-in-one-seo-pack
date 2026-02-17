<template>
	<div class="aioseo-wizard-success">
		<wizard-header />
		<wizard-container>
			<wizard-body>
				<div class="header">
					{{ strings.congratulations }}
				</div>

				<!-- SEO Checklist Progress Section -->
				<div class="checklist-progress">
					<div class="checklist-header">
						{{ strings.yourSeoChecklistProgress }}
					</div>

					<div class="checklist-description">
						{{ strings.checklistDescription }}
					</div>

				<!-- Divider -->
				<div class="section-divider"></div>

				<!-- Just Completed Section -->
				<div class="just-completed">
					<div class="just-completed-header">
						{{ strings.justCompleted }}
					</div>

					<div
						v-for="item in justCompletedItems"
						:key="item.name"
						class="checklist-item completed"
					>
						<div class="item-icon">
							<svg-circle-check class="check-icon" />
						</div>
						<div class="item-content">
							<div class="item-title">
								{{ item.title }}
							</div>
							<div class="item-description">
								{{ item.description }}
							</div>
						</div>
					</div>
				</div>

				<!-- Divider -->
				<div
					v-if="nextActionItems.length"
					class="section-divider"
				></div>

				<!-- What's Next Section -->
				<div
					v-if="nextActionItems.length"
					class="whats-next"
				>
					<div class="whats-next-header">
						{{ strings.whatsNext }}
					</div>

					<div
						v-for="item in nextActionItems"
						:key="item.name"
						class="checklist-item pending"
						:class="{ 'community': 'join-community' === item.name }"
					>
						<div class="item-icon">
							<svg-circle-exclamation v-if="'join-community' !== item.name" class="warning-icon" />
							<svg-people-outline v-else />
						</div>
						<div class="item-content">
							<div class="item-title">
								{{ item.title }}
								<span class="item-time">{{ item.time?.label }}</span>
							</div>
							<div
								v-if="'join-community' !== item.name"
								class="item-description"
							>
								{{ item.description }}
							</div>
							<div
								v-else
								class="item-description social-buttons"
							>
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
					</div>

					<!-- Remaining tasks indicator -->
					<div
						v-if="remainingTasksCount > 0"
						class="remaining-tasks"
					>
						{{ remainingTasksText }}
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
							:href="links.getUpsellUrl('onboarding-wizard', 'success', 'liteUpgrade')"
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
					<div class="spacer"></div>
					<base-button
						type="blue"
						tag="a"
						:href="checklistUrl"
					>{{ strings.goToSeoChecklist }}</base-button>
				</template>
			</wizard-body>

		</wizard-container>
	</div>
</template>

<script setup>
import {
	DISCOUNT_PERCENTAGE,
	UPSELL_FEATURE_LIST
} from '@/vue/plugins/constants'
import { merge } from 'lodash-es'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore,
	useSearchStatisticsStore,
	useSeoChecklistStore
} from '@/vue/stores'

import { useWizard } from '@/vue/composables/Wizard'

import BaseButton from '@/vue/components/common/base/Button'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgFacebook from '@/vue/components/common/svg/facebook/Index'
import SvgIconTwitter from '@/vue/components/common/svg/icon/Twitter'
import SvgPeopleOutline from '@/vue/components/common/svg/PeopleOutline'
import SvgYoutube from '@/vue/components/common/svg/Youtube'
import WizardBody from '@/vue/components/common/wizard/Body'
import WizardContainer from '@/vue/components/common/wizard/Container'
import WizardHeader from '@/vue/components/common/wizard/Header'

import { __, _n, sprintf } from '@/vue/plugins/translations'
import { computed, onMounted } from 'vue'

const td = import.meta.env.VITE_TEXTDOMAIN

// Stores
const licenseStore = useLicenseStore()
const rootStore = useRootStore()
const searchStatisticsStore = useSearchStatisticsStore()
const seoChecklistStore = useSeoChecklistStore()

// Composables
const { strings: composableStrings } = useWizard({
	stage : 'success'
})

// Fetch checklist data on mount
onMounted(() => {
	seoChecklistStore.fetchChecks({
		orderBy           : 'priority',
		orderDir          : 'asc',
		limit             : 100,
		offset            : 0,
		searchTerm        : '',
		filter            : 'all',
		additionalFilters : {}
	})
})

// Data
// Just Completed: Always show Setup Wizard + GSC if completed
const justCompletedItems = computed(() => {
	const items = []
	const completed = seoChecklistStore.completedItems

	// Always show setup wizard as completed (since user is on this page, wizard is done)
	const setupWizard = completed.find(item => 'setup-wizard' === item.name)
	items.push(setupWizard || {
		name        : 'setup-wizard',
		title       : strings.finishSetupWizard,
		description : strings.finishSetupDescription,
		completed   : true
	})

	// Show GSC if completed in checklist OR if GSC is connected
	const gsc = completed.find(item => 'connect-google-search-console' === item.name)
	if (gsc || searchStatisticsStore.isConnected) {
		items.push(gsc || {
			name        : 'connect-google-search-console',
			title       : strings.connectGoogleSearchConsole,
			description : strings.connectGoogleDescription,
			completed   : true
		})
	}

	return items
})

// What's Next: Show first 2 highest priority pending items + join community
const nextActionItems = computed(() => {
	const priorityOrder = { high: 1, medium: 2, optional: 3 }
	const topTasks = [ ...seoChecklistStore.pendingItems ]
		.sort((a, b) => priorityOrder[a.priority] - priorityOrder[b.priority])
		.slice(0, 2)

	// Add hardcoded "Join our Community" task as the third item
	topTasks.push({
		name        : 'join-community',
		title       : strings.joinCommunity,
		description : strings.joinCommunityDescription,
		time        : {
			label : strings.twoMinutes
		}
	})

	return topTasks
})

// Calculate remaining tasks count (excluding the 2 shown and join community)
const remainingTasksCount = computed(() => {
	return Math.max(0, seoChecklistStore.pendingItems.length - 2)
})

// Remaining tasks text with proper pluralization
const remainingTasksText = computed(() => {
	const count = remainingTasksCount.value
	return sprintf(
		// Translators: 1 - Number of remaining tasks.
		_n(
			'... and %1$d more task',
			'... and %1$d more tasks',
			count,
			td
		),
		count
	)
})

const checklistUrl = computed(() => {
	return `${rootStore.aioseo.urls.aio.settings}#/seo-checklist`
})

const strings = merge(composableStrings, {
	congratulations            : __('Initial setup complete! Just a few more tasks to go 🚀', td),
	yourSeoChecklistProgress   : __('Your SEO Checklist', td),
	checklistDescription       : __('You\'ve completed the basics with the Setup  Wizard. Here are a few more tasks from our SEO Checklist to help your site perform better in search engines.', td),
	justCompleted              : __('Just Completed', td),
	finishSetupWizard          : __('Finish Setup Wizard', td),
	finishSetupDescription     : __('You\'ve completed the initial AIOSEO setup and configured essential SEO settings.', td),
	connectGoogleSearchConsole : __('Connect Google Search Console', td),
	connectGoogleDescription   : __('Your site is connected to Google Search Console so you can monitor performance and submit sitemaps.', td),
	whatsNext                  : __('What\'s Next', td),
	joinCommunity              : __('Join our community to get SEO tips and best practices', td),
	joinCommunityDescription   : __('Connect with other AIOSEO users, get tips, and stay updated on the latest SEO best practices.', td),
	twoMinutes                 : __('Instant', td),
	joinOnFacebook             : __('Follow on Facebook', td),
	followOnTwitter            : __('Follow on X', td),
	followOnYouTube            : __('Follow on YouTube', td),
	goToSeoChecklist           : __('Go to SEO Checklist', td),
	bonusText                  : sprintf(
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
</script>

<style lang="scss">
.aioseo-wizard-success {
	color: $black;
	padding-bottom: 40px;

	.header {
		font-size: 24px;
		font-weight: 700;
		margin-bottom: 24px;
		color: $black;
	}

	// SEO Checklist Progress Section
	.checklist-progress {
		background-color: #F9FAFB;
		border-radius: 8px;
		padding: 20px;
		margin-bottom: 0;

		.checklist-header {
			font-size: 20px;
			font-weight: 700;
			line-height: 30px;
			color: $black;
			margin-bottom: 12px;
		}

		.checklist-description {
			font-size: 16px;
			font-weight: 400;
			line-height: 1.6;
			color: $black;
			margin-bottom: 20px;
		}

		.progress-bar-spacing {
			margin-bottom: 16px;
		}

		// Just Completed section
		.just-completed {
			margin-top: 0;
			margin-bottom: 0;

			.just-completed-header {
				font-size: 20px;
				font-weight: 600;
				color: $black;
				margin-bottom: 20px;
			}
		}

		// Divider between completed tasks and what's next
		.section-divider {
			height: 1px;
			background-color: $border;
			margin: 24px 0;
		}

		// What's Next inside the progress box
		.whats-next {
			margin-top: 0;
			margin-bottom: 0;

			.whats-next-header {
				font-size: 20px;
				font-weight: 600;
				color: $black;
				margin-bottom: 20px;
			}

			.remaining-tasks {
				font-size: 14px;
				font-weight: 400;
				color: $placeholder-color;
				margin-top: 16px;
			}
		}
	}

	// Checklist Items (both in progress section and what's next)
	.checklist-item {
		display: flex;
		align-items: flex-start;
		margin-bottom: 20px;

		&:last-child {
			margin-bottom: 0;
		}

		.item-icon {
			margin-right: 16px;
			min-width: 24px;
			display: flex;
			align-items: center;
			justify-content: center;

			svg {
				width: 24px;
				height: 24px;
			}

			.check-icon {
				color: #10B981;
			}

			.warning-icon {
				color: #F59E0B;
			}
		}

		.item-content {
			flex: 1;

			.item-title {
				font-size: 14px;
				font-weight: 700;
				color: $black;
				margin-bottom: 8px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 16px;

				.item-status {
					font-size: 14px;
					font-weight: 700;
					color: $black;
				}

				.item-time {
					font-size: 14px;
					font-weight: 400;
					color: $placeholder-color;
					white-space: nowrap;
					flex-shrink: 0;
				}
			}

		.item-description {
			font-size: 14px;
			font-weight: 400;
			color: $black;
			line-height: 1.5;
			padding-right: 100px;

			&.social-buttons {
				display: flex;
				gap: 10px;
				flex-wrap: wrap;
				padding-right: 0;

			.social-button {
				background-color: #F3F4F6;
				color: $black;
				border: 1px solid #E5E7EB;

				&:hover {
					background-color: #E5E7EB;
				}

				svg {
					width: 14px;
					height: 14px;
					margin-right: 10px;
				}

			&.facebook svg {
				color: #4064AC;
			}

			&.twitter svg {
				circle {
					fill: $black !important;
				}
				path {
					fill: white !important;
				}
			}

			&.youtube svg path {
				fill: #D63E22 !important;
			}
			}
			}
		}
	}

	.checklist-item.community {
		.item-icon {
			svg {
				color: $black;
			}
		}
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