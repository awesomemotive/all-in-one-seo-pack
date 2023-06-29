import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useRootStore
} from '@/vue/stores'

const clearNotificationNotices = notifications => {
	const notificationCount = document.querySelector('.aioseo-menu-notification-counter')
	if (notificationCount) {
		if (notifications.active.length) {
			notificationCount.innerText = notifications.active.length
		} else {
			notificationCount.remove()
			const notificationIndicator = document.querySelector('#wp-admin-bar-aioseo-notifications')
			if (notificationIndicator) {
				notificationIndicator.remove()
			}

			// Remove from the sidebar too
			const sidebarNotificationsLink      = document.querySelector('#toplevel_page_aioseo .wp-first-item')
			const sidebarNotificationsLinkPulse = document.querySelector('#toplevel_page_aioseo .wp-first-item .aioseo-menu-notification-indicator')
			if (sidebarNotificationsLink?.contains(sidebarNotificationsLinkPulse)) {
				sidebarNotificationsLink.remove()
			}
		}
	}
}

export const useNotificationsStore = defineStore('NotificationsStore', {
	state : () => ({
		active            : [],
		new               : [],
		dismissed         : [],
		force             : false,
		showNotifications : false
	}),
	getters : {
		activeNotifications         : state => state.active,
		activeNotificationsCount    : state => state.active.length,
		dismissedNotifications      : state => state.dismissed,
		dismissedNotificationsCount : state => state.dismissed.length
	},
	actions : {
		toggleNotifications () {
			this.showNotifications = !this.showNotifications
		},
		dismissNotifications (payload) {
			const reversed            = payload.reverse()
			reversed.forEach(slug => {
				const notificationIndex = this.active.findIndex(n => n.slug === slug)
				if (-1 !== notificationIndex) {
					this.active.splice(notificationIndex, 1)
				}
			})

			return http.post(links.restUrl('notifications/dismiss'))
				.send(payload)
				.then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}

					this.updateNotifications(response.body.notifications)
				})
		},
		updateNotifications (notifications) {
			if (notifications.new.length && window.aioseoNotifications) {
				window.aioseoNotifications.newNotifications = notifications.new.length
			}

			this.active    = notifications.active
			this.new       = notifications.new
			this.dismissed = notifications.dismissed

			clearNotificationNotices(notifications)
		},
		processButtonAction (action) {
			const rootStore = useRootStore()
			return http.post(links.restUrl(`${action}`))
				.send({
					network : rootStore.aioseo.data.isNetworkAdmin
				})
				.then(response => {
					this.updateNotifications(response.body.notifications)

					if (!response.body.success) {
						throw new Error(response.body.message)
					}
				})
		}
	}
})