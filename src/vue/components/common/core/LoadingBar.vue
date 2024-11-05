<template>
	<div class="aioseo-loading-bar">
		<div class="aioseo-loading-bar__bar">
			<div
				class="aioseo-loading-bar__progress"
				:style="cssProgress"
			/>
		</div>
		<div
			class="aioseo-loading-bar__number"
			v-if="props.showNumber"
		>
			{{ getPercent }}%
		</div>
		<slot />
	</div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
	showNumber : {
		type    : Boolean,
		default : true
	},
	percent : Number
})

const getPercent = computed(() => {
	return props.percent || 0
})

const cssProgress = computed(() => {
	return { width: getPercent.value + '%' }
})

</script>

<style lang="scss">
.aioseo-loading-bar {
	display: flex;
	flex-direction: column;
	width: 100%;
	gap: 12px;

	&__bar {
		position: relative;
		width: 100%;
		height: 10px;
		background-color: $gray;
		border-radius: 100px;
		align-items: center;
		padding: 0;
	}

	&__progress {
		transition: width 1.5s ease-in-out;
		border-radius: 100px;
		background: $blue;
		height: 10px;
		width: 0;
	}

	&__number {
		font-size: 18px;
		font-weight: 700;
		text-align: center;
		color: $blue;
	}
}
</style>