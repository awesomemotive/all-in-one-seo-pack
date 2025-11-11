const allHeaderElements = document.querySelectorAll('.aioseo-toc-header-area') || []
const collapsedElements = document.querySelectorAll('.aioseo-toc-header-collapsible') || []

// If .aioseo-toc-header-area exists, add on it's parent the class .aioseo-toc-header, not exists,
// add the class to it's parent.
allHeaderElements.forEach(headerElement => {
	if (headerElement) {
		// Only add if the parent element has the class .aioseo-toc-header
		if (!headerElement.parentElement.classList.contains('aioseo-toc-header')) {
			headerElement.parentElement.classList.add('aioseo-toc-header')
		}
	}
})

// On click .aioseo-toc-header-collapsible,
// get the nearest element of .aioseo-toc-contents and toggle the class .aioseo-toc-collapsed.
collapsedElements.forEach(collapsedElement => {
	collapsedElement.addEventListener('click', () => {
		const commonParent = collapsedElement?.closest('.aioseo-toc-header')

		// Within the common parent, find the .aioseo-toc-contents element.
		const contentsElement = commonParent ? commonParent.querySelector('.aioseo-toc-contents') : null

		if (contentsElement) {
			contentsElement.classList.toggle('aioseo-toc-collapsed')

			// Get .aioseo-toc-header-collapsible-closed element and toggle the class .aioseo-toc-collapsed.
			const collapsedHeader = commonParent.querySelector('.aioseo-toc-header-collapsible-closed')
			if (collapsedHeader) {
				collapsedHeader.classList.toggle('aioseo-toc-collapsed')
			}

			// Get .aioseo-toc-header-collapsible-open element and toggle the class .aioseo-toc-collapsed.
			const expandedHeader = commonParent.querySelector('.aioseo-toc-header-collapsible-open')
			if (expandedHeader) {
				expandedHeader.classList.toggle('aioseo-toc-collapsed')
			}
		}
	})
})