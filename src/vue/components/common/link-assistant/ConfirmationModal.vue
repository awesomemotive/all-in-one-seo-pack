<template>
	<core-modal
		:show="show"
		:classes="[ 'aioseo-link-assistant-confirmation-modal' ]"
		no-header
		@close="$emit('closeModal', true)"
	>
		<template #body >
			<div class="aioseo-modal-body">
				<button
					class="close"
					@click.stop="$emit('closeModal', true)"
				>
					<svg-close @click="$emit('closeModal', true)" />
				</button>

				<h3>{{ areYouSure }}</h3>

				<div class="reset-description"
					v-html="strings.actionCannotBeUndone"
				/>

				<base-button
					type="blue"
					size="medium"
					@click="$emit('doBulkAction', $event)"
				>
					{{ confirmation }}
				</base-button>

				<base-button
					type="gray"
					size="medium"
					@click="$emit('closeModal', true)"
				>
					{{ strings.noChangedMind }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script>
import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgClose from '@/vue/components/common/svg/Close'
export default {
	emits      : [ 'closeModal', 'doBulkAction' ],
	components : {
		CoreModal,
		SvgClose
	},
	props : {
		selectedRows : {
			required : true
		},
		strings : {
			type     : Object,
			required : true
		},
		show : Boolean
	},
	computed : {
		areYouSure () {
			if ('all' === this.selectedRows) {
				return this.strings.areYouSureAll
			}
			if (Array.isArray(this.selectedRows) && 1 < this.selectedRows.length) {
				return this.strings.areYouSureMultiple
			}
			return this.strings.areYouSureSingle
		},
		confirmation () {
			if ('all' === this.selectedRows) {
				return this.strings.yesAll
			}
			if (Array.isArray(this.selectedRows) && 1 < this.selectedRows.length) {
				return this.strings.yesMultiple
			}
			return this.strings.yesSingle
		}
	}
}
</script>

<style lang="scss">
.aioseo-link-assistant-confirmation-modal {
	.modal-container {
		max-width: 650px !important;
	}

	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		align-items: center !important;
		justify-content: center;
		flex-direction: column;
		position: relative;
	}

	.aioseo-button:not(.close) {
		margin-top: 16px;
	}

	h3 {
		font-size: 20px;
		margin-bottom: 16px;
		text-align: center;
	}

	.reset-description {
		font-size: 16px;
		color: $black;
		margin-bottom: 16px;
		text-align: center;
		max-width: 515px;
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
}
</style>