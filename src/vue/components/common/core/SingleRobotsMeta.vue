<template>
	<div class="aioseo-robots-meta">
		<base-toggle
			v-model="postEditorStore.currentPost.default"
			@update:modelValue="postEditorStore.isDirty = true"
		>
			{{ strings.useDefaultSettings }}
		</base-toggle>

		<div
			v-if="!postEditorStore.currentPost.default"
			class="global-robots-settings aioseo-description"
		>
			<span class="robots-meta-title">{{ strings.robotsMeta }}</span>
			<grid-row
				class="settings"
			>
				<grid-column
					xl="3"
					md="4"
					sm="6"
					v-if="!postEditorStore.currentPost.isHomePage"
				>
					<base-checkbox
						size="medium"
						v-model="postEditorStore.currentPost.noindex"
						@update:modelValue="postEditorStore.isDirty = true"
					>
						{{ strings.noindex }}
					</base-checkbox>
				</grid-column>
				<grid-column
					xl="3"
					md="4"
					sm="6"
				>
					<base-checkbox
						size="medium"
						v-model="postEditorStore.currentPost.nofollow"
						@update:modelValue="postEditorStore.isDirty = true"
					>
						{{ strings.nofollow }}
					</base-checkbox>
				</grid-column>
				<grid-column
					xl="3"
					md="4"
					sm="6"
				>
					<base-checkbox
						size="medium"
						v-model="postEditorStore.currentPost.noarchive"
						@update:modelValue="postEditorStore.isDirty = true"
					>
						{{ strings.noarchive }}
					</base-checkbox>
				</grid-column>
				<grid-column
					xl="3"
					md="4"
					sm="6"
				>
					<base-checkbox
						size="medium"
						v-model="postEditorStore.currentPost.notranslate"
						@update:modelValue="postEditorStore.isDirty = true"
					>
						{{ strings.notranslate }}
					</base-checkbox>
				</grid-column>
				<grid-column
					xl="3"
					md="4"
					sm="6"
				>
					<base-checkbox
						size="medium"
						v-model="postEditorStore.currentPost.noimageindex"
						@update:modelValue="postEditorStore.isDirty = true"
					>
						{{ strings.noimageindex }}
					</base-checkbox>
				</grid-column>
				<grid-column
					xl="3"
					md="4"
					sm="6"
				>
				<base-checkbox
						size="medium"
						v-model="postEditorStore.currentPost.nosnippet"
						@update:modelValue="postEditorStore.isDirty = true"
					>
						{{ strings.nosnippet }}
					</base-checkbox>
				</grid-column>
				<grid-column
					xl="3"
					md="4"
					sm="6"
				>
					<base-checkbox
						size="medium"
						v-model="postEditorStore.currentPost.noodp"
						@update:modelValue="postEditorStore.isDirty = true"
					>
						{{ strings.noodp }}
					</base-checkbox>
				</grid-column>
			</grid-row>

			<div class="global-robots-settings-options">
				<div
					v-if="!postEditorStore.currentPost.nosnippet"
					class="aioseo-description max-snippet"
				>
					<span>{{ strings.maxSnippet }}</span>
					<base-input
						type="number"
						size="medium"
						v-model="postEditorStore.currentPost.maxSnippet"
						@update:modelValue="postEditorStore.isDirty = true"
					/>
				</div>

				<div
					class="aioseo-description max-video-preview"
				>
					<span>{{ strings.maxVideoPreview }}</span>
					<base-input
						type="number"
						size="medium"
						v-model="postEditorStore.currentPost.maxVideoPreview"
						@update:modelValue="postEditorStore.isDirty = true"
					/>
				</div>

				<div
					v-if="!postEditorStore.currentPost.noimageindex"
					class="aioseo-description max-image-preview"
				>
					<span>{{ strings.maxImagePreview }}</span>
					<base-select
						size="medium"
						:options="imagePreviewOptions"
						:modelValue="getImagePreview(postEditorStore.currentPost.maxImagePreview)"
						@update:modelValue="value => saveImagePreview(value.value)"
					/>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		BaseCheckbox,
		GridColumn,
		GridRow
	},
	data () {
		return {
			strings : {
				useDefaultSettings : this.$t.__('Use Default Settings', this.$td),
				robotsMeta         : this.$t.__('Robots meta:', this.$td),
				maxSnippet         : this.$t.__('Max Snippet', this.$td),
				maxVideoPreview    : this.$t.__('Max Video Preview', this.$td),
				maxImagePreview    : this.$t.__('Max Image Preview', this.$td),
				standard           : this.$t.__('Standard', this.$td),
				none               : this.$t.__('None', this.$td),
				large              : this.$t.__('Large', this.$td),
				noindex            : this.$t.__('No Index', this.$td),
				nofollow           : this.$t.__('No Follow', this.$td),
				noarchive          : this.$t.__('No Archive', this.$td),
				notranslate        : this.$t.__('No Translate', this.$td),
				noimageindex       : this.$t.__('No Image Index', this.$td),
				nosnippet          : this.$t.__('No Snippet', this.$td),
				noodp              : this.$t.__('No ODP', this.$td)
			}
		}
	},
	computed : {
		imagePreviewOptions () {
			return [
				{ label: this.strings.none, value: 'none' },
				{ label: this.strings.standard, value: 'standard' },
				{ label: this.strings.large, value: 'large' }
			]
		}
	},
	methods : {
		getImagePreview (option) {
			return this.imagePreviewOptions.find(h => h.value === option)
		},
		saveImagePreview (value) {
			this.postEditorStore.currentPost.maxImagePreview = value
			this.postEditorStore.isDirty                     = true
		}
	}
}
</script>

<style lang="scss">
.aioseo-robots-meta {

	.global-robots-settings {
		margin-top: 16px;
		font-weight: $font-bold;

		> .settings {
			margin-top: 12px;
			font-weight: 400;

			--aioseo-gutter: 12px;

			@include aioseoGrid(4, 150px);

			.aioseo-col {
				max-width: none;
			}
		}
	}

	.global-robots-settings-options {
		display: flex;
		gap: 12px;
		margin-top: 16px;

		> .aioseo-description {
			margin: 0;

			.aioseo-input,
			.aioseo-select {
				min-width: 200px;
				font-weight: 400;
				margin-top: 4px;
			}
		}

		@media screen and (max-width: 782px) {
			display: block;

			.max-snippet,
			.max-video-preview {
				margin-right: 0;
				margin-bottom: 20px;
			}

			> div {

				.aioseo-input,
				.aioseo-select {
					min-width: 100%;
				}
			}
		}
	}
}

.edit-post-sidebar,
.editor-sidebar {
	.global-robots-settings {
		padding-top: 12px;

		> .settings {
			padding: 4px 0 12px;

			label {
				font-size: 14px;
			}
		}
		.robots-meta-title {
			padding-top: 4px;
			display: inline-block;
		}
	}
	.global-robots-settings-options {
		flex-wrap: wrap;
	}
	.max-snippet {
		margin-right: 30px !important;
	}
	.max-video-preview {
		margin-right: 0 !important;
	}
	.max-image-preview {
		margin-top: 20px !important;
	}
}
</style>