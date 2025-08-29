<template>
	<div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description">{{ strings.priceIndicator }}</span>
			<base-select
				size="medium"
				:options="currencies"
			/>
		</div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description mt-8">{{ strings.currenciesAccepted }}</span>
			<base-select
				size="medium"
				label="currenciesAccepted"
				track-by="value"
				class="aioseo-multiselect"
				:options="currencies"
				multiple
			/>
		</div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description mt-8">{{ strings.paymentMethods }}</span>
			<base-input
				type="text"
				size="medium"
			/>
		</div>
	</div>
</template>

<script setup>
import { CURRENCY_LIST } from '@/vue/plugins/constants'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const currencies = CURRENCY_LIST

const strings = {
	priceIndicator     : __('Price Indicator', td),
	currenciesAccepted : __('Currencies Accepted', td),
	paymentMethods     : __('Payment Methods Accepted', td)
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