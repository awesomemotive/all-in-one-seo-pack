<template>
	<posts-table
		:posts="searchStatisticsStore.data?.seoStatistics?.pages?.topWinning || defaultPages"
		:columns="[ 'row', 'postTitle', 'seoScore', 'clicks', 'impressions', 'position', 'diffDecay' ]"
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