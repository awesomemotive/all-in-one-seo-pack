<template>
	<div class="aioseo-limit-modified-date-avada">
		<div
			class="aioseo-limit-modified-date-avada__toggle"
			:class="{ 'aioseo-limit-modified-date-avada__toggle--disabled': props.buttonDisabled }"
			@click.prevent="toggleOptions"
		>
			<svg-caret :class="{ rotated: showOptions }" />
		</div>

		<div
			class="aioseo-limit-modified-date-avada__options"
			v-if="showOptions"
		>
			<div
				class="aioseo-limit-modified-date-avada__option"
				@click.prevent="save"
			>
				{{ props.buttonTitle }}
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import SvgCaret from '@/vue/components/common/svg/Caret'

const showOptions = ref(false)
const props = defineProps({
	buttonTitle    : String,
	buttonEvent    : String,
	buttonDisabled : Boolean
})

const toggleOptions = () => {
	if (props.buttonDisabled) {
		return
	}

	showOptions.value = !showOptions.value
}

const save = () => {
	showOptions.value = false
	emitter.emit(props.buttonEvent)
}
</script>

<style lang="scss">
.aioseo-limit-modified-date-avada {
	height: 100%;
	position: relative;

	&__toggle {
		height: 100%;
		cursor: pointer;
		background: #198fd9;
		transition: background-color .2s ease-in-out;
		display: flex;
		justify-content: center;
		align-items: center;
		position: relative;

		.aioseo-caret {
			width: 18px;
			height: 18px;
			transition: transform 0.3s;
			margin: 0 5px;

			&.rotated {
				transform: rotate(-180deg);
			}
		}

		&::before {
			content: '';
			width: 1px;
			height: 80%;
			background-color: #34434a;
			opacity: 0.2;
		}

		&:hover {
			background-color: #3bc4fe;
		}

		&--disabled {
			cursor: not-allowed;
			background: #4a5259;

			&:hover {
				background: #4a5259;
			}
		}
	}

	&__options {
		position: absolute;
		top: 100%;
		right: 0;
		width: 170px;
		display: flex;
		justify-content: end;
		border-radius: 0 0 6px 6px;
		box-shadow: 0 15px 30px 0 rgba(0, 0, 0, 0.30);
		overflow: hidden;
	}

	&__option {
		background: #198fd9;
		display: inline-flex;
		height: 54px;
		padding: 10px;
		justify-content: center;
		align-items: center;
		gap: 10px;
		flex-shrink: 0;
		cursor: pointer;
		font-size: 12px;
		font-style: normal;
		font-weight: 700;
		line-height: 16px;
		color: $white;
		transition: background-color .2s ease-in-out;

		&:hover {
			background-color: #3bc4fe;
		}
	}
}
</style>