import { computed } from 'vue'

import {
	useRootStore
} from '@/vue/stores'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useAccessControl = () => {
	const strings = {
		tooltip : sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			__('By default, only Administrators have permission to manage %1$s within WordPress. With Access Controls, you can easily extend access permissions to other user roles.', td),
			import.meta.env.VITE_SHORT_NAME
		),
		accessControl      : __('Access Control Settings', td),
		useDefaultSettings : __('Use Default Settings', td)
	}

	const roles = [
		{
			label       : __('Administrator', td),
			name        : 'administrator',
			description : sprintf(
				// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
				__('By default Admins have access to %1$sall SEO site settings%2$s', td),
				'<strong>',
				'</strong>'
			)
		},
		{
			label       : __('Editor', td),
			name        : 'editor',
			description : sprintf(
				// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
				__('By default Editors have access to %1$sSEO settings for General Settings, Search Appearance, Social Networks, and Redirects as well as all settings for individual pages and posts.%2$s', td),
				'<strong>',
				'</strong>'
			)
		},
		{
			label       : __('Author', td),
			name        : 'author',
			description : sprintf(
				// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
				__('By default Authors have access to %1$sSEO settings for individual pages and posts that they already have permission to edit.%2$s', td),
				'<strong>',
				'</strong>'
			)
		},
		{
			label       : __('Contributor', td),
			name        : 'contributor',
			description : sprintf(
				// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
				__('By default Contributors have access to %1$sSEO settings for individual pages and posts that they already have permission to edit.%2$s', td),
				'<strong>',
				'</strong>'
			)
		},
		{
			label       : __('SEO Manager', td),
			name        : 'seoManager',
			description : sprintf(
				// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
				__('By default SEO Managers have access to %1$sSEO settings for General Settings, Sitemaps, Link Assistant, Redirects, Local SEO, and individual pages and posts.%2$s', td),
				'<strong>',
				'</strong>'
			)
		},
		{
			label       : __('SEO Editor', td),
			name        : 'seoEditor',
			description : sprintf(
				// Translators: 1 - Opening HTML bold tag, 2 - Closing HTML bold tag.
				__('By default SEO Editors have access to %1$sSEO settings for individual pages and posts.%2$s', td),
				'<strong>',
				'</strong>'
			)
		}
	]

	const getRoles = computed(() => {
		const rootStore = useRootStore()
		return roles.concat(Object.keys(rootStore.aioseo.user.customRoles).map(role => ({
			label       : rootStore.aioseo.user.roles[role],
			name        : role,
			description : sprintf(
				// Translators: 1 - The name of the WP role, 2 - Opening bold tag, 3 - Closing bold tag, 4 - Plugin Name ("All in One SEO").
				__('By default the %1$s role %2$shas no access%3$s to %4$s settings.', td),
				rootStore.aioseo.user.roles[role],
				'<strong>',
				'</strong>',
				import.meta.env.VITE_NAME
			),
			dynamic : true
		})))
	})

	return {
		getRoles,
		strings
	}
}