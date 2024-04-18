import { sanitizeString } from '@/vue/utils/strings'

export const useDirtyOptions = () => {
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
			value = sanitizeString(value)
		}

		return null === value ? '' : value
	}

	// We need to stringify, then parse, then stringify in order to make a clone of these options.
	const normalize = object => {
		if (!object) {
			return {}
		}

		return JSON.stringify(JSON.parse(JSON.stringify(object)), replacer)
	}

	const actions = {
		updateOriginalOptions (key, payload) {
			this[key] = JSON.parse(JSON.stringify(payload))
		},
		disableDirtyCheck (key) {
			this.disabled.push(key)
		}
	}

	return {
		actions,
		normalize
	}
}