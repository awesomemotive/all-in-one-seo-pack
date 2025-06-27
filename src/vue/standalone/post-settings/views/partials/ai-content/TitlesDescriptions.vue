<template>
	<div class="titles-descriptions">
		<div
			class="list"
			v-if="suggestions.length"
		>
			<div
				class="suggestions"
				:key="suggestionsKey"
			>
				<div
					class="suggestion"
					:class="[
						{'single' : type === 'title'}
					]"
					v-for="(suggestion, index) in suggestions"
					:key="index"
				>
					<p>{{suggestion}}</p>

					<button
						@click="setSuggestion(suggestion)"
						type="button"
					>
						<svg-circle-plus
							@click="setSuggestion(suggestion)"
						/>
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import { getPostEditedContent } from '@/vue/plugins/tru-seo/components/postContent'
import { getPostEditedExcerpt } from '@/vue/plugins/tru-seo/components/postExcerpt'
import { stripFullTags } from '@/app/tru-seo/analyzer/researches/stringProcessing/stripHTMLTags'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'

export default {
	emits : [ 'closeModal' ],
	setup () {
		return {
			getAssetUrl,
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore(),
			settingsStore   : useSettingsStore()
		}
	},
	components : {
		SvgCirclePlus
	},
	props : {
		type : {
			type     : String,
			required : true
		},
		suggestions : {
			type     : Array,
			required : true
		},
		generateFn : {
			type     : Function,
			required : true
		}
	},
	data () {
		return {
			loading              : false,
			triedFetchingResults : false,
			postContent          : null,
			suggestionsKey       : 0
		}
	},
	methods : {
		setSuggestion (value) {
			this.postEditorStore.isDirty = true

			if ('title' === this.type) {
				this.postEditorStore.updateTitle(value)
				this.$emit('closeModal')
				return
			}

			this.postEditorStore.updateDescription(value)
			this.$emit('closeModal')
		}
	},
	beforeMount () {
		this.postContent = stripFullTags(getPostEditedContent()).trim() || stripFullTags(getPostEditedExcerpt()).trim()
	}
}
</script>

<style lang="scss" scoped>
.titles-descriptions {
	.list {
		.aioseo-alert {
			margin: 16px 16px 0 16px;
		}

		.suggestions {
			display: flex;
			flex-direction: column;
			gap: 16px;
			max-height: calc(90vh - 120px);
			overflow-y: auto;

			.suggestion {
				display: flex;
				align-items: center;
				position: relative;
				padding: 7px 55px 7px 10px;
				border-radius: 3px;
				font-size: 14px;
				color: #141b38;
				border: 1px solid $border;
				height: auto;
				min-height: 42px;

				p {
					margin: 0;
				}

				button {
					display: flex;
					align-items: center;
					justify-content: center;
					position: absolute;
					top: 4px;
					right: 4px;
					width: 32px;
					height: 32px;
					background-color: $background;
					border: 1px solid $input-border;
					border-radius: 4px;
					cursor: pointer;

					svg {
						width: 14px;
						height: 14px;
					}
				}
			}
		}
	}
}
</style>