import {
	useRootStore
} from '@/vue/stores'

export const AccessControl = {
	computed : {
		getRoles () {
			const rootStore = useRootStore()
			return this.roles.concat(Object.keys(rootStore.aioseo.user.customRoles).map(role => ({
				label       : rootStore.aioseo.user.roles[role],
				name        : role,
				description : this.$t.sprintf(
					// Translators: 1 - The name of the WP role, 2 - Opening bold tag, 3 - Closing bold tag, 4 - Plugin Name ("All in One SEO").
					this.$t.__('By default the %1$s role %2$shas no access%3$s to %4$s settings.', this.$td),
					rootStore.aioseo.user.roles[role],
					'<strong>',
					'</strong>',
					import.meta.env.VITE_NAME
				),
				dynamic : true
			})))
		}
	}
}