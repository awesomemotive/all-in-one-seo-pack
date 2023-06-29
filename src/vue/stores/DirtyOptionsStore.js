import { defineStore } from 'pinia'

import {
	useIndexNowStore,
	useOptionsStore,
	useRedirectsStore
} from '@/vue/stores'

import { decodeHTMLEntities } from '@/vue/utils/helpers'

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

			return !all.every(([ a, b ]) => normalize(a) === normalize(b))
		}
	},
	actions : {
		updateOriginalOptions (key, payload) {
			this[key] = JSON.parse(JSON.stringify(payload))
		},
		disableDirtyCheck (key) {
			this.disabled.push(key)
		}
	}
})

// We need to stringify, then parse, then stringify in order to make a clone of these options.
const normalize = object => {
	if (!object) {
		return {}
	}

	return JSON.stringify(JSON.parse(JSON.stringify(object)), replacer)
}

const replacer = (key, value) => {
	if ('licenseKey' === key) {
		value = ''
	}

	if ('rules' === key && Array.isArray(value)) {
		value.forEach((rule, index) => {
			const r = JSON.parse(rule)
			if (null === r.userAgent && 'allow' === r.rule && null === r.directoryPath) {
				value.splice(index, 1)
			}
		})
	}

	if ('separator' === key) {
		value = decodeHTMLEntities(value)
	}

	return null === value ? '' : value
}