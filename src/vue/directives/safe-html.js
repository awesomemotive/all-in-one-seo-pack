import DOMPurify from 'dompurify'

// Every "Learn More" link in the admin relies on target="_blank", and DOMPurify drops
// target by default. Nothing else in the codebase needs widening beyond the defaults.
const CONFIG = {
	ADD_ATTR : [ 'target' ]
}

/**
 * Writes sanitized markup into the element.
 *
 * NOTE: The value is deliberately not entity-decoded first. Some of our strings show markup as
 * visible text (e.g. "<em>&lt;head&gt;</em>"), and decoding would turn that into a real tag for
 * DOMPurify to strip, silently emptying the element.
 *
 * @since 5.0.6
 *
 * @param  {HTMLElement} el      The element the directive is bound to.
 * @param  {Object}      binding The directive binding.
 * @return {void}
 */
const setContent = (el, binding) => {
	// Coerce so numeric bindings still render, but leave null/undefined empty.
	const value = null === binding.value || undefined === binding.value ? '' : String(binding.value)

	el.innerHTML = DOMPurify.sanitize(value, CONFIG)
}

export default {
	mounted : setContent,
	updated : (el, binding) => {
		// Like v-html, leave the DOM alone when the value is unchanged. Rewriting it on every parent
		// update replaces the nodes under the pointer, so a click that straddles a re-render is lost.
		if (binding.value === binding.oldValue) {
			return
		}

		setContent(el, binding)
	}
}