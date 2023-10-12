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
								<core-feature-card
									:feature="link"
									:can-activate="false"
									:can-manage="allowed(link.access)"
									static-card
								>
									<template #title>
										<component
											:is="link.icon"
										/>
										{{ link.name }}
									</template>

									<template #description>
										{{ link.description }}
									</template>
								</core-feature-card>
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
							:cta-link="$links.utmUrl('dashboard-cta')"
							:feature-list="$constants.UPSELL_FEATURE_LIST"
							:button-text="strings.ctaButton"
							:learn-more-link="$links.getUpsellUrl('dashboard-cta', null, $isPro ? 'pricing' : 'liteUpgrade')"
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
	useLicenseStore,
	useNotificationsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { merge } from 'lodash-es'
import { useNotifications } from '@/vue/composables'
import { Notifications } from '@/vue/mixins'

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

export default {
	setup () {
		const { strings } = useNotifications()

		return {
			licenseStore       : useLicenseStore(),
			notificationsStore : useNotificationsStore(),
			rootStore          : useRootStore(),
			settingsStore      : useSettingsStore(),
			composableStrings  : strings
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
	mixins : [ Notifications ],
	data () {
		return {
			allowed,
			dismissed            : false,
			visibleNotifications : 2,
			strings              : merge(this.composableStrings, {
				pageName                     : this.$t.__('Dashboard', this.$td),
				noNewNotificationsThisMoment : this.$t.__('There are no new notifications at this moment.', this.$td),
				seeAllDismissedNotifications : this.$t.__('See all dismissed notifications.', this.$td),
				seoSiteScore                 : this.$t.__('SEO Site Score', this.$td),
				seoOverview                  : this.$t.sprintf(
					// Translators: 1 - The plugin shortname ("AIOSEO").
					this.$t.__('%1$s Overview', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				seoSetup         : this.$t.__('SEO Setup', this.$td),
				support          : this.$t.__('Support', this.$td),
				readSeoUserGuide : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					this.$t.__('Read the %1$s user guide', this.$td),
					import.meta.env.VITE_NAME
				),
				accessPremiumSupport   : this.$t.__('Access our Premium Support', this.$td),
				viewChangelog          : this.$t.__('View the Changelog', this.$td),
				watchVideoTutorials    : this.$t.__('Watch video tutorials', this.$td),
				gettingStarted         : this.$t.__('Getting started? Read the Beginners Guide', this.$td),
				quicklinks             : this.$t.__('Quicklinks', this.$td),
				quicklinksTooltip      : this.$t.__('You can use these quicklinks to quickly access our settings pages to adjust your site\'s SEO settings.', this.$td),
				searchAppearance       : this.$t.__('Search Appearance', this.$td),
				manageSearchAppearance : this.$t.__('Configure how your website content will look in Google, Bing and other search engines.', this.$td),
				seoAnalysis            : this.$t.__('SEO Analysis', this.$td),
				manageSeoAnalysis      : this.$t.__('Check how your site scores with our SEO analyzer and compare against your competitor\'s site.', this.$td),
				localSeo               : this.$t.__('Local SEO', this.$td),
				manageLocalSeo         : this.$t.__('Improve local SEO rankings with schema for business address, open hours, contact, and more.', this.$td),
				socialNetworks         : this.$t.__('Social Networks', this.$td),
				manageSocialNetworks   : this.$t.__('Setup Open Graph for Facebook, Twitter, etc. to show the right content / thumbnail preview.', this.$td),
				tools                  : this.$t.__('Tools', this.$td),
				manageTools            : this.$t.__('Fine-tune your site with our powerful tools including Robots.txt editor, import/export and more.', this.$td),
				sitemap                : this.$t.__('Sitemaps', this.$td),
				manageSitemap          : this.$t.__('Manage all of your sitemap settings, including XML, Video, News and more.', this.$td),
				linkAssistant          : this.$t.__('Link Assistant', this.$td),
				manageLinkAssistant    : this.$t.__('Manage existing links, get relevant suggestions for adding internal links to older content, discover orphaned posts and more.', this.$td),
				redirects              : this.$t.__('Redirection Manager', this.$td),
				manageRedirects        : this.$t.__('Easily create and manage redirects for your broken links to avoid confusing search engines and users, as well as losing valuable backlinks.', this.$td),
				searchStatistics       : this.$t.__('Search Statistics', this.$td),
				manageSearchStatistics : this.$t.__('Track how your site is performing in search rankings and generate reports with actionable insights.', this.$td),
				ctaHeaderText          : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('Get more features in %1$s %2$s:', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				ctaButton : this.$t.sprintf(
					// Translators: 1 - "Pro", 2 - A discount percentage (e.g. "50%").
					this.$t.__('Upgrade to %1$s and Save %2$s', this.$td),
					'Pro',
					this.$constants.DISCOUNT_PERCENTAGE
				),
				dismissAll          : this.$t.__('Dismiss All', this.$td),
				relaunchSetupWizard : this.$t.__('Relaunch Setup Wizard', this.$td)
			})
		}
	},
	computed : {
		moreNotifications () {
			if (1 === this.remainingNotificationsCount) {
				return this.$t.__('You have 1 more notification', this.$td)
			}

			return this.$t.sprintf(
				// Translators: 1 - The amount of remaining notifications.
				this.$t.__('You have %1$s more notifications', this.$td),
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
				{ icon: 'svg-book', text: this.strings.readSeoUserGuide, link: this.$links.utmUrl('dashboard-support-box', 'user-guide', 'doc-categories/getting-started/'), blank: true },
				{ icon: 'svg-message', text: this.strings.accessPremiumSupport, link: this.$links.utmUrl('dashboard-support-box', 'premium-support', 'contact/'), blank: true },
				{ icon: 'svg-history', text: this.strings.viewChangelog, link: this.$links.utmUrl('dashboard-support-box', 'changelog', 'changelog/'), blank: true },
				// { icon: 'svg-video-camera', text: this.strings.watchVideoTutorials, link: this.$links.utmUrl('dashboard-support-box', 'video-tutorials', 'doc-type/videos/'), blank: true },
				{ icon: 'svg-book', text: this.strings.gettingStarted, link: this.$links.utmUrl('dashboard-support-box', 'beginners-guide', 'docs/quick-start-guide/'), blank: true }
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