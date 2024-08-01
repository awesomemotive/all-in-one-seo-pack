const attachDataValues = (element, data, dataAttributes) => {
	const mention = element
	Object.keys(data).forEach(key => {
	  if (-1 < dataAttributes.indexOf(key)) {
			mention.dataset[key] = data[key]
	  } else {
			delete mention.dataset[key]
	  }
	})
	return mention
}

const getMentionCharIndex = (text, mentionDenotationChars) => {
	return mentionDenotationChars.reduce((prev, mentionChar) => {
		const mentionCharIndex = text.lastIndexOf(mentionChar)

		if (mentionCharIndex > prev.mentionCharIndex) {
			return {
				mentionChar,
				mentionCharIndex
			}
		}
		return {
			mentionChar      : prev.mentionChar,
			mentionCharIndex : prev.mentionCharIndex
		}
	},
	{ mentionChar: null, mentionCharIndex: -1 })
}

const hasValidChars = (text, allowedChars) => {
	return allowedChars.test(text)
}

const hasValidMentionCharIndex = (mentionCharIndex, text, isolateChar) => {
	if (-1 < mentionCharIndex) {
	  if (
			isolateChar &&
		!(0 === mentionCharIndex || !!text[mentionCharIndex - 1].match(/\s/g))
	  ) {
			return false
	  }
	  return true
	}
	return false
}

export {
	attachDataValues,
	getMentionCharIndex,
	hasValidChars,
	hasValidMentionCharIndex
}