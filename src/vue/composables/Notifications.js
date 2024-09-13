import { ref, computed } from 'vue'

import {
	useNotificationsStore
} from '$/vue/stores'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useNotifications = () => {
	const strings = {
		notifications       : __('Notifications', td),
		newNotifications    : __('New Notifications', td),
		activeNotifications : __('Active Notifications', td)
	}

	const dismissed          = ref(false)
	const notificationsStore = useNotificationsStore()

	const notificationsCount = computed(() => {
		return dismissed.value ? notificationsStore.dismissedNotificationsCount : notificationsStore.activeNotificationsCount
	})

	const notifications = computed(() => {
		return dismissed.value ? notificationsStore.dismissedNotifications : notificationsStore.activeNotifications
	})

	const notificationTitle = computed(() => {
		return dismissed.value ? strings.notifications : strings.newNotifications
	})

	return {
		dismissed,
		notificationTitle,
		notifications,
		notificationsCount,
		strings
	}
}