<template>
	<div
		v-if="show"
		class="aioseo-loader-progress"
		:class="customClass"
	>
		<div
			class="aioseo-loader-progress__value"
			:style="{
				animationDuration : duration + 's'
			}"
		/>
	</div>
</template>

<script setup>
defineProps({
	show : {
		type    : Boolean,
		default : true
	},
	duration : {
		type    : Number,
		default : 5
	},
	customClass : {
		type    : String,
		default : ''
	}
})
</script>

<style lang="scss" scoped>
.aioseo-loader-progress {
	background: $gray;
	justify-content: flex-start;
	border-radius: 100px;
	align-items: center;
	position: relative;
	padding: 0;
	display: flex;
	height: 10px;
	width: 100%;

	&__value {
		animation: analyze-load 5s normal infinite;
		border-radius: 100px;
		background: $blue;
		height: 10px;
		width: 0;
	}

	@keyframes analyze-load {
		0% { width: 0; }
		100% { width: 100%; }
	}
}
</style>