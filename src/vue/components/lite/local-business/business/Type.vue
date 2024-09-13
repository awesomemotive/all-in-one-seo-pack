<template>
	<div>
		<base-select
			size="large"
			:options="LOCAL_SEO_BUSINESS_TYPES"
		/>
	</div>
</template>

<script>
import { LOCAL_SEO_BUSINESS_TYPES } from '@/vue/plugins/constants'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	data () {
		return {
			LOCAL_SEO_BUSINESS_TYPES,
			strings : {
				businessType : __('Type', td)
			}
		}
	}
}
</script>