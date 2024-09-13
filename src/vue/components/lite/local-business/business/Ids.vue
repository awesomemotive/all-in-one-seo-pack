<template>
	<div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description">{{ strings.vatID }}</span>
			<base-input
				type="text"
				size="medium"
			/>
		</div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description mt-8">{{ strings.taxID }}</span>
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
				vatID : __('VAT ID:', td),
				taxID : __('Tax ID:', td)
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