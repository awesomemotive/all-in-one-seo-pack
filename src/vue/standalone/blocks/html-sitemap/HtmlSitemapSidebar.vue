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
					:modelValue="getSortOrder(this.$root.$data.order_by)"
					@update:modelValue="values => this.$root.$data.order_by = values.value"
					track-by="value"
				/>
			</div>

			<div class="aioseo-sidebar-row">
				<p class="aioseo-sidebar-title">{{ strings.sortDirection }}</p>
				<base-select
					size="medium"
					:options="sortDirections"
					:modelValue="getSortDirection(this.$root.$data.order)"
					@update:modelValue="values => this.$root.$data.order = values.value"
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
export default {
	components : {
		HtmlSitemapExcludeObjects,
		HtmlSitemapIncludedObjects
	},
	data () {
		return {
			sortDirections : [
				{ label: this.$t.__('Ascending', this.$td), value: 'asc' },
				{ label: this.$t.__('Descending', this.$td), value: 'desc' }
			],
			sortOrders : [
				{ label: this.$t.__('Publish Date', this.$td), value: 'publish_date' },
				{ label: this.$t.__('Last Updated', this.$td), value: 'last_updated' },
				{ label: this.$t.__('Alphabetical', this.$td), value: 'alphabetical' },
				{ label: this.$t.__('Post/Term ID', this.$td), value: 'id' }
			],
			strings : {
				useDefault           : this.$t.__('Use Default Settings', this.$td),
				postTypes            : this.$t.__('Post Types', this.$td),
				taxonomies           : this.$t.__('Taxonomies', this.$td),
				includeAllPostTypes  : this.$t.__('Include All Post Types', this.$td),
				includeAllTaxonomies : this.$t.__('Include All Taxonomies', this.$td),
				showLabel            : this.$t.__('Show Labels', this.$td),
				publicationDate      : this.$t.__('Show Publication Date', this.$td),
				// nofollow        : this.$t.__('No Follow Links', this.$td),
				archives             : this.$t.__('Compact Archives', this.$td),
				sortOrder            : this.$t.__('Sort Order', this.$td),
				sortDirection        : this.$t.__('Sort Direction', this.$td),
				excludePostsPages    : this.$t.__('Exclude Posts / Pages', this.$td),
				excludeTerms         : this.$t.__('Exclude Terms', this.$td)
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