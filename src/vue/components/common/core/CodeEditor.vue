<template>
	<div
		:id="editorId"
		class="aioseo-code-editor"
	/>
</template>

<script>
import { basicSetup, EditorView } from 'codemirror'
import { json } from '@codemirror/lang-json'
import { placeholder } from '@codemirror/view'

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
	computed : {
		languageExtension () {
			switch (this.language) {
				case 'json':
				default:
					return json()
			}
		}
	},
	mounted () {
		const context = this

		const extensions = [
			basicSetup,
			this.languageExtension,
			EditorView.domEventHandlers({
				paste (event, editorView) {
					context.$emit('paste', {
						event,
						editorView
					})
				},
				blur (event, editorView) {
					context.$emit('blur', {
						event,
						editorView
					})
				},
				input (_event, editorView) {
					context.$emit('change', editorView.viewState.state.doc.toString())
				}
			}),
			placeholder(context.placeholder)
		]

		if (this.readonly) {
			extensions.push(EditorView.editable.of(false))
		}

		this.$nextTick(() => {
			this.editorInstance = new EditorView({
				doc        : this.value,
				extensions : extensions,
				parent     : document.getElementById(context.editorId)
			})
		})
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-code-editor {
	display: flex;
	flex : 1;
	flex-direction: column;

	font-family: monospace;

	.cm-editor {
		display: flex;
		flex : 1;
		flex-direction: column;

		.cm-scroller {
			position: absolute;
			width: 100%;
		}
	}

	.cm-editor,
	.cm-gutters {
		border-radius: 3px;;
	}

	.cm-focused {
		border-color: $blue;
		box-shadow: 0 0 0 1px $blue;
		outline: 1px solid $blue;
	}
}
</style>