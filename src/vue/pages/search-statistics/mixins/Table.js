import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import dateFormat from '@/vue/utils/dateFormat'

export default {
	data () {
		return {
			orderBy         : 'clicks',
			orderDir        : 'desc',
			resultsPerPage  : 10,
			selectedFilters : {}
		}
	},
	methods : {
		getSeries (row) {
			if (2 > row.graph?.length) {
				return []
			}

			const uniquePositions = []
			row.graph.forEach((tick) => {
				if (!uniquePositions.includes(tick.position)) {
					uniquePositions.push(tick.position)
				}
			})

			if (1 === uniquePositions.length) {
				return []
			}

			const rootStore = useRootStore()
			return [ {
				name : this.strings.position,
				data : row.graph?.map((tick) => ({
					x : dateFormat(new Date(tick.date + ' 00:00:00'), rootStore.aioseo.data.dateFormat),
					y : tick.position
				}))
			} ]
		},
		openPostDetail (post) {
			this.$router.push({
				name  : 'post-detail',
				query : {
					postId        : post.postId,
					previousRoute : this.$route.name
				}
			})
		},
		processFilter (filter) {
			const licenseStore = useLicenseStore()
			this.showUpsell = (!this.$isPro || licenseStore.isUnlicensed) && 'all' !== filter.slug
			this.processFilterTable(filter)
		},
		processAdditionaFilterOptionSelected ({ name, selectedValue }) {
			this.selectedFilters[name] = selectedValue
		}
	},
	mounted () {
		this.orderBy  = this.defaultSorting?.orderBy || this.orderBy
		this.orderDir = this.defaultSorting?.orderDir || this.orderDir
	}
}