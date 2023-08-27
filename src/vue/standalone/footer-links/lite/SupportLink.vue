<template>
	<a
		:href="links.utmUrl('footer', rootStore.aioseo.page, 'https://aioseo.com/plugin/lite-support')"
		target="_blank"
		rel="noreferrer noopener"
	>
		{{strings.support}}
	</a>
</template>

<script>
import { useRootStore } from '@/vue/stores'
import links from '@/vue/utils/links'

export default {
	setup () {
		return {
			links,
			rootStore : useRootStore()
		}
	},
	data () {
		return {
			strings : {
				support : this.$t.__('Support', this.$td)
			}
		}
	}
}
</script>