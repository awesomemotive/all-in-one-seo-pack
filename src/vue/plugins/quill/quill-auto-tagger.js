const addTags = originalPhrase => {
	const {
		invalidClosingTags,
		invalidOpeningTags,
		tagNamePattern
	} = dissectPhrase(originalPhrase)

	// Add opening/closing tags at the start/end of the phrase.
	let validPhrase = originalPhrase
	invalidClosingTags.forEach((closingTag) => {
		const tagName = closingTag.match(tagNamePattern)[1]
		validPhrase = `<${tagName}>` + validPhrase
	})

	invalidOpeningTags.forEach((openingTag) => {
		const tagName = openingTag.match(tagNamePattern)[1]
		validPhrase = validPhrase + `</${tagName}>`
	})

	return validPhrase
}

const removeTags = (originalPhrase, modifiedPhrase) => {
	const {
		invalidClosingTags,
		invalidOpeningTags,
		tagNamePattern
	} = dissectPhrase(originalPhrase)

	// Strip off the tags we added.
	invalidClosingTags.forEach((closingTag) => {
		const tagName = closingTag.match(tagNamePattern)[1]
		const pattern = new RegExp(`^<${tagName}>`)
		modifiedPhrase = modifiedPhrase.replace(pattern, '')
	})

	invalidOpeningTags.forEach((openingTag) => {
		const tagName = openingTag.match(tagNamePattern)[1]
		const pattern = new RegExp(`<\\/${tagName}>$`)
		modifiedPhrase = modifiedPhrase.replace(pattern, '')
	})

	return modifiedPhrase
}

const dissectPhrase = originalPhrase => {
	const tagPattern        = new RegExp(/(<[^<>]*>)/)
	const closingTagPattern = new RegExp(/<\s?\/[^<>]*>/)
	const tagNamePattern    = new RegExp(/<\s?\/?([^<\s>]*).*>/)

	// Split the phrase with HTML tags as the delimiters.
	const nodes = originalPhrase.split(tagPattern)

	// Get a list of all the HTML tags.
	const tags = []
	nodes.forEach((node) => {
		if (node.match(tagPattern)) {
			tags.push(node)
		}
	})

	// Figure out which tags are invalid.
	const invalidTags = []
	tags.forEach((tag, index) => {
		if (!tag) {
			return
		}

		if (tag.match(closingTagPattern)) {
			// If the current tag is a closing one, it's invalid because it otherwise would have been stripped below.
			invalidTags.push(tag)
			tags[index] = ''
			return
		}

		// If we get to this point, the tag is an opening one.
		// We'll now check if we can find a closing tag for it.
		const tagName = tag.match(tagNamePattern)[1]
		let matchFound = false
		tags.forEach((tag2, index2) => {
			if (index === index2 || !tag2 || matchFound) {
				return
			}

			if (!tag2.match(closingTagPattern)) {
				return
			}

			const tagName2 = tag2.match(tagNamePattern)[1]

			// If we can find a closing tag, unset both the opening and closing tag.
			if (tagName === tagName2) {
				tags[index] = ''
				tags[index2] = ''
				matchFound = true
				return
			}

			// If we can't find a closing tag, it's an invalid opening tag.
			invalidTags.push(tag)
			tags[index] = ''
		})
	})

	// Separate invalid tags into closing and opening ones.
	const invalidOpeningTags = []
	const invalidClosingTags = []
	invalidTags.forEach((invalidTag) => {
		if (!invalidTag.match(tagPattern)) {
			return
		}

		if (invalidTag.match(closingTagPattern)) {
			invalidClosingTags.push(invalidTag)
			return
		}
		invalidOpeningTags.push(invalidTag)
	})

	return { invalidClosingTags, invalidOpeningTags, tagNamePattern }
}

export {
	addTags,
	removeTags
}