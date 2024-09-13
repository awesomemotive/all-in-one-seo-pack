import { ref, computed } from 'vue'

import {
	usePluginsStore
} from '@/vue/stores'

export const useMiOrEm = () => {
	const installingPlugin  = ref(false)
	const miInstalled       = ref(false)
	const miInstalledFailed = ref(false)
	const showMiPromo       = ref(true)

	const pluginsStore = usePluginsStore()
	const gaActivated = computed(() => {
		return pluginsStore.plugins.miLite.activated ||
			pluginsStore.plugins.emLite.activated ||
			pluginsStore.plugins.miPro.activated ||
			pluginsStore.plugins.emPro.activated
	})

	const prefersEm = computed(() => {
		return (pluginsStore.plugins.emLite.installed ||
			pluginsStore.plugins.emPro.installed) &&
			(!pluginsStore.plugins.miLite.installed &&
			!pluginsStore.plugins.miPro.installed)
	})

	const installMi = () => {
		installingPlugin.value = true

		pluginsStore.installPlugins([
			{
				plugin : 'miLite',
				type   : 'plugin'
			}
		])
			.then(response => {
				installingPlugin.value = false
				if (response.body.failed.length) {
					miInstalledFailed.value = true
					return
				}

				miInstalled.value = true
				setTimeout(() => {
					showMiPromo.value = false

					// Update the active status globally.
					pluginsStore.plugins.miLite.activated = true
				}, 3000)
			})
			.catch(error => {
				console.error(error)
			})
	}

	return {
		gaActivated,
		installMi,
		installingPlugin,
		miInstalled,
		prefersEm,
		showMiPromo
	}
}