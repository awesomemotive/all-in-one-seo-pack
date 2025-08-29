<template>
	<transition-slide
		class="aioseo-notification"
		:active="active"
	>
		<div>
			<div class="icon">
				<svg-circle-close class="error" />
			</div>

			<div class="body">
				<div class="title">
					<div>{{ strings.title }}</div>
				</div>

				<div
					class="notification-content"
					v-html="content"
				/>

				<div class="actions">
					<base-button
						size="small"
						type="green"
						tag="a"
						:href="links.getUpsellUrl('notification-unlicensed-addons', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
						target="_blank"
					>
						{{ strings.upgrade }}
					</base-button>
				</div>
			</div>
		</div>
	</transition-slide>
</template>

<script setup>
import { ref, computed } from 'vue'

import links from '@/vue/utils/links'
import {
	useRootStore
} from '@/vue/stores'

import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import TransitionSlide from '@/vue/components/common/transition/Slide'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	notification : {
		type     : Object,
		required : true
	}
})

const rootStore = useRootStore()

const active = ref(true)

const strings = {
	title : sprintf(
		// Translators: 1 - Plugin short name ("AIOSEO").
		__('%1$s Addons Not Configured Properly', td),
		import.meta.env.VITE_SHORT_NAME
	),
	learnMore : __('Learn More', td),
	upgrade   : __('Upgrade', td)
}

const content = computed(() => {
	let addonsList = '<ul>'
	props.notification.addons.forEach(addon => {
		addonsList += '<li><strong>' + import.meta.env.VITE_SHORT_NAME + ' - ' + addon.name + '</strong></li>'
	})
	addonsList += '</ul>'
	return props.notification.message + addonsList
})
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