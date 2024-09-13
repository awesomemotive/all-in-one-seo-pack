import { ref } from 'vue'

import {
	useNotificationsStore,
	useRootStore
} from '@/vue/stores'

export const useUrl = () => {
	const button1Loading = ref(false)
	const button2Loading = ref(false)

	const getHref = (href) => {
		if (!href) {
			return ''
		}

		let url = href
		if (isRoute(href)) {
			const actionArray      = href.split('#')
			const actionArrayParts = actionArray[1].split(':')

			const rootStore = useRootStore()
			url = rootStore.aioseo.urls.aio.dashboard.replace('page=aioseo', `page=${actionArrayParts[0]}#/${actionArrayParts[1]}`)
		}
		return url
	}

	const getTarget = (action) => {
		return isRoute(action) ? null : '_blank'
	}

	const isRoute = (action) => {
		return action.startsWith('http://route#') || action.startsWith('https://route#')
	}

	const isAction = (action) => {
		return action.startsWith('http://action#') || action.startsWith('https://action#')
	}

	const getTagType = (action) => {
		return isAction(action) ? 'button' : 'a'
	}

	const processButtonClick = (action, button) => {
		[ `button${button}Loading` ].value = true

		if (isAction(action)) {
			return processAction(action, button)
		}

		if (isRoute(action)) {
			return processRoute(action)
		}

		[ `button${button}Loading` ].value = false
	}

	const processAction = (action, button) => {
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
					[ `button${button}Loading` ].value = false
				}
			})
	}

	const processRoute = (action) => {
		const rootStore = useRootStore()

		const pattern = /[^#?]*/gm
		const matches = action.match(pattern)
		const slug    = matches[2].split(':')
		const url     = rootStore.aioseo.urls.aio.dashboard.replace('page=aioseo', `page=${slug[0]}#/${slug[1]}`)
		if (url === window.location.href) {
			window.location.reload()
		}
	}

	return {
		button1Loading,
		button2Loading,
		getHref,
		getTagType,
		getTarget,
		processAction,
		processButtonClick,
		processRoute
	}
}