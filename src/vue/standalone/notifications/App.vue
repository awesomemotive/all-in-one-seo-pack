<template>
	<div
		v-if="display"
		@click.stop="hideNotificationsPopup"
		@mouseover="hideNotificationsPopup"
		class="aioseo-menu-new-notifications"
	>{{ strings.newNotifications }}</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	data () {
		return {
			interval : null,
			display  : false,
			strings  : {
				newNotifications : __('You have new notifications!', td)
			}
		}
	},
	methods : {
		showNotificationsPopup () {
			if (!this.interval) {
				return
			}

			if (window.aioseoNotifications && parseInt(window.aioseoNotifications.newNotifications)) {
				this.display = true
				const menuItem = document.querySelector('#wp-admin-bar-aioseo-main')
				if (menuItem) {
					menuItem.classList.add('new-notifications')
				}
			}
		},
		hideNotificationsPopup () {
			this.interval = null
			setTimeout(() => {
				this.display = false
				const menuItem = document.querySelector('#wp-admin-bar-aioseo-main')
				if (menuItem) {
					menuItem.classList.remove('new-notifications')
				}
			}, 500)
		}
	},
	created () {
		this.interval = setInterval(this.showNotificationsPopup, 500)
		this.showNotificationsPopup()

		setTimeout(() => {
			this.interval = null
			this.display = false
		}, 5000)
	}
}
</script>

<style lang="scss">
#wp-admin-bar-aioseo-main {
	.aioseo-menu-new-notifications {
		position: absolute;
		top: 42px;
		transform: translateX(-30px);
		white-space: nowrap;
		padding: 2px 12px;
		border-radius: 2px;
		color: #fff;
		background-color: $orange3;

		&:before{
			content: '';
			display: block;
			height: 15px;
			width: 15px;
			background-color: inherit;
			border: inherit;
			position: absolute;
			top: -7px;
			left: calc(50% - 7px);
			clip-path: polygon(0% 0%, 100% 100%, 0% 100%);
			transform: rotate(135deg);
			border-radius: 0 0 0 2px;
		}
	}
}
</style>