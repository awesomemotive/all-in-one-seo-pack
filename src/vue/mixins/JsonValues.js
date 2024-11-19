export const JsonValues = {
	methods : {
		getJsonValue (value, defaultValue = null) {
			try {
				value = JSON.parse(value)
			} catch (error) {
				value = defaultValue
			}
			return value
		},
		setJsonValue (value) {
			return JSON.stringify(value)
		},
		getJsonValues (values) {
			return values.map(v => JSON.parse(v))
		},
		setJsonValues (values) {
			return values.map(v => JSON.stringify(v))
		}
	}
}