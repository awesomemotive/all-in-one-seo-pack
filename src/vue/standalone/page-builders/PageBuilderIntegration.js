import { createApp, h } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { loadPiniaStores } from '@/vue/stores'

import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import { injectCSSToParent } from './helpers'

let aioseoScoreBtnApp = null,
	aioseoLimitModifiedDateApp = null,
	aioseoModalApp = null

class PageBuilderIntegration {
	mountComponent = ({ name, component, rootContainer, data = {}, props = {}, useRouter = false }) => {
		let app = createApp({
			name : name,
			data () {
				return data
			},
			render : () => h(
				component,
				{
					...props
				}
			)
		})

		app = loadPlugins(app)
		app = loadComponents(app)

		if (useRouter) {
			const router = createRouter({
				history : createWebHistory(),
				routes  : [
					{
						path      : '/:pathMatch(.*)*', // Will match everything and prevent warnings.
						component : component
					}
				]
			})

			app.use(router)

			router.app = app
		}

		loadPiniaStores(app)

		app.mount(rootContainer)

		return app
	}

	constructor (options) {
		this.scoreBtn          = options?.scoreBtn
		this.limitModifiedDate = options?.limitModifiedDate
		this.metabox           = options?.metabox
		this.injectStyles      = options?.injectStyles

		this.mount = async () => {
			const createElement = (tag, attributes) => {
				const $wrapper = document.createElement(tag)
				for (const key in attributes) {
					$wrapper.setAttribute(key, attributes[key])
				}

				return $wrapper
			}

			if (this.scoreBtn) {
				if (!this.scoreBtn.node.$wrapper.querySelector(`#${this.scoreBtn.node.attributes.id}`)) {
					this.scoreBtn.node.$wrapper.insertAdjacentElement('beforeend', createElement(this.scoreBtn.node.tag, this.scoreBtn.node.attributes))
				}

				aioseoScoreBtnApp?.unmount()
				aioseoScoreBtnApp = this.mountComponent({
					name          : this.scoreBtn.appName,
					component     : this.scoreBtn.component,
					rootContainer : this.scoreBtn.node.$wrapper.querySelector(`#${this.scoreBtn.node.attributes.id}`),
					props         : {
						onClick () {
							document.dispatchEvent(new Event('aioseo-pagebuilder-toggle-modal'))
						}
					}
				})
			}

			if (this.limitModifiedDate) {
				const mountLimitModifiedDate = ($wrapper) => {
					if (!$wrapper) {
						return
					}

					if (!$wrapper.querySelector(`#${this.limitModifiedDate.node.attributes.id}`)) {
						$wrapper.insertAdjacentElement('beforeend', createElement(this.limitModifiedDate.node.tag, {
							...this.limitModifiedDate.node.attributes,
							class : $wrapper.querySelector('[role="menuitem"]')?.classList.value
						}))
					}

					aioseoLimitModifiedDateApp?.unmount()
					aioseoLimitModifiedDateApp = this.mountComponent({
						name          : this.limitModifiedDate.appName,
						component     : this.limitModifiedDate.component,
						rootContainer : $wrapper.querySelector(`#${this.limitModifiedDate.node.attributes.id}`)
					})
				}

				if (this.limitModifiedDate.node?.mountLater) {
					this.limitModifiedDate.node.mountLater(mountLimitModifiedDate)
				} else {
					mountLimitModifiedDate(this.limitModifiedDate.node.$wrapper)
				}
			}

			if (this.metabox) {
				aioseoModalApp?.unmount()
				aioseoModalApp = this.mountComponent({
					name          : this.metabox.appName,
					component     : this.metabox.component,
					rootContainer : '#aioseo-modal-portal',
					data          : {
						tableContext  : window.aioseo.currentPost.context,
						screenContext : 'metabox'
					},
					useRouter : true
				})
			}

			// Inject styles into the parent window if specified.
			if (this.injectStyles) {
				// If building for production.
				if (false !== import.meta.env.PROD) {
					const $elements = document.querySelectorAll('link[href*="all-in-one-seo-pack"]')
					$elements.forEach(element => {
						injectCSSToParent(element.href, element.id)
					})
				} else {
					const $elements = document.querySelectorAll('style[data-vite-dev-id*="all-in-one-seo-pack"], link[id*="aioseo/"]')
					$elements.forEach(element => {
						injectCSSToParent(element?.textContent || element?.href, 'aioseo-' + (element?.getAttribute('data-vite-dev-id') || element?.id || '').toLowerCase())
					})
				}
			}
		}
	}
}

export default PageBuilderIntegration