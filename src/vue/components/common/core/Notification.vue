<template>
	<transition-slide
		class="aioseo-notification"
		:class="[ `aioseo-notification-${notification.notification_name}` ]"
		:active="active"
	>
		<div>
			<div class="icon">
				<component
					:is="getIcon"
					:class="notification.type"
				/>
			</div>

			<div class="body">
				<div class="title">
					<div>{{ sanitizeString(notification.title) }}</div>
					<div class="date">
						{{ getDate }}
					</div>
				</div>

				<div
					class="notification-content"
					v-html="notification.content"
				/>

				<div class="actions">
					<base-button
						v-if="notification.button1_label && notification.button1_action"
						size="small"
						:type="getButtonType(1)"
						class="aioseo-button1"
						:tag="getTagType(notification.button1_action)"
						:href="getHref(notification.button1_action)"
						:target="getTarget(notification.button1_action)"
						@click="processButtonClick(notification.button1_action, 1)"
						:loading="button1Loading"
					>
						{{ notification.button1_label }}
					</base-button>

					<base-button
						v-if="notification.button2_label && notification.button2_action"
						size="small"
						:type="getButtonType(2)"
						class="aioseo-button2"
						:tag="getTagType(notification.button2_action)"
						:href="getHref(notification.button2_action)"
						:target="getTarget(notification.button2_action)"
						@click="processButtonClick(notification.button2_action, 2)"
						:loading="button2Loading"
					>
						{{ notification.button2_label }}
					</base-button>

					<a
						v-if="!notification.dismissed"
						href="#"
						class="dismiss"
						@click.stop.prevent="processDismissNotification"
					>{{ strings.dismiss }}</a>
				</div>
			</div>
		</div>
	</transition-slide>
</template>

<script>
import {
	useNotificationsStore
} from '@/vue/stores'

import {
	dateSqlToLocalRelative
} from '@/vue/utils/date'

import { useUrl } from '@/vue/composables/Url'
import { sanitizeString } from '@/vue/utils/strings'

import BaseButton from '@/vue/components/common/base/Button'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgGear from '@/vue/components/common/svg/Gear'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'dismissed-notification' ],
	setup () {
		const {
			button1Loading,
			button2Loading,
			getHref,
			getTagType,
			getTarget,
			processButtonClick
		} = useUrl()

		return {
			button1Loading,
			button2Loading,
			getHref,
			getTagType,
			getTarget,
			notificationsStore : useNotificationsStore(),
			processButtonClick,
			sanitizeString
		}
	},
	components : {
		BaseButton,
		SvgCircleCheck,
		SvgCircleClose,
		SvgCircleExclamation,
		SvgGear,
		TransitionSlide
	},
	props : {
		notification : {
			type     : Object,
			required : true
		}
	},
	data () {
		return {
			active  : true,
			strings : {
				dismiss : __('Dismiss', td)
			}
		}
	},
	computed : {
		getIcon () {
			switch (this.notification.type) {
				case 'warning':
					return 'svg-circle-exclamation'
				case 'error':
					return 'svg-circle-close'
				case 'info':
					return 'svg-gear'
				case 'success':
				default:
					return 'svg-circle-check'
			}
		},
		getDate () {
			return dateSqlToLocalRelative(this.notification.start)
		}
	},
	methods : {
		processDismissNotification () {
			this.active = false
			this.notificationsStore.dismissNotifications([ this.notification.slug ])
			this.$emit('dismissed-notification')
		},
		getButtonType (buttonNumber) {
			switch (this.notification.notification_name) {
				case 'search-console-site-not-connected':
				case 'search-console-site-not-verified':
				case 'email-reports-enable-reminder':
					return 1 === buttonNumber ? 'blue' : 'gray'
				default:
					return 'gray'
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-notification {
	margin-bottom: 20px;

	> div {
		display: flex;
		align-items: flex-start;
		padding-bottom: 10px;
		border-bottom: 1px solid $border;

		.icon {
			margin-right: 14px;

			svg {
				width: 20px;
				height: 20px;
				color: $green;

				&.warning {
					color: $orange;
				}

				&.info {
					color: $blue;
				}

				&.success {
					color: $green;
				}

				&.error {
					color: $red;
				}
			}
		}

		.body {
			font-size: $font-md;
			line-height: 22px;
			margin-right: 20px;
			flex: 1;

			.title {
				font-size: $font-md;
				font-weight: 600;
				color: $black;
				margin-bottom: 9px;
				display: flex;
				align-items: center;

				div:first-child {
					flex: 1;
					margin-right: 5px;
					line-height: 22px;
				}

				.date {
					font-weight: initial;
					color: $placeholder-color;
					font-size: 12px;
				}
			}

			.notification-content {
				margin-bottom: 12px;
				max-width: 400px;

				img {
					max-width: 100%;
				}
			}

			.actions {
				flex-wrap: wrap;
				display: flex;
				align-items: center;

				> * {
					margin-bottom: 12px;
				}

				.aioseo-button {
					margin-right: 20px;
				}

				.dismiss {
					color: $placeholder-color;
					font-size: 14px;
				}
			}
		}
	}
}
</style>