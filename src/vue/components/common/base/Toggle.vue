<template>
	<label
		class="aioseo-toggle"
		:class="{ disabled: disabled }"
		tabindex="0"
		@keydown.enter="labelToggle"
		@keydown.space="labelToggle"
	>
		<div class="toggle-content">
			<input
				:disabled="disabled"
				type="checkbox"
				:checked="modelValue"
				@input="$emit('update:modelValue', $event.target.checked)"
				ref="input"
			>
			<span class="toggle-switch"></span>
		</div>
		<slot />
	</label>
</template>

<script>
export default {
	props : {
		modelValue : Boolean,
		disabled   : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	methods : {
		labelToggle () {
			this.$refs.input.click()
		}
	}
}
</script>

<style lang="scss">
.aioseo-toggle {
	color: $black;
	display: inline-flex;
	align-items: center;
	vertical-align: middle;

	&:active,
	&:focus {
		outline: 2px solid transparent;
	}

	&.disabled {
		pointer-events: none;

		.toggle-content {
			opacity: 0.5;
		}
	}

	.toggle-content {
		position: relative;
		display: inline-block;
		width: 32px;
		height: 18px;
		margin-right: 8px;

		input {
			display: none;

			&:checked {

				+ .toggle-switch {
					border: 1px solid $blue;
					background-color: $blue;

					&:focus {
						outline: 2px solid transparent;
					}

					&:before {
						background-color: #fff;
						transform: translateX(13px);
					}
				}
			}

			&:focus {

				+ .toggle-switch {
					box-shadow: 0 0 1px $blue;
					outline: 2px solid transparent;
				}
			}
		}

		.toggle-switch {
			position: absolute;
			cursor: pointer;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background-color: #fff;
			border: 1px solid $input-border;
			border-radius: 16px;
			transition: 0.2s;

			&:before {
				position: absolute;
				content: "";
				height: 12px;
				width: 12px;
				left: 3px;
				bottom: 2px;
				background-color: $input-border;
				border-radius: 50%;
				transition: 0.2s;
			}
		}
	}
}
</style>