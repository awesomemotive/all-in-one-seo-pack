import {
	usePostEditorStore
} from '@/vue/stores'
import { fetchData } from './initAnalyzerData'

/**
 * Update the current score on the headline analyzer button and save the data to the Post Editor store
 *
 * @param   {boolean} reload - Whether to reload the data
 * @returns {void}
 */
export async function HeadlineCurrentScore (reload = false) {
	const postEditorStore = usePostEditorStore()

	const fetchAndUpdate = async () => {
		const response = await fetchData()
		const seoButton = document.querySelector('button[aria-controls="aioseo-headline-analyzer:aioseo-headline-analyzer"]')
		const btnScore = document.querySelector('#aioseo-headline-analyzer-sidebar-button-score')

		if (response) {
			const headlineResult = JSON.parse(response.data[Object.keys(response.data)[0]])

			if (!headlineResult) {
				if (seoButton) {
					seoButton.style.display = 'block'
					seoButton.setAttribute('aioseo-button-color', 'gray')
				}

				if (btnScore) {
					btnScore.innerHTML = 'N/A'
				}
				return
			}

			// Save data to store
			postEditorStore.updatePostHeadlineAnalyzerData(response.data, response.headline)

			const currentScore = headlineResult.score
			const classOnScore = 40 > currentScore ? 'red' : 70 > currentScore	? 'orange'	: 'green'

			// Set latest score
			postEditorStore.updateLatestScore(currentScore)
			if (seoButton) {
				seoButton.style.display = 'block'
				seoButton.setAttribute('aioseo-button-color', classOnScore)
			}

			if (btnScore) {
				btnScore.innerHTML = `${currentScore}/100`
			}
		} else {
			postEditorStore.updatePostHeadlineAnalyzerData({}, '')

			if (seoButton) {
				seoButton.style.display = 'block'
				seoButton.setAttribute('aioseo-button-color', 'gray')
			}

			if (btnScore) {
				btnScore.innerHTML = 'N/A'
			}
		}
	}

	if (reload) {
		fetchAndUpdate()
	}

	const callback = async function (mutationsList, observer) {
		const pinnedItemsDiv = document.querySelector('.interface-pinned-items')

		if (pinnedItemsDiv) {
			observer.disconnect() // disconnect so we don't fetch data multiple times
			fetchAndUpdate()
		}
	}

	const observer = new MutationObserver(callback)
	const config = { childList: true, subtree: true }
	observer.observe(document.body, config)
}