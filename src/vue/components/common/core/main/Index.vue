<template>
	<div>
		<core-notifications />

		<div class="aioseo-main">
			<core-header
				:page-name="pageName"
			/>
			<grid-container :class="containerClasses">
				<core-main-tabs
					v-if="showTabs"
					:key="tabsKey"
					:tabs="tabs"
					:showSaveButton="shouldShowSaveButton"
				>
					<template #extra>
						<slot name="extra" />
					</template>
				</core-main-tabs>

				<transition name="route-fade" mode="out-in">
					<slot />
				</transition>

				<div
					v-if="shouldShowSaveButton"
					class="save-changes"
				>
					<base-button
						type="blue"
						size="medium"
						:loading="rootStore.loading"
						@click="processSaveChanges"
					>
						{{ strings.saveChanges }}
					</base-button>
				</div>
			</grid-container>
		</div>

		<core-help
			v-if="helpPanelStore.docs && Object.keys(helpPanelStore.docs).length"
		/>
	</div>
</template>

<script>
import {
	useHelpPanelStore,
	useNotificationsStore,
	useRootStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import '@/vue/assets/scss/main.scss'

import { getParams, removeParam } from '@/vue/utils/params'
import { SaveChanges } from '@/vue/mixins'
import CoreHeader from '@/vue/components/common/core/Header'
import CoreHelp from '@/vue/components/common/core/Help'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreNotifications from '@/vue/components/common/core/Notifications'
import GridContainer from '@/vue/components/common/grid/Container'
export default {
	setup () {
		return {
			helpPanelStore     : useHelpPanelStore(),
			notificationsStore : useNotificationsStore(),
			rootStore          : useRootStore()
		}
	},
	components : {
		CoreHeader,
		CoreHelp,
		CoreMainTabs,
		CoreNotifications,
		GridContainer
	},
	mixins : [ SaveChanges ],
	props  : {
		pageName : {
			type     : String,
			required : true
		},
		showTabs : {
			type : Boolean,
			default () {
				return true
			}
		},
		showSaveButton : {
			type : Boolean,
			default () {
				return true
			}
		},
		excludeTabs : {
			type : Array,
			default () {
				return []
			}
		},
		containerClasses : {
			type : Array,
			default () {
				return []
			}
		}
	},
	data () {
		return {
			tabsKey : 0,
			strings : {
				saveChanges : this.$t.__('Save Changes', this.$td)
			}
		}
	},
	watch : {
		excludeTabs () {
			this.tabsKey += 1
		}
	},
	computed : {
		tabs () {
			return this.$router.options.routes
				.filter(route => route.name && route.meta && route.meta.name)
				.filter(route => allowed(route.meta.access))
				.filter(route => !route.meta.license || license.hasMinimumLevel(route.meta.license))
				.filter(route => {
					if ('lite' === route.meta.display && this.$isPro) {
						return false
					}
					if ('pro' === route.meta.display && !this.$isPro) {
						return false
					}
					return true
				})
				.filter(route => !this.excludeTabs.includes(route.name))
				.map(route => {
					return {
						slug   : route.name,
						name   : route.meta.name,
						url    : { name: route.name },
						access : route.meta.access,
						pro    : !!route.meta.pro
					}
				})
		},
		shouldShowSaveButton () {
			if (this.$route && this.$route.name) {
				const route = this.$router.options.routes.find(route => route.name === this.$route.name)
				if (route && route.meta && route.meta.hideSaveButton) {
					return false
				}
			}
			return this.showSaveButton
		}
	},
	mounted () {
		if (getParams().notifications) {
			if (!this.notificationsStore.showNotifications) {
				this.notificationsStore.toggleNotifications()
			}

			setTimeout(() => {
				removeParam('notifications')
			}, 500)
		}

		if (this.notificationsStore.force && this.notificationsStore.active.length) {
			// First, disable the force show.
			this.notificationsStore.force = false

			// Then show the notifications.
			this.notificationsStore.toggleNotifications()
		}
	}
}
</script>