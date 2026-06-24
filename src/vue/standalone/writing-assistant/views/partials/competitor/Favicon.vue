<template>
	<span class="aioseo-writing-assistant-favicon">
		<img v-if="imageSource" :src="imageSource" alt="favicon" />
	</span>
</template>

<script>
import { getFaviconUrl } from '@/vue/utils/favicon'

export default {
	props : {
		domain : String
	},
	computed : {
		imageSource () {
			return getFaviconUrl(this.domain)
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant-favicon {
	img {
		max-width: 100%;
	}
}
</style>