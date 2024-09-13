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
		const response                      = await fetchData()
		const headlineAnalyzerButtonWrapper = document.querySelector('button[aria-label="Headline Analyzer"]')
		const headlineAnalyzerButton        = document.getElementById('aioseo-headline-analyzer-sidebar-button')
		const headlineScore                 = document.getElementById('aioseo-headline-analyzer-sidebar-button-score')

		if (response) {
			const headlineResult = JSON.parse(response.data[Object.keys(response.data)[0]])

			if (!headlineResult) {
				if (headlineAnalyzerButton) {
					headlineAnalyzerButtonWrapper.style.display = 'flex'
					headlineAnalyzerButton.style.display        = 'flex'
					headlineAnalyzerButton.setAttribute('aioseo-button-color', 'gray')
				}

				if (headlineScore) {
					headlineScore.innerHTML = 'N/A'
				}
				return
			}

			// Save data to store
			postEditorStore.updatePostHeadlineAnalyzerData(response.data, response.headline)

			const currentScore = headlineResult.score
			const classOnScore = 40 > currentScore ? 'red' : 70 > currentScore	? 'orange'	: 'green'

			// Set latest score
			postEditorStore.updateLatestScore(currentScore)
			if (headlineAnalyzerButton) {
				headlineAnalyzerButtonWrapper.style.display = 'flex'
				headlineAnalyzerButton.style.display        = 'flex'
				headlineAnalyzerButton.setAttribute('aioseo-button-color', classOnScore)
			}

			if (headlineScore) {
				headlineScore.innerHTML = `${currentScore}/100`
			}
		} else {
			postEditorStore.updatePostHeadlineAnalyzerData({}, '')

			if (headlineAnalyzerButton) {
				headlineAnalyzerButtonWrapper.style.display = 'flex'
				headlineAnalyzerButton.style.display        = 'flex'
				headlineAnalyzerButton.setAttribute('aioseo-button-color', 'gray')
			}

			if (headlineScore) {
				headlineScore.innerHTML = 'N/A'
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