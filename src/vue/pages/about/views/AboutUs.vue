<template>
	<div class="aioseo-about-us">

		<div class="aioseo-about-us-welcome">
			<div class="welcome-intro">
				<div>{{ strings.welcome.p1 }}</div>
				<div>{{ strings.welcome.p2 }}</div>
				<div>{{ strings.welcome.p3 }}</div>
				<div>{{ strings.welcome.p4 }}</div>
				<div>{{ strings.welcome.p5 }}</div>
			</div>

			<div class="welcome-image">
				<figure>
					<img
						:src="getAssetUrl(teamImg)"
						:alt="strings.welcome.imageCaption"
					/>
					<figcaption>{{ strings.welcome.imageCaption }}</figcaption>
				</figure>
			</diV>
		</div>

		<div class="aioseo-about-us-plugins">
			<grid-row>
				<grid-column
					sm="12"
					md="6"
					v-for="(plugin, pluginName) in localPlugins"
					:key="pluginName"
					:id="pluginName"
					class="plugin"
				>
					<div class="plugin-main">
						<div>
							<img
								:alt=" plugins[pluginName].name + ' Plugin image'"
								:src="plugins[pluginName].icon"
							/>
						</div>
						<div>
							<div class="main-name">{{ plugins[pluginName].name }}</div>
							<div>{{ plugins[pluginName].description }}</div>
						</div>
					</div>

					<div class="plugin-footer">
						<div class="footer-status">
							<div class="footer-status-label">{{ strings.plugins.status }}</div>
							<div
								v-if="!plugins[pluginName].installed"
								class="footer-status footer-status-not-installed"
							>
								{{ strings.plugins.statuses.notInstalled }}
							</div>
							<div
								v-if="plugins[pluginName].installed && !plugins[pluginName].activated"
								class="footer-status footer-status-deactivated"
							>
								{{ strings.plugins.statuses.deactivated }}
							</div>
							<div
								v-if="plugins[pluginName].installed && plugins[pluginName].activated"
								class="footer-status footer-status-activated"
							>
								{{ strings.plugins.statuses.activated }}
							</div>
						</div>

						<div class="footer-action">
							<base-button
								v-if="!plugins[pluginName].installed && plugins[pluginName].canInstall"
								type="blue"
								@click="activate(pluginName)"
								:loading="plugins[pluginName].loading"
							>
								{{ strings.plugins.actions.install }}
							</base-button>

							<base-button
								v-if="!plugins[pluginName].installed && !plugins[pluginName].canInstall"
								type="blue"
								tag="a"
								target="_blank"
								:href="plugin.wpLink"
							>
								<svg-external /> {{ strings.plugins.actions.install }}
							</base-button>

							<base-button
								v-if="plugins[pluginName].installed && !plugins[pluginName].activated"
								type="green"
								:disabled="!plugins[pluginName].canActivate"
								@click="activate(pluginName)"
								:loading="plugins[pluginName].loading"
							>
								{{ strings.plugins.actions.activate }}
							</base-button>

							<base-button
								v-if="plugins[pluginName].installed && plugins[pluginName].activated && 0 !== getPluginAdminUrl(pluginName, plugin).length"
								type="gray"
								tag="a"
								:href="getPluginAdminUrl(pluginName, plugin)"
							>
								{{ strings.plugins.actions.manage }}
							</base-button>
						</div>
					</div>
				</grid-column>
			</grid-row>
		</div>
	</div>
</template>

<script>
import {
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import teamImg from '@/vue/assets/images/about/team.jpg'

// Plugin image urls.
import afwpImg from '@/vue/assets/images/about/plugins/afwp.png'
import blcImg from '@/vue/assets/images/about/plugins/blc.svg'
import eddImg from '@/vue/assets/images/about/plugins/edd.png'
import emImg from '@/vue/assets/images/about/plugins/em.png'
import ffImg from '@/vue/assets/images/about/plugins/ff.png'
import ifImg from '@/vue/assets/images/about/plugins/if.png'
import miImg from '@/vue/assets/images/about/plugins/mi.png'
import omImg from '@/vue/assets/images/about/plugins/om.png'
import peImg from '@/vue/assets/images/about/plugins/pe.png'
import rafflepressImg from '@/vue/assets/images/about/plugins/rafflepress.png'
import scImg from '@/vue/assets/images/about/plugins/sc.png'
import smtpImg from '@/vue/assets/images/about/plugins/smtp.png'
import spImg from '@/vue/assets/images/about/plugins/sp.png'
import swpImg from '@/vue/assets/images/about/plugins/swp.svg'
import tfImg from '@/vue/assets/images/about/plugins/tf.png'
import tpImg from '@/vue/assets/images/about/plugins/tp.png'
import wpformsImg from '@/vue/assets/images/about/plugins/wpforms.png'
import wpspImg from '@/vue/assets/images/about/plugins/wpsp.png'
import yfImg from '@/vue/assets/images/about/plugins/yf.png'
import wpcodeImg from '@/vue/assets/images/about/plugins/wpcode.svg'
import charitableImg from '@/vue/assets/images/about/plugins/charitable.svg'
import duplicatorImg from '@/vue/assets/images/about/plugins/duplicator.svg'

import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgExternal from '@/vue/components/common/svg/External'
export default {
	setup () {
		return {
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		GridColumn,
		GridRow,
		SvgExternal
	},
	data () {
		return {
			getAssetUrl,
			teamImg,
			localPlugins     : [],
			networkActivated : [],
			strings          : {
				welcome : {
					p1 : this.$t.sprintf(
						// Translators: 1 - The plugin name ("All in One SEO"), 2 - The plugin name ("All in One SEO").
						this.$t.__('Welcome to %1$s, the original SEO plugin for WordPress. At %2$s, we build software that helps you rank your website in search results and gain organic traffic.', this.$td),
						import.meta.env.VITE_NAME,
						import.meta.env.VITE_NAME
					),
					p2 : this.$t.__('Over the years, we found that most other WordPress SEO plugins were bloated, buggy, slow, and very hard to use. So we designed our plugin as an easy and powerful tool.', this.$td),
					p3 : this.$t.__('Our goal is to take the pain out of optimizing your website for search engines.', this.$td),
					p4 : this.$t.sprintf(
						// Translators: 1 - The plugin name ("All in One SEO"), 2 - Company name ("Awesome Motive").
						this.$t.__('%1$s is brought to you by %2$s, the same team that’s behind the largest WordPress resource site, WPBeginner, the most popular lead-generation software, OptinMonster, the best WordPress analytics plugin, MonsterInsights and many more.', this.$td),
						import.meta.env.VITE_NAME,
						'Awesome Motive'
					),
					p5           : this.$t.__('Yup, we know a thing or two about building awesome products that customers love.', this.$td),
					imageCaption : this.$t.sprintf(
						// Translators: 1 - Company name ("Awesome Motive").
						this.$t.__('The %1$s Team', this.$td),
						'Awesome Motive'
					)
				},
				plugins : {
					actions : {
						install  : this.$t.__('Install Free Plugin', this.$td),
						activate : this.$t.__('Activate', this.$td),
						manage   : this.$t.__('Manage', this.$td)
					},
					status   : this.$t.__('Status:', this.$td),
					statuses : {
						activated    : this.$t.__('Activated', this.$td),
						deactivated  : this.$t.__('Deactivated', this.$td),
						notInstalled : this.$t.__('Not Installed', this.$td)
					}
				}
			},
			pluginData : {
				brokenLinkChecker : {
					name        : 'Broken Link Checker',
					description : this.$t.__('Broken Link Checker by AIOSEO is an essential tool for ensuring that all internal and external links on your website are functioning correctly. Quickly check your site for broken links and easily fix them to improve SEO.', this.$td),
					icon        : getAssetUrl(blcImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				optinMonster : {
					name        : 'OptinMonster',
					description : this.$t.__('Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', this.$td),
					icon        : getAssetUrl(omImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				wpForms : {
					name        : 'WPForms',
					description : this.$t.__('The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 1000+ form templates. Trusted by over 6 million websites as the best forms plugin.', this.$td),
					icon        : getAssetUrl(wpformsImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				wpFormsPro : {
					name       : 'WPForms Pro',
					free       : 'wpForms',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				miLite : {
					name        : 'MonsterInsights',
					description : this.$t.__('The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', this.$td),
					icon        : getAssetUrl(miImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				miPro : {
					name       : 'MonsterInsights Pro',
					free       : 'miLite',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				emLite : {
					name        : 'ExactMetrics',
					description : this.$t.__('The ExactMetrics Google Analytics for WordPress plugin helps you properly setup all the powerful Google Analytics tracking features without writing any code or hiring a developer.', this.$td),
					icon        : getAssetUrl(emImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				emPro : {
					name       : 'ExactMetrics Pro',
					free       : 'emLite',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				wpMail : {
					name        : 'WP Mail SMTP',
					description : this.$t.__('Improve your WordPress email deliverability and make sure that your website emails reach user’s inbox with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.', this.$td),
					icon        : getAssetUrl(smtpImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				wpMailPro : {
					name       : 'WP Mail SMTP Pro',
					free       : 'wpMail',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				wpcode : {
					name        : 'WPCode',
					description : this.$t.__('Future proof your WordPress customizations with the most popular code snippet management plugin for WordPress. Trusted by over 1,500,000+ websites for easily adding code to WordPress right from the admin area.', this.$td),
					icon        : getAssetUrl(wpcodeImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				wpcodePro : {
					name       : 'WPCode Pro',
					free       : 'wpcode',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				seedProd : {
					name        : 'SeedProd Coming Soon',
					description : this.$t.__('The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', this.$td),
					icon        : getAssetUrl(spImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				seedProdPro : {
					name       : 'SeedProd Coming Soon Pro',
					free       : 'seedProd',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				trustPulse : {
					name        : 'TrustPulse',
					description : this.$t.__('Boost your sales and conversions by up to 15% with real-time social proof notifications. TrustPulse helps you show live user activity and purchases to help convince other users to purchase.', this.$td),
					icon        : getAssetUrl(tpImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				rafflePress : {
					name        : 'RafflePress',
					description : this.$t.__('Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', this.$td),
					icon        : getAssetUrl(rafflepressImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				rafflePressPro : {
					name       : 'RafflePress Pro',
					free       : 'rafflePress',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				facebookFeed : {
					name        : 'Smash Balloon Facebook Feeds',
					description : this.$t.__('Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', this.$td),
					icon        : getAssetUrl(ffImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				facebookFeedPro : {
					name       : 'Smash Balloon Facebook Feeds Pro',
					free       : 'facebookFeed',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				instagramFeed : {
					name        : 'Smash Balloon Instagram Feeds',
					description : this.$t.__('Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', this.$td),
					icon        : getAssetUrl(ifImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				instagramFeedPro : {
					name       : 'Smash Balloon Instagram Feeds Pro',
					free       : 'instagramFeed',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				twitterFeed : {
					name        : 'Smash Balloon Twitter Feeds',
					description : this.$t.__('Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', this.$td),
					icon        : getAssetUrl(tfImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				twitterFeedPro : {
					name       : 'Smash Balloon Twitter Feeds Pro',
					free       : 'twitterFeed',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				youTubeFeed : {
					name        : 'Smash Balloon YouTube Feeds',
					description : this.$t.__('Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', this.$td),
					icon        : getAssetUrl(yfImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				youTubeFeedPro : {
					name       : 'Smash Balloon YouTube Feeds Pro',
					free       : 'youTubeFeed',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				pushEngage : {
					name        : 'PushEngage',
					description : this.$t.__('Connect with your visitors after they leave your website with the leading web push notification software. Over 10,000+ businesses worldwide use PushEngage to send 15 billion notifications each month.', this.$td),
					icon        : getAssetUrl(peImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				searchWp : {
					name        : 'SearchWP',
					description : this.$t.__('The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.', this.$td),
					icon        : getAssetUrl(swpImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false,
					installUrl  : this.$links.utmUrl('aioseo', 'about-us', 'https://searchwp.com/')
				},
				affiliateWp : {
					name        : 'AffiliateWP',
					description : this.$t.__('The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.', this.$td),
					icon        : getAssetUrl(afwpImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false,
					installUrl  : this.$links.utmUrl('aioseo', 'about-us', 'https://affiliatewp.com/')
				},
				wpSimplePay : {
					name        : 'WP Simple Pay',
					description : this.$t.__('The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.', this.$td),
					icon        : getAssetUrl(wpspImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				wpSimplePayPro : {
					name       : 'WP Simple Pay Pro',
					free       : 'wpSimplePay',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				easyDigitalDownloads : {
					name        : 'Easy Digital Downloads',
					description : this.$t.__('The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.', this.$td),
					icon        : getAssetUrl(eddImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				easyDigitalDownloadsPro : {
					name       : 'Easy Digital Downloads Pro',
					free       : 'easyDigitalDownloads',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				sugarCalendar : {
					name        : 'Sugar Calendar',
					description : this.$t.__('A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', this.$td),
					icon        : getAssetUrl(scImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				sugarCalendarPro : {
					name       : 'Sugar Calendar Pro',
					free       : 'sugarCalendar',
					installed  : false,
					canInstall : false,
					activated  : false,
					loading    : false
				},
				charitable : {
					name        : 'WP Charitable',
					description : this.$t.__('Top-rated WordPress donation and fundraising plugin. Over 10,000+ non-profit organizations and website owners use Charitable to create fundraising campaigns and raise more money online.', this.$td),
					icon        : getAssetUrl(charitableImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				},
				duplicator : {
					name        : 'Duplicator - WordPress Migration & Backup Plugin',
					description : this.$t.__('Leading WordPress backup & site migration plugin. Over 1,500,000+ smart website owners use Duplicator to make reliable and secure WordPress backups to protect their websites. It also makes website migration really easy.', this.$td),
					icon        : getAssetUrl(duplicatorImg),
					installed   : false,
					canInstall  : false,
					activated   : false,
					loading     : false
				}
			}
		}
	},
	methods : {
		activate (pluginName) {
			if (!this.plugins[pluginName].installed && this.plugins[pluginName].installUrl) {
				window.open(this.plugins[pluginName].installUrl, '_blank').focus()
				return
			}

			this.plugins[pluginName].loading = true
			this.pluginsStore.installPlugins([
				{
					plugin : pluginName,
					type   : 'plugin'
				}
			]).then((response) => {
				this.plugins[pluginName].loading = false
				if (Object.keys(response.body.completed).length) {
					this.plugins[pluginName].installed = true
					this.plugins[pluginName].activated = true

					if (this.rootStore.aioseo.data.isNetworkAdmin) {
						this.networkActivated.push(pluginName)
					}
				} else if (Object.keys(response.body.failed).length) {
					throw new Error(response.body.failed)
				}
			})
				.catch(error => {
					this.plugins[pluginName].loading = false
					console.error(`Unable to install ${pluginName}: ${error}`)
				})
		},
		getPluginAdminUrl (pluginName, plugin) {
			if (!this.rootStore.aioseo.data.isNetworkAdmin) {
				return plugin.adminUrl
			}

			if (!plugin.networkAdminUrl) {
				return plugin.adminUrl
			}

			if (!this.networkActivated.includes(pluginName)) {
				return plugin.adminUrl
			}

			return plugin.networkAdminUrl
		}
	},
	computed : {
		plugins () {
			// Get description and logo for premium versions from free versions.
			Object.keys(this.localPlugins).forEach(pluginName => {
				if (this.pluginData[pluginName].free) {
					this.pluginData[pluginName].description = this.pluginData[this.pluginData[pluginName].free].description
					this.pluginData[pluginName].icon        = this.pluginData[this.pluginData[pluginName].free].icon
				}
			})
			return this.pluginData
		}
	},
	mounted () {
		this.localPlugins = { ...this.pluginsStore.plugins }

		// Set installation and activation status for each plugin.
		Object.keys(this.localPlugins).forEach(pluginName => {
			this.pluginData[pluginName].installed   = this.localPlugins[pluginName].installed
			this.pluginData[pluginName].canInstall  = this.localPlugins[pluginName].canInstall
			this.pluginData[pluginName].canActivate = this.localPlugins[pluginName].canActivate
			this.pluginData[pluginName].activated   = this.localPlugins[pluginName].activated
			// Don't render free version if premium version is installed.
			if (this.plugins[pluginName].free) {
				if (this.localPlugins[pluginName].installed) {
					delete this.localPlugins[this.plugins[pluginName].free]
				} else {
					delete this.localPlugins[pluginName]
				}
			}
		})
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-about-us {
	.aioseo-about-us-welcome,
	.aioseo-about-us-plugins {
		margin-top: var(--aioseo-gutter);
		width: 100%;
	}

	.aioseo-about-us-welcome,
	.aioseo-about-us-plugins .plugin .plugin-main,
	.aioseo-about-us-plugins .plugin .plugin-footer {
		background-color: #fff;
		box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
		border: 1px solid $border;
	}

	.aioseo-about-us-welcome {
		display: flex;
		align-items: center;
		gap: calc(var(--aioseo-gutter) * 2);
		padding: calc(var(--aioseo-gutter) * 2);
		font-size: 14px;
		color: $black;

		@media only screen and (max-width: 1042px) {
			flex-direction: column;
		}

		.welcome-intro {
			flex: 2 2 auto;

			div {
				line-height: 1.5;

				+ div {
					margin-top: 20px;
				}

				&:first-of-type {
					font-size: 24px;
					line-height: 30px;
					font-weight: $font-bold;
					margin: 0 0 20px;
				}

			}
		}

		.welcome-image {
			text-align: center;

			figure {
				margin: 0;
			}

			img {
				max-width: 100%;
			}
			figcaption {
				margin: 16px 0 0 0;
				color: $placeholder-color;
			}
		}
	}
	.aioseo-about-us-plugins {
		.plugin {
			display: flex;
			flex-direction: column;
			font-size: 14px;
			line-height: 24px;

			.plugin-main {
				display: flex;
				flex-direction: row;
				padding: 22px;
				flex-grow: 1;

				img {
					width: 50px;
					margin: 0 30px 0 0;
					max-width: 50px;
					max-height: 50px;
				}

				.main-name {
					margin: 0 0 10px 0;
					font-size: 14px;
					font-weight: bold;
					color: $black;
				}
			}

			.plugin-footer {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 12px;

				.footer-status {
					font-size: $font-md;
					line-height: 22px;
					font-weight: bold;

					div {
						display: inline-block;
						margin-right: 12px;
					}

					.footer-status-label {
						color: $placeholder-color;
					}

					.footer-status-not-installed {
						color: $black2;
					}

					.footer-status-deactivated {
						color: $red;
					}

					.footer-status-activated {
						color: $green;
					}
				}

				.footer-action {
					button,
					a {
						width: fit-content;
						height: fit-content;
						padding: 7px 14px;
						font-size: inherit;
						font-size: 12px;
						line-height: 18px;
					}

					.aioseo-button {
						svg.aioseo-external {
							width: 14px;
							height: 14px;
							margin-right: 10px;
						}
					}
				}
			}
		}
	}
}
</style>