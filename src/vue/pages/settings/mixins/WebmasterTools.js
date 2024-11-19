import {
	usePluginsStore
} from '@/vue/stores'

import { MetaTag } from '@/vue/mixins/MetaTag'
export const WebmasterTools = {
	mixins : [ MetaTag ],
	props  : {
		tool : {
			type     : Object,
			required : true
		},
		isConnected : {
			type : Boolean,
			default () {
				return false
			}
		}
	}
}

export const MiOrEm = {
	data () {
		return {
			installingPlugin  : false,
			miInstalled       : false,
			miInstalledFailed : false,
			showMiPromo       : true
		}
	},
	computed : {
		gaActivated () {
			return this.pluginsStore.plugins.miLite.activated ||
				this.pluginsStore.plugins.emLite.activated ||
				this.pluginsStore.plugins.miPro.activated ||
				this.pluginsStore.plugins.emPro.activated
		},
		prefersEm () {
			return (this.pluginsStore.plugins.emLite.installed ||
				this.pluginsStore.plugins.emPro.installed) &&
				(!this.pluginsStore.plugins.miLite.installed &&
				!this.pluginsStore.plugins.miPro.installed)
		}
	},
	methods : {
		installMi () {
			this.installingPlugin = true

			const pluginsStore = usePluginsStore()
			pluginsStore.installPlugins([
				{
					plugin : 'miLite',
					type   : 'plugin'
				}
			])
				.then(response => {
					this.installingPlugin = false
					if (response.body.failed.length) {
						this.miInstalledFailed = true
						return
					}

					this.miInstalled = true
					setTimeout(() => {
						this.showMiPromo = false

						// Update the active status globally.
						this.pluginsStore.plugins.miLite.activated = true
					}, 3000)
				})
				.catch(error => {
					console.error(error)
				})
		}
	}
}