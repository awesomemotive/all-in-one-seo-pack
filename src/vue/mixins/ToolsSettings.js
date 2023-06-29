import {
	useLicenseStore,
	useOptionsStore
	// useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'

export const ToolsSettings = {
	computed : {
		toolsSettings () {
			const licenseStore = useLicenseStore()
			const settings = [
				{
					value  : 'webmasterTools',
					label  : this.$t.__('Webmaster Tools', this.$td),
					access : 'aioseo_general_settings'
				},
				{
					value  : 'rssContent',
					label  : this.$t.__('RSS Content', this.$td),
					access : 'aioseo_general_settings'
				},
				{
					value  : 'advanced',
					label  : this.$t.__('Advanced', this.$td),
					access : 'aioseo_general_settings'
				},
				{
					value  : 'searchAppearance',
					label  : this.$t.__('Search Appearance', this.$td),
					access : 'aioseo_search_appearance_settings'
				},
				{
					value  : 'social',
					label  : this.$t.__('Social Networks', this.$td),
					access : 'aioseo_social_networks_settings'
				},
				{
					value  : 'sitemap',
					label  : this.$t.__('Sitemaps', this.$td),
					access : 'aioseo_sitemap_settings'
				},
				{
					value  : 'robots',
					label  : this.$t.__('Robots.txt', this.$td),
					access : 'aioseo_tools_settings'
				},
				{
					value  : 'breadcrumbs',
					label  : this.$t.__('Breadcrumbs', this.$td),
					access : 'aioseo_general_settings'
				}
			]

			const optionsStore = useOptionsStore()
			if (optionsStore.internalOptions.internal.deprecatedOptions.includes('badBotBlocker')) {
				settings.push({
					value  : 'blocker',
					label  : this.$t.__('Bad Bot Blocker', this.$td),
					access : 'aioseo_tools_settings'
				})
			}

			// const rootStore = useRootStore()
			// if (rootStore.aioseo.data.server.apache) {
			//  settings.push({ value: 'htaccess', label: this.$t.__('.htaccess', this.$td) })
			// }

			if (this.$isPro) {
				settings.push({
					value  : 'accessControl',
					label  : this.$t.__('Access Control', this.$td),
					access : 'aioseo_admin'
				})
			}

			if (!licenseStore.isUnlicensed && this.showImageSeoReset) {
				settings.push({
					value  : 'image',
					label  : this.$t.__('Image SEO', this.$td),
					access : 'aioseo_search_appearance_settings'
				})
			}

			if (!licenseStore.isUnlicensed && this.showLocalBusinessReset) {
				settings.push({
					value  : 'localBusiness',
					label  : this.$t.__('Local Business SEO', this.$td),
					access : 'aioseo_local_seo_settings'
				})
			}

			if (!licenseStore.isUnlicensed && this.showRedirectsReset) {
				settings.push({
					value  : 'redirects',
					label  : this.$t.__('Redirects', this.$td),
					access : 'aioseo_redirects_settings'
				})
			}

			if (!licenseStore.isUnlicensed && this.showLinkAssistantReset) {
				settings.push({
					value  : 'linkAssistant',
					label  : this.$t.__('Link Assistant', this.$td),
					access : 'aioseo_link_assistant_settings'
				})
			}

			return settings.filter(setting => allowed(setting.access))
		},
		showImageSeoReset () {
			const addon = addons.getAddon('aioseo-image-seo')
			return addon && addon.isActive && !addon.requiresUpgrade
		},
		showLocalBusinessReset () {
			const addon = addons.getAddon('aioseo-local-business')
			return addon && addon.isActive && !addon.requiresUpgrade
		},
		showRedirectsReset () {
			const addon = addons.getAddon('aioseo-redirects')
			return addon && addon.isActive && !addon.requiresUpgrade
		},
		showLinkAssistantReset () {
			const addon = addons.getAddon('aioseo-link-assistant')
			return addon && addon.isActive && !addon.requiresUpgrade
		}
	}
}