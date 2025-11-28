<template>
	<div class="aioseo-robots-meta">
		<base-toggle v-model="postEditorStore.currentPost.default">
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
						@update:modelValue="value => {postEditorStore.currentPost.maxImagePreview = value.value}"
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

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

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
				useDefaultSettings : __('Use Default Settings', td),
				robotsMeta         : __('Robots meta:', td),
				maxSnippet         : __('Max Snippet', td),
				maxVideoPreview    : __('Max Video Preview', td),
				maxImagePreview    : __('Max Image Preview', td),
				standard           : __('Standard', td),
				none               : __('None', td),
				large              : __('Large', td),
				noindex            : __('No Index', td),
				nofollow           : __('No Follow', td),
				noarchive          : __('No Archive', td),
				notranslate        : __('No Translate', td),
				noimageindex       : __('No Image Index', td),
				nosnippet          : __('No Snippet', td),
				noodp              : __('No ODP', td)
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