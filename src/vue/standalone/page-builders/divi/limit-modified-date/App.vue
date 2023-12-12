<template>
	<div class="aioseo-limit-modified-date-divi">
		<div class="et-fb-button-group">
			<button
				class="aioseo-limit-modified-date-divi__button-toggle et-fb-button et-fb-button--elevate et-fb-button--success"
				@click.prevent="showOptions = !showOptions"
			>
				<svg-caret :class="{ rotated: !showOptions }" />
			</button>
		</div>

		<div
			class="aioseo-limit-modified-date-divi__options et-fb-button-group"
			v-if="showOptions"
		>
			<button
				class="et-fb-button et-fb-button--elevate et-fb-button--success"
				@click.prevent="save"
			>
				{{ props.buttonTitle }}
			</button>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import emitter from 'tiny-emitter/instance'
import SvgCaret from '@/vue/components/common/svg/Caret'

const showOptions = ref(false)
const props = defineProps({
	buttonTitle : String,
	buttonEvent : String
})

const save = () => {
	showOptions.value = false
	emitter.emit(props.buttonEvent)
}
</script>

<style lang="scss">
.aioseo-limit-modified-date-divi {
	margin-left: 1px;

	&__button-toggle {
		.aioseo-caret {
			width: 18px;
			height: 18px;
			transition: transform 0.3s;

			&.rotated {
				transform: rotate(-180deg);
			}
		}
	}

	&__options {
		position: absolute;
		top: calc(-100% - 1px);
		right: 0;
	}
}
</style>