import {
	useSearchStatisticsStore
} from '@/vue/stores'

export const GoogleSearchConsole = {
	data () {
		return {
			forceDisconnect : false,
			showModal       : false,
			showAlert       : false,
			loading         : false,
			alertMessage    : '',
			returnTo        : 'search-statistics'
		}
	},
	methods : {
		connect () {
			this.loading = true

			const searchStatisticsStore = useSearchStatisticsStore()
			searchStatisticsStore.getAuthUrl({ returnTo: this.returnTo })
				.then(url => {
					window.location = url
				})
		},
		reconnect () {
			this.loading = true

			const searchStatisticsStore = useSearchStatisticsStore()
			searchStatisticsStore.getReauthUrl({ returnTo: this.returnTo })
				.then(url => {
					window.location = url
				})
		},
		disconnect () {
			this.loading = true

			const searchStatisticsStore = useSearchStatisticsStore()
			searchStatisticsStore.deleteAuth({
				force : this.forceDisconnect
			}).then(response => {
				this.showModal = false
				this.showAlert = false

				if (!response.success) {
					this.forceDisconnect = true
					this.showAlert       = true
					this.alertMessage    = response.message
				}
			}).finally(() => {
				this.loading = false
			})
		}
	}
}