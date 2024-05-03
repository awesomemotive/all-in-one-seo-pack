<template>
	<core-alert
		class="aioseo-alert-actionable"
		:type="type"
		:size="size"
		:showClose="showClose"
		@close-alert="$emit('close-alert')"
	>
		<svg-circle-exclamation-solid v-if="'yellow' === type" />

		<svg-circle-check-solid v-if="'green' === type" />

		<div class="aioseo-alert-actionable__content">
			<strong>{{ title }}</strong>

			<p>
				{{ text }}

				<a
					v-if="buttonType === 'link' && button"
					href="#"
					@click.prevent="$emit('click')"
				>
					{{ button }} <span>→</span>
				</a>
			</p>

			<base-button
				v-if="buttonType !== 'link' && button"
				size="medium"
				type="blue"
				tag="button"
				@click.stop="$emit('click')"
			>
				<span>{{ button }}</span>
			</base-button>
		</div>
	</core-alert>
</template>

<script setup>
import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import SvgCircleExclamationSolid from '@/vue/components/common/svg/circle/ExclamationSolid'

defineProps({
	title      : String,
	text       : String,
	button     : String,
	buttonType : String,

	// Inherited from CoreAlert
	type      : String,
	size      : String,
	showClose : Boolean
})

defineEmits([ 'click', 'close-alert' ])
</script>

<style lang="scss">
.aioseo-alert-actionable {
	display: flex;
	align-items: start;
	gap: 8px;

	.aioseo-circle-exclamation-solid {
		width: 20px;
		height: 20px;
		color: $orange;
	}

	.aioseo-circle-check-solid {
		width: 20px;
		height: 20px;
		color: $green;
	}

	&__content {
		flex: 1;

		strong {
			display: block;
			font-size: 14px;
			line-height: 22px;
		}

		p {
			font-size: 14px;
			line-height: 22px;
			margin: 0;

			a {
				color: $blue;
				text-decoration: underline;
				font-weight: 700;
				line-height: 1;

				span {
					vertical-align: text-bottom;
				}
			}
		}

		button {
			margin-top: 5px;
		}
	}
}
</style>