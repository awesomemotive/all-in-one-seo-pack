<template>
	<core-modal
		:show="props.showModal"
		no-header
		@close="$emit('cancel')"
		:classes="[ 'aioseo-ai-content-settings__disconnect-modal' ]"
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
					:loading="props.loading"
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
import { __, sprintf } from '@/vue/plugins/translations'

import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgClose from '@/vue/components/common/svg/Close'

const td      = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	areYouSureReset      : __('Are you sure you want to disconnect from AI Content?', td),
	actionCannotBeUndone : sprintf(
		// Translators: 1 - Plugin Short Name ("AIOSEO").
		__('This action will disconnect %1$s from AI Content. By disconnecting from AI Content, you will no longer be able to use the AI Content features.', td),
		import.meta.env.VITE_SHORT_NAME
	),
	yesContinue : __('Yes, I want to disconnect', td),
	noCancel    : __('No, I changed my mind', td)
}

const props = defineProps({
	showModal : {
		type    : Boolean,
		default : false
	},
	loading : {
		type    : Boolean,
		default : false
	}
})
defineEmits([ 'continue', 'cancel' ])
</script>

<style lang="scss">
.aioseo-ai-content-settings__disconnect-modal {
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