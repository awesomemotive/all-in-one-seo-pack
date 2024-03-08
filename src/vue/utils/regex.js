export const escapeRegex = (string) => {
	return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}
export const isRegexValid = (string) => {
	try {
		new RegExp(string)
		return true
	} catch (e) {
		return false
	}
}