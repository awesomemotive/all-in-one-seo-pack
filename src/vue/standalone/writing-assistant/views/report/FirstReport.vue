<template>
	<div class="aioseo-writing-assistant-first-report">
		<img :src="getAssetUrl(loginImg)" />
		<div>
			<div class="aioseo-writing-assistant-first-report__title">
				{{ strings.title }}
			</div>
			<div class="aioseo-writing-assistant-first-report__description">
				{{ strings.description }}
			</div>
		</div>
		<generate-report
			button-size="medium"
			:show-reports-left="true"
		/>
	</div>
</template>

<script setup>
import loginImg from '@/vue/assets/images/writing-assistant/connected.svg'
import GenerateReport from '@/vue/standalone/writing-assistant/views/report/GenerateReport'
import { getAssetUrl } from '@/vue/utils/helpers'
import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	title       : __('Your Account is Connected to SEOBoost! 🎉', td),
	description : __('Now you\'re ready to harness the power of data-driven insights. Create your first report and discover how to optimize your content for better search visibility and engagement.', td)
}
</script>

<style lang="scss">
.aioseo-writing-assistant-first-report {
	display: flex;
	flex-direction: column;
	align-items: center;
	text-align: center;
	gap: 20px;
	padding: 40px 20px;

	&__title {
		font-size: 16px;
		font-weight: 700;
		line-height: 24px;
		margin-top: 20px;
	}

	&__description {
		max-width: 600px;
		margin-top: 8px;
		line-height: 22px;
	}

	button {
		> img {
			margin-right: 8px;
		}

		> .aioseo-caret {
			width: 16px;
			height: 16px;
			margin: 0 0 0 8px;
			color: #fff;
			fill: #fff;
			transform: rotate(-90deg);
			path {
				fill: #fff !important;
			}
		}
	}

	.aioseo-writing-assistant-sidebar & {
		text-align: left;
		padding: 0;
		&__title {
			margin-top: 0
		}
	}
}
</style>