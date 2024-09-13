export const useSeoRevisions = () => {
	const getAuthorEmailAndLogin = (revision) => {
		// User might have been deleted.
		if ('object' !== typeof revision?.author || !revision.author.email || !revision.author.login) {
			return ''
		}

		return revision.author.email + ' (' + revision.author.login + ')'
	}

	return {
		getAuthorEmailAndLogin
	}
}