<template>
	<div class="aioseo-notification-cards">
		<template
			v-if="notifications.length"
		>
			<component
				:is="notification.component ? notification.component : 'core-notification'"
				v-for="notification in notifications"
				:key="notification.slug"
				:notification="notification"
				ref="notification"
				@dismissed-notification="$emit('dismissed-notification')"
			/>
		</template>

		<div
			v-if="!notifications.length"
			key="no-notifications"
		>
			<slot name="no-notifications">
				<div class="no-notifications">
					<img
						alt="Dannie the Detective"
						:src="getAssetUrl(dannieDetectiveImg)"
					/>

					<div class="great-scott">
						{{ strings.greatScott }}
					</div>
					<div class="no-new-notifications">
						{{ strings.noNewNotifications }}
					</div>
					<a
						v-if="dismissedCount"
						href="#"
						class="dismiss"
						@click.stop.prevent="$emit('toggle-dismissed')"
					>
						{{ strings.seeDismissed }}
					</a>
				</div>
			</slot>
		</div>
	</div>
</template>

<script>
import { getAssetUrl } from '@/vue/utils/helpers'
import dannieDetectiveImg from '@/vue/assets/images/aio/dannie-detective.png'
import CoreNotification from '@/vue/components/common/core/Notification'
import NotificationsReview from '@/vue/components/common/notifications/Review'
import NotificationsReview2 from '$/vue/components/common/notifications/Review2'
import NotificationsUnlicensedAddons from '@/vue/components/common/notifications/UnlicensedAddons'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits      : [ 'toggle-dismissed', 'dismissed-notification' ],
	components : {
		CoreNotification,
		NotificationsReview,
		NotificationsReview2,
		NotificationsUnlicensedAddons
	},
	props : {
		dismissedCount : {
			type     : Number,
			required : true
		},
		notifications : {
			type     : Array,
			required : true
		}
	},
	data () {
		return {
			dannieDetectiveImg,
			strings : {
				greatScott         : __('Great Scott! Where\'d they all go?', td),
				noNewNotifications : __('You have no new notifications.', td),
				seeDismissed       : __('See Dismissed Notifications', td)
			}
		}
	},
	methods : {
		getAssetUrl
	}
}
</script>

<style lang="scss">
.aioseo-notification-cards {
	.aioseo-notification:last-child {
		> div {
			border-bottom: none;
			margin-bottom: none;
		}
	}

	.no-notifications {
		display: flex;
		align-items: center;
		flex-direction: column;
		font-size: 14px;
		line-height: 22px;
		color: $placeholder-color;

		@media (min-height: 500px) {
			padding-top: 100px;
		}

		img {
			width: 30%;
			max-width: 108px;
			height: auto;
		}

		.great-scott {
			margin: 20px 0 8px;
			font-size: 18px;
			font-weight: 600;
			color: $black2;
		}

		.no-new-notifications {
			margin-bottom: 20px;
		}
	}
}
</style>