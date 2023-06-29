<template>
	<div class="aioseo-ai-generator">
		<core-tooltip
			type="action"
			:offset="'-30px,0'"
		>
			<button
				type="button"
				@click="settingsStore.setModalState({
					modalName : 'aiGenerator',
					value : type
				})"
			>
				<svg-ai-robot
				@click="settingsStore.setModalState({
					modalName : 'aiGenerator',
					value : type
				})"
				/>
			</button>

			<template #tooltip>
				{{ strings.useAiGenerator }}
			</template>
		</core-tooltip>

		<ai-modal
			v-if="settingsStore.modals.aiGenerator === type"
			:type="type"
		/>
	</div>
</template>

<script>
import {
	useSettingsStore
} from '@/vue/stores'

import AiModal from '../../../AIOSEO_VERSION/partials-ai/Modal'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgAiRobot from '@/vue/components/common/svg/ai/Robot'
export default {
	setup () {
		return {
			settingsStore : useSettingsStore()
		}
	},
	components : {
		AiModal,
		CoreTooltip,
		SvgAiRobot
	},
	props : {
		type : {
			type     : String,
			required : true
		}
	},
	data () {
		return {
			strings : {
				useAiGenerator : this.$t.__('Use AI Generator', this.$td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-general {

	.aioseo-ai-generator {

		button {
			display: flex;
			align-items: center;
			justify-content: center;

			svg.aioseo-ai-robot {
				width: 15px;
				height: 13px;
				color: $black;

				&:hover {
					color: $black2;
				}
			}
		}
	}

	.snippet-title-row {
		.aioseo-editor .aioseo-editor-single .ql-editor {
			padding: 7px 55px 7px 10px;
		}
	}

	.snippet-description-row {
		.aioseo-editor .ql-editor {
			padding: 15px 55px 15px 15px;
		}
	}
}
</style>