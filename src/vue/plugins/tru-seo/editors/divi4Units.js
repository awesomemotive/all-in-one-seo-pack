// How a Divi 4 module is identified, read and written to. Kept free of the builder's globals so
// every rule is testable without a builder: the adapter passes in the node and the canvas window.

// React exposes a DOM node's fiber under a per-build suffix: `__reactInternalInstance$` on the
// React 16 Divi 4 ships, `__reactFiber$` from 17 on. Both are matched so a future Divi bump does
// not silently turn every module into "not a unit".
const FIBER_KEY_PREFIXES = [ '__reactInternalInstance$', '__reactFiber$' ]

// How far up from a module's DOM node its component sits. Measured at 2-3 hops
// (`ETBuilderModule`, then `WithVisibilitySensor(ETBuilderModule)`); the ceiling only stops a walk
// that would otherwise climb to the document root.
const MAX_FIBER_HOPS = 30

const MODULE_CLASS = /et-module-([0-9a-f-]{36})/

/**
 * The module's stable id, taken from the `et-module-<uuid>` class Divi renders onto it.
 *
 * Preferred over the module's ADDRESS ("0.1.0.0") as a unit key: an address is a position and
 * changes when anything above the module is reordered or deleted, which would silently re-point
 * every mark on the page. The uuid survives both.
 *
 * @param {?Element} node A module element.
 * @returns {?string} The uuid, or null when the node is not a module.
 */
export const moduleUuid = (node) => {
	const match = MODULE_CLASS.exec(node?.className || '')

	return match ? match[1] : null
}

/**
 * Reads a Divi 4 module's component props: its `address`, `type` and stored `content`.
 *
 * THE STORED VALUE, NOT THE RENDERED DOM, and that is the whole point of reading props at all.
 * Divi renders `content` through the_content-style filters, so the DOM carries `<p>` wrappers and
 * entity-decoded text the stored value does not. Marks measured against the DOM could not be
 * located in the string a write has to edit.
 *
 * NOTE: this reads React's internals, which is why every caller treats null as "not a unit" and
 * degrades to no highlighting rather than failing. Divi 4 exposes no public read API --
 * `ET_Builder.API` on the canvas window carries registration helpers only.
 *
 * @param {?Element} node A module element.
 * @returns {?Object} The component's props, or null when they cannot be read.
 */
export const readModuleProps = (node) => {
	if (!node) {
		return null
	}

	const key = Object.keys(node).find(name => FIBER_KEY_PREFIXES.some(prefix => name.startsWith(prefix)))
	if (!key) {
		return null
	}

	let fiber = node[key],
		hops  = 0

	while (fiber && hops < MAX_FIBER_HOPS) {
		// Host fibers hold the DOM element in `stateNode`, whose `props` is undefined, so only a
		// component instance can satisfy this.
		const props = fiber.stateNode?.props

		if (props && 'string' === typeof props.address) {
			return props
		}

		fiber = fiber.return
		hops++
	}

	return null
}

/**
 * Whether a module's props describe a unit that holds reader-visible text.
 *
 * Decided from the SHAPE of `content`, not a list of module names. A leaf module stores its prose
 * as an HTML string; a section, row or column stores its CHILDREN there as an object, so
 * containers drop out without this file having to know they exist -- and a third-party module that
 * stores prose the same way is included for free.
 *
 * @param {?Object} props A module's props.
 * @returns {boolean} True when the module is a text unit.
 */
export const isTextModuleProps = (props) => 'string' === typeof props?.content && !!props.content.trim()

// Attributes that hold reader-visible text outside `content`, split by where they read relative to
// it. A list rather than a shape test: every Divi 4 attribute is a string, settings included.
const LEADING_TEXT_ATTRS  = [ 'title', 'heading', 'subhead', 'subtitle', 'name', 'position', 'author', 'job_title', 'company_name', 'artist_name', 'album_name' ]
const TRAILING_TEXT_ATTRS = [ 'button_text', 'button_one_text', 'button_two_text' ]

/**
 * Undoes Divi's attribute encoding ({@see Common/Standalone/PageBuilders/Divi.php:decodeAttributeValue}).
 *
 * @param {string} value The raw attribute value.
 * @returns {string} The decoded value.
 */
export const decodeAttrText = (value) => String(value ?? '')
	.replaceAll('%22', '"')
	.replaceAll('%92', '\\')
	.replaceAll('%91', '[')
	.replaceAll('%93', ']')

/**
 * Reads a module's text attributes, in the order they read around its `content`.
 *
 * @param {?Object} props A module's props.
 * @returns {{leading: Array<{name: string, text: string}>, trailing: Array<{name: string, text: string}>}} The attributes.
 */
export const readTextAttrs = (props) => {
	const attrs = props?.attrs || {}
	const read  = (names) => names
		.filter(name => 'string' === typeof attrs[name] && !!attrs[name].trim())
		.map(name => ({ name, text: decodeAttrText(attrs[name]) }))

	return { leading: read(LEADING_TEXT_ATTRS), trailing: read(TRAILING_TEXT_ATTRS) }
}

/**
 * Finds the inline TinyMCE editor Divi has open over one module, if any.
 *
 * Divi 4 has no exposed store write, so a module's own inline editor IS the write path: setting its
 * content and firing `blur` is what commits to the builder's state (measured; `setContent` alone
 * commits nothing). The editor exists only after the user has clicked that module's text -- Divi
 * creates it on a TRUSTED click and synthetic events do not open it -- so callers must treat null
 * as a refusal rather than an error.
 *
 * Matched through `targetElm`, the element TinyMCE was attached to, so the editor is tied to the
 * module by containment rather than by the creation-ordered `react-tinymce-N` id.
 *
 * @param {?Window}  canvasWindow The builder canvas window.
 * @param {?Element} moduleNode   The module element.
 * @returns {?Object} The editor, or null when the module has none open.
 */
export const findInlineEditor = (canvasWindow, moduleNode) => {
	const editors = canvasWindow?.tinymce?.editors
	if (!editors?.length || !moduleNode) {
		return null
	}

	for (const editor of editors) {
		const target = editor?.targetElm || editor?.getElement?.()

		if (target && (moduleNode === target || moduleNode.contains(target))) {
			return editor
		}
	}

	return null
}

/**
 * Finds the change handler Divi passed to one module's inline TinyMCE.
 *
 * THIS IS THE COMMIT. Divi's editor `setup` binds TinyMCE events to React props
 * (`e.on(name, evt => props[handler](evt, e))`), so the prop is Divi's own change pipeline: it
 * reads `editor.getContent()` and writes the module. Measured on 4.27.8, in the database:
 *   setContent alone          -> nothing
 *   setContent + fire('change') -> nothing
 *   setContent + fire('blur')   -> commits, but ONLY while the editor is still active, which a
 *                                  click on our popover has already ended
 *   setContent + props.onChange -> commits, active or not, and survives Divi's save
 *
 * Read off `memoizedProps` rather than `stateNode.props`: the handler sits on the `TinyMCE`
 * wrapper element's fiber, which has no component instance of its own.
 *
 * @param {?Object} editor A TinyMCE editor.
 * @returns {?Function} The handler, or null when it cannot be reached.
 */
export const findEditorChangeHandler = (editor) => {
	const start = editor?.targetElm || editor?.getElement?.()
	if (!start) {
		return null
	}

	const key = Object.keys(start).find(name => FIBER_KEY_PREFIXES.some(prefix => name.startsWith(prefix)))
	if (!key) {
		return null
	}

	let fiber = start[key],
		hops  = 0

	while (fiber && hops < MAX_FIBER_HOPS) {
		const props = fiber.memoizedProps

		if (props && 'function' === typeof props.onChange) {
			return props.onChange
		}

		fiber = fiber.return
		hops++
	}

	return null
}