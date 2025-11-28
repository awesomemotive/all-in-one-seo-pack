<template>
	<div class="promotional-alert">
		<core-alert
			type="blue"
		>
			<div class="promotional-alert-content">
				<div class="promotional-alert-icon">
					<svg-ai-content />
				</div>
				<div class="promotional-alert-text">
					<span>{{ strings.promotionalAlertText }}</span>
				</div>
			</div>
		</core-alert>
	</div>
</template>

<script setup>
import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	promotionalAlertText : __('Our new AI-powered keyword reports are 100% free for a limited time only. Enjoy!', td)
}
</script>

<style lang="scss" scoped>
.promotional-alert {
	margin-bottom: 24px;

	.aioseo-alert {
		border: 1px solid $blue;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
		padding: 16px 20px;
		position: relative;
		overflow: hidden;

		.promotional-alert-content {
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.promotional-alert-icon {
			flex-shrink: 0;
			width: 32px;
			height: 32px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: $blue;
			border-radius: 50%;
			color: #fff;
			box-shadow: 0 2px 6px rgba(0, 123, 255, 0.25);

			.aioseo-ai-content {
				width: 18px;
				height: 18px;
			}
		}
	}
}
</style>