import { computed } from 'vue'

import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useToolsSettings = () => {
	const toolsSettings = computed(() => {
		const settings = [
			{
				value  : 'webmasterTools',
				label  : __('Webmaster Tools', td),
				access : 'aioseo_general_settings'
			},
			{
				value  : 'rssContent',
				label  : __('RSS Content', td),
				access : 'aioseo_general_settings'
			},
			{
				value  : 'advanced',
				label  : __('Advanced', td),
				access : 'aioseo_general_settings'
			},
			{
				value  : 'searchAppearance',
				label  : __('Search Appearance', td),
				access : 'aioseo_search_appearance_settings'
			},
			{
				value  : 'social',
				label  : __('Social Networks', td),
				access : 'aioseo_social_networks_settings'
			},
			{
				value  : 'sitemap',
				label  : __('Sitemaps', td),
				access : 'aioseo_sitemap_settings'
			},
			{
				value  : 'robots',
				label  : __('Robots.txt', td),
				access : 'aioseo_tools_settings'
			},
			{
				value  : 'breadcrumbs',
				label  : __('Breadcrumbs', td),
				access : 'aioseo_general_settings'
			}
		]

		const rootStore = useRootStore()
		if (rootStore.isPro) {
			settings.push({
				value  : 'accessControl',
				label  : __('Access Control', td),
				access : 'aioseo_admin'
			})
		}

		const licenseStore = useLicenseStore()
		if (!licenseStore.isUnlicensed && showAddonReset('aioseo-image-seo')) {
			settings.push({
				value  : 'image',
				label  : __('Image SEO', td),
				access : 'aioseo_search_appearance_settings'
			})
		}

		if (!licenseStore.isUnlicensed && showAddonReset('aioseo-local-business')) {
			settings.push({
				value  : 'localBusiness',
				label  : __('Local Business SEO', td),
				access : 'aioseo_local_seo_settings'
			})
		}

		if (!licenseStore.isUnlicensed && showAddonReset('aioseo-redirects')) {
			settings.push({
				value  : 'redirects',
				label  : __('Redirects', td),
				access : 'aioseo_redirects_settings'
			})
		}

		if (!licenseStore.isUnlicensed && showAddonReset('aioseo-link-assistant')) {
			settings.push({
				value  : 'linkAssistant',
				label  : __('Link Assistant', td),
				access : 'aioseo_link_assistant_settings'
			})
		}

		if (!licenseStore.isUnlicensed && showAddonReset('aioseo-eeat')) {
			settings.push({
				value  : 'eeat',
				label  : __('Author SEO (E-E-A-T)', td),
				access : 'aioseo_search_appearance_settings'
			})
		}

		return settings.filter(setting => allowed(setting.access))
	})

	const showAddonReset = (slug) => {
		const addon = addons.getAddon(slug)
		return addon && addon.isActive && !addon.requiresUpgrade
	}

	return {
		toolsSettings
	}
}