<template>
	<div
		class="aioseo-notifications"
		ref="aioseo-notifications"
	>
		<transition name="notifications-slide">
			<div
				v-if="notificationsStore.showNotifications"
				class="notification-menu"
			>
				<div class="notification-header">
					<span class="new-notifications">({{ notificationsCount }}) {{ notificationTitle }}</span>
					<div
						class="dismissed-notifications"
					>
						<a
							href="#"
							@click.stop.prevent="dismissed = true"
							v-if="!dismissed && notificationsStore.dismissedNotificationsCount"
						>{{ strings.dismissedNotifications }}</a>

						<a
							href="#"
							@click.stop.prevent="dismissed = false"
							v-if="dismissed && notificationsStore.dismissedNotificationsCount"
						>{{ strings.activeNotifications }}</a>
					</div>
					<div
						@click.stop="notificationsStore.toggleNotifications"
					>
						<svg-close />
					</div>
				</div>

				<core-notification-cards
					class="notification-cards"
					:notifications="filteredNotifications"
					:dismissedCount="notificationsStore.dismissedNotificationsCount"
					@toggle-dismissed="dismissed = !dismissed"
				/>

				<div class="notification-footer">
					<div
						class="pagination"
					>
						<template
							v-if="totalPages > 1"
						>
							<div
								class="page-number"
								v-for="(page, index) in pages"
								:class="{ active: page.number === 1 + currentPage }"
								:key="index"
								@click.stop="currentPage = page.number - 1"
							>
								{{ page.number }}
							</div>
						</template>
					</div>

					<div
						v-if="!dismissed"
						class="dismiss-all"
					>
						<a
							v-if="notifications.length"
							href="#"
							class="dismiss"
							@click.stop.prevent="processDismissAllNotifications"
						>{{ strings.dismissAll }}</a>
					</div>
				</div>
			</div>
		</transition>

		<transition name="notifications-fade">
			<div
				@click="notificationsStore.toggleNotifications"
				v-if="notificationsStore.showNotifications"
				class="overlay"
			/>
		</transition>
	</div>
</template>

<script>
import {
	useNotificationsStore
} from '@/vue/stores'

import { useNotifications } from '@/vue/composables'
import { merge } from 'lodash-es'
import { Notifications } from '@/vue/mixins'
import CoreNotificationCards from '@/vue/components/common/core/NotificationCards'
import SvgClose from '@/vue/components/common/svg/Close'
export default {
	setup () {
		const { strings } = useNotifications()

		return {
			notificationsStore : useNotificationsStore(),
			composableStrings  : strings
		}
	},
	components : {
		CoreNotificationCards,
		SvgClose
	},
	mixins : [ Notifications ],
	data () {
		return {
			dismissed        : false,
			maxNotifications : Number.MAX_SAFE_INTEGER,
			currentPage      : 0,
			totalPages       : 1,
			strings          : merge(this.composableStrings, {
				dismissedNotifications : this.$t.__('Dismissed Notifications', this.$td),
				dismissAll             : this.$t.__('Dismiss All', this.$td)
			})
		}
	},
	watch : {
		'notificationsStore.showNotifications' (newValue) {
			if (newValue) {
				this.currentPage = 0
				this.setMaxNotifications()
				this.addBodyClass()
			} else {
				this.removeBodyClass()
			}
		},
		dismissed () {
			this.setMaxNotifications()
		},
		notifications () {
			this.setMaxNotifications()
		}
	},
	computed : {
		filteredNotifications () {
			const notifications = [ ...this.notifications ]
			return notifications.splice(0 === this.currentPage ? 0 : this.currentPage * this.maxNotifications, this.maxNotifications)
		},
		pages () {
			const pages = []
			for (let i = 0; i < this.totalPages; i++) {
				pages.push({
					number : i + 1
				})
			}

			return pages
		}
	},
	methods : {
		escapeListener (event) {
			if ('Escape' === event.key && this.notificationsStore.showNotifications) {
				this.notificationsStore.toggleNotifications()
			}
		},
		addBodyClass () {
			document.body.classList.add('aioseo-show-notifications')
		},
		removeBodyClass () {
			document.body.classList.remove('aioseo-show-notifications')
		},
		documentClick (event) {
			if (!this.notificationsStore.showNotifications) {
				return
			}

			const target = event && event.target ? event.target : null

			const notificationsLink = document.querySelector('#wp-admin-bar-aioseo-notifications')
			if (notificationsLink && (notificationsLink === target || notificationsLink.contains(target))) {
				return
			}

			const sidebarNotificationsLink      = document.querySelector('#toplevel_page_aioseo .wp-first-item')
			const sidebarNotificationsLinkPulse = document.querySelector('#toplevel_page_aioseo .wp-first-item .aioseo-menu-notification-indicator')
			if (
				sidebarNotificationsLink &&
				sidebarNotificationsLink.contains(sidebarNotificationsLinkPulse) &&
				(
					sidebarNotificationsLink === target ||
					sidebarNotificationsLink.contains(target)
				)
			) {
				return
			}

			const element = this.$refs['aioseo-notifications']
			if (element && (element === target || element.contains(target))) {
				return
			}

			this.notificationsStore.toggleNotifications()
		},
		notificationsLinkClick (event) {
			event.preventDefault()
			this.notificationsStore.toggleNotifications()
		},
		processDismissAllNotifications () {
			const slugs = []
			this.notifications.forEach(notification => {
				slugs.push(notification.slug)
			})
			this.notificationsStore.dismissNotifications(slugs)
				.then(() => {
					this.setMaxNotifications()
				})
		},
		setMaxNotifications () {
			const previousCurrentPage = this.currentPage
			this.currentPage          = 0
			this.totalPages           = 1
			this.maxNotifications     = Number.MAX_SAFE_INTEGER

			this.$nextTick(async () => {
				const totalHeight = []
				const notificationCards = document.querySelectorAll('.notification-menu .aioseo-notification')
				if (notificationCards) {
					notificationCards.forEach(card => {
						let height         = card.offsetHeight
						const style        = window.getComputedStyle ? getComputedStyle(card, null) : card.currentStyle
						const marginTop    = parseInt(style.marginTop) || 0
						const marginBottom = parseInt(style.marginBottom) || 0
						height            += marginTop + marginBottom
						totalHeight.push(height)
					})
				}

				const container = document.querySelector('.notification-menu .aioseo-notification-cards')
				if (container) {
					let cardsVisible = 0,
						cardsHeight  = 0
					for (let i = 0; i < totalHeight.length; i++) {
						cardsHeight += totalHeight[i]
						if (cardsHeight > container.offsetHeight) {
							break
						}
						cardsVisible++
					}

					this.maxNotifications = cardsVisible || 1

					// Set the total number of pages.
					this.totalPages = Math.ceil(totalHeight.length / cardsVisible)
				}

				this.currentPage = previousCurrentPage > (this.totalPages - 1)
					? this.totalPages - 1
					: previousCurrentPage
			})
		}
	},
	mounted () {
		document.addEventListener('keydown', this.escapeListener)
		document.addEventListener('click', this.documentClick)

		const notificationsLink = document.querySelector('#wp-admin-bar-aioseo-notifications .ab-item')
		if (notificationsLink) {
			notificationsLink.addEventListener('click', this.notificationsLinkClick)
		}

		const sidebarNotificationsLink      = document.querySelector('#toplevel_page_aioseo .wp-first-item')
		const sidebarNotificationsLinkPulse = document.querySelector('#toplevel_page_aioseo .wp-first-item .aioseo-menu-notification-indicator')
		if (sidebarNotificationsLink && sidebarNotificationsLinkPulse) {
			sidebarNotificationsLink.addEventListener('click', this.notificationsLinkClick)
		}
	}
}
</script>

<style lang="scss">
body.aioseo-show-notifications {
	.aioseo-main {
		pointer-events: none;
		user-select: none;
	}
}
.aioseo-notifications {

	a.dismiss {
		color: $placeholder-color;
		font-size: 14px;
	}

	.notification-menu {
		display: flex;
		flex-direction: column;
		height: calc(100% - var(--wp-admin--admin-bar--height, 32px));
		width: 100%; /* 0 width - change this with JavaScript */
		max-width: 570px;
		position: fixed; /* Stay in place */
		z-index: 1053; /* Stay on top */
		top: 0;
		right: 0;
		bottom: 0;
		background-color: #fff; /* Black*/
		overflow-x: hidden; /* Disable horizontal scroll */
		transition: 0.5s; /*0.5 second transition effect to slide in the sidenav*/

		.notification-header {
			height: 64px;
			display: flex;
			align-items: center;
			padding: 0 20px;
			color: #fff;
			background-color: $blue;

			.new-notifications {
				font-size: 18px;
				font-weight: 600;
			}

			.dismissed-notifications {
				margin-left: 25px;
				flex: 1 1 auto;

				a {
					font-size: 12px;
					color: #fff;
				}
			}

			svg.aioseo-close {
				width: 14px;
				height: 14px;
				cursor: pointer;

				&:hover {
					color: darken(#fff, 20%)
				}
			}
		}

		.notification-cards {
			flex: 1;
			padding: 24px;
			overflow: auto;
		}

		.notification-footer {
			height: 90px;
			padding: 24px;
			display: flex;
			align-items: center;

			div.pagination {
				flex: 1;
				display: flex;
				align-items: center;

				.page-number {
					font-size: 13px;
					color: $black;
					background: $border;
					height: 30px;
					width: 30px;
					display: flex;
					align-items: center;
					justify-content: center;
					border-radius: 2px;
					margin-right: 4px;
					cursor: pointer;

					&:last-child {
						margin-right: 0;
					}

					&.active,
					&:hover {
						color: #fff;
						background-color: $blue;
					}
				}
			}
		}
	}

	.overlay {
		position: fixed;
		z-index: 1052;
		top: 0;
		right: 0;
		bottom: 0;
		left: 160px;
		background-color: $black;
		opacity: 0.5;
		transition: 0.5s;
	}

	.notifications-fade-enter-active, .notifications-fade-leave-active {
		transition: opacity .5s;
	}
	.notifications-fade-enter-from, .notifications-fade-leave-to {
		opacity: 0;
	}

	.notifications-slide-enter-active, .notifications-slide-leave-active {
		transition: all .5s ease-in-out;
	}

	.notifications-slide-enter-from, .notifications-slide-leave-to {
		right: -570px;
	}
}
</style>