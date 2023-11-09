<template>
	<core-blur>
		<div class="aioseo-search-statistics-content-rankings">
			<grid-row>
				<grid-column>
					<core-alert
						class="description"
						type="blue"
						show-close
					>
						{{ strings.alert }}
					</core-alert>

					<div class="aioseo-search-statistics-content-rankings__title">
						<h2>{{ strings.title }}</h2>
					</div>

					<posts-table
						:posts="searchStatisticsStore.data?.contentRankings?.paginated || defaultPages"
						:columns="[ 'postTitle', 'indexStatus', 'lastUpdated', 'loss', 'drop', 'performance' ]"
						show-items-per-page
						show-table-footer
					/>
				</grid-column>
			</grid-row>
		</div>
	</core-blur>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreBlur from '@/vue/components/common/core/Blur'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import PostsTable from '../../partials/PostsTable'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreAlert,
		CoreBlur,
		GridColumn,
		GridRow,
		PostsTable
	},
	data () {
		return {
			strings : {
				title : this.$t.__('Content Rankings', this.$td),
				alert : this.$t.__('The Content Rankings report provides valuable insights into the performance of your content in search results and helps you optimize your posts for better results. This report is generated on a monthly basis, covering the past 12 months leading up to the current month. By regularly reviewing this report, you can identify trends in your post rankings and make informed decisions to improve your content\'s visibility and ultimately increase rankings in search results.', this.$td)
			},
			defaultPages : {
				rows   : [],
				totals : {
					page  : 0,
					pages : 0,
					total : 0
				}
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-content-rankings {
	&__title {
		h2 {
			font-weight: 700;
			font-size: 14px;
			line-height: 125%;
			color: $black2-hover;
		}
	}

	.aioseo-alert {
		margin-bottom: 20px;
	}

	.aioseo-search-statistics-post-table {
		margin-top: -25px;
	}
}
</style>