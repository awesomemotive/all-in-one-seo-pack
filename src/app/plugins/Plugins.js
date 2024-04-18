class Plugins {
	constructor () {
		// Initialize!
		this.plugins()
	}

	plugins () {
		this.selector = document.querySelector('tr[data-plugin="' + window.aioseoPlugins.basename + '"]')
		if (!this.selector) {
			return
		}

		// Add a class we can target from our CSS.
		this.selector.classList.add('aioseo-plugin-row')

		// Add target="_blank" to each of our external links.
		const rowActions = this.selector.querySelectorAll('span.proupgrade a, span.docs a, span.support a')
		if (rowActions.length) {
			rowActions.forEach(link => {
				link.setAttribute('target', '_blank')
			})
		}

		// Add target="_blank" to each of our external links.
		const secondRowActions = this.selector.querySelectorAll('.column-description .active a')
		if (secondRowActions.length) {
			secondRowActions.forEach(link => {
				link.setAttribute('target', '_blank')
			})
		}
	}
}

new Plugins()