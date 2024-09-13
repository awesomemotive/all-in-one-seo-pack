<template>
	<util-draggable
		ref="modal-container"
		:completelyDraggable="false"
	>
		<div
			class="aioseo-pagebuilder-modal"
			:class="{'aioseo-pagebuilder-modal-is-closed' : !isOpen}"
			>
			<div
				class="aioseo-pagebuilder-modal-header"
				@mousedown.prevent="mouseEvent => $refs['modal-container'].dragMouseDown(mouseEvent)"
			>
				<div class="aioseo-pagebuilder-modal-header-title">{{ strings.header }}</div>

				<core-score-button
					v-if="score && truSeoShouldAnalyze()"
					:score="score"
					class="aioseo-score-button--active"
				/>

				<div
					class="aioseo-pagebuilder-modal-header-close"
					v-on:click="$emit('update:is-open', false)"
				>
					<svg-close />
				</div>
			</div>

			<div class="aioseo-pagebuilder-modal-body edit-post-sidebar editor-sidebar">
				<PostSettings />
			</div>
		</div>
	</util-draggable>
</template>

<script>
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import PostSettings from '@/vue/standalone/post-settings/App'
import SvgClose from '@/vue/components/common/svg/Close'
import UtilDraggable from '@/vue/components/common/util/Draggable'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'update:is-open' ],
	setup () {
		return {
			truSeoShouldAnalyze
		}
	},
	components : {
		CoreScoreButton,
		PostSettings,
		SvgClose,
		UtilDraggable
	},
	props : {
		isOpen : {
			type    : Boolean,
			default : false
		},
		score : {
			type    : Number,
			default : 0
		}
	},
	data () {
		return {
			strings : {
				header : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('%1$s Settings', td),
					import.meta.env.VITE_SHORT_NAME
				)
			}
		}
	},
	methods : {
		toggleModal () {
			this.isOpen = !this.isOpen
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-pagebuilder-modal {
	.aioseo-score-button {
		margin-left: auto;
		margin-right: 20px;
	}
}
</style>