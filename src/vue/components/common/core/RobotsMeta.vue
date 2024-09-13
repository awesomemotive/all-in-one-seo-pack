<template>
	<div class="aioseo-robots-meta">
		<base-toggle
			v-model="options.default"
		>
			{{ strings.useDefaultSettings }}
		</base-toggle>

		<div
			v-if="!options.default"
			class="global-robots-settings aioseo-description"
		>
			{{ strings.robotsMeta }}
			<grid-row
				class="settings"
			>
				<grid-column
					v-for="(setting, index) in robotsSettings"
					:key="index"
					xl="3"
					md="4"
					sm="6"
				>
					<base-checkbox
						size="medium"
						v-model="options[setting.value]"
					>
						{{ setting.label }}
					</base-checkbox>
				</grid-column>
			</grid-row>

			<div class="global-robots-settings-options">
				<div
					v-if="!options.nosnippet"
					class="max-snippet aioseo-description"
				>
					{{ strings.maxSnippet }}
					<base-input
						type="number"
						size="medium"
						v-model="options.maxSnippet"
					/>
				</div>

				<div
					class="max-video-preview aioseo-description"
				>
					{{ strings.maxVideoPreview }}
					<base-input
						type="number"
						size="medium"
						v-model="options.maxVideoPreview"
					/>
				</div>

				<div
					v-if="!options.noimageindex"
					class="max-image-preview aioseo-description"
				>
					{{ strings.maxImagePreview }}
					<base-select
						size="medium"
						:options="imagePreviewOptions"
						:modelValue="getImagePreviewOption(options.maxImagePreview)"
						@update:modelValue="value => options.maxImagePreview = value.value"
					/>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		BaseCheckbox,
		GridColumn,
		GridRow
	},
	props : {
		options : {
			type     : Object,
			required : true
		},
		mainOptions : Object,
		global      : Boolean
	},
	data () {
		return {
			settings : [
				{ value: 'noindex', label: __('No Index', td) },
				{ value: 'nofollow', label: __('No Follow', td) },
				{ value: 'noarchive', label: __('No Archive', td) },
				{ value: 'notranslate', label: __('No Translate', td) },
				{ value: 'noimageindex', label: __('No Image Index', td) },
				{ value: 'nosnippet', label: __('No Snippet', td) },
				{ value: 'noodp', label: __('No ODP', td) }
			],
			globalSettings : [
				{ value: 'noindexPaginated', label: __('No Index Paginated', td) },
				{ value: 'nofollowPaginated', label: __('No Follow Paginated', td) },
				{ value: 'noindexFeed', label: __('No Index RSS Feeds', td) }
			],
			strings : {
				useDefaultSettings : __('Use Default Settings', td),
				robotsMeta         : __('Robots meta:', td),
				maxSnippet         : __('Max Snippet', td),
				maxVideoPreview    : __('Max Video Preview', td),
				maxImagePreview    : __('Max Image Preview', td),
				standard           : __('Standard', td),
				none               : __('None', td),
				large              : __('Large', td)
			}
		}
	},
	watch : {
		noindex (newVal) {
			if (this.mainOptions) {
				this.mainOptions.show = !newVal
			}
		},
		default (newVal) {
			if (!this.mainOptions) {
				return
			}

			if (newVal) {
				this.mainOptions.show = true
				return
			}

			this.mainOptions.show = !this.noindex
		}
	},
	computed : {
		robotsSettings () {
			return this.global ? this.settings.concat(this.globalSettings) : this.settings
		},
		noindex () {
			return this.options.noindex
		},
		default () {
			return this.options.default
		},
		imagePreviewOptions () {
			return [
				{ label: this.strings.none, value: 'none' },
				{ label: this.strings.standard, value: 'standard' },
				{ label: this.strings.large, value: 'large' }
			]
		}
	},
	methods : {
		getImagePreviewOption (option) {
			return this.imagePreviewOptions.find(o => o.value === option)
		}
	}
}
</script>

<style lang="scss">
.aioseo-robots-meta {

	.global-robots-settings.aioseo-description {
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
</style>