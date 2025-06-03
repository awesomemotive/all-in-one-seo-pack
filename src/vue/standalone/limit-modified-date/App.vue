<template>
	<div v-if="postEditorStore.currentPost.id">
		<span :class="{'components-checkbox-control__input-container--block-editor': isBlockEditor()}">
			<input
				id="aioseo-limit-modified-date-input"
				:class="{'components-checkbox-control__input': isBlockEditor()}"
				type="checkbox"
				v-model="postEditorStore.currentPost.limit_modified_date"
				@change="addLimitModifiedDateAttribute"
			/>

			<svg
				v-if="canShowSvg"
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				width="24"
				height="24"
				role="img"
				class="components-checkbox-control__checked"
				aria-hidden="true"
				focusable="false"
			>
				<path d="M18.3 5.6L9.9 16.9l-4.6-3.4-.9 1.2 5.8 4.3 9.3-12.6z" />
			</svg>
		</span>

		<label class="components-checkbox-control__label" for="aioseo-limit-modified-date-input">{{ strings.label }}</label>
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import { isBlockEditor, isWooCommerceProduct, isClassicEditor } from '@/vue/utils/context'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'standalone-update-post' ],
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	data () {
		return {
			strings : {
				label : __('Don\'t update the modified date', td)
			}
		}
	},
	watch : {
		'postEditorStore.currentPost.limit_modified_date' (newVal) {
			// Update the hidden aioseo-post-settings field.
			window.aioseoBus.$emit('standalone-update-post', { limit_modified_date: newVal })
		}
	},
	computed : {
		canShowSvg () {
			return (isBlockEditor() && this.postEditorStore.currentPost.limit_modified_date)
		}
	},
	methods : {
		addLimitModifiedDateAttribute () {
			if (!isBlockEditor()) {
				return
			}

			window.wp.data.dispatch('core/editor').editPost({
				aioseo_limit_modified_date : this.postEditorStore.currentPost.limit_modified_date
			})
		},
		isClassicEditor,
		isWooCommerceProduct,
		isBlockEditor
	}
}
</script>

<style lang="scss">
#aioseo-limit-modified-date > div {
	display: flex;
	align-items: center;
	gap: 4px;

	.components-checkbox-control__input-container {
		&--block-editor {
			height: 20px;
			width: 20px;
			margin-right: 8px;
			position: relative;

			input {
				height: 20px;
				width: 20px;

				&[type="checkbox"] {
					border-radius: 2px;

					&:checked {
						background: $blue5;
					}

					&:focus {
						box-shadow: 0 0 0 2px $white, 0 0 0 4px $blue5;
					}
				}
			}
		}
	}

	label {
		max-width: 100%;
	}
}
</style>