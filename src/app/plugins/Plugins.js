import { elemLoaded } from '@/vue/utils/elemLoaded'
import { __, sprintf } from '@wordpress/i18n'

class Plugins {
	constructor () {
		// Initialize!
		this.plugins()
		this.pluginsInstall()
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

	pluginsInstall () {
		const conflictingPlugins = window.aioseoPlugins.conflictingPlugins
		if (!conflictingPlugins) {
			return
		}

		conflictingPlugins.forEach(slug => {
			elemLoaded('.plugin-card.plugin-card-' + slug, slug + 'InstallPlugin')
			document.addEventListener('animationstart', function (event) {
				if (slug + 'InstallPlugin' !== event.animationName) {
					return
				}

				this.selector = document.querySelector('.plugin-card.plugin-card-' + slug)
				if (!this.selector) {
					return
				}

				const noticeExists = this.selector.querySelector('.notice')
				if (noticeExists) {
					return
				}

				// Append our notice.
				const notice = document.createElement('div')
				notice.classList.add('notice', 'inline', 'notice-error', 'notice-alt')

				const td         = import.meta.env.VITE_TEXTDOMAIN
				const message    = sprintf(
					// Translators: 1 - Opening HTML strong tag, 2 - The plugin name ("All in One SEO"), 3 - Closing HTML strong tag.
					__('%1$sThis plugin is known to conflict with %2$s.%3$s We don\'t recommend that you activate this plugin in order to prevent conflicts.', td),
					'<strong>',
					import.meta.env.VITE_NAME,
					'</strong>'
				)
				notice.innerHTML = `<p>${message}</p>`

				this.selector.prepend(notice)
			})
		})
	}
}

new Plugins()