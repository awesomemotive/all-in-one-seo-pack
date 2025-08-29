<template>
	<div class="aioseo-feature-manager">
		<div class="aioseo-feature-manager-header">
			<div class="buttons" v-if="getAddons.filter(addon => addon.canActivate === true).length > 0">
				<div class="button-content">
					<base-button
						size="medium"
						type="blue"
						:loading="loading.activateAll"
						@click="activateAllFeatures"
					>{{ strings.activateAllFeatures }}</base-button>
					<base-button
						v-if="!licenseStore.isUnlicensed"
						size="medium"
						type="gray"
						:loading="loading.deactivateAll"
						@click="deactivateAllFeatures"
					>{{ strings.deactivateAllFeatures }}</base-button>
				</div>
			</div>
			<div class="search">
				<base-input
					v-model="search"
					size="medium"
					:placeholder="strings.searchForFeatures"
					prepend-icon="search"
				/>
			</div>
		</div>

		<div class="aioseo-feature-manager-addons">
			<core-alert
				v-if="rootStore.isPro && licenseStore.isUnlicensed"
				type="red"
			>
				<strong>{{ yourLicenseIsText }}</strong>
				{{ strings.aValidLicenseIsRequired }}

				<div class="buttons">
					<base-button
						type="blue"
						size="small"
						tag="a"
						:href="rootStore.aioseo.data.isNetworkAdmin ? rootStore.aioseo.urls.aio.networkSettings : rootStore.aioseo.urls.aio.settings"
					>
						{{ strings.enterLicenseKey }}
					</base-button>

					<base-button
						type="green"
						size="small"
						tag="a"
						target="_blank"
						:href="links.getUpsellUrl('feature-manager-upgrade', 'no-license-key', 'pricing')"
					>
						{{ strings.purchaseLicense }}
					</base-button>
				</div>
			</core-alert>

			<grid-row>
				<grid-column
					v-for="addon in getAddons"
					:key="addon.sku"
					sm="6"
					lg="4"
				>
					<core-feature-card
						ref="addons"
						:can-activate="addon.canActivate"
						:can-manage="allowed(addon.capability)"
						:feature="addon"
					>
						<template #title>
							<!--
								This allows us to pass in one of our svg components built into the plugin,
								i.e. `svg-sitemaps-pro`
								or we can send in a base64 encoded svg as well,
								or we can pass in an image URL.
							-->
							<component
								:is="getIconComponent(addon.icon)"
								:src="getIconSrc(addon.icon, addon.image)"
							/>
							{{ addon.name }}
						</template>

						<template #description>
							<div v-html="getAddonDescription(addon)" />
						</template>
					</core-feature-card>
				</grid-column>
			</grid-row>
		</div>

		<cta
			v-if="licenseStore.isUnlicensed"
			class="feature-manager-upsell"
			:type="2"
			:button-text="strings.ctaButtonText"
			:floating="false"
			:cta-link="links.getUpsellUrl('feature-manager', 'main-cta', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			:learn-more-link="links.getUpsellUrl('feature-manager', 'main-cta-all-features', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="UPSELL_FEATURE_LIST"
		>
			<template #header-text>
				<span class="large">{{ strings.ctaHeaderText }}</span>
			</template>

			<template #description>
				{{ upgradeToday }}
			</template>

			<template #featured-image>
				<img
					alt="Purchase AIOSEO Today!"
					:src="getAssetUrl(ctaImg)"
				/>
			</template>
		</cta>

		<core-modal
			:show="showNetworkModal"
			no-header
			@close="closeNetworkModal(false)"
			:classes="[ 'aioseo-feature-manager-modal' ]"
		>
			<template #body>
				<div class="aioseo-modal-body">
					<button
						class="close"
						@click.stop="closeNetworkModal(false)"
					>
						<svg-close @click.stop="closeNetworkModal(false)" />
					</button>

					<h3>{{ strings.areYouSureNetworkChange }}</h3>

					<div class="reset-description">
						{{ networkChangeMessage }}
					</div>

					<base-button
						type="blue"
						size="medium"
						@click="closeNetworkModal(true)"
					>
						{{ strings.yesProcessNetworkChange }}
					</base-button>

					<base-button
						type="gray"
						size="medium"
						@click="closeNetworkModal(false)"
					>
						{{ strings.noChangedMind }}
					</base-button>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import { UPSELL_FEATURE_LIST } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useAddonsStore,
	useLicenseStore,
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { getAssetUrl } from '@/vue/utils/helpers'

import { useLicense } from '@/vue/composables/License'

import ctaImg from '@/vue/assets/images/upsells/news-sitemap.png'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreFeatureCard from '@/vue/components/common/core/FeatureCard'
import CoreModal from '@/vue/components/common/core/modal/Index'
import Cta from '@/vue/components/common/cta/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgCode from '@/vue/components/common/svg/Code'
import SvgEeat from '@/vue/components/common/svg/Eeat'
import SvgImageSeo from '@/vue/components/common/svg/ImageSeo'
import SvgLinkAssistant from '@/vue/components/common/svg/link/Assistant'
import SvgLocalBusiness from '@/vue/components/common/svg/local/Business'
import SvgRedirect from '@/vue/components/common/svg/Redirect'
import SvgSitemapsPro from '@/vue/components/common/svg/SitemapsPro'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			yourLicenseIsText
		} = useLicense()

		return {
			UPSELL_FEATURE_LIST,
			addonsStore  : useAddonsStore(),
			licenseStore : useLicenseStore(),
			links,
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore(),
			yourLicenseIsText
		}
	},
	components : {
		CoreAlert,
		CoreFeatureCard,
		CoreModal,
		Cta,
		GridColumn,
		GridRow,
		SvgClose,
		SvgCode,
		SvgEeat,
		SvgImageSeo,
		SvgLinkAssistant,
		SvgLocalBusiness,
		SvgRedirect,
		SvgSitemapsPro
	},
	data () {
		return {
			allowed,
			ctaImg,
			showNetworkModal : false,
			maybeActivate    : false,
			maybeDeactivate  : false,
			search           : null,
			loading          : {
				activateAll   : false,
				deactivateAll : false
			},
			strings : {
				videoNewsSitemaps     : __('Video and News Sitemaps', td),
				imageSeoOptimization  : __('Image SEO Optimization', td),
				localBusinessSeo      : __('Local Business SEO', td),
				advancedWooCommerce   : __('Advanced WooCommerce', td),
				customTaxonomies      : __('SEO for Categories, Tags and Custom Taxonomies', td),
				andMore               : __('And many more...', td),
				activateAllFeatures   : __('Activate All Features', td),
				deactivateAllFeatures : __('Deactivate All Features', td),
				searchForFeatures     : __('Search for Features...', td),
				ctaHeaderText         : sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					__('Upgrade %1$s to Pro and Unlock all Features!', td),
					import.meta.env.VITE_SHORT_NAME
				),
				ctaButtonText           : __('Unlock All Features', td),
				aValidLicenseIsRequired : __('A valid license key is required in order to use our addons.', td),
				enterLicenseKey         : __('Enter License Key', td),
				purchaseLicense         : __('Purchase License', td),
				areYouSureNetworkChange : __('This is a network-wide change.', td),
				yesProcessNetworkChange : __('Yes, process this network change', td),
				noChangedMind           : __('No, I changed my mind', td)
			},
			descriptions : {
				aioseoEeat : {
					description : __('Optimize your site for Google\'s E-E-A-T ranking factor by proving your writer\'s expertise through author schema markup and new UI elements.', td),
					version     : 0
				},
				aioseoImageSeo : {
					description : __('Globally control the Title attribute and Alt text for images in your content. These attributes are essential for both accessibility and SEO.', td),
					version     : 0
				},
				aioseoIndexNow : {
					description : __('Add IndexNow support to instantly notify search engines when your content has changed. This helps the search engines to prioritize the changes on your website and helps you rank faster.', td),
					version     : 0
				},
				aioseoLinkAssistant : {
					description : __('Super-charge your SEO with Link Assistant! Get relevant suggestions for adding internal links to older content as well as finding any orphaned posts that have no internal links. Use our reporting feature to see all link suggestions or add them directly from any page or post.', td),
					version     : 0
				},
				aioseoLocalBusiness : {
					description : __('Local Business schema markup enables you to tell Google about your business, including your business name, address and phone number, opening hours and price range. This information may be displayed as a Knowledge Graph card or business carousel.', td),
					version     : 0
				},
				aioseoNewsSitemap : {
					description : __('Our Google News Sitemap lets you control which content you submit to Google News and only contains articles that were published in the last 48 hours. In order to submit a News Sitemap to Google, you must have added your site to Google’s Publisher Center and had it approved.', td),
					version     : 0
				},
				aioseoRedirects : {
					description : __('Our Redirection Manager allows you to create and manage redirects for 404s or modified posts.', td),
					version     : 0
				},
				aioseoRestApi : {
					description : __('Manage your post and term SEO meta via the WordPress REST API. This addon also works seamlessly with headless WordPress installs.', td),
					version     : 0
				},
				aioseoVideoSitemap : {
					description : __('The Video Sitemap works in much the same way as the XML Sitemap module, it generates an XML Sitemap specifically for video content on your site. Search engines use this information to display rich snippet information in search results.', td),
					version     : 0
				}
			}
		}
	},
	computed : {
		upgradeToday () {
			return sprintf(
				// Translators: 1 - Plugin short name ("AIOSEO"), 2 - "Pro".
				__('%1$s %2$s comes with many additional features to help take your site\'s SEO to the next level!', td),
				import.meta.env.VITE_SHORT_NAME,
				'Pro'
			)
		},
		getAddons () {
			return this.addonsStore.addons
				.filter(addon => !this.search || addon.name.toLowerCase().includes(this.search.toLowerCase()))
		},
		networkChangeMessage () {
			if (this.activated) {
				return __('Are you sure you want to deactivate these addons across the network?', td)
			}

			return __('Are you sure you want to activate these addons across the network?', td)
		}
	},
	methods : {
		getAssetUrl,
		closeNetworkModal (changeStatus = false) {
			this.showNetworkModal = false

			if (changeStatus) {
				const action         = this.maybeActivate ? 'actuallyActivateAllFeatures' : 'actuallyDeactivateAllFeatures'
				this.maybeActivate   = false
				this.maybeDeactivate = false
				this[action]()
			}
		},
		getIconComponent (icon) {
			if (!icon) {
				return 'img'
			}
			return icon.startsWith('svg-') ? icon : 'img'
		},
		getIconSrc (icon, image) {
			return 'string' === typeof icon && icon.startsWith('svg-')
				? null
				: (
					'string' === typeof icon
						? `data:image/svg+xml;base64,${icon}`
						: image
				)
		},
		getAddonDescription (addon) {
			const camelizedName = addon.sku.replace(/-./g, x => x.toUpperCase()[1])
			if (this.descriptions[camelizedName]?.description && addon.descriptionVersion <= this.descriptions[camelizedName]?.version) {
				return this.descriptions[camelizedName].description
			}
			return addon.description
		},
		activateAllFeatures () {
			// First, check to see if this user is licensed and has an active license.
			// If not, we want to redirect the user to a new page with an upsell.
			if (!this.rootStore.isPro || !this.licenseStore.license.isActive) {
				return window.open(links.getUpsellUrl(this.rootStore.aioseo.data.isNetworkAdmin ? 'network-activate-all-features' : 'activate-all-features', 'liteUpgrade'))
			}

			if (this.rootStore.aioseo.data.isNetworkAdmin) {
				this.showNetworkModal = true
				this.maybeActivate    = true
				return
			}

			this.actuallyActivateAllFeatures()
		},
		actuallyActivateAllFeatures () {
			this.loading.activateAll = true
			const addons = this.addonsStore.addons
				.filter(addon => !addon.requiresUpgrade)
				.map(addon => ({
					plugin : addon.basename
				}))
			this.pluginsStore.installPlugins(addons)
				.then(response => {
					const completed = Object.keys(response.body.completed).map(k => response.body.completed[k])
					this.addonsStore.addons.map(addon => {
						if (completed.includes(addon.basename)) {
							addon.isActive = true
						}

						return addon
					})
					this.loading.activateAll = false
				})
		},
		deactivateAllFeatures () {
			if (this.rootStore.aioseo.data.isNetworkAdmin) {
				this.showNetworkModal = true
				this.maybeDeactivate  = true
				return
			}

			this.actuallyDeactivateAllFeatures()
		},
		actuallyDeactivateAllFeatures () {
			this.loading.deactivateAll = true
			const addons = this.addonsStore.addons
				.filter(addon => !addon.requiresUpgrade)
				.filter(addon => addon.installed)
				.map(addon => ({
					plugin : addon.basename
				}))
			this.pluginsStore.deactivatePlugins(addons)
				.then(response => {
					const completed = Object.keys(response.body.completed).map(k => response.body.completed[k])
					this.addonsStore.addons.map(addon => {
						if (completed.includes(addon.basename)) {
							addon.isActive = false
						}

						return addon
					})
					this.loading.deactivateAll = false
				})
		}
	}
}
</script>

<style lang="scss">
.aioseo-feature-manager {
	.aioseo-alert {
		margin-bottom: var(--aioseo-gutter);

		&.install-failed {
			margin-top: var(--aioseo-gutter);
			margin-bottom: 0;
		}

		.buttons {
			margin-top: 8px;

			.aioseo-button + .aioseo-button {
				margin-left: 12px;
			}
		}
	}

	.aioseo-feature-manager-header {
		border-bottom: 2px solid $border;
		margin: 0 0 var(--aioseo-gutter);
		display: flex;
		align-items: center;

		.buttons {
			flex: 1 0;

			.aioseo-button {
				margin-right: 16px;
				margin-bottom: 10px;
			}
		}

		.aioseo-input {
			max-width: 325px;
			margin-bottom: 10px;
		}
	}

	.aioseo-feature-manager-addons > .aioseo-row {
			@include aioseoGrid(3, 290px);

			.aioseo-col {
				max-width: none;
			}
	}

	.feature-manager-upsell {
		margin-top: var(--aioseo-gutter);
	}
}

.aioseo-feature-manager-modal {
	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;

		h3 {
			font-size: 18px;
			margin-bottom: 16px;
		}

		.reset-description {
			font-size: 14px;
			color: $black;
			margin-bottom: 16px;
			text-align: center;
			max-width: 515px;
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

		.aioseo-button:not(.close) {
			margin-top: 16px;
		}
	}
}
</style>