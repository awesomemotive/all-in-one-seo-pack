import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import {
	loadPinia,
	useRootStore
} from '@/vue/stores'

import App from './App.vue'
import translate from '@/vue/plugins/translations'
import { merge } from 'lodash-es'

// Router placeholder to prevent errors when using router-link.
const router = createRouter({
	history : createWebHistory(),
	routes  : [
		{
			path      : '/:pathMatch(.*)*', // Will match everything and prevent warnings.
			component : App
		}
	]
})

const app = createApp({ ...App, name: 'Standalone/SeoPreview' })

app.use(router)
router.app = app

// Use the pinia store.
loadPinia(app, router)

const rootStore = useRootStore()
const aioseo = JSON.parse(JSON.stringify(window.aioseoSeoPreview))
rootStore.aioseo = merge({ ...rootStore.aioseo }, { ...aioseo || {} })

app.$t  = app.config.globalProperties.$t  = translate
app.$td = app.config.globalProperties.$td = import.meta.env.VITE_TEXTDOMAIN

const elemDiv = document.createElement('div')
elemDiv.setAttribute('id', 'aioseo-modal-portal')

// If building for production.
if (false !== import.meta.env.PROD) {
	const elemShadowWrapper = document.createElement('div')
	const elemShadowRoot = elemShadowWrapper.attachShadow({ mode: 'open' })
	const elemDir = document.createElement('div')

	elemShadowWrapper.setAttribute('class', 'aioseo-seo-preview-shadow-wrapper')
	elemShadowWrapper.setAttribute('style', 'margin:0;padding:0;border:0')
	elemDir.setAttribute('dir', document?.dir || 'ltr')
	elemDir.setAttribute('style', 'margin:0;padding:0;border:0')
	elemShadowRoot.appendChild(elemDir)
	elemDir.appendChild(elemDiv)

	document.body.appendChild(elemShadowWrapper)
} else {
	document.body.appendChild(elemDiv)
}

app.mount(elemDiv)