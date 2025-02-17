<template>
	<div
		class="aioseo-alert"
		:class="{
			[type] : type,
			[size] : size
		}"
	>
		<slot />

		<span
			@click="$emit('close-alert')"
			class="aioseo-alert-close"
			v-if="showClose"
		>
			<svg-close />
		</span>
	</div>
</template>

<script>
import SvgClose from '@/vue/components/common/svg/Close'
export default {
	emits      : [ 'close-alert' ],
	components : {
		SvgClose
	},
	props : {
		type : {
			type : String,
			default () {
				return 'blue'
			}
		},
		size : {
			type : String,
			default () {
				return 'medium'
			}
		},
		showClose : Boolean
	}
}
</script>

<style lang="scss">
@use 'sass:color';

.aioseo-alert {
	position: relative;
	border-radius: 3px;
	padding: 12px;
	font-size: 16px;
	color: $black;
	line-height: 24px;

	&.medium {
		padding: 8px 12px;
		font-size: 14px;
		line-height: 22px;
	}

	&.small {
		padding: 8px;
		font-size: 14px;
		line-height: 22px;
	}

	&.smaller {
		padding: 6px;
		font-size: 12px;
		line-height: 18px;
	}

	.aioseo-alert-close {
		cursor: pointer;
		position: absolute;
		top: -9px;
		right: -9px;
		width: 18px;
		height: 18px;
		border-radius: 50%;
		padding: 5px;
		display: inline-flex;
		justify-content: center;
		align-content: center;

		svg {
			margin: 0 !important;
			width: 100%;
			height: 100%;
		}
	}

	&.blue {
		border: 1px solid $blue;
		background-color: $inline-background;

		.aioseo-alert-close {
			background-color: $blue;
			color: #fff;

			&:hover {
				background-color: $blue2;
			}
		}
	}

	&.green {
		border: 1px solid $green;
		background-color: #f2fdf8;

		.aioseo-alert-close {
			background-color: $green;
			color: #fff;

			&:hover {
				background-color: $green2;
			}
		}
	}

	&.red {
		border: 1px solid $red;
		background-color: color.adjust($red2, $lightness: 55%);

		.aioseo-alert-close {
			background-color: $red;
			color: #fff;

			&:hover {
				background-color: $red2;
			}
		}
	}

	&.yellow {
		border: 1px solid $orange;
		background-color: $yellow;

		.aioseo-alert-close {
			background-color: $orange;
			color: #fff;
		}
	}

	&.no-border {
		border-width: 0;
	}

	&.text-center {
		text-align: center;
	}
}
</style>