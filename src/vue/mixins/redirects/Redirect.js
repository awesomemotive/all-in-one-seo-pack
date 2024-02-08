import { sanitizeString } from '@/vue/utils/strings'
import links from '@/vue/utils/links'

export default {
	setup () {
		return {
			links
		}
	},
	methods : {
		redirectHasUnPublishedPost (redirect) {
			return redirect.post_id && 'publish' !== redirect.postStatus
		},
		validateRedirect (redirect) {
			if (!redirect.url.url) {
				return false
			}

			const errors = []
			if (redirect.url.regex) {
				try {
					new RegExp(redirect.url.url)
				} catch (e) {
					errors.push(this.$t.__('The regex syntax is invalid.', this.$td))
					return errors
				}
			}

			if (!redirect.url.regex && !sanitizeString(redirect.url.url)) {
				errors.push(this.$t.__('Your URL is invalid.', this.$td))
				return errors
			}

			if ('http' === redirect.url.url.substr(0, 4)) {
				errors.push(this.$t.__('Please enter a valid relative source URL.', this.$td))
			}

			if (redirect.url.url.match(/%[a-zA-Z]+%/)) {
				errors.push(this.$t.__('Permalinks are not currently supported.', this.$td))
			}

			if ('/(.*)' === redirect.url.url || '^/(.*)' === redirect.url.url) {
				errors.push(this.$t.__('This redirect is supported using the Relocate Site feature under Full Site Redirect tab.', this.$td))
			}

			// Loop detection.
			if (redirect.url.url && redirect.url.url.length && redirect.targetUrl && redirect.targetUrl.length) {
				let compareSource = redirect.url.ignoreSlash ? this.$links.unTrailingSlashIt(redirect.url.url) : redirect.url.url,
					compareTarget = redirect.url.ignoreSlash ? this.$links.unTrailingSlashIt(redirect.targetUrl) : redirect.targetUrl
				compareSource = redirect.url.ignoreCase ? compareSource.toLowerCase() : compareSource
				compareTarget = redirect.url.ignoreCase ? compareTarget.toLowerCase() : compareTarget
				if (compareSource === compareTarget ||
					(
						redirect.url.regex &&
						compareTarget.match(compareSource)
					)
				) {
					errors.push(this.$t.__('Your source is the same as a target and this will create a loop.', this.$td))
				}
			}

			// Protected path
			if (0 < this.redirectsStore?.protectedPaths.length) {
				const normalizedPaths = this.redirectsStore.protectedPaths.map(path => path.replace(/\/$/, ''))
				if (redirect.url.url.match(new RegExp('^(' + normalizedPaths.join('|') + ')'))) {
					errors.push(this.$t.__('Your source is a protected path and cannot be redirected.', this.$td))
				}
			}

			return errors
		}
	}
}