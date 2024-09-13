import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

export const useSaveChanges = () => {
	const processSaveChanges = (routeName) => {
		window.aioseoBus.$emit('saving-changes')

		const rootStore = useRootStore()
		rootStore.loading = true

		let switchBack = false,
			saved = false,
			action = 'saveChanges'

		setTimeout(() => {
			switchBack = true
			if (saved) {
				rootStore.loading = false
			}
		}, 1500)

		const optionsStore = useOptionsStore()
		if ('htaccess-editor' === routeName) {
			action = 'saveHtaccess'
			optionsStore.htaccessError = null
		}

		if (rootStore.aioseo.data.isNetworkAdmin && 'robots-editor' === routeName) {
			action = 'saveNetworkRobots'
		}

		optionsStore[action]()
			.then(response => {
				if (response && response.body.redirection) {
					return
				}

				if (switchBack || 'htaccess-editor' === routeName) {
					rootStore.loading = false
				} else {
					saved = true
				}

				window.aioseoBus.$emit('changes-saved')
			})
	}

	return {
		processSaveChanges
	}
}