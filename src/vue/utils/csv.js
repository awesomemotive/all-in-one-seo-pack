export const arrayToCsv = (value) => {
	return value.map(function (d) {
		if ('object' === typeof d) {
			return JSON.stringify(Object.values(d))
		}

		return d
	})
		.join('\n')
		.replace(/(^\[)|(\]$)/mg, '')
}