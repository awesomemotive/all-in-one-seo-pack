export const useJsonValues = () => {
	const getJsonValue = (value, defaultValue = null) => {
		if (!value) {
			return defaultValue
		}

		if ('object' === typeof value) {
			return value
		}

		try {
			value = JSON.parse(value)
		} catch (error) {
			value = defaultValue
		}
		return value
	}

	const setJsonValue = (value) => {
		return JSON.stringify(value)
	}

	const getJsonValues = (values) => {
		return values.map(v => JSON.parse(v))
	}

	const setJsonValues = (values) => {
		return values.map(v => JSON.stringify(v))
	}

	return {
		getJsonValue,
		getJsonValues,
		setJsonValue,
		setJsonValues
	}
}