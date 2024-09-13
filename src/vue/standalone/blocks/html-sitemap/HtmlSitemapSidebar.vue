<template>
	<div>
		<div class="aioseo-sidebar-row">
			<base-toggle
				v-model="$root.$data.default"
			>
				{{ strings.useDefault }}
			</base-toggle>
		</div>

		<template v-if="!$root.$data.default">
			<div class="aioseo-sidebar-row">
				<base-toggle
					v-model="$root.$data.archives"
				>
					{{ strings.archives }}
				</base-toggle>
			</div>

			<div
				class="aioseo-sidebar-row"
				v-if="!$root.$data.archives"
			>
				<base-toggle
					v-model="$root.$data.show_label"
				>
					{{ strings.showLabel }}
				</base-toggle>
			</div>

			<div
				class="aioseo-sidebar-row"
				v-if="!$root.$data.archives"
			>
				<base-toggle
					v-model="$root.$data.publication_date"
				>
					{{ strings.publicationDate }}
				</base-toggle>
			</div>

			<!-- <div class="aioseo-sidebar-row">
				<base-toggle
					v-model="$root.$data.nofollow_links"
				>
					{{ strings.nofollow }}
				</base-toggle>
			</div> -->

			<div
				class="aioseo-sidebar-row"
				v-if="!$root.$data.archives"
			>
				<p class="aioseo-sidebar-title">{{ strings.postTypes }}</p>
				<html-sitemap-included-objects
					type="post_types"
					:excluded="[ 'attachment' ]"
				/>
			</div>

			<div
				class="aioseo-sidebar-row"
				v-if="!$root.$data.archives"
			>
				<p class="aioseo-sidebar-title">{{ strings.taxonomies }}</p>
				<html-sitemap-included-objects
					type="taxonomies"
				/>
			</div>

			<div
				class="aioseo-sidebar-row"
				v-if="!$root.$data.archives"
			>
				<p class="aioseo-sidebar-title">{{ strings.sortOrder }}</p>
				<base-select
					size="medium"
					:options="sortOrders"
					:modelValue="getSortOrder($root.$data.order_by)"
					@update:modelValue="values => $root.$data.order_by = values.value"
					track-by="value"
				/>
			</div>

			<div class="aioseo-sidebar-row">
				<p class="aioseo-sidebar-title">{{ strings.sortDirection }}</p>
				<base-select
					size="medium"
					:options="sortDirections"
					:modelValue="getSortDirection($root.$data.order)"
					@update:modelValue="values => $root.$data.order = values.value"
					track-by="value"
				/>
			</div>

			<div
				class="aioseo-sidebar-row"
				v-if="!$root.$data.archives"
			>
				<p class="aioseo-sidebar-title">{{ strings.excludePostsPages }}</p>

				<html-sitemap-exclude-objects
					type="posts"
				/>
			</div>

			<div
				class="aioseo-sidebar-row"
				v-if="!$root.$data.archives"
			>
				<p class="aioseo-sidebar-title">{{ strings.excludeTerms }}</p>

				<html-sitemap-exclude-objects
					type="terms"
				/>
			</div>
		</template>
	</div>
</template>

<script>
import HtmlSitemapExcludeObjects from '@/vue/components/common/html-sitemap/ExcludeObjects'
import HtmlSitemapIncludedObjects from '@/vue/components/common/html-sitemap/IncludedObjects'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		HtmlSitemapExcludeObjects,
		HtmlSitemapIncludedObjects
	},
	data () {
		return {
			sortDirections : [
				{ label: __('Ascending', td), value: 'asc' },
				{ label: __('Descending', td), value: 'desc' }
			],
			sortOrders : [
				{ label: __('Publish Date', td), value: 'publish_date' },
				{ label: __('Last Updated', td), value: 'last_updated' },
				{ label: __('Alphabetical', td), value: 'alphabetical' },
				{ label: __('Post/Term ID', td), value: 'id' }
			],
			strings : {
				useDefault           : __('Use Default Settings', td),
				postTypes            : __('Post Types', td),
				taxonomies           : __('Taxonomies', td),
				includeAllPostTypes  : __('Include All Post Types', td),
				includeAllTaxonomies : __('Include All Taxonomies', td),
				showLabel            : __('Show Labels', td),
				publicationDate      : __('Show Publication Date', td),
				// nofollow        : __('No Follow Links', td),
				archives             : __('Compact Archives', td),
				sortOrder            : __('Sort Order', td),
				sortDirection        : __('Sort Direction', td),
				excludePostsPages    : __('Exclude Posts / Pages', td),
				excludeTerms         : __('Exclude Terms', td)
			}
		}
	},
	methods : {
		getSortOrder (option) {
			return this.sortOrders.find(o => o.value === option)
		},
		getSortDirection (option) {
			return this.sortDirections.find(o => o.value === option)
		}
	}
}
</script>

<style lang="scss">
.block-editor-block-card {
	align-items: center;
}

.block-editor-block-card__title.block-editor-block-card__title {
	margin: 0;
}

.aioseo-sidebar-row {
	& + & {
		margin-top: 16px;
	}

	.aioseo-sidebar-title {
		font-weight: 500;
	}
}
</style>