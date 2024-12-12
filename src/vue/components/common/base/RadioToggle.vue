<template>
	<div
		class="aioseo-radio-toggle"
		:class="{ inline: inline, circle: circle }"
	>
		<div
			v-for="(option, index) in options"
			:key="index"
		>
			<input
				:id="`id_${name}_${index}`"
				:name="name"
				type="radio"
				@input="$emit('update:modelValue', option.value)"
				:checked="option.value === modelValue"
				:disabled="disabled"
			/>

			<label
				:for="`id_${name}_${index}`"
				:class="[
					{ [option.activeClass || 'default']: option.value === modelValue  },
					{ disabled: disabled }
				]"
			>
				<slot :name="option.slot">
					{{ option.label }}
				</slot>
			</label>
		</div>
	</div>
</template>

<script>
export default {
	props : {
		options : {
			type     : Array,
			required : true
		},
		name : {
			type     : String,
			required : true
		},
		modelValue : [ String, Boolean ],
		disabled   : {
			type : Boolean,
			default () {
				return false
			}
		},
		inline : Boolean,
		circle : Boolean
	}
}
</script>

<style lang="scss">
@use 'sass:color';

.aioseo-radio-toggle {
	display: flex;
	align-items: center;
	height: 40px;

	div {
		height: 100%;
	}

	&.inline {
		display: inline-flex;
	}

	div:first-child {
		overflow: hidden;
		border-radius: 3px 0 0 3px;

		label {
			border-radius: 3px 0 0 3px;
		}
	}

	div:last-child {
		overflow: hidden;
		border-radius: 0 3px 3px 0;

		label {
			border-radius: 0 3px 3px 0;
		}
	}

	input {
		position: absolute !important;
		clip: rect(0, 0, 0, 0);
		height: 1px;
		width: 1px;
		border: 0;
		overflow: hidden;

		&:checked + label {
			background-color: $blue;
			color: #fff;

			&.dark {
				background-color: $black2;
				color: #fff;
			}
		}
	}

	label {
		height: 100%;
		background-color: $border;
		color: $black;
		font-size: 14px;
		line-height: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		transition: all 0.1s ease-in-out;
		position: relative;
		padding: 6px 14px;
		font-weight: $font-bold;

		&.disabled {
			cursor: default;
			pointer-events: none;
			opacity: 0.5;
		}

		&:hover {
			background-color: color.adjust($border, $lightness: -5%);
			cursor: pointer;
		}

		p {
			position: absolute;
			bottom: 15px;
			margin: 0;
		}
	}

	&.small {
		height: 32px;

		label {
			font-size: 12px;
			padding: 6px 14px;
		}
	}

	&.circle {
		height: 36px;

		label {
			background: #fff;
			color: $placeholder-color;
		}
		input + label {
			border-radius: 50%;
			width: 36px;
			height: 36px;
			padding: 8px;
		}
		input:checked + label {
			background: $border;
			color: $black2-hover;
		}
	}
}

// The RTL classes override this so we need to target it specifically.
[dir] {
	.aioseo-radio-toggle {
		&.circle {
			label {
				background: #fff;
				color: $placeholder-color;
			}
			input + label {
				border-radius: 50%;
				width: 36px;
				height: 36px;
				padding: 8px;
			}
			input:checked + label {
				background: $border;
				color: $black2-hover;
			}
		}
	}
}
</style>