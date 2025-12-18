<template>
	<div class="aioseo-add-template-tag">
		<svg-plus />
		<slot />
	</div>
</template>

<script>
import SvgPlus from '@/vue/components/common/svg/Plus'
export default {
	components : {
		SvgPlus
	}
}
</script>

<style lang="scss">
.aioseo-add-template-tag {
	background-color: #fff;
	display: flex;
	align-items: center;
	border-radius: 3px;
	padding: 2px 8px;
	color: $black;
	font-size: $font-sm;
	line-height: 18px;
	border: 1px solid $input-border;
	cursor: pointer;
	user-select: none;
	font-weight: $font-bold;

	&:hover {
		background-color: $background;
	}

	svg.aioseo-plus {
		width: 10px;
		height: 10px;
		color: $blue;
		margin-right: 4px;
	}
}
</style>