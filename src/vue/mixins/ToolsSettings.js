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

			if (this.$isPro) {
				settings.push({
					value  : 'accessControl',
					label  : this.$t.__('Access Control', this.$td),
					access : 'aioseo_admin'
				})
			}

			if (!licenseStore.isUnlicensed && this.showAddonReset('aioseo-image-seo')) {
				settings.push({
					value  : 'image',
					label  : this.$t.__('Image SEO', this.$td),
					access : 'aioseo_search_appearance_settings'
				})
			}

			if (!licenseStore.isUnlicensed && this.showAddonReset('aioseo-local-business')) {
				settings.push({
					value  : 'localBusiness',
					label  : this.$t.__('Local Business SEO', this.$td),
					access : 'aioseo_local_seo_settings'
				})
			}

			if (!licenseStore.isUnlicensed && this.showAddonReset('aioseo-redirects')) {
				settings.push({
					value  : 'redirects',
					label  : this.$t.__('Redirects', this.$td),
					access : 'aioseo_redirects_settings'
				})
			}

			if (!licenseStore.isUnlicensed && this.showAddonReset('aioseo-link-assistant')) {
				settings.push({
					value  : 'linkAssistant',
					label  : this.$t.__('Link Assistant', this.$td),
					access : 'aioseo_link_assistant_settings'
				})
			}

			if (!licenseStore.isUnlicensed && this.showAddonReset('aioseo-eeat')) {
				settings.push({
					value  : 'eeat',
					label  : this.$t.__('Author SEO (E-E-A-T)', this.$td),
					access : 'aioseo_search_appearance_settings'
				})
			}

			return settings.filter(setting => allowed(setting.access))
		}
	},
	methods : {
		showAddonReset (slug) {
			const addon = addons.getAddon(slug)
			return addon && addon.isActive && !addon.requiresUpgrade
		}
	}
}