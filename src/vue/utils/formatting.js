/**
 * Escapes a URL by removing invalid characters and adding necessary encoding.
 *
 * @since 4.6.2
 *
 * @param   {string} url The URL to be escaped.
 * @returns {string}     The escaped URL.
 */
export const escUrl = url => {
	if (!url) {
		return ''
	}

	url = url.trim().replace(' ', '%20')
	url = url.replace(/[^a-z0-9-~+_.?#=!&;,/:%@$|*'()[\]\\x80-\\xff]/gi, '')

	if ('' === url) {
		return url
	}

	if (!url.toLowerCase().startsWith('mailto:')) {
		const strip = [ '%0d', '%0a', '%0D', '%0A' ]
		strip.forEach(s => {
			url = url.replace(new RegExp(s, 'g'), '')
		})
	}

	url = url.replace(';//', '://')

	if (url.includes('[') || url.includes(']')) {
		const urlObj = new URL(url)
		let front = ''

		if (urlObj.protocol) {
			front += urlObj.protocol + '//'
		} else if ('/' === url[0]) {
			front += '//'
		}

		if (urlObj.username) {
			front += urlObj.username
		}

		if (urlObj.password) {
			front += ':' + urlObj.password
		}

		if (urlObj.username || urlObj.password) {
			front += '@'
		}

		if (urlObj.hostname) {
			front += urlObj.hostname
		}

		if (urlObj.port) {
			front += ':' + urlObj.port
		}

		const endDirty = url.replace(front, '')
		const endClean = endDirty.replace('[', '%5B').replace(']', '%5D')
		url = url.replace(endDirty, endClean)
	}

	return url
}