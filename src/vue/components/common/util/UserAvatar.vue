<template>
	<img
		v-if="src"
		:src='src'
		:width='size'
		:height='size'
		alt='avatar'
		loading='lazy'
		decoding='async'
		class="aioseo-user-avatar"
	/>

	<svg-dannie-profile
		v-else
		:width="size"
		:height="size"
		class="aioseo-user-avatar aioseo-user-avatar--dannie"
	/>
</template>

<script>
import SvgDannieProfile from '@/vue/components/common/svg/dannie/Profile'

export default {
	components : {
		SvgDannieProfile
	},
	props : {
		src  : String,
		size : {
			required : true,
			type     : Number
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-user-avatar {
	border-radius: 50%;
	height: inherit;
	width: inherit;

	&--dannie {
		border: 1px solid $input-border;
	}
}
</style>