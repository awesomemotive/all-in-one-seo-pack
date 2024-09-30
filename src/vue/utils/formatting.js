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

/**
 * Verifies that an email is valid. This is the same as the PHP function `is_email()`.
 *
 * @since 4.7.2
 *
 * @param 	{string} 		 email Email address to verify.
 * @returns {string|boolean} 	   Valid email address on success, false on failure.
 */
export const isEmail = (email) => {
	// Test for the minimum length the email can be.
	if (6 > email.length) {
		return false
	}

	// Test for an @ character after the first position.
	if (-1 === email.indexOf('@', 1)) {
		return false
	}

	// Split out the local and domain parts.
	const parts  = email.split('@')
	const local  = parts[0]
	const domain = parts[1]

	/*
	 * LOCAL PART
	 * Test for invalid characters.
	 */
	const localPattern = /^[a-zA-Z0-9!#$%&'*+/=?^_`{|}~.-]+$/
	if (!localPattern.test(local)) {
		return false
	}

	/*
	 * DOMAIN PART
	 * Test for sequences of periods.
	 */
	if (/\.\./.test(domain)) {
		return false
	}

	// Test for leading and trailing periods and whitespace.
	if (domain.trim().replace(/^\./, '').replace(/\.$/, '') !== domain.trim()) {
		return false
	}

	// Split the domain into subs.
	const subs = domain.split('.')

	// Assume the domain will have at least two subs.
	if (2 > subs.length) {
		return false
	}

	// Loop through each sub.
	const subPattern = /^[a-z0-9-]+$/i
	for (let i = 0; i < subs.length; i++) {
		const sub = subs[i]
		// Test for leading and trailing hyphens and whitespace.
		if (sub.trim().replace(/^-|-$/, '') !== sub.trim()) {
			return false
		}

		// Test for invalid characters.
		if (!subPattern.test(sub)) {
			return false
		}
	}

	// Congratulations, your email made it!
	return email
}