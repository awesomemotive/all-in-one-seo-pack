<template>
	<div class="aioseo-limit-modified-date-wpbakery">
		<div
			class="aioseo-limit-modified-date-wpbakery__toggle"
			@click.prevent="showOptions = !showOptions"
		>
			<svg-caret :class="{ rotated: showOptions }" />
		</div>

		<div
			class="aioseo-limit-modified-date-wpbakery__options"
			v-if="showOptions"
		>
			<div
				class="aioseo-limit-modified-date-wpbakery__option"
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
	buttonTitle : String,
	buttonEvent : String
})

const save = () => {
	showOptions.value = false
	emitter.emit(props.buttonEvent)
}
</script>

<style lang="scss">
.aioseo-limit-modified-date-wpbakery {
	height: 100%;
	position: relative;
	margin-right: 10px;

	&__toggle {
		height: 100%;
		cursor: pointer;
		background: $white;
		border-top-right-radius: 5px;
		border-bottom-right-radius: 5px;
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
			background-color: #e9f2f6;
		}

		@at-root {
			.vc_navbar-frontend:not(.vc_post-custom-layout-selected) {
				.aioseo-limit-modified-date-wpbakery__toggle {
					opacity: 0.2;
					pointer-events: none;
				}
			}
		}
	}

	&__options {
		position: absolute;
		top: 100%;
		right: 0;
		width: 200px;
		display: flex;
		justify-content: end;
	}

	&__option {
		padding: 15px;
		line-height: 1;
		color: $white;
		cursor: pointer;
		transition: background-color .2s ease-in-out;
		background-color: #00447F;

		&:hover {
			background-color: #0772CE;
		}
	}
}
</style>