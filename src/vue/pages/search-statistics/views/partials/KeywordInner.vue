<template>
	<div class="keyword-inner">
		<div class="keyword-inner-loading" v-if="loading" >
			<core-loader dark />
		</div>

		<core-wp-table
			v-if="row.pages && ! loading"
			ref="table"
			class="posts-table"
			:key="1"
			:columns="postColumns"
			:rows="row.pages"
			:show-header="false"
			:show-bulk-actions="false"
			:show-table-footer="false"
		>
			<template #post_title="{ row }">
				<div class="post-title">
					<a
						v-if="row.postId"
						href="#"
						@click.prevent="openPostDetail(row)"
					>
						{{ row.objectTitle }}
					</a>

					<span
						v-else
						class="post-title"
					>
						{{ row.objectTitle }}
					</span>
				</div>
				<div
					class="row-actions"
					v-if="row.postId"
				>
					<span>
						<a
							class="view"
							:href="row.context.permalink"
							target="_blank"
						>
							<span>{{ viewPost(row.context.postType.singular) }}</span>
						</a> |
					</span>

					<span>
						<a
							class="edit"
							:href="row.context.editLink"
							target="_blank"
						>
							<span>{{ editPost(row.context.postType.singular) }}</span>
						</a>
					</span>
				</div>
			</template>

			<template #clicks="{ row }">
				{{ numbers.compactNumber(row.clicks) }}
			</template>

			<template #ctr="{ row }">
				{{ parseFloat(row.ctr) }}%
			</template>

			<template #impressions="{ row }">
				{{ numbers.compactNumber(row.impressions) }}
			</template>

			<template #position="{ row }">
				<statistic
					v-if="row.difference.comparison"
					type="position"
					:total="row.position"
					:difference="row.difference.position"
					:tooltip-offset="'-150px,0'"
				/>
			</template>
		</core-wp-table>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import { usePostTypes } from '@/vue/composables/PostTypes'

import numbers from '@/vue/utils/numbers'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Statistic from './Statistic'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			editPost,
			viewPost
		} = usePostTypes()

		return {
			editPost,
			searchStatisticsStore : useSearchStatisticsStore(),
			viewPost
		}
	},
	components : {
		CoreLoader,
		CoreWpTable,
		Statistic
	},
	props : {
		index : {
			type     : Number,
			required : true
		},
		postDetail : {
			type     : Boolean,
			required : false,
			default  : false
		}
	},
	data () {
		return {
			numbers,
			loading : false
		}
	},
	computed : {
		postColumns () {
			return [
				{
					slug  : 'post_title',
					label : __('Title', td),
					width : '100%'
				},
				{
					slug  : 'clicks',
					label : __('Clicks', td),
					width : '120px'
				},
				{
					slug  : 'ctr',
					label : __('Avg. CTR', td),
					width : '120px'
				},
				{
					slug  : 'impressions',
					label : __('Impressions', td),
					width : '120px'
				},
				{
					slug  : 'position',
					label : __('Position', td),
					width : '120px'
				}
			]
		},
		keywords () {
			if (this.postDetail) {
				return this.searchStatisticsStore.data.postDetail.keywords.paginated.rows
			}

			return this.searchStatisticsStore.data.keywords.paginated.rows
		},
		row () {
			return this.keywords[this.index]
		}
	},
	methods : {
		openPostDetail (post) {
			this.$router.push({
				path  : '/post-detail',
				query : {
					postId        : post.postId,
					previousRoute : this.$route.name
				}
			})
		}
	},
	mounted () {
		if (!this.row || this.row?.pages?.length) {
			return
		}

		this.loading = true
		this.searchStatisticsStore.getPagesByKeywords([ this.row.keyword ]).then(data => {
			this.loading = false

			const pages = data[this.row.keyword]
			if (!pages) {
				return
			}

			if (this.postDetail) {
				this.searchStatisticsStore.data.postDetail.keywords.paginated.rows[this.index].pages = Object.values(pages).slice(0, 10)
			} else {
				this.searchStatisticsStore.data.keywords.paginated.rows[this.index].pages = Object.values(pages).slice(0, 10)
			}
		})
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-keywords-table .keyword-inner {
	&-loading {
		padding: 20px;

		.aioseo-loading-spinner {
			position: relative;
			margin: 0 auto;
		}
	}

	.aioseo-wp-table .wp-table {
		td.manage-column,
		th.manage-column {
			padding: 8px 10px;
		}
	}
}
</style>