<template>
	<div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description">{{ strings.emailAddress }}</span>
			<base-input
				type="text"
				size="medium"
			/>
		</div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description mt-8">{{ strings.phoneNumber }}</span>
			<base-phone
			/>
		</div>
		<div class="aioseo-col col-xs-12 text-xs-left">
			<span class="field-description mt-8">{{ strings.faxNumber }}</span>
			<base-phone
			/>
		</div>
	</div>
</template>

<script>
import BasePhone from '@/vue/components/common/base/Phone'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		BasePhone
	},
	data () {
		return {
			strings : {
				emailAddress : __('Email Address', td),
				phoneNumber  : __('Phone Number', td),
				faxNumber    : __('Fax Number', td)
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