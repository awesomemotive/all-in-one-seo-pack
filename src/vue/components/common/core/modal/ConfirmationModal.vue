<template>
	<core-modal
		:show="props.show"
		no-header
		@close="emit('closeModal')"
		:classes="[ 'aioseo-confirmation-modal' ]"
	>
		<template #body>
			<div class="aioseo-modal-body">
				<button
					class="close"
					@click.stop="emit('closeModal')"
				>
					<svg-close @click="emit('closeModal')" />
				</button>

				<h3>{{ props.strings.areYouSure }}</h3>

				<div class="reset-description">
					{{ props.strings.actionCannotBeUndone }}
				</div>

				<base-button
					type="blue"
					size="medium"
					@click="emit('doAction')"
				>
					{{ props.strings.confirmation }}
				</base-button>

				<base-button
					type="gray"
					size="medium"
					@click="emit('closeModal')"
				>
					{{ props.strings.noChangedMind }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import BaseButton from '@/vue/components/common/base/Button'
import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgClose from '@/vue/components/common/svg/Close'

const props = defineProps({
	show : {
		type     : Boolean,
		required : true
	},
	strings : {
		type     : Object,
		required : true
	}
})

const emit = defineEmits([ 'doAction', 'closeModal' ])
</script>

<style lang="scss">
.aioseo-confirmation-modal {
	.modal-container {
		max-width: 620px !important;
	}

	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;

		h3 {
			font-size: 20px;
			margin-bottom: 16px;
		}

		.reset-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
			text-align: center;
			max-width: 515px;
			line-height: 1.6;
		}

		button.close {
			position: absolute;
			right: 11px;
			top: 11px;
			width: 24px;
			height: 24px;
			background-color: #fff;
			border: none;
			display: flex;
			align-items: center;

			svg.aioseo-close {
				cursor: pointer;
				width: 14px;
				height: 14px;
			}
		}

		.aioseo-description {
			max-width: 510px;
			text-align: center;
		}

		.aioseo-button:not(.close) {
			margin-top: 16px;
		}
	}
}
</style>