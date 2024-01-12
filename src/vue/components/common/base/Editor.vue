<template>
	<div class="aioseo-editor">
		<div
			:class="[
				{ 'aioseo-editor-line-numbers': lineNumbers },
				{ 'aioseo-editor-single'      : single },
				{ 'aioseo-editor-monospace'   : monospace },
				{ 'aioseo-editor-description' : description }
			]"
			ref="quill"
			@paste="emitPasteEvent"
		/>

		<div
			v-if="lineNumbers"
			ref="line-numbers"
			class="aioseo-line-numbers"
		/>

		<div
			class="aioseo-append-icon"
			v-if="$slots['append-icon']"
		>
			<slot name="append-icon" />
		</div>

		<div
			class="aioseo-append-button"
			v-if="$slots['append-button']"
		>
			<slot name="append-button" />
		</div>

		<template
			v-for="(tag, index) in tags.context(tagsContext)"
			:key="index"
		>
			<div
				v-show="false"
				ref="select-template"
			>
				<span
					class="aioseo-tag"
				>
					<span class="tag-name">{{ tag.name }}</span>

					<span
						v-if="tag.custom && tag.customValue"
						class="tag-custom"
					>
						- {{ tag.customValue }}
					</span>

					<span class="tag-toggle">
						<svg-caret />
					</span>
				</span>
			</div>

			<div
				v-show="false"
				ref="menu-template"
			>
				<div class="aioseo-tag-item">
					<div>
						<svg-plus />
					</div>

					<div>
						<div class="aioseo-tag-title">
							{{ tag.name }}
						</div>

						<div class="aioseo-tag-description">
							{{ tag.description }}
						</div>
					</div>
				</div>
			</div>
		</template>

		<div
			v-show="false"
			ref="tag-search"
		>
			<base-input
				size="medium"
				:placeholder="strings.searchPlaceholder"
				prependIcon="search"
			/>

			<!-- TODO: Figure out how to make this tooltip work with quill -->
			<svg-trash />
		</div>

		<div
			v-show="false"
			ref="tag-custom"
		>
			<base-input
				size="small"
				:placeholder="strings.enterCustomFieldName"
			/>
		</div>

		<div
			v-show="false"
			ref="documentation-div"
			v-html="$links.getDocLink(strings.learnMoreSmartTags, 'smartTags', true)"
		/>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import tags from '@/vue/utils/tags'
import { addTags, removeTags } from '@/vue/plugins/quill/quill-auto-tagger'
import Quill from 'quill'
import '@/vue/plugins/quill/quill-line-numbers'
import '@/vue/plugins/quill/quill-mention'
import 'quill/dist/quill.snow.css'
import '@/vue/plugins/quill/quill-clipboard'
import '@/vue/plugins/quill/quill-character-counter'
import '@/vue/plugins/quill/quill-auto-link'
import '@/vue/plugins/quill/quill-phrase-editor-formats'
import '@/vue/plugins/quill/quill-preserve-whitespace'
import BaseInput from '@/vue/components/common/base/Input'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgPlus from '@/vue/components/common/svg/Plus'
import SvgTrash from '@/vue/components/common/svg/Trash'

const QuillEditor = []

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore(),
			tagsStore       : useTagsStore()
		}
	},
	emits      : [ 'counter', 'selection-change', 'updateEditor', 'focus', 'blur', 'update:modelValue' ],
	components : {
		BaseInput,
		SvgCaret,
		SvgPlus,
		SvgTrash
	},
	props : {
		modelValue : {
			type    : String,
			default : ''
		},
		minimumLineNumbers : {
			type : Number,
			default () {
				return 5
			}
		},
		single                 : Boolean,
		lineNumbers            : Boolean,
		allowTags              : Boolean,
		disabled               : Boolean,
		tagsContext            : String,
		forceUpdates           : Boolean,
		monospace              : Boolean,
		defaultMenuOrientation : String,
		description            : Boolean,
		showToolbar            : Boolean,
		preserveWhitespace     : Boolean,
		autoLink               : {
			type : [ Object, Boolean ],
			default () {
				return false
			}
		}
	},
	data () {
		return {
			tags,
			localTags    : [],
			quill        : null,
			html         : '',
			insertExact  : false,
			cachedPhrase : '',
			strings      : {
				searchPlaceholder    : this.$t.__('Search for an item...', this.$td),
				enterCustomFieldName : this.$t.__('Enter a custom field/taxonomy name...', this.$td),
				learnMoreSmartTags   : this.$t.__('Learn more about Smart Tags', this.$td),
				removeSmartTag       : this.$t.__('Remove Smart Tag', this.$td)
			}
		}
	},
	watch : {
		disabled () {
			if (this.disabled) {
				QuillEditor[this._uid].disable()
			} else {
				QuillEditor[this._uid].enable()
			}
		},
		modelValue () {
			if (this.forceUpdates) {
				this.startup(true)
			}
		},
		'tagsStore.liveTags' : {
			deep : true,
			handler () {
				this.localTags       = this.getTags()
				const counter        = QuillEditor[this._uid].getModule('counter')
				if (counter) {
					counter.options.tags = this.localTags
					this.$emit('counter', counter.calculate())
				}
			}
		},
		tags : {
			deep : true,
			handler () {
				// Only update the tags if they have changed.
				const newTags = this.getTags()
				if (JSON.stringify(this.localTags) !== JSON.stringify(newTags)) {
					this.localTags = newTags
					this.startup(true)
				}
			}
		}
	},
	methods : {
		emitPasteEvent (event) {
			this.$emit('paste', event)
		},
		getTags () {
			const t = this.tagsContext
				? [ ...tags.context(this.tagsContext) ]
				: [ ...this.tagsStore.tags ]
					.filter(tag => !tag.deprecated)
			return t.map((item, index) => {
				const value = this.postEditorStore.currentPost ? (this.tagsStore.liveTags[item.id] || item.value) : item.value
				return {
					...item,
					valueText : value,
					value     : this.$refs['select-template'][index] ? this.$refs['select-template'][index].innerHTML : '',
					menuHtml  : this.$refs['menu-template'][index] ? this.$refs['menu-template'][index].innerHTML : ''
				}
			})
		},
		update () {
			if (this.allowTags) {
				const counter = QuillEditor[this._uid].getModule('counter')
				this.$emit('counter', counter.calculate())
			}

			let html = QuillEditor[this._uid].getText() ? QuillEditor[this._uid].root.innerHTML : ''

			const frag    = document.createRange().createContextualFragment(html)
			const fragNew = document.createRange().createContextualFragment('')
			frag.childNodes.forEach(node => {
				// Quill wraps everything in <p /> tags so we are going to loop through those.
				if ('P' !== node.tagName) {
					return
				}

				node.childNodes.forEach(n => {
					if (3 === n.nodeType) {
						fragNew.appendChild(n.cloneNode(true))
						return
					}

					if ('SPAN' === n.tagName && this.allowTags) {
						const aioTag = n.querySelector('.aioseo-tag')
						if (aioTag) {
							const name = aioTag.querySelector('.tag-name')
							if (name) {
								const tag = this.localTags.find(t => t.name === name.textContent)
								if (tag.custom) {
									const custom = aioTag.querySelector('.tag-custom')
									if (custom) {
										const newNode = document.createTextNode(`#${tag.id}-${custom.innerText.replace(' - ', '')}`)
										fragNew.appendChild(newNode)
										return
									}
								}
								const newNode = document.createTextNode(`#${tag.id}`)
								fragNew.appendChild(newNode)
							}
						}
					}
				})

				const br = document.createElement('br')
				fragNew.appendChild(br)
			})

			fragNew.normalize()

			const div = document.createElement('div')
			div.appendChild(fragNew.cloneNode(true))

			html = div.innerHTML.replace(/<br\s*[/]?>/gi, this.single ? '' : '\n').trim()

			// Trim off the spaces we might have appended after smart tags at startup.
			html = html.replace(/&nbsp;/gi, ' ').trim()
			this.$emit('update:modelValue', html)
		},
		insertToCursor (text) {
			QuillEditor[this._uid].focus()

			QuillEditor[this._uid].insertText(QuillEditor[this._uid].getSelection().index, text, Quill.sources.USER)
			QuillEditor[this._uid].setSelection(QuillEditor[this._uid].getSelection().index + text.length, Quill.sources.USER)
		},
		insertTag (tagId) {
			const mention = QuillEditor[this._uid].getModule('mention')
			mention.removeOrphanedMentionChar()

			const textBefore = mention.getTextBeforeCursor()
			this.insertExact = true
			const tag  = tagId ? this.localTags.find(t => t.id === tagId) : null
			let   text = tag ? `#${tag.id}` : '#' === textBefore.charAt(textBefore.length - 1) ? '' : '#'

			const delta = QuillEditor[this._uid].getContents(0, mention.cursorPos)
			if (
				delta.ops.length &&
				(
					'string' !== typeof delta.ops.pop().insert ||
					!textBefore.match(/\s$/)
				)
			) {
				// If there's no space in front of the tag yet, add one.
				// Also check if there actually is any text before the tag so that we don't add a space to the beginning of the value.
				text = ' ' + text
			}

			QuillEditor[this._uid].focus()

			if (tagId) {
				mention.removeOrphanedMentionChar()
			}

			QuillEditor[this._uid].insertText(QuillEditor[this._uid].getSelection().index, text, Quill.sources.USER)
			QuillEditor[this._uid].setSelection(QuillEditor[this._uid].getSelection().index + text.length, Quill.sources.USER)
			this.insertExact = false

			if (!tagId) {
				setTimeout(() => {
					mention.mentionCharPos = QuillEditor[this._uid].getSelection().index - 1
					mention.silentInsert   = true
					mention.showMentionList()
				}, 0)
			} else {
				mention.hideMentionList()
			}
		},
		maybeCloseMenu (event) {
			const element = event.target
			if (
				element.classList.contains('aioseo-tag') ||
				element.closest('.aioseo-tag') ||
				element.closest('.add-tags')
			) {
				return
			}

			if (element.classList.contains('ql-mention-list-container') || element.closest('.ql-mention-list-container')) {
				const prependIcon = element.classList.contains('prepend-icon') ? element : element.closest('.prepend-icon')
				if (prependIcon) {
					prependIcon.nextSibling.focus()
				}
				return
			}

			const mention = QuillEditor[this._uid].getModule('mention')
			if (mention.isOpen) {
				mention.hideMentionList()
				mention.removeOrphanedMentionChar()
			}
		},
		async startup (reset = false) {
			if (this.allowTags && !this.$refs['tag-search']) {
				return
			}

			QuillEditor[this._uid] = this.startQuill()

			if (reset) {
				QuillEditor[this._uid].setText('')
			}

			let value = this.modelValue
			if (value && value.length && value.match(/#[^\s]*$/)) {
				// If the value ends with a smart tag, append a space on startup to prevent weird input behaviour.
				// https://github.com/awesomemotive/aioseo/issues/1351
				value = value.trim() + '&nbsp;'
			}

			// Make sure newlines are kept intact.
			value = value
				? (
					this.single
						? value.replace('\n', ' ')
						: '<p>' +
							value.split('\n')
								.map(v => '' === v ? '<br>' : v)
								.join('</p><p>') +
							'</p>'
				)
				: value

			const delta = QuillEditor[this._uid].clipboard.convert(value)
			QuillEditor[this._uid].setContents(delta)

			const mention = QuillEditor[this._uid].getModule('mention')
			if (mention) {
				mention.removeOrphanedMentionChar(true)
			}

			if (this.allowTags) {
				const counter = QuillEditor[this._uid].getModule('counter')
				this.$emit('counter', counter.calculate())
			}

			this.removeTrailingNewLine()

			// This prevents the editor for turning dirty after we remove the trailing line.
			await this.$nextTick()

			// We will add the update event here
			QuillEditor[this._uid].on('text-change', () => this.update())
			QuillEditor[this._uid].on('selection-change', (range, oldRange, source) => {
				if ('api' === source) {
					this.update()
				}

				if (!range) {
					this.$emit('blur', QuillEditor[this._uid])
				} else {
					this.$emit('focus', QuillEditor[this._uid])
				}

				this.$emit('selection-change', {
					range,
					oldRange,
					source
				})
			})

			document.addEventListener('click', this.maybeCloseMenu)

			if (this.disabled) {
				QuillEditor[this._uid].disable()
			}

			if (!reset) {
				QuillEditor[this._uid].history.clear()
			}
		},
		startQuill () {
			return new Quill(this.$refs.quill, {
				modules : {
					toolbar     : !this.showToolbar ? [] : [ 'bold', 'italic', 'underline', 'autoLink'/* , { list: 'bullet' }, { list: 'ordered' } */ ],
					lineNumbers : this.lineNumbers
						? {
							container     : this.$refs['line-numbers'],
							defaultLength : this.minimumLineNumbers
						}
						: null,
					mention : this.allowTags
						? {
							defaultMenuOrientation    : this.defaultMenuOrientation || 'bottom',
							dataAttributes            : [ 'id', 'value', 'denotationChar', 'link', 'target', 'custom', 'customValue' ],
							allowedChars              : /^[A-Za-z\s_]*$/,
							mentionDenotationChars    : [ '#' ],
							spaceAfterInsert          : true,
							mentionPrependClass       : 'aioseo-tag-search',
							mentionPrependClassCustom : 'aioseo-tag-custom',
							prependMentionList        : this.$refs['tag-search'].innerHTML,
							customFieldInput          : this.$refs['tag-custom'].innerHTML,
							documentationDiv          : this.$refs['documentation-div'].innerHTML,
							listItemClassNoMatch      : 'aioseo-tag-no-match',
							renderItemNoMatch () {
								return 'No matches found'
							},
							renderItem (item) {
								return `${item.menuHtml}`
							},
							source : (searchTerm, renderList, mentionChar, returnItem = false, customValue = '') => {
								const localTags = [ ...this.localTags ]
								if (localTags[0].custom) {
									localTags[0].customValue = customValue
								}

								if (0 === searchTerm.length) {
									return renderList(localTags, searchTerm, returnItem, this.insertExact)
								}

								const matches = []
								for (let i = 0; i < localTags.length; i++) {
									if (
										~localTags[i].name.toLowerCase().indexOf(searchTerm.toLowerCase()) ||
									~localTags[i].id.toLowerCase().indexOf(searchTerm.toLowerCase())
									) { matches.push(localTags[i]) }
								}

								return renderList(matches, searchTerm, returnItem, this.insertExact)
							}
						}
						: {},
					counter : this.allowTags
						? {
							tags : this.localTags
						}
						: null,
					clipboard : {
						newLines : !this.single
					},
					autoLink : {
						enabled : !!this.autoLink,
						...this.autoLink

					},
					keyboard : {
						bindings : {
							enter : {
								key     : 13,
								handler : () => {
									return !this.single
								}
							}
						}
					},
					preserveWhiteSpace : this.preserveWhitespace
				},
				theme   : 'snow',
				formats : !this.showToolbar ? [ 'mention' ] : [ 'bold', 'underline', 'italic', 'link', 'list', 'autoLink', 'aioseoInline' ]
			})
		},
		setPhrase (value) {
			// We are caching the phrase at this point, so we can use it later to undo the next few lines.
			this.cachedPhrase = value

			value = addTags(value)
			value = value.replace(/<span([^>]*)>/g, '<aioseo-inline$1>').replace(/<\/span>/g, '</aioseo-inline>')

			const delta = QuillEditor[this._uid].clipboard.convert(value)
			QuillEditor[this._uid].setContents(delta)
		},
		getPhrase () {
			return QuillEditor[this._uid].getText()
		},
		getPhraseWithFormats () {
			return QuillEditor[this._uid].getContents()
		},
		getPhraseHtml () {
			let value = QuillEditor[this._uid].root.childNodes[0].innerHTML

			value = value.replace(/<aioseo-inline([^>]*)>/g, '<span$1>').replace(/<\/aioseo-inline>/g, '</span>')
			value = removeTags(this.cachedPhrase, value)

			return value
		},
		removeTrailingNewLine () {
			// Remove the trailing new line that Quill adds by default.
			const editor = document.querySelector('.aioseo-editor-description .ql-editor')
			if (this.description && editor) {
				editor.innerHTML = editor.innerHTML.replace(/<p><br><\/p>$/i, '')
			}
		}
	},
	mounted () {
		this.localTags = this.getTags()
		this.startup(true)

		if (this.tagsContext) {
			window.aioseoBus.$on('updateEditor' + this.tagsContext, (uid) => {
				if (uid !== this._uid) {
					this.startup(true)
				}
			})
		}
	},
	beforeUnmount () {
		document.removeEventListener('click', this.maybeCloseMenu)
	},
	unmounted () {
		if (this.tagsContext) {
			window.aioseoBus.$emit('updateEditor' + this.tagsContext, this._uid)
		}
	}
}
</script>

<style lang="scss">
.aioseo-editor {
	position: relative;

	.aioseo-append-icon {
		height: 24px;
		width: 24px;
		position: absolute;
		right: 0;
		top: 50%;
		transform: translateX(-50%) translateY(-50%);
		cursor: pointer;

		svg {
			// transform: rotate(-36deg);
			transition: transform .4s ease-in-out;
			color: $placeholder-color;
		}

		&:hover {
			svg {
				transform: rotate(360deg);
				color: $green;
			}
		}
	}

	.aioseo-append-button {

		// This fixes the button wrapper taking up space below the input.
		.aioseo-ai-generator {
			line-height: 0;

			> * {
				line-height: 22px;
			}
		}

		button {
			position: absolute;
			right: 4px;
			top: 4px;

			width: 32px;
			height: 32px;

			background-color: $background;
			border: 1px solid $input-border;
			border-radius: 4px;

			cursor: pointer;
		}
	}

	.aioseo-editor-description {
		.ql-editor {
			min-height: 100px;
		}
	}

	.aioseo-editor-line-numbers {
		.ql-editor {
			padding: 15px 15px 15px 45px;
		}
	}

	.aioseo-editor-single {
		.ql-editor {
			padding: 7px 10px;
		}

		&.aioseo-editor-line-numbers {
			.ql-editor {
				padding: 8px 10px 8px 45px;
			}
		}
	}

	.aioseo-editor-monospace {
		.ql-editor {
			font-family: monospace;
		}
	}

	.aioseo-line-numbers {
		background: #F7F6F7;
		position: absolute;
		text-align: right;
		top: 1px;
		width: 29px;
		left: 1px;
		border-radius: 3px 0 0 3px;
		padding: 15px 9px 0 0;
		display: flex;
		height: calc(100% - 2px);
		flex-direction: column;
		overflow: hidden;

		div {
			min-height: 25px;
			color: $placeholder-color;
			font-size: 12px;
			line-height: 1.9;
		}
	}

	.ql-disabled {
		pointer-events: none;
		background-color: $box-background;
	}

	.ql-toolbar.ql-snow {
		display: none;
	}

	.ql-editor {
		padding: 7px 10px;
		border-radius: 3px;
		font-size: $font-md;
		color: $black;
		border: 1px solid $input-border;
		height: auto;

		&:focus {
			border: 1px solid $blue;
			box-shadow: 0 0 0 1px $blue;
		}

		.mention {
			.ql-mention-denotation-char {
				display: none;
			}

			.aioseo-tag {
				height: 20px;
				margin: 0 1px;
				color: $black;
				font-weight: $font-bold;
				font-size: 12px;
				line-height: 18px;
				padding: 0 32px 0 8px;
				background-color: $background;
				border-radius: 3px;
				cursor: pointer;
				position: relative;
				display: inline-flex;
				align-items: center;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;

				.tag-toggle {
					display: inline-flex;
					align-items: center;
					justify-content: center;
					width: 24px;
					background-color: $border;
					position: absolute;
					top: 0;
					right: 0;
					bottom: 0;
					border-radius: 0px 3px 3px 0px;

					svg.aioseo-caret {
						width: 18px;
						height: 18px;
						transition: transform 0.3s;

						&.rotated {
							transform: rotate(180deg);
						}
					}
				}
			}
		}
	}

	.ql-mention-list-container {
		color: $black;
		background-color: #fff;
		max-width: 275px;
		width: 100%;
		margin-top: 3px;
		border: 1px solid $input-border;
		border-radius: 3px;
		box-shadow: 0px 3px 15px rgba(0, 0, 0, 0.1);
		z-index: 9001;

		.aioseo-tag-custom,
		.aioseo-tag-search {
			padding: 8px;
			border-bottom: 1px solid $border;
		}

		.aioseo-tag-search {
			display: flex;
			align-items: center;

			input {
				flex: 1;
			}

			.prepend-icon svg.aioseo-search {
				width: 12px;
			}

			.aioseo-trash {
				color: $placeholder-color;
				min-width: 24px;
				max-width: 24px;
				height: 24px;
				margin-left: 12px;

				&:hover {
					cursor: pointer;
					color: $red;
				}
			}
		}

		.aioseo-tag-custom {
			display: none;
		}

		.ql-mention-list {
			list-style: none;
			margin: 0;
			padding: 0;
			max-height: 210px;
			overflow: auto;

			li {
				color: $black;
				margin: 0;
				background-color: transparent;
				border-bottom: 1px solid $border;
				padding: 8px;
				cursor: pointer;
				font-size: 14px;

				&:last-child {
					border-bottom: 0;
				}

				&:hover,
				&.selected {
					color: $blue;
					background-color: $inline-background;

					.aioseo-tag-description {
						color: $black;
					}
				}

				.aioseo-tag-item {
					display: flex;

					> div:first-child:not(:last-child) {
						margin: 1px 13px 1px 5px;
					}

					.aioseo-tag-title {
						font-weight: $font-bold;

						+ .aioseo-tag-description {
							margin-top: 2px;
						}
					}
				}

				svg.aioseo-plus {
					width: 12px;
					height: 12px;
					color: $blue;
				}

				&.aioseo-tag-no-match {
					cursor: default;
					padding: 12px;
					font-size: 16px;
					font-weight: $font-bold;

					&:hover,
					&.highlight {
						color: initial;
						background-color: transparent;
					}
				}
			}
		}

		.aioseo-documentation-link {
			height: 39px;
			display: flex;
			align-items: center;
			padding: 12px;
			border-top: 1px solid #D0D1D7;
			font-size: 12px;
			font-weight: 700;
		}
	}

	.ql-clipboard {
		left: -100000px;
		height: 1px;
		overflow-y: hidden;
		position: absolute;
		top: 50%;
	}

	.ql-container {
		font-family: $font-family;
		height: auto;

		p {
			font-size: $font-md;
			margin: 0;
			line-height: 25px;
		}

		&.ql-snow {
			border: 0;
		}
	}
}
</style>