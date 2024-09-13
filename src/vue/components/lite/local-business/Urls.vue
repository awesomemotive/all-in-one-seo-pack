<template>
	<div class="aioseo-col col-xs-12 text-xs-left">
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description">{{ strings.websiteDesc }}</span>
			<base-input
				type="text"
				size="medium"
			/>
		</div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description mt-8">{{ strings.aboutDesc }}</span>
			<base-input
				type="text"
				size="medium"
			/>
		</div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description mt-8">{{ strings.contactDesc }}</span>
			<base-input
				type="text"
				size="medium"
			/>
		</div>
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	data () {
		return {
			strings : {
				websiteDesc : __('Website URL:', td),
				aboutDesc   : __('About Page URL:', td),
				contactDesc : __('Contact Page URL:', td)
			}
		}
	}
}
</script>

<style scoped>
.field-description {
	display: inline-block;
	margin-bottom: 10px;
	font-size: 14px;
}
.mt-8 {
	margin-top: 8px;
}
</style>