<template>
	<div class="aioseo-html-tags-editor">
		<core-alert-unfiltered-html v-if="checkUnfilteredHtml" />

		<div v-if="!disabled">
			<div
				v-if="showTags && allowTags && tagsDescription.length"
				class="aioseo-description tags-description"
			>
				<slot name="tags-description">
					{{ tagsDescription }}
				</slot>
			</div>

			<div
				v-if="showTags && allowTags"
				class="add-tags"
			>
				<core-add-template-tag
					v-for="(tag, index) in filteredTags"
					:key="index"
					@click.native="insertTag(tag.id)"
				>
					{{ tag.name }}
				</core-add-template-tag>

				<div
					v-if="!disableEmoji"
				>
					<button
						class="aioseo-show-emoji-button"
						@click.prevent="showEmojiPicker = !showEmojiPicker"
					>
						😀
					</button>

					<core-emoji
						v-model:show="showEmojiPicker"
						@selected-emoji="insertSelectedEmoji"
					/>
				</div>

				<a
					v-if="showAllTagsLink"
					class="aioseo-view-all-tags"
					href="#"
					@click.prevent="insertTag()"
				>
					{{ strings.allTags }}&nbsp;&rarr;
				</a>
			</div>
		</div>

		<base-editor
			ref="editor"
			:modelValue="modelValue"
			@update:modelValue="value => $emit('update:modelValue', value)"
			:allow-tags="allowTags"
			:line-numbers="lineNumbers"
			:single="single"
			:disabled="disabled"
			:minimum-line-numbers="minimumLineNumbers"
			@counter="count => $emit('counter', count)"
			:tags-context="tagsContext"
			:default-menu-orientation="defaultMenuOrientation"
			:description="description"
		>
			<template #append-icon>
				<slot name="append-icon" />
			</template>

			<template #append-button>
				<slot name="append-button" />
			</template>
		</base-editor>
	</div>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { getCurrentInstance } from 'vue'
import tags from '@/vue/utils/tags'
import BaseEditor from '@/vue/components/common/base/Editor'
import CoreAddTemplateTag from '@/vue/components/common/core/AddTemplateTag'
import CoreAlertUnfilteredHtml from '@/vue/components/common/core/alert/UnfilteredHtml'
import CoreEmoji from '@/vue/components/common/core/Emoji'
export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	emits      : [ 'counter', 'update:modelValue' ],
	components : {
		BaseEditor,
		CoreAddTemplateTag,
		CoreAlertUnfilteredHtml,
		CoreEmoji
	},
	props : {
		single      : Boolean,
		lineNumbers : {
			type : Boolean,
			default () {
				return true
			}
		},
		minimumLineNumbers : {
			type : Number,
			default () {
				return 13
			}
		},
		allowTags : {
			type : Boolean,
			default () {
				return true
			}
		},
		showTags : {
			type : Boolean,
			default () {
				return true
			}
		},
		showAllTagsLink : {
			type : Boolean,
			default () {
				return true
			}
		},
		defaultTags : Array,
		modelValue  : {
			type    : String,
			default : ''
		},
		tagsContext            : String,
		defaultMenuOrientation : String,
		description            : Boolean,
		disableEmoji           : Boolean,
		tagsDescription        : {
			type : String,
			default () {
				const app = getCurrentInstance()

				return app.appContext.app.$t.__('Click on the tags below to insert variables into your template.', app.appContext.app.$td)
			}
		},
		checkUnfilteredHtml : {
			type : Boolean,
			default () {
				return false
			}
		},
		disabled : {
			type : Boolean,
			default (props) {
				const rootStore = useRootStore()
				return props.checkUnfilteredHtml && !rootStore.aioseo.user.unfilteredHtml
			}
		}
	},
	data () {
		return {
			showEmojiPicker : false,
			strings         : {
				allTags : this.$t.__('View all tags', this.$td)
			}
		}
	},
	computed : {
		filteredTags () {
			const tagContext = this.tagsContext ? tags.context(this.tagsContext) : this.rootStore.aioseo.tags.tags
			return !this.defaultTags
				? tagContext
					.filter(tag => !tag.deprecated)
					.slice(0, 3)
				: this.defaultTags.map(dt => tagContext.find(t => t.id === dt))
					.filter(tag => tag)
		}
	},
	methods : {
		insertTag (tagId) {
			this.$refs.editor.insertTag(tagId)
		},
		insertSelectedEmoji (emoji) {
			this.$refs.editor.insertToCursor(emoji.native)
		}
	}
}
</script>

<style lang="scss">
.aioseo-html-tags-editor {
	.no-access {
		margin-bottom: 20px;
	}

	.aioseo-description.tags-description {
		margin: 0 0 12px;
	}

	.add-tags {
		display: flex;
		align-items: center;
		margin-bottom: 12px;
		gap: 8px;

		button {
			height: 24px;
			background: #fff;
			border-radius: 3px;
			padding: 0 3px;
			line-height: 24px;
			color: $black;
			font-size: 14px;
			border: 1px solid $input-border;
			cursor: pointer;
			user-select: none;
			font-weight: 400;

			&:hover {
				background-color: $background;
			}
		}

		a {
			font-size: 12px;
			line-height: 18px;

			&.no-underline {
				padding-left: 10px;
			}
		}
	}
}
</style>