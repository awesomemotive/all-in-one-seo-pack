import { defineStore } from 'pinia'

import {
	useIndexNowStore,
	useOptionsStore,
	useRedirectsStore
} from '@/vue/stores'

import { useDirtyOptions } from '@/vue/composables/DirtyOptions'

export const useDirtyOptionsStore = defineStore('DirtyOptionsStore', {
	state : () => ({
		disabled        : [],
		options         : {},
		dynamicOptions  : {},
		networkOptions  : {},
		redirectOptions : {},
		indexNowOptions : {}
	}),
	getters : {
		isDirty : state => {
			const dirtyOptions   = useDirtyOptions()
			const indexNowStore  = useIndexNowStore()
			const optionsStore   = useOptionsStore()
			const redirectsStore = useRedirectsStore()

			const all = [
				// Root options.
				[
					state.options,
					optionsStore.options
				],
				// Network options.
				[
					state.networkOptions,
					optionsStore.networkOptions
				],
				// Dynamic options.
				[
					state.dynamicOptions,
					optionsStore.dynamicOptions
				]
			]

			// Redirect options.
			if (!state.disabled.includes('redirects')) {
				all.push([
					state.redirectOptions,
					redirectsStore.options
				])
			}

			// Index Now options.
			if (!state.disabled.includes('indexNow')) {
				all.push([
					state.indexNowOptions,
					indexNowStore.options
				])
			}

			return !all.every(([ a, b ]) => dirtyOptions.normalize(a) === dirtyOptions.normalize(b))
		}
	},
	actions : {
		...useDirtyOptions().actions
	}
})