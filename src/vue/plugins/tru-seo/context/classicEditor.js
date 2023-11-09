import {
	truSeoShouldAnalyze,
	maybeUpdatePost
} from '@/vue/plugins/tru-seo/components/helpers'
import { isWooCommerceProduct } from '@/vue/utils/context'
import { maybeUpdateTaxonomies } from '@/vue/plugins/tru-seo/components/taxonomies'

export const watchClassicEditor = () => {
	let isAnalyzing = false

	if (document.querySelector('#wp-content-wrap.tmce-active')) {
		// Run Analysis once tinyMCE loads.
		const mceActiveInterval = window.setInterval(() => {
			if (!window.tinyMCE || !window.tinyMCE.activeEditor) {
				return
			}
			window.clearInterval(mceActiveInterval)
			maybeUpdatePost()
			window.tinyMCE.get('content').on('keyup', () => {
				maybeUpdatePost(500)
			})
			window.tinyMCE.get('content').on('paste', () => {
				maybeUpdatePost(500)
			})
		}, 50)
	} else {
		// No TinyMCE. Look for a proper #content textarea.
		const textEditor = document.querySelector('textarea#content')
		if (textEditor) {
			textEditor.addEventListener('keyup', () => {
				maybeUpdatePost(500)
			})
			textEditor.addEventListener('paste', () => {
				maybeUpdatePost(500)
			})
		}
	}

	// Title Change.
	const titleInput = document.querySelector('#post input#title')
	if (titleInput) {
		titleInput.addEventListener('input', () => {
			maybeUpdatePost(500)
		})
	}

	// Excerpt Change.
	addExcerptWatcher()

	// Slug Change.
	const slug = document.querySelector('#post_name')
	if (slug) {
		slug.addEventListener('change', () => {
			maybeUpdatePost(500)
		})
	}

	const permalinkButtons = document.querySelector('#edit-slug-buttons')
	if (permalinkButtons) {
		permalinkButtons.addEventListener('click', (event) => {
			if (event.target === permalinkButtons.querySelector('#edit-slug-buttons button.save')) {
				maybeUpdatePost()
			}
		})
	}

	// Category Change.
	const catInputs = document.querySelector('#categorychecklist')
	if (catInputs) {
		catInputs.addEventListener('change', function () {
			maybeUpdateTaxonomies()
		})
	}

	// Watch for switching between tinyMCE and the text editor.
	const callback = function (mutationsList) {
		mutationsList.forEach(mutation => {
			if ('class' === mutation.attributeName) {
				if (document.querySelector('#wp-content-wrap.tmce-active')) {
					if (!window.tinyMCE) {
						return
					}
					window.tinyMCE.get('content').on('keyup', () => {
						maybeUpdatePost(500)
					})
					window.tinyMCE.get('content').on('paste', () => {
						maybeUpdatePost(500)
					})
				}
				const textEditor = document.querySelector('#content')
				if (textEditor) {
					textEditor.addEventListener('keyup', () => {
						maybeUpdatePost(500)
					})
					textEditor.addEventListener('paste', () => {
						maybeUpdatePost(500)
					})
				}

				// Excerpt Change.
				addExcerptWatcher()
			}
		})
	}
	const mutationObserver = new MutationObserver(callback)
	const contentWrap = document.querySelector('#wp-content-wrap')
	if (contentWrap) {
		mutationObserver.observe(contentWrap, { attributes: true })
	}
	const excerptWrap = document.querySelector('#wp-excerpt-wrap')
	if (excerptWrap) {
		mutationObserver.observe(excerptWrap, { attributes: true })
	}
	setInterval(() => {
		if (isAnalyzing) {
			isAnalyzing = false
		}
	}, 500)

	// Run Analysis on load and content change
	if (truSeoShouldAnalyze() && !isWooCommerceProduct()) {
		isAnalyzing = true
		setInterval(() => {
			if (
				window.tinyMCE &&
				window.tinyMCE.activeEditor &&
				window.tinyMCE.activeEditor.isDirty() &&
				!isAnalyzing
			) {
				isAnalyzing = true

				maybeUpdatePost()
			}
		}, 500)
	}
}

const addExcerptWatcher = () => {
	if (document.querySelector('#wp-excerpt-wrap.tmce-active')) {
		const mceActiveInterval = window.setInterval(() => {
			if (!window.tinyMCE) {
				return
			}

			window.clearInterval(mceActiveInterval)
			maybeUpdatePost()

			window.tinyMCE.get('excerpt').on('keyup', () => {
				maybeUpdatePost(500)
			})
			window.tinyMCE.get('excerpt').on('paste', () => {
				maybeUpdatePost(500)
			})
		}, 50)
	}

	// Excerpt textarea when TinyMCE is not enabled
	const excerptInput = document.querySelector('#post textarea#excerpt')
	if (excerptInput) {
		excerptInput.addEventListener('keyup', () => {
			maybeUpdatePost(500)
		})
		excerptInput.addEventListener('paste', () => {
			maybeUpdatePost(500)
		})
	}
}