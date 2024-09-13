<template>
	<div class="aioseo-divi-seo-admin-notice-container">
		<core-alert v-html="strings.alert" />
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		CoreAlert
	},
	data () {
		return {
			strings : {
				alert : sprintf(
					// Translators: 1 - The plugin name ("All in One SEO"), 2 - Opening HTML link tag, 3 - Closing HTML link tag.
					__('The options below are disabled because you are using %1$s to manage your SEO. They can be changed in the %2$sSearch Appearance menu%3$s.', td),
					import.meta.env.VITE_NAME,
					`<a href="${this.rootStore.aioseo.urls.aio.searchAppearance}" target="_blank">`,
					'</a>'
				)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-divi-seo-admin-notice-container {
	margin-bottom: 20px;
}

#wrap-seo .et-epanel-box {
	filter: blur(2px);
}
</style>