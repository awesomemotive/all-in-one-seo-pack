import {
	useNotificationsStore,
	useRootStore
} from '@/vue/stores'

export const Url = {
	data () {
		return {
			button1Loading : false,
			button2Loading : false
		}
	},
	methods : {
		getHref (href) {
			if (!href) {
				return ''
			}

			let url = href
			if (this.isRoute(href)) {
				const actionArray      = href.split('#')
				const actionArrayParts = actionArray[1].split(':')

				const rootStore = useRootStore()
				url = rootStore.aioseo.urls.aio.dashboard.replace('page=aioseo', `page=${actionArrayParts[0]}#/${actionArrayParts[1]}`)
			}
			return url
		},
		getTarget (action) {
			return this.isRoute(action) ? null : '_blank'
		},
		isRoute (action) {
			return action.startsWith('http://route#') || action.startsWith('https://route#')
		},
		isAction (action) {
			return action.startsWith('http://action#') || action.startsWith('https://action#')
		},
		getTagType (action) {
			return this.isAction(action) ? 'button' : 'a'
		},
		processButtonClick (action, button) {
			this[`button${button}Loading`] = true

			if (this.isAction(action)) {
				return this.processAction(action, button)
			}

			if (this.isRoute(action)) {
				return this.processRoute(action, button)
			}

			this[`button${button}Loading`] = false
		},
		processAction (action, button) {
			const pattern = /[^#?]*/gm
			const matches = action.match(pattern)
			const slug    = matches[2]

			const notificationsStore = useNotificationsStore()
			notificationsStore.processButtonAction(slug)
				.then(() => {
					if (matches[4] && matches[4].startsWith('redirect=')) {
						const rootStore        = useRootStore()
						const actionArrayParts = matches[4].replace('redirect=', '').split(':')
						const url              = rootStore.aioseo.urls.aio.dashboard.replace('page=aioseo', `page=${actionArrayParts[0]}#/${actionArrayParts[1]}`)
						if (url === window.location.href) {
							window.location.reload()
							return
						}
						window.location.href = url
					} else if (matches[4] && matches[4].startsWith('refresh')) {
						window.location.reload()
					} else {
						this[`button${button}Loading`] = false
					}
				})
		},
		processRoute (action) {
			const rootStore = useRootStore()

			const pattern = /[^#?]*/gm
			const matches = action.match(pattern)
			const slug    = matches[2].split(':')
			const url     = rootStore.aioseo.urls.aio.dashboard.replace('page=aioseo', `page=${slug[0]}#/${slug[1]}`)
			if (url === window.location.href) {
				window.location.reload()
			}
		}
	}
}