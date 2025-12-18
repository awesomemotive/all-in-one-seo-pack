/**
 * Initializes click handlers for Bricks panel buttons using event delegation.
 *
 * @param {Object[]} buttons       - Array of { selector, tab } configurations.
 * @param {Element}  parentElement - Parent element for event delegation.
 * @returns {Function} Cleanup function.
 */
const initSeoButtonHandler = (buttons, parentElement = document.body) => {
	const handleClick = (event) => {
		for (const config of buttons) {
			const $button = event.target.closest(config.selector)
			if (!$button) {
				continue
			}

			event.preventDefault()
			event.stopPropagation()

			// Dispatch event to open the modal with the specified tab.
			document.dispatchEvent(new CustomEvent('aioseo-pagebuilder-toggle-modal', {
				detail : { tab: config.tab }
			}))

			return
		}
	}

	parentElement.addEventListener('click', handleClick, true)

	// Return cleanup function.
	return () => {
		parentElement.removeEventListener('click', handleClick, true)
	}
}

export default initSeoButtonHandler