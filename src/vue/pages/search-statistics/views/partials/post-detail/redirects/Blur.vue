<template>
	<core-blur>
		<redirects :redirects="redirects" />
		<br><br><br>
	</core-blur>
</template>

<script>
import links from '@/vue/utils/links'

import CoreBlur from '@/vue/components/common/core/Blur'
import Redirects from './Redirects'
export default {
	components : {
		CoreBlur,
		Redirects
	},
	computed : {
		redirects () {
			const from = [
				links.docLinks.home,
				links.docLinks.googleSearchConsole,
				links.docLinks.googleAnalytics
			]

			return { from }
		}
	}
}
</script>