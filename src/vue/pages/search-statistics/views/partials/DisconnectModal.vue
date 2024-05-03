<template>
	<core-modal
		:show="showModal"
		no-header
		@close="$emit('cancel')"
		:classes="[ 'aioseo-search-statistics-settings-modal' ]"
	>
		<template #body>
			<div class="aioseo-modal-body">
				<button
					class="close"
					@click.stop="$emit('cancel')"
				>
					<svg-close @click="$emit('cancel')" />
				</button>

				<h3>{{ strings.areYouSureReset }}</h3>

				<div class="reset-description">
					{{ strings.actionCannotBeUndone }}
				</div>

				<base-button
					type="blue"
					size="medium"
					@click="$emit('continue')"
					:loading="loading"
				>
					{{ strings.yesContinue }}
				</base-button>

				<base-button
					type="gray"
					size="medium"
					@click="$emit('cancel')"
				>
					{{ strings.noCancel }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import { __, sprintf } from '@wordpress/i18n'

import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgClose from '@/vue/components/common/svg/Close'

const td      = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	areYouSureReset      : __('Are you sure you want to disconnect Google Search Console?', td),
	actionCannotBeUndone : sprintf(
		// Translators: 1 - Plugin Short Name ("AIOSEO").
		__('This action will disconnect %1$s from Google Search Console. By disconnecting from Google Search Console, you will no longer receive valuable insights on how your content is being discovered.', td),
		import.meta.env.VITE_SHORT_NAME
	),
	yesContinue : __('Yes, I want to disconnect', td),
	noCancel    : __('No, I changed my mind', td)
}

defineProps({
	showModal : Boolean,
	loading   : Boolean
})
defineEmits([ 'continue', 'cancel' ])
</script>

<style lang="scss">
.aioseo-search-statistics-settings-modal {
	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;

		h3 {
			font-size: 20px;
			margin-bottom: 16px;
		}

		.reset-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
			text-align: center;
			max-width: 515px;
			line-height: 1.6;
		}

		button.close {
			position: absolute;
			right: 11px;
			top: 11px;
			width: 24px;
			height: 24px;
			background-color: #fff;
			border: none;
			display: flex;
			align-items: center;

			svg.aioseo-close {
				cursor: pointer;
				width: 14px;
				height: 14px;
			}
		}

		.aioseo-description {
			max-width: 510px;
			text-align: center;
		}

		.aioseo-button:not(.close) {
			margin-top: 16px;
		}
	}
}
</style>