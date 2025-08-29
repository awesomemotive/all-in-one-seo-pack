<template>
	<div class="aioseo-dashboard">
		<core-main
			:page-name="strings.pageName"
			:show-tabs="false"
			:show-save-button="false"
		>
			<div>
				<div
					v-if="settingsStore.settings.showSetupWizard && allowed('aioseo_setup_wizard')"
					class="dashboard-getting-started"
				>
					<core-getting-started />
				</div>

				<grid-row>
					<grid-column
						md="6"
					>
						<core-card
							v-if="!rootStore.aioseo.setupWizard.isCompleted"
							slug="dashboardSeoSetup"
							:header-text="strings.seoSetup"
						>
							<core-seo-setup />
						</core-card>

						<core-card
							slug="dashboardOverview"
							:header-text="strings.seoOverview"
						>
							<core-overview />
						</core-card>

						<grid-row
							v-if="quickLinks.length > 0"
							class="aioseo-quicklinks-cards-row"
						>
							<grid-column>
								<div class="aioseo-quicklinks-title">
									{{ strings.quicklinks }}

									<core-tooltip>
										<svg-circle-question-mark />

										<template #tooltip>
											{{ strings.quicklinksTooltip }}
										</template>
									</core-tooltip>
								</div>
							</grid-column>

							<grid-column
								v-for="(link, index) in quickLinks"
								:key="index"
								lg="6"
								class="aioseo-quicklinks-cards"
							>
								<div class="feature-card-body">
									<div class="feature-card-header">
										<component
											:is="link.icon"
										/>
										{{ link.name }}
									</div>
									<div class="feature-card-description">
										{{ link.description }}

										<div
											v-if="link.manageUrl && allowed(link.access)"
											class="learn-more"
										>
											<a :href="getHref(link.manageUrl)">{{ strings.manage }}</a>
											<a
												:href="getHref(link.manageUrl)"
												class="no-underline"
											>
												&rarr;
											</a>
										</div>
									</div>
								</div>
							</grid-column>
						</grid-row>
					</grid-column>

					<grid-column
						md="6"
					>
						<core-card
							slug="dashboardSeoSiteScore"
							:header-text="strings.seoSiteScore"
						>
							<core-seo-site-score />
						</core-card>

						<core-card
							class="dashboard-notifications"
							slug="dashboardNotifications"
						>
							<template #header>
								<div
									class="notifications-count"
									v-if="notificationsCount"
								>
									({{ notificationsCount }})
								</div>
								<div>{{ notificationTitle }}</div>

								<a
									v-if="dismissed"
									class="show-dismissed-notifications"
									href="#"
									@click.prevent="dismissed = false"
								>{{ strings.activeNotifications }}</a>
							</template>

							<core-notification-cards
								:notifications="filteredNotifications"
								:dismissedCount="notificationsStore.dismissedNotificationsCount"
								@toggle-dismissed="dismissed = !dismissed"
							>
								<template #no-notifications>
									<div class="no-dashboard-notifications">
										<div>
											{{ strings.noNewNotificationsThisMoment }}
										</div>
										<a
											v-if="notificationsStore.dismissedNotificationsCount"
											href="#"
											@click.prevent="dismissed = true"
										>{{ strings.seeAllDismissedNotifications }}</a>
									</div>
								</template>
							</core-notification-cards>

							<div
								v-if="notifications.length > visibleNotifications"
								class="notification-footer"
							>
								<div class="more-notifications">
									<a
										href="#"
										@click.stop.prevent="notificationsStore.toggleNotifications"
									>
										{{ moreNotifications }}
									</a>

									<a
										class="no-underline"
										href="#"
										@click.stop.prevent="notificationsStore.toggleNotifications"
									>
										&rarr;
									</a>
								</div>
							</div>
						</core-card>

						<core-card
							class="dashboard-support"
							slug="dashboardSupport"
							:header-text="strings.support"
						>
							<div
								v-for="(option, index) in supportOptions"
								:key="index"
								class="aioseo-settings-row"
							>
								<a
									:href="option.link"
									:target="option.blank ? '_blank' : null"
								>
									<component
										:is="option.icon"
									/>
									{{ option.text }}
								</a>
							</div>
						</core-card>

						<cta
							v-if="licenseStore.isUnlicensed"
							class="dashboard-cta"
							:type="3"
							:floating="false"
							:cta-link="links.getUpsellUrl('dashboard-cta', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
							:feature-list="UPSELL_FEATURE_LIST"
							:button-text="strings.ctaButton"
							:learn-more-link="links.getUpsellUrl('dashboard-cta', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
						>
							<template #header-text>
								{{ strings.ctaHeaderText }}
							</template>
						</cta>
					</grid-column>
				</grid-row>
			</div>
		</core-main>
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
	useNotificationsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { merge } from 'lodash-es'

import { useNotifications } from '@/vue/composables/Notifications'
import { useUrl } from '@/vue/composables/Url'

import CoreCard from '@/vue/components/common/core/Card'
import CoreFeatureCard from '@/vue/components/common/core/FeatureCard'
import CoreGettingStarted from '@/vue/components/common/core/GettingStarted'
import CoreMain from '@/vue/components/common/core/main/Index'
import CoreNotificationCards from '@/vue/components/common/core/NotificationCards'
import CoreOverview from '@/vue/components/common/core/Overview'
import CoreSeoSetup from '@/vue/components/common/core/SeoSetup'
import CoreSeoSiteScore from '@/vue/components/AIOSEO_VERSION/core/seo-site-score/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import Cta from '@/vue/components/common/cta/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgBook from '@/vue/components/common/svg/Book'
import SvgBuild from '@/vue/components/common/svg/Build'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgClipboardCheckmark from '@/vue/components/common/svg/ClipboardCheckmark'
import SvgHistory from '@/vue/components/common/svg/History'
import SvgLinkAssistant from '@/vue/components/common/svg/link/Assistant'
import SvgLocationPin from '@/vue/components/common/svg/LocationPin'
import SvgMessage from '@/vue/components/common/svg/Message'
import SvgRedirect from '@/vue/components/common/svg/Redirect'
import SvgRocket from '@/vue/components/common/svg/Rocket'
import SvgShare from '@/vue/components/common/svg/Share'
import SvgSitemapsPro from '@/vue/components/common/svg/SitemapsPro'
import SvgStatistics from '@/vue/components/common/svg/Statistics'
import SvgTitleAndMeta from '@/vue/components/common/svg/TitleAndMeta'
import SvgVideoCamera from '@/vue/components/common/svg/VideoCamera'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			dismissed,
			notificationTitle,
			notifications,
			notificationsCount,
			strings
		} = useNotifications()

		const {
			getHref
		} = useUrl()

		return {
			UPSELL_FEATURE_LIST,
			composableStrings  : strings,
			dismissed,
			getHref,
			licenseStore       : useLicenseStore(),
			links,
			notificationTitle,
			notifications,
			notificationsCount,
			notificationsStore : useNotificationsStore(),
			rootStore          : useRootStore(),
			settingsStore      : useSettingsStore()
		}
	},
	components : {
		CoreCard,
		CoreFeatureCard,
		CoreGettingStarted,
		CoreMain,
		CoreNotificationCards,
		CoreOverview,
		CoreSeoSetup,
		CoreSeoSiteScore,
		CoreTooltip,
		Cta,
		GridColumn,
		GridRow,
		SvgBook,
		SvgBuild,
		SvgCircleQuestionMark,
		SvgClipboardCheckmark,
		SvgHistory,
		SvgLinkAssistant,
		SvgLocationPin,
		SvgMessage,
		SvgRedirect,
		SvgRocket,
		SvgShare,
		SvgSitemapsPro,
		SvgStatistics,
		SvgTitleAndMeta,
		SvgVideoCamera
	},
	data () {
		return {
			allowed,
			visibleNotifications : 2,
			strings              : merge(this.composableStrings, {
				pageName                     : __('Dashboard', td),
				noNewNotificationsThisMoment : __('There are no new notifications at this moment.', td),
				seeAllDismissedNotifications : __('See all dismissed notifications.', td),
				seoSiteScore                 : __('SEO Site Score', td),
				seoOverview                  : sprintf(
					// Translators: 1 - The plugin shortname ("AIOSEO").
					__('%1$s Overview', td),
					import.meta.env.VITE_SHORT_NAME
				),
				seoSetup         : __('SEO Setup', td),
				support          : __('Support', td),
				readSeoUserGuide : sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					__('Read the %1$s user guide', td),
					import.meta.env.VITE_NAME
				),
				accessPremiumSupport   : __('Access our Premium Support', td),
				viewChangelog          : __('View the Changelog', td),
				watchVideoTutorials    : __('Watch video tutorials', td),
				gettingStarted         : __('Getting started? Read the Beginners Guide', td),
				quicklinks             : __('Quicklinks', td),
				quicklinksTooltip      : __('You can use these quicklinks to quickly access our settings pages to adjust your site\'s SEO settings.', td),
				manage                 : __('Manage', td),
				searchAppearance       : __('Search Appearance', td),
				manageSearchAppearance : __('Configure how your website content will look in Google, Bing and other search engines.', td),
				seoAnalysis            : __('SEO Analysis', td),
				manageSeoAnalysis      : __('Check how your site scores with our SEO analyzer and compare against your competitor\'s site.', td),
				localSeo               : __('Local SEO', td),
				manageLocalSeo         : __('Improve local SEO rankings with schema for business address, open hours, contact, and more.', td),
				socialNetworks         : __('Social Networks', td),
				manageSocialNetworks   : __('Setup Open Graph for Facebook, X (Twitter), etc. to show the right content / thumbnail preview.', td),
				tools                  : __('Tools', td),
				manageTools            : __('Fine-tune your site with our powerful tools including Robots.txt editor, import/export and more.', td),
				sitemap                : __('Sitemaps', td),
				manageSitemap          : __('Manage all of your sitemap settings, including XML, Video, News and more.', td),
				linkAssistant          : __('Link Assistant', td),
				manageLinkAssistant    : __('Manage existing links, get relevant suggestions for adding internal links to older content, discover orphaned posts and more.', td),
				redirects              : __('Redirection Manager', td),
				manageRedirects        : __('Easily create and manage redirects for your broken links to avoid confusing search engines and users, as well as losing valuable backlinks.', td),
				searchStatistics       : __('Search Statistics', td),
				manageSearchStatistics : __('Track how your site is performing in search rankings and generate reports with actionable insights.', td),
				ctaHeaderText          : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro".
					__('Get more features in %1$s %2$s:', td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				ctaButton : sprintf(
					// Translators: 1 - "Pro", 2 - A discount percentage (e.g. "50%").
					__('Upgrade to %1$s and Save %2$s', td),
					'Pro',
					DISCOUNT_PERCENTAGE
				),
				dismissAll          : __('Dismiss All', td),
				relaunchSetupWizard : __('Relaunch Setup Wizard', td)
			})
		}
	},
	computed : {
		moreNotifications () {
			if (1 === this.remainingNotificationsCount) {
				return __('You have 1 more notification', td)
			}

			return sprintf(
				// Translators: 1 - The amount of remaining notifications.
				__('You have %1$s more notifications', td),
				this.remainingNotificationsCount
			)
		},
		remainingNotificationsCount () {
			return this.notifications.length - this.visibleNotifications
		},
		filteredNotifications () {
			const notifications = [ ...this.notifications ]
			return notifications.splice(0, this.visibleNotifications)
		},
		supportOptions () {
			const options = [
				{ icon: 'svg-book', text: this.strings.readSeoUserGuide, link: links.utmUrl('dashboard-support-box', 'user-guide', 'doc-categories/getting-started/'), blank: true },
				{ icon: 'svg-message', text: this.strings.accessPremiumSupport, link: links.utmUrl('dashboard-support-box', 'premium-support', 'contact/'), blank: true },
				{ icon: 'svg-history', text: this.strings.viewChangelog, link: links.utmUrl('dashboard-support-box', 'changelog', 'changelog/'), blank: true },
				// { icon: 'svg-video-camera', text: this.strings.watchVideoTutorials, link: links.utmUrl('dashboard-support-box', 'video-tutorials', 'doc-type/videos/'), blank: true },
				{ icon: 'svg-book', text: this.strings.gettingStarted, link: links.utmUrl('dashboard-support-box', 'beginners-guide', 'docs/quick-start-guide/'), blank: true }
			]

			if (!allowed('aioseo_setup_wizard')) {
				return options
			}

			return this.settingsStore.settings.showSetupWizard
				? options
				: options.concat({
					icon  : 'svg-rocket',
					text  : this.strings.relaunchSetupWizard,
					link  : this.rootStore.aioseo.urls.aio.wizard,
					blank : false
				})
		},
		quickLinks () {
			return [
				{
					icon        : 'svg-title-and-meta',
					description : this.strings.manageSearchAppearance,
					name        : this.strings.searchAppearance,
					manageUrl   : this.rootStore.aioseo.urls.aio.searchAppearance,
					access      : 'aioseo_search_appearance_settings'
				},
				{
					icon        : 'svg-clipboard-checkmark',
					description : this.strings.manageSeoAnalysis,
					name        : this.strings.seoAnalysis,
					manageUrl   : this.rootStore.aioseo.urls.aio.seoAnalysis,
					access      : 'aioseo_seo_analysis_settings'
				},
				{
					icon        : 'svg-location-pin',
					description : this.strings.manageLocalSeo,
					name        : this.strings.localSeo,
					manageUrl   : this.rootStore.aioseo.urls.aio.localSeo,
					access      : 'aioseo_local_seo_settings'
				},
				{
					icon        : 'svg-share',
					description : this.strings.manageSocialNetworks,
					name        : this.strings.socialNetworks,
					manageUrl   : this.rootStore.aioseo.urls.aio.socialNetworks,
					access      : 'aioseo_social_networks_settings'
				},
				{
					icon        : 'svg-statistics',
					description : this.strings.manageSearchStatistics,
					name        : this.strings.searchStatistics,
					manageUrl   : this.rootStore.aioseo.urls.aio.searchStatistics,
					access      : 'aioseo_search_statistics_settings'
				},
				/* {
					icon        : 'svg-build',
					description : this.strings.manageTools,
					name        : this.strings.tools,
					manageUrl   : this.rootStore.aioseo.urls.aio.tools,
					access      : 'aioseo_tools_settings'
				}, */
				{
					icon        : 'svg-sitemaps-pro',
					description : this.strings.manageSitemap,
					name        : this.strings.sitemap,
					manageUrl   : this.rootStore.aioseo.urls.aio.sitemaps,
					access      : 'aioseo_sitemap_settings'
				},
				{
					icon        : 'svg-link-assistant',
					description : this.strings.manageLinkAssistant,
					name        : this.strings.linkAssistant,
					manageUrl   : this.rootStore.aioseo.urls.aio.linkAssistant,
					access      : 'aioseo_link_assistant_settings'
				},
				{
					icon        : 'svg-redirect',
					description : this.strings.manageRedirects,
					name        : this.strings.redirects,
					manageUrl   : this.rootStore.aioseo.urls.aio.redirects,
					access      : 'aioseo_redirects_settings'
				}
			].filter(link => allowed(link.access))
		}
	},
	methods : {
		processDismissAllNotifications () {
			const slugs = []
			this.notifications.forEach(notification => {
				slugs.push(notification.slug)
			})
			this.notificationsStore.dismissNotifications(slugs)
		}
	}
}
</script>

<style lang="scss">
.aioseo-dashboard {

	.aioseo-main > .aioseo-container {
		padding-top: 0;
	}

	.dashboard-getting-started {
		padding-top: 1px;

		> div {
			margin-top: calc(var(--aioseo-gutter) - 1px);
			margin-bottom: 0;
		}
	}

	.dashboard-notifications {
		.notifications-count {
			margin-right: 5px;

			&+ div {
				flex: 1;
			}
		}

		.no-dashboard-notifications {
			padding-top: 0;
			font-size: 14px;
			color: $black;
			text-align: center;

			> div {
				font-weight: 600;
				margin-bottom: 10px;
			}

			a {
				color: $black2;
				font-size: 14px;
			}
		}

		.show-dismissed-notifications {
			margin: 0 5px;
			font-size: 14px;
			color: $black2;
		}

		.aioseo-notification-cards {
			.aioseo-notification {
				&:last-child {
					> div {
						padding-bottom: 0;
					}
				}
			}
		}

		.notification-footer {
			margin-top: 10px;
			padding-top: 20px;
			border-top: 1px solid $border;
			display: flex;

			.more-notifications {
				flex: 1;

				a {
					margin-right: 5px;
					font-weight: 600;
					font-size: 14px;
				}
			}

			a.dismiss {
				color: $placeholder-color;
				font-size: 14px;
			}
		}
	}

	.dashboard-support {
		.aioseo-settings-row {
			margin-bottom: var(--aioseo-gutter);

			a {
				display: flex;
				align-items: center;
				font-size: $font-md;
				line-height: 22px;
				font-weight: 600;
				color: $black;
				text-decoration: none;

				&:hover {
					color: $blue;
				}
			}

			svg {
				color: $blue;
				width: 16px;
				height: 16px;
				margin-right: 8px;
			}
		}
	}

	.aioseo-quicklinks-cards-row.aioseo-row {
		--aioseo-gutter: 16px;

		@include aioseoGrid(2, 204px);

		> * {
			max-width: none;
			padding: 0;
		}

		.aioseo-col:first-child {
			grid-column: 1 / -1;
			grid-row: 1 / -1;
		}

		.aioseo-quicklinks-cards {
			height: 100%;
			border: 1px solid $border;
			background: #fff;
			box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
			color: $black;
			display: flex;
			flex-direction: column;

			.feature-card-body {
				line-height: 22px;
				padding: $gutter;
				flex: 1;

				.feature-card-header {
					display: flex;
					align-items: center;
					font-size: $font-md;
					font-weight: $font-bold;
					margin-bottom: 12px;

					img,
					svg {
						width: 24px;
						height: 24px;
						margin-right: 10px;
					}
				}

				.feature-card-description {
					color: $black2;
					font-size: $font-md;

					.learn-more {
						margin-top: 12px;
						font-size: $font-md;
					}
				}
			}
		}
	}

	.aioseo-quicklinks-title {
		font-size: 18px;
		line-height: 22px;
		color: $black;
		font-weight: 600;
		display: flex;
		align-items: center;

		svg {
			width: 16px;
			height: 16px;
			color: $placeholder-color;
		}
	}

	.dashboard-cta {
		margin-top: var(--aioseo-gutter);
	}
}
</style>