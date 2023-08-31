import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

export const SaveChanges = {
	emits   : [ 'changes-saved' ],
	methods : {
		processSaveChanges () {
			const rootStore   = useRootStore()
			rootStore.loading = true

			let switchBack = false,
				saved      = false,
				action     = 'saveChanges'

			setTimeout(() => {
				switchBack = true
				if (saved) {
					rootStore.loading = false
				}
			}, 1500)

			const optionsStore = useOptionsStore()
			if ('htaccess-editor' === this.$router.currentRoute.value.name) {
				action = 'saveHtaccess'

				optionsStore.htaccessError = null
			}

			if (
				rootStore.aioseo.data.isNetworkAdmin &&
				'robots-editor' === this.$router.currentRoute.value.name
			) {
				action = 'saveNetworkRobots'
			}

			optionsStore[action]()
				.then(response => {
					if (response && response.body.redirection) {
						return
					}

					if (switchBack || 'htaccess-editor' === this.$router.currentRoute.value.name) {
						rootStore.loading = false
					} else {
						saved = true
					}

					window.aioseoBus.$emit('changes-saved')
				})
		}
	}
}