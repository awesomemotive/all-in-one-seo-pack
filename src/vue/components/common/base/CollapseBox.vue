<template>
	<div
		class="aioseo-collapse-box"
		:class="{
			'aioseo-collapse-box__open': open,
			'aioseo-collapse-box__closed': !open
		}"
	>
		<div
			class="aioseo-collapse-box__header"
			@click="open = !open"
		>
			<slot name="title"></slot>
			<svg-right-arrow
				class="aioseo-collapse-box__header-arrow"
				:style="{
					transform: open ? 'rotate(-90deg)' : 'rotate(-0deg)'
				}"
			/>
		</div>
		<transition-slide
			name="aioseo-collapse-box"
			:active="open"
		>
			<div
				class="aioseo-collapse-box__content"
			>
				<slot name="body"></slot>
			</div>
		</transition-slide>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import SvgRightArrow from '@/vue/components/common/svg/right-arrow/Simple'
import TransitionSlide from '@/vue/components/common/transition/Slide'

const props = defineProps({
	open : {
		type    : Boolean,
		default : true
	}
})

const open = ref(props.open)
</script>

<style lang="scss">
.aioseo-collapse-box {
	display: flex;
	flex-direction: column;

	&__header {
		display: flex;
		align-items: center;
		cursor: pointer;
		font-weight: 700;
		font-size: 14px;
		gap: 12px;
		color: $black;
		padding: 12px 12px 12px 0;

		&-arrow {
			width: 14px;
			height: 14px;
		}
	}

	&__closed {
		.aioseo-collapse-box__header {
			padding: 12px;
			background: $box-background;
			border: 1px solid $input-border;
			margin-bottom: 12px;
		}
	}
}
</style>