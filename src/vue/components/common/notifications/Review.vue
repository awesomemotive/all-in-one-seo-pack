<template>
	<transition-slide
		class="aioseo-notification"
		:active="active"
	>
		<div>
			<div class="icon">
				<svg-circle-check class="success" />
			</div>

			<div class="body">
				<div class="title">
					<div>{{ title }}</div>
				</div>

				<div
					class="notification-content"
					v-html="content"
				/>

				<div class="actions">
					<template
						v-if="1 === step"
					>
						<base-button
							size="small"
							type="blue"
							@click.stop="step = 2"
						>
							{{ strings.yesILoveIt }}
						</base-button>

						<base-button
							size="small"
							type="gray"
							@click.stop="step = 3"
						>
							{{ strings.notReally }}
						</base-button>
					</template>

					<template
						v-if="2 === step"
					>
						<base-button
							tag="a"
							href="https://aioseo.com/aioseo-wordpress-rating"
							size="small"
							type="blue"
							target="_blank"
							rel="noopener noreferrer"
							@click="processDismissNotification(false)"
						>
							{{ strings.okYouDeserveIt }}
						</base-button>

						<base-button
							size="small"
							type="gray"
							@click.stop.prevent="processDismissNotification(true)"
						>
							{{ strings.nopeMaybeLater }}
						</base-button>
					</template>
					<template
						v-if="3 === step"
					>
						<base-button
							tag="a"
							:href="feedbackUrl"
							size="small"
							type="blue"
							target="_blank"
							rel="noopener noreferrer"
							@click="processDismissNotification(false)"
						>
							{{ strings.giveFeedback }}
						</base-button>

						<base-button
							size="small"
							type="gray"
							@click.stop.prevent="processDismissNotification(false)"
						>
							{{ strings.noThanks }}
						</base-button>
					</template>

					<a
						v-if="!notification.dismissed"
						class="dismiss"
						href="#"
						@click.stop.prevent="processDismissNotification(false)"
					>{{ strings.dismiss }}</a>
				</div>
			</div>
		</div>
	</transition-slide>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useNotificationsStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'dismissed-notification' ],
	setup () {
		return {
			licenseStore       : useLicenseStore(),
			notificationsStore : useNotificationsStore(),
			optionsStore       : useOptionsStore(),
			rootStore          : useRootStore()
		}
	},
	components : {
		SvgCircleCheck,
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
			step    : 1,
			active  : true,
			strings : {
				dismiss        : __('Dismiss', td),
				yesILoveIt     : __('Yes, I love it!', td),
				notReally      : __('Not Really...', td),
				okYouDeserveIt : __('Ok, you deserve it', td),
				nopeMaybeLater : __('Nope, maybe later', td),
				giveFeedback   : __('Give feedback', td),
				noThanks       : __('No thanks', td)
			}
		}
	},
	computed : {
		title () {
			switch (this.step) {
				case 2:
					return __('That\'s Awesome!', td)
				case 3:
					return __('Help us improve', td)
				default:
					return sprintf(
						// Translators: 1 - The plugin short name ("AIOSEO").
						__('Are you enjoying %1$s?', td),
						import.meta.env.VITE_SHORT_NAME
					)
			}
		},
		content () {
			switch (this.step) {
				case 2:
					return __('Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', td)
				case 3:
					return sprintf(
						// Translators: 1 - The plugin name ("All in One SEO").
						__('We\'re sorry to hear you aren\'t enjoying %1$s. We would love a chance to improve. Could you take a minute and let us know what we can do better?', td),
						import.meta.env.VITE_NAME
					)
				default:
					return ''
			}
		},
		feedbackUrl () {
			const key = this.optionsStore.options.general && this.licenseStore.licenseKey ? this.licenseStore.licenseKey : ''
			const pro = this.rootStore.isPro ? 'pro' : 'lite'
			return links.utmUrl(
				'notification-review-notice',
				this.rootStore.aioseo.version,
				'https://aioseo.com/plugin-feedback/' +
					'?wpf7528_24=' + encodeURIComponent(this.rootStore.aioseo.urls.home) +
					'&wpf7528_26=' + key +
					'&wpf7528_27=' + pro +
					'&wpf7528_28=' + this.rootStore.aioseo.version
			)
		}
	},
	methods : {
		processDismissNotification (delay = false) {
			this.active = false
			this.notificationsStore.dismissNotifications([ this.notification.slug + (delay ? '-delay' : '') ])
			this.$emit('dismissed-notification')
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
			}

			.notification-content {
				margin-bottom: 12px;
				max-width: 400px;
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