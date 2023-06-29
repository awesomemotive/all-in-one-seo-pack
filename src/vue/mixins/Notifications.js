import {
	useNotificationsStore
} from '@/vue/stores'

export const Notifications = {
	computed : {
		notificationsCount () {
			const notificationsStore = useNotificationsStore()
			return this.dismissed ? notificationsStore.dismissedNotificationsCount : notificationsStore.activeNotificationsCount
		},
		notifications () {
			const notificationsStore = useNotificationsStore()
			return this.dismissed ? notificationsStore.dismissedNotifications : notificationsStore.activeNotifications
		},
		notificationTitle () {
			return this.dismissed ? this.strings.notifications : this.strings.newNotifications
		}
	}
}