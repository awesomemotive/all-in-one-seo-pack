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
				v-if="$isPro && licenseStore.isUnlicensed"
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
						:href="$links.getUpsellUrl('feature-manager-upgrade', 'no-license-key', 'pricing')"
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
			:cta-link="$links.utmUrl('feature-manager', 'main-cta')"
			:learn-more-link="$links.getUpsellUrl('feature-manager', 'main-cta', $isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="$constants.UPSELL_FEATURE_LIST"
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
			v-if="showNetworkModal"
			no-header
			@close="closeNetworkModal(false)"
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
import {
	useAddonsStore,
	useLicenseStore,
	usePluginsStore,
	useRootStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { getAssetUrl } from '@/vue/utils/helpers'
import { License } from '@/vue/mixins/License'

import ctaImg from '@/vue/assets/images/upsells/news-sitemap.png'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreFeatureCard from '@/vue/components/common/core/FeatureCard'
import CoreModal from '@/vue/components/common/core/modal/Index'
import Cta from '@/vue/components/common/cta/Index'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgCode from '@/vue/components/common/svg/Code'
import SvgImageSeo from '@/vue/components/common/svg/ImageSeo'
import SvgLinkAssistant from '@/vue/components/common/svg/link/Assistant'
import SvgLocalBusiness from '@/vue/components/common/svg/local/Business'
import SvgRedirect from '@/vue/components/common/svg/Redirect'
import SvgSitemapsPro from '@/vue/components/common/svg/SitemapsPro'

export default {
	setup () {
		return {
			addonsStore  : useAddonsStore(),
			licenseStore : useLicenseStore(),
			pluginsStore : usePluginsStore(),
			rootStore    : useRootStore()
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
		SvgImageSeo,
		SvgLinkAssistant,
		SvgLocalBusiness,
		SvgRedirect,
		SvgSitemapsPro
	},
	mixins : [ License ],
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
				videoNewsSitemaps     : this.$t.__('Video and News Sitemaps', this.$td),
				imageSeoOptimization  : this.$t.__('Image SEO Optimization', this.$td),
				localBusinessSeo      : this.$t.__('Local Business SEO', this.$td),
				advancedWooCommerce   : this.$t.__('Advanced WooCommerce', this.$td),
				customTaxonomies      : this.$t.__('SEO for Categories, Tags and Custom Taxonomies', this.$td),
				andMore               : this.$t.__('And many more...', this.$td),
				activateAllFeatures   : this.$t.__('Activate All Features', this.$td),
				deactivateAllFeatures : this.$t.__('Deactivate All Features', this.$td),
				searchForFeatures     : this.$t.__('Search for Features...', this.$td),
				ctaHeaderText         : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					this.$t.__('Upgrade %1$s to Pro and Unlock all Features!', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				ctaButtonText           : this.$t.__('Unlock All Features', this.$td),
				aValidLicenseIsRequired : this.$t.__('A valid license key is required in order to use our addons.', this.$td),
				enterLicenseKey         : this.$t.__('Enter License Key', this.$td),
				purchaseLicense         : this.$t.__('Purchase License', this.$td),
				areYouSureNetworkChange : this.$t.__('This is a network-wide change.', this.$td),
				yesProcessNetworkChange : this.$t.__('Yes, process this network change', this.$td),
				noChangedMind           : this.$t.__('No, I changed my mind', this.$td)
			},
			descriptions : {
				aioseoImageSeo : {
					description : '<p>' + this.$t.__('Globally control the Title attribute and Alt text for images in your content. These attributes are essential for both accessibility and SEO.', this.$td) + '</p>',
					version     : 0
				},
				aioseoVideoSitemap : {
					description : '<p>' + this.$t.__('The Video Sitemap works in much the same way as the XML Sitemap module, it generates an XML Sitemap specifically for video content on your site. Search engines use this information to display rich snippet information in search results.', this.$td) + '</p>',
					version     : 0
				},
				aioseoNewsSitemap : {
					description : '<p>' + this.$t.__('Our Google News Sitemap lets you control which content you submit to Google News and only contains articles that were published in the last 48 hours. In order to submit a News Sitemap to Google, you must have added your site to Google’s Publisher Center and had it approved.', this.$td) + '</p>',
					version     : 0
				},
				aioseoLocalBusiness : {
					description : '<p>' + this.$t.__('Local Business schema markup enables you to tell Google about your business, including your business name, address and phone number, opening hours and price range. This information may be displayed as a Knowledge Graph card or business carousel.', this.$td) + '</p>',
					version     : 0
				}
			}
		}
	},
	computed : {
		upgradeToday () {
			return this.$t.sprintf(
				// Translators: 1 - Plugin short name ("AIOSEO"), 2 - "Pro".
				this.$t.__('%1$s %2$s comes with many additional features to help take your site\'s SEO to the next level!', this.$td),
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
				return this.$t.__('Are you sure you want to deactivate these addons across the network?', this.$td)
			}

			return this.$t.__('Are you sure you want to activate these addons across the network?', this.$td)
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
			if (this.descriptions[camelizedName] && this.descriptions[camelizedName].description && addon.descriptionVersion <= this.descriptions[camelizedName].version) {
				return this.descriptions[camelizedName].description
			}
			return addon.description
		},
		activateAllFeatures () {
			// First, check to see if this user is licensed and has an active license.
			// If not, we want to redirect the user to a new page with an upsell.
			if (!this.$isPro || !this.licenseStore.license.isActive) {
				return window.open(this.$links.utmUrl(this.rootStore.aioseo.data.isNetworkAdmin ? 'network-activate-all-features' : 'activate-all-features'))
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

	.aioseo-modal-body {
		padding: 20px 50px 50px;
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