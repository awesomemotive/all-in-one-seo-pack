import { sanitizeUrl } from '@/vue/utils/strings'

/**
 * Writes a sanitized URL into the element's href.
 *
 * sanitizeUrl() allows only http, https, mailto and tel, so a javascript: or data: URL becomes
 * "#" rather than something that runs when the link is clicked. Vue already prevents a binding
 * from breaking out of the attribute, but it does not look at the scheme.
 *
 * @since 5.0.6
 *
 * @param  {HTMLElement} el      The element the directive is bound to.
 * @param  {Object}      binding The directive binding.
 * @return {void}
 */
const setHref = (el, binding) => {
	// Mirror Vue's own attribute binding, where null and undefined drop the attribute entirely.
	if (null === binding.value || undefined === binding.value) {
		el.removeAttribute('href')

		return
	}

	el.setAttribute('href', sanitizeUrl(String(binding.value)))
}

export default {
	mounted : setHref,
	updated : setHref
}