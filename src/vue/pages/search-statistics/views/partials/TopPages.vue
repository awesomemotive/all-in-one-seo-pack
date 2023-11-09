<template>
	<posts-table
		:posts="searchStatisticsStore.data?.seoStatistics?.pages?.topPages || defaultPages"
		:columns="[ 'row', 'postTitle', 'indexStatus', 'seoScore', 'clicks', 'impressions', 'position', 'diffPosition' ]"
		:isLoading="searchStatisticsStore.loading.seoStatistics"
		:show-header="false"
	/>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import PostsTable from './PostsTable'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		PostsTable
	},
	data () {
		return {
			defaultPages : {
				rows : []
			}
		}
	}
}
</script>