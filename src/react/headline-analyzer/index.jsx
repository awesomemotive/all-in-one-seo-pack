import icons from './icons'
import { http, restUrl } from './functions'

import HeadlineSwitcher from './components/HeadlineSwitcher'
import HeadlinePanelPreviousScores from './components/HeadlinePanelPreviousScores'
import HeadlinePanelWordBalance from './components/HeadlinePanelWordBalance'
import HeadlinePanelSentiment from './components/HeadlinePanelSentiment'
import HeadlinePanelType from './components/HeadlinePanelType'
import HeadlinePanelCharacterCount from './components/HeadlinePanelCharacterCount'
import HeadlinePanelWordCount from './components/HeadlinePanelWordCount'
import HeadlinePanelStartEndWords from './components/HeadlinePanelStartEndWords'
import HeadlinePanelSearchPreview from './components/HeadlinePanelSearchPreview'
import { headlineAnalyzerNotice } from './constants'

import './assets/scss/main.scss'

const { __ } = window.wp.i18n
const { Fragment, useState, useEffect } = window.wp.element
const { registerPlugin } = window.wp.plugins
const { PluginSidebar, PluginSidebarMoreMenuItem } = window.wp.editPost
const { select } = window.wp.data

const td = import.meta.env.VITE_TEXTDOMAIN

let timeout
export const debounceContext = (fn, time) => {
	return ((...args) => {
		const functionCall = () => fn(...args)

		clearTimeout(timeout)
		timeout = setTimeout(functionCall, time)
	}).call()
}

const HeadlineAnalyzer = () => {
	let postTitle = select('core/editor').getEditedPostAttribute('title')

	// Fetch data from the API.
	const fetchData = () => {
		if (!postTitle) {
			const newAnalyzerData = {
				dataExist : false
			}
			setAnalyzer({ ...analyzer, ...newAnalyzerData })
			return
		}

		http(window.aioseo.nonce).post(restUrl('analyze-headline'))
			.send({
				headline            : postTitle,
				shouldStoreHeadline : false
			})
			.then(response => {
				const newAnalyzerData = {
					dataExist : false
				}

				const headlineResult = JSON.parse(response.body[Object.keys(response.body)[0]])
				if (headlineResult.analysed) {
					newAnalyzerData.currentHeadlineData = headlineResult
					newAnalyzerData.headlineData = headlineResult
					newAnalyzerData.dataExist = true
					if ('undefined' !== typeof analyzer.headlineData) {
						newAnalyzerData.previousHeadlinesData = [
							analyzer.headlineData,
							...previousScores
						]
					}
				}
				setAnalyzer({ ...analyzer, ...newAnalyzerData })
			})
			.catch(error => {
				const newAnalyzerData = {
					dataExist : false
				}
				setAnalyzer({ ...analyzer, ...newAnalyzerData })
				console.error('Couldn\'t fetch score for headline:', error)
			})
	}

	// Get score for the first time on page load.
	useEffect(() => fetchData(), [])

	// Fetch new results after the title was changed.
	window.wp.data.subscribe(() => {
		// If the title is the same, abort.
		if (postTitle === select('core/editor').getEditedPostAttribute('title')) {
			return
		}
		postTitle = select('core/editor').getEditedPostAttribute('title')

		debounceContext(() => fetchData(), 2000)
	})

	const textHeadlineAnalyzer = __('SEO Headline Analyzer', td)
	const emptyTitleWarning = __(
		'Write your post title to see the analyzer data. This Headline Analyzer tool enables you to write irresistible SEO headlines that drive traffic, shares, and rank better in search results.',
		td
	)

	const [ analyzer, setAnalyzer ] = useState({})

	const previousScores =
		'undefined' !== typeof analyzer.previousHeadlinesData
			? analyzer.previousHeadlinesData
			: []

	const pinnedIcon = document.querySelector(
		`.components-button[aria-label='${textHeadlineAnalyzer}'] svg`
	)
	if (pinnedIcon) {
		const scoreTag = document.createElement('span')
		if (
			analyzer.dataExist &&
			'undefined' !== typeof analyzer.currentHeadlineData.score
		) {
			const currentScore = analyzer.currentHeadlineData.score
			const classOnScore =
				40 > currentScore
					? 'red'
					: 60 >= currentScore
						? 'orange'
						: 'green'
			pinnedIcon.parentNode.setAttribute(
				'aioseo-button-color',
				classOnScore
			)
			if (!pinnedIcon.nextElementSibling) {
				scoreTag.innerHTML = `${currentScore}/100`
				pinnedIcon.parentNode.insertBefore(
					scoreTag,
					pinnedIcon.nextSibling
				)
			} else {
				pinnedIcon.nextElementSibling.innerHTML = `${currentScore}/100`
			}
		} else {
			pinnedIcon.parentNode.setAttribute('aioseo-button-color', 'red')
			if (!pinnedIcon.nextElementSibling) {
				scoreTag.innerHTML = '00/100'
				pinnedIcon.parentNode.insertBefore(
					scoreTag,
					pinnedIcon.nextSibling
				)
			} else {
				pinnedIcon.nextElementSibling.innerHTML = '00/100'
			}
		}
	}

	// Removing unpin button in the Headline Analyzer.
	const wrapper = document.querySelector('.aioseo-headline-analyzer-wrapper')
	if (wrapper) {
		const headingElements = wrapper.parentNode.querySelectorAll(
			'.components-panel__header'
		)
		if (headingElements) {
			headingElements.forEach(function (element) {
				const unpinButton = element.querySelector('.interface-complementary-area__pin-unpin-item')
				if (unpinButton && null !== unpinButton) {
					unpinButton.style.display = 'none'
				}
			})
		}
	}

	const updateAnalyzerData = data => {
		setAnalyzer({ ...analyzer, ...data })
	}

	return (
		<Fragment>
			<PluginSidebarMoreMenuItem
				target="aioseo-headline-analyzer"
			>
				{textHeadlineAnalyzer}
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="aioseo-headline-analyzer"
				title={textHeadlineAnalyzer}
				className="aioseo-headline-analyzer-wrapper"
				// icon={icons.headlineBlack}
			>
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlineSwitcher
							analyzer={analyzer}
							setAnalyzer={updateAnalyzerData}
						/>
					)
					: (
						<p className="aioseo-headline-analyzer-empty-title-warning">
							{emptyTitleWarning}
						</p>
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed &&
				0 < previousScores.length
					? (
						<HeadlinePanelPreviousScores
							analyzer={analyzer}
							setAnalyzer={updateAnalyzerData}
						/>
					)
					: (
						''
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlinePanelWordBalance data={analyzer.headlineData} />
					)
					: (
						''
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlinePanelSentiment data={analyzer.headlineData} />
					)
					: (
						''
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlinePanelType data={analyzer.headlineData} />
					)
					: (
						''
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlinePanelCharacterCount data={analyzer.headlineData} />
					)
					: (
						''
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlinePanelWordCount data={analyzer.headlineData} />
					)
					: (
						''
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlinePanelStartEndWords data={analyzer.headlineData} />
					)
					: (
						''
					)}
				{'undefined' !== typeof analyzer.headlineData &&
				analyzer.dataExist &&
				analyzer.headlineData.analysed
					? (
						<HeadlinePanelSearchPreview data={analyzer.headlineData} />
					)
					: (
						''
					)}
				<div className="aioseo-headline-analyzer-bottom-notice">
					<p dangerouslySetInnerHTML={{ __html: headlineAnalyzerNotice() }}></p>
				</div>
			</PluginSidebar>
		</Fragment>
	)
}

registerPlugin('aioseo-headline-analyzer', {
	icon   : icons.headline,
	render : HeadlineAnalyzer
})