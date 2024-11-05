<template>
	<span class="aioseo-writing-assistant-favicon">
		<img v-if="domain" :src="imageSource" alt="favicon" />
	</span>
</template>

<script>
export default {
	props : {
		domain : String
	},
	computed : {
		imageSource () {
			return 'https://www.google.com/s2/favicons?sz=32&domain=' + encodeURI(this.domain)
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