<template>
	<div
		:id="editorId"
		class="aioseo-code-editor"
	/>
</template>

<script>
import { createEditor } from 'prism-code-editor'
import 'prism-code-editor/prism/languages/json'

export default {
	props : {
		editorId : {
			type     : String,
			required : true
		},
		language : {
			type : String,
			default () {
				return 'json'
			}
		},
		placeholder : {
			type : String,
			default () {
				return ''
			}
		},
		value : {
			type : String,
			default () {
				return ''
			}
		},
		readonly : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	data () {
		return {
			editorInstance : null
		}
	},
	methods : {
		/**
		 * Get the current editor value.
		 *
		 * @since 4.9.4
		 *
		 * @returns {string} The current editor content.
		 */
		getValue () {
			if (!this.editorInstance) {
				return ''
			}

			return this.editorInstance.value
		},
		/**
		 * Set the editor value programmatically.
		 *
		 * @since 4.9.4
		 *
		 * @param {string} newValue The new content to set.
		 * @returns {void}
		 */
		setValue (newValue) {
			if (!this.editorInstance) {
				return
			}

			this.editorInstance.setOptions({ value: newValue })
		}
	},
	mounted () {
		const parent = document.getElementById(this.editorId)
		if (!parent) {
			return
		}

		const context = this

		this.editorInstance = createEditor(
			parent,
			{
				language     : this.language,
				value        : this.value,
				readOnly     : this.readonly,
				lineNumbers  : true,
				tabSize      : 4,
				insertSpaces : false,
				onUpdate (value) {
					context.$emit('change', value)
				}
			}
		)

		// Set the native placeholder on the textarea.
		if (this.placeholder) {
			this.editorInstance.textarea.placeholder = this.placeholder
		}

		// Emit paste and blur events from the underlying textarea.
		this.editorInstance.textarea.addEventListener('paste', (event) => {
			this.$emit('paste', { event })
		})

		this.editorInstance.textarea.addEventListener('blur', (event) => {
			this.$emit('blur', { event })
		})
	},
	beforeUnmount () {
		if (this.editorInstance) {
			this.editorInstance.remove()
			this.editorInstance = null
		}
	}
}
</script>

<style lang="scss">
@import 'prism-code-editor/layout.css';
@import 'prism-code-editor/themes/github-light.css';

.aioseo-app .aioseo-code-editor {
	display: flex;
	flex: 1;
	flex-direction: column;

	.prism-code-editor {
		display: flex;
		flex: 1;
		flex-direction: column;
		border: 1px solid $border;
		border-radius: 3px;

		&.pce-focus {
			border-color: $blue;
			box-shadow: 0 0 0 1px $blue;
			outline: none;
		}
	}

	.pce-textarea {
		&:focus {
			box-shadow: none;
		}

		&::placeholder {
			color: $placeholder-color;
			opacity: 1;
		}
	}
}
</style>