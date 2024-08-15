<template>
	<div class="aioseo-index-status">
		<svg-circle-check-solid />
	</div>
</template>

<script setup>
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
</script>

<style lang="scss">
.aioseo-index-status {
	display: inline-flex;

	.aioseo-circle-check-solid {
		width: 30px;
		height: 30px;
		color: $green;
	}
}
</style>