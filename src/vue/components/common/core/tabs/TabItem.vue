<template>
	<transition-slide
		class="aioseo-tab-item"
		:active="active"
	>
		<div class="aioseo-tab-item-content">
			<slot name="content"></slot>
		</div>
	</transition-slide>
</template>

<script>
import TransitionSlide from '@/vue/components/common/transition/Slide'

export default {
	components : {
		TransitionSlide
	},
	props : {
		active : {
			type     : Boolean,
			required : true
		}
	}
}
</script>

<style lang="scss">
.aioseo-tab-item {
	display : none;
	font-size: 14px;

	&-active {
		display : block;
	}

	&-content {
		padding-top: 15px;
	}
}
</style>