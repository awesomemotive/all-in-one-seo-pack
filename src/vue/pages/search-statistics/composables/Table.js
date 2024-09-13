import { ref } from 'vue'
import { useRouter } from 'vue-router'

import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

export const useTable = (params = {}) => {
	const {
		processFilterTable,
		showUpsell
	} = params

	const orderBy        = ref('clicks')
	const orderDir       = ref('desc')
	const resultsPerPage = ref(10)

	const router = useRouter()
	const openPostDetail = (post) => {
		router.push({
			name  : 'post-detail',
			query : {
				postId        : post.objectId,
				previousRoute : router.currentRoute.value.name
			}
		})
	}

	const rootStore     = useRootStore()
	const licenseStore  = useLicenseStore()
	const processFilter = (filter) => {
		showUpsell.value = (!rootStore.isPro || licenseStore.isUnlicensed) && 'all' !== filter.slug
		processFilterTable(filter)
	}

	return {
		openPostDetail,
		orderBy,
		orderDir,
		processFilter,
		resultsPerPage
	}
}