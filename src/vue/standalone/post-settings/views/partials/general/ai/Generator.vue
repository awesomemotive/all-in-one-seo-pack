<template>
	<div class="aioseo-ai-generator">
		<core-tooltip
			type="action"
			:offset="'-30px,0'"
		>
			<button
				type="button"
				@click="showModal = true"
			>
				<svg-ai-content />
			</button>

			<template #tooltip>
				{{ strings.useAiGenerator }}
			</template>
		</core-tooltip>

		<component
			:is="`${feature.slug}-modal`"
			:feature="feature"
			:show="showModal"
			@closeModal="showModal = false"
		/>
	</div>
</template>

<script>
import {
	useSettingsStore
} from '@/vue/stores'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'

import MetaTitleModal from '@/vue/standalone/post-settings/views/partials/ai-content/MetaTitleModal'
import MetaDescriptionModal from '@/vue/standalone/post-settings/views/partials/ai-content/MetaDescriptionModal'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			settingsStore : useSettingsStore()
		}
	},
	components : {
		CoreTooltip,
		SvgAiContent,
		MetaTitleModal,
		MetaDescriptionModal
	},
	props : {
		feature : {
			type     : Object,
			required : true
		}
	},
	data () {
		return {
			strings : {
				useAiGenerator : __('Use AI Generator', td)
			},
			showModal : false
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