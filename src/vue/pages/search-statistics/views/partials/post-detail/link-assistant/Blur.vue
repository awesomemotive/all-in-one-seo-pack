<template>
	<core-blur>
		<link-assistant :links="links" />
		<br><br><br><br><br><br><br><br><br><br>
	</core-blur>
</template>

<script>
import CoreBlur from '@/vue/components/common/core/Blur'
import LinkAssistant from './LinkAssistant'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreBlur,
		LinkAssistant
	},
	data () {
		return {
			strings : {
				title : __('3 posts are redirecting to this post', td)
			}
		}
	},
	computed : {
		links () {
			return {}
		}
	}
}
</script>