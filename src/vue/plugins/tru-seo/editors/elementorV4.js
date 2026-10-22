import { findSentenceRanges, matchCase } from '@/vue/utils/regex'
import { isElementorEditor } from '@/vue/utils/context'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'
import { buildTextStream } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { revealElementorNode } from './elementorReveal'
import { requestPostUpdate } from '@/vue/plugins/tru-seo/spellingSuggestions'
import elementorV3Adapter, {
	armPenClosePoll,
	ensurePenOpenWatch,
	suppressNextPenArm,
	getElementorRoot,
	isDynamicUnit,
	isPenOpenAt,
	isWordInUnits,
	subscribeToInvalidation,
	watchElementRenders
} from './elementor'

import {
	alignProjectedMarks,
	clearDecorations as clearDomDecorations,
	decorateNode,
	ensureDecorationStyles,
	foldNbsp
} from './domDecorator'

import { editStoredHtml, parseInert } from './storedHtmlEdit'

// Read from the experiment flag, not from V4's packages: those are footer scripts that land
// after resolveAdapter() has already memoised its answer, which would pin V3 for the page.
//
// NOTE: the flag is site-level, so a page built only from V3 widgets resolves here too, and the
// unit source has to cover those widgets.
const isV4Site = () => !!window?.elementorCommon?.config?.experimentalFeatures?.e_atomic_elements

// Where V4's editor packages publish themselves, read straight off the window: this build
// carries no `@elementor/*` dependency and no vite externals entry to resolve one through.
const getElementApi = () => window?.elementorV2?.editorElements || null

// V4's exports still move, so a renamed one must read as "not ready" rather than throw mid-paint.
const requiredElementApi = [
	'getElements',
	'getElementSetting',
	'getElementType',
	'updateElementSettings'
]

// Null unless every symbol this adapter drives V4 through is callable, so a caller can never
// half-use a drifted API.
const getUsableElementApi = () => {
	const api = getElementApi()

	return api && requiredElementApi.every(name => 'function' === typeof api[name]) ? api : null
}

// A separate namespace from the element API: it carries the html parser and the constructors
// a write needs.
const getPropsApi = () => window?.elementorV2?.editorProps || null

// Null unless the whole write vocabulary is callable: the union can't be hand-assembled
// without losing per-child styling (see buildHtmlValue), so a renamed export means "cannot write".
const getUsablePropsApi = () => {
	const api = getPropsApi()
	if ('function' !== typeof api?.parseHtmlChildren) {
		return null
	}

	const utils = [ 'htmlV3PropTypeUtil', 'stringPropTypeUtil' ]

	return utils.every(name => 'function' === typeof api[name]?.create) ? api : null
}

// Elementor's own text prop-type keys (inline-editing-eligibility).
const HTML_PROP_TYPE         = 'html-v3'
const STRING_PROP_TYPE       = 'string'
const ESCAPED_HTML_PROP_TYPE = 'escaped-html'

// Atomic text props: Elementor 4.3 stores them as one HTML string under this type.
const isEscapedHtmlPropValue = (value) => {
	return !!value && ESCAPED_HTML_PROP_TYPE === value.$$type && (null === value.value || 'string' === typeof value.value)
}

const isStringPropValue = (value) => {
	return !!value && STRING_PROP_TYPE === value.$$type && (null === value.value || 'string' === typeof value.value)
}

// Looser than Elementor's zod schema: `children` is required there but optional in the PHP that
// produces the value, and rejecting it would only lose analysis coverage.
const isHtmlPropValue = (value) => {
	if (!value || HTML_PROP_TYPE !== value.$$type || !value.value || 'object' !== typeof value.value) {
		return false
	}

	const content = value.value.content

	return null === content || undefined === content || isStringPropValue(content)
}

// Mirrors Elementor's isAllowedBySchema, including its union arm.
const isTextPropSchema = (schema) => {
	if (!schema) {
		return false
	}

	if (HTML_PROP_TYPE === schema.key || STRING_PROP_TYPE === schema.key || ESCAPED_HTML_PROP_TYPE === schema.key) {
		return true
	}

	if ('union' !== schema.kind) {
		return false
	}

	return !!schema.prop_types?.[HTML_PROP_TYPE] || !!schema.prop_types?.[STRING_PROP_TYPE] || !!schema.prop_types?.[ESCAPED_HTML_PROP_TYPE]
}

// Value-first, like Elementor's isInlineEditingAllowed: testing the schema would admit a dynamic
// value, so analysis would read tag syntax and a fix would overwrite a live binding.
const isTextPropValue = (value, schema) => {
	if (null === value || undefined === value) {
		return isTextPropSchema(schema)
	}

	return isHtmlPropValue(value) || isStringPropValue(value) || isEscapedHtmlPropValue(value)
}

const INLINE_EDITING_CONTROL = 'inline-editing'

// Which props of an element type are its content: an `html-v3` value, or a prop bound to an
// `inline-editing` control.
//
// NOTE: the eligibility test above is a guard, not discovery. Sweeping it across every prop
// admits config strings (`tag`, `_cssid`, URLs) into analysis and makes them fix targets.
// Walked structurally so a new wrapper can't hide a binding; `visited` guards cycles.
const collectInlineEditableKeys = (controls) => {
	const keys    = new Set()
	const visited = new Set()

	const visit = (node) => {
		if (!node || 'object' !== typeof node || visited.has(node)) {
			return
		}

		visited.add(node)

		if (INLINE_EDITING_CONTROL === node.type && 'string' === typeof node.bind) {
			keys.add(node.bind)
		}

		Object.values(node).forEach(visit)
	}

	visit(controls)

	return keys
}

// Memoised per element type, per pass, so repeated elements don't re-walk one controls tree.
const getInlineEditableKeys = (cache, type, elementType) => {
	if (!cache.has(type)) {
		cache.set(type, collectInlineEditableKeys(elementType.controls))
	}

	return cache.get(type)
}

// A traversal node: `getElements()` answers V1 containers, while a container's child
// collection holds bare Backbone models. Both carry the model, which is all a walk needs.
const toElementNode = (candidate) => {
	const model = candidate?.model || candidate || null
	if ('function' !== typeof model?.get) {
		return null
	}

	const id = model.get('id') || candidate?.id || null
	if (!id) {
		return null
	}

	// Elementor's own rule (getSelectedElements): a widget is addressed by its widgetType, a
	// structural element by its elType.
	return { id, type: model.get('widgetType') || model.get('elType') || null, model }
}

const getChildElements = (model) => {
	const children = model?.get?.('elements')
	if (Array.isArray(children)) {
		return children
	}

	// A Backbone collection. Read `.models` rather than spreading it — spreading an object
	// that turns out not to be iterable throws, and this walk has to survive drift.
	return Array.isArray(children?.models) ? children.models : []
}

// Depth-first in document order: unit order becomes the analysed content's word order.
//
// NOTE: the recursion is drift insurance; `seen` dedupes elements getElements() already
// flattened and doubles as the cycle guard.
const walkElements = (api) => {
	const nodes = []
	const seen  = new Set()

	const visit = (candidate) => {
		const node = toElementNode(candidate)
		if (!node || seen.has(node.id)) {
			return
		}

		seen.add(node.id)
		nodes.push(node)
		getChildElements(node.model).forEach(visit)
	}

	const roots = api.getElements()
	if (Array.isArray(roots)) {
		roots.forEach(visit)
	}

	return nodes
}

// foldNbsp, not normalizeWhitespaces: this is the space a mark's `range` indexes, and folding a
// literal six-character `&nbsp;` to one would put every later offset five characters early.
const projectHtml = (html) => (html ? foldNbsp(buildTextStream(parseInert(html)).text) : '')

// A `string` prop holds plain text, so parsing it would silently eat anything shaped like a
// tag; only `html-v3` and `escaped-html` content is markup.
const projectPropText = (value) => {
	if (isStringPropValue(value)) {
		return foldNbsp(value.value || '')
	}

	if (isHtmlPropValue(value)) {
		return projectHtml(value.value.content?.value || '')
	}

	if (isEscapedHtmlPropValue(value)) {
		return projectHtml(value.value || '')
	}

	return ''
}

// The `[data-id]` wrapper — the element's node in the canvas, NOT necessarily the node marks
// paint into: it carries an inline `display: contents`, so its own rect is 0x0, and Elementor
// resolves the paintable node per element class.
const resolveElementNode = (id) => {
	try {
		return getElementorRoot()?.querySelector(`[data-id="${id}"]`) || null
	} catch (_e) {
		return null
	}
}

// Returns `{ value, text }` for an (element, prop) pair that is atomic (T1) content, else null.
//
// NOTE: the whole-canvas unit (T3) and legacy units (T2) both answer null here; legacy units are
// routed to V3 elsewhere ({@see getLegacyUnits}).
const readAtomicProp = (api, schema, inlineEditable, ownerId, attrName) => {
	const propSchema = schema?.[attrName]

	// Cheap gate before the read: getElementSetting() is a full view-tree scan per call, and
	// this runs for every prop of every element on every edit.
	if (!inlineEditable.has(attrName) && !isTextPropSchema(propSchema)) {
		return null
	}

	// The singular read: the plural form throws when given one argument. An untouched prop is
	// stored as null but renders its schema default, so fall back the way Elementor does.
	const value = api.getElementSetting(ownerId, attrName) ?? propSchema?.default ?? null

	// A `disabled` prop is never rendered, and a write through buildHtmlValue() would drop the
	// flag and re-enable it.
	if (true === value?.disabled) {
		return null
	}

	if (!isTextPropValue(value, propSchema) || (!isHtmlPropValue(value) && !isEscapedHtmlPropValue(value) && !inlineEditable.has(attrName))) {
		return null
	}

	return { value, text: projectPropText(value) }
}

// The projection alone. Null means "not atomic content"; '' means "empty atomic content".
const projectAtomicProp = (api, schema, inlineEditable, ownerId, attrName) => {
	const read = readAtomicProp(api, schema, inlineEditable, ownerId, attrName)

	return read ? read.text : null
}

// T1's projection for one marked unit, through the same reader getContentUnits() uses.
const projectAtomicUnit = (api, keyCache, type, parsed) => {
	const elementType = api.getElementType(type)

	return elementType
		? projectAtomicProp(
			api,
			elementType.propsSchema,
			getInlineEditableKeys(keyCache, type, elementType),
			parsed.ownerId,
			parsed.attrName
		)
		: null
}

// Elementor's own marker for "this wrapper is a leaf atomic widget", published for external APIs.
const ATOMIC_WRAPPER_ATTRIBUTE = 'data-atomic'

// Which node a unit's marks paint into — not getNode(), which stays on the `[data-id]` wrapper
// getContentUnits() ranks by.
//
// NOTE: mirrors Elementor's getDomElement(): a leaf atomic widget paints into its first child
// (its wrapper is `display: contents`), a nested element into the wrapper itself.
const resolvePaintNode = (ownerId) => {
	try {
		const wrapper = resolveElementNode(ownerId)
		if (!wrapper) {
			return null
		}

		if (!wrapper.hasAttribute?.(ATOMIC_WRAPPER_ATTRIBUTE)) {
			return wrapper
		}

		// An atomic widget that rendered bare text still has its text under the wrapper. The
		// alignment guard below is what keeps that honest, not this fallback.
		return wrapper.firstElementChild || wrapper
	} catch (_e) {
		return null
	}
}

// Where an open inline editor shows up in the canvas DOM: atomic widgets mount a tiptap editor
// into the paint node, and `contenteditable="true"` also covers the legacy pen
// ({@see isSuspended}).
const INLINE_EDITING_SELECTOR = '[contenteditable="true"], .tiptap, .ProseMirror'

// Whether `node` is, contains, or sits inside an open inline editor.
//
// NOTE: both directions, because tiptap mounts inside the paint node while a paint node can
// itself sit under another element's editable subtree.
const isEditingAt = (node, selector) => {
	try {
		return !!(node?.closest?.(selector) || node?.querySelector?.(selector))
	} catch (_e) {
		// A node that cannot be queried is one that cannot be cleared of an open editor.
		return true
	}
}

const isInlineEditingAt = (node) => isEditingAt(node, INLINE_EDITING_SELECTOR)

// tiptap's own markers only, so a legacy pen is invisible to it ({@see isSuspended}).
const ATOMIC_INLINE_EDITING_SELECTOR = '.tiptap, .ProseMirror'

const isAtomicInlineEditingAt = (node) => isEditingAt(node, ATOMIC_INLINE_EDITING_SELECTOR)

// How many macrotask turns a dismissal is given before the write is refused. Macrotasks, because
// React's Scheduler and tiptap's destroy() both run behind timers a microtask wait never yields to.
const INLINE_EDITOR_DISMISS_TURNS = 12

const nextTurn = () => new Promise(resolve => setTimeout(resolve, 0))

// Elementor's own dismissal route: CanvasInlineEditor dismisses on a canvas-document `mousedown`
// outside its content element, and the TruSEO popover isn't one of the panels it otherwise watches.
//
// NOTE: dispatched on the document itself, so no delegated jQuery handler in Elementor matches it.
const requestInlineEditorDismissal = (doc) => {
	try {
		// The canvas window's own constructor, never this one's: the canvas is an iframe, so an
		// event built here belongs to a different realm.
		//
		// NOTE: the composable's click-outside handler hears this too and cancels any armed
		// advance, so a caller must capture its advance keys before awaiting the write.
		const MouseEventCtor = doc?.defaultView?.MouseEvent
		if ('function' !== typeof MouseEventCtor || 'function' !== typeof doc.dispatchEvent) {
			return false
		}

		doc.dispatchEvent(new MouseEventCtor('mousedown', { bubbles: true, cancelable: true }))

		return true
	} catch (_e) {
		return false
	}
}

// True once nothing under `resolveNode()` is inline edited, after driving Elementor's dismissal
// and observing it complete.
//
// NOTE: an open editor keeps the document it opened with, so the next keystroke would overwrite
// the fix. The node is re-resolved each turn because unmounting the editor re-renders the widget.
const dismissInlineEditing = async (resolveNode) => {
	try {
		if (!isInlineEditingAt(resolveNode())) {
			return true
		}

		if (!requestInlineEditorDismissal(getElementorRoot()?.ownerDocument)) {
			return false
		}

		for (let turn = 0; turn < INLINE_EDITOR_DISMISS_TURNS; turn++) {
			await nextTurn()

			if (!isInlineEditingAt(resolveNode())) {
				return true
			}
		}

		return false
	} catch (_e) {
		// Fail closed, for isInlineEditingAt()'s own reason: a canvas that cannot be probed is one
		// that cannot be confirmed clear of an editor holding a stale document.
		return false
	}
}

// Every (element, content prop) pair in document order. Shared by the read and write paths so
// they never disagree on which props are content.
const eachAtomicProp = (api, visit) => {
	const keyCache = new Map()

	for (const element of walkElements(api)) {
		// Per element, so one throwing widget doesn't drop the rest of the page's content or writes.
		try {
			const elementType = api.getElementType(element.type)
			const schema      = elementType?.propsSchema
			if (!schema) {
				continue
			}

			const inlineEditable = getInlineEditableKeys(keyCache, element.type, elementType)

			for (const attrName of Object.keys(schema)) {
				const read = readAtomicProp(api, schema, inlineEditable, element.id, attrName)
				if (read) {
					visit({ ownerId: element.id, attrName, value: read.value, text: read.text })
				}
			}
		} catch (_e) {
			continue
		}
	}
}

// T1 — atomic units from the props model, so a unit's text and its write target share one
// coordinate space and a lagging remote render can't be read as content.
const getAtomicUnits = (api) => {
	const units = []

	eachAtomicProp(api, ({ ownerId, attrName, text }) => {
		units.push({
			unitId   : `${ownerId}:${attrName}`,
			ownerId,
			attrName,
			html     : text,
			writable : true,
			getNode  : () => resolveElementNode(ownerId)
		})
	})

	return units
}

// One (element, prop) pair by name, through the same gate as the enumeration so a write can't
// reach a dynamic binding, a config string or a legacy widget.
const readAtomicPropById = (api, ownerId, attrName) => {
	const type = walkElements(api).find(element => element.id === ownerId)?.type
	if (!type) {
		return null
	}

	const elementType = api.getElementType(type)
	if (!elementType?.propsSchema) {
		return null
	}

	return readAtomicProp(
		api,
		elementType.propsSchema,
		getInlineEditableKeys(new Map(), type, elementType),
		ownerId,
		attrName
	)
}

// The tier test: T1 is an element type with an atomic schema (getElementType() answers null
// otherwise). Not "the element walk knows this id" — getElements() returns legacy widgets too.
//
// NOTE: a type that can't be classified answers true, so the write refuses instead of handing V3
// a prop union it would silently skip.
const isAtomicType = (api, type) => {
	if (!type) {
		return false
	}

	try {
		return !!api.getElementType(type)
	} catch (_e) {
		return true
	}
}

// The same test for an ownerId. A throwing walk answers false: the unit source then reads the
// whole document as non-atomic too.
const isAtomicElement = (api, ownerId) => {
	try {
		return isAtomicType(api, walkElements(api).find(element => element.id === ownerId)?.type)
	} catch (_e) {
		return false
	}
}

// The opt-in marker V3's own scrape uses ({@see elementor.js:getUnits}); the probe must answer for
// the same set as the roster.
const LEGACY_MARKER_SELECTOR = '[data-elementor-setting-key]'

// T2 — legacy widgets, which have no props schema and would otherwise vanish from a mixed page.
// Delegated to V3 and handed on unchanged: their writes go through V3 too, which re-resolves the
// target in the stored value ({@see elementor.js:replaceMark}).
const getLegacyUnits = () => {
	try {
		return elementorV3Adapter.getContentUnits() || []
	} catch (_e) {
		return []
	}
}

// Whether the document has any legacy marker, so an atomic-only page skips the per-keystroke
// `set-is-modified` repaints ({@see onInvalidated}). Probed live: a legacy widget can be added
// at any time.
//
// NOTE: only bounds a cost, so a throwing canvas answers true (an extra idempotent pass).
const hasLegacyUnits = () => {
	try {
		const root = getElementorRoot()

		return root ? !!root.querySelector(LEGACY_MARKER_SELECTOR) : false
	} catch (_e) {
		return true
	}
}

// One legacy unit by id, through V3's single-unit lookup rather than a filter over the whole
// roster ({@see elementor.js:resolveContentUnit}). Not memoised: the roster changes with renders.
const findLegacyUnit = (unitId) => {
	try {
		return elementorV3Adapter.getContentUnit?.(unitId) || null
	} catch (_e) {
		return null
	}
}

// Why a T2 unit can't carry a mark, in V3 decorate()'s order: the shared write refuses on
// exactly these grounds, so painting one would offer a fix that can only refuse.
const refuseLegacyUnit = (unit) => {
	if (!unit.ownerId) {
		return 'no-owner'
	}

	if (false === unit.writable) {
		return 'not-writable'
	}

	return isDynamicUnit(unit) ? 'dynamic' : null
}

// Sorts units into reading order across tiers, so assessments that read the opening of the
// content see a leading legacy widget where it actually is.
//
// NOTE: ranked from one `[data-id]` walk keyed by ownerId, not per-unit getNode() lookups. An
// unresolved unit inherits its predecessor's rank; ties keep the order their tier emitted.
const sortUnitsByDocumentPosition = (units) => {
	// A throw here must not reach getContentUnits()'s catch and discard the recovered units.
	try {
		const root  = getElementorRoot()
		const order = new Map()

		if (root) {
			root.querySelectorAll('[data-id]').forEach((node, index) => {
				const id = node.getAttribute('data-id')

				// First occurrence wins, so a duplicated id cannot pull its element later than
				// where it actually opens.
				if (id && !order.has(id)) {
					order.set(id, index)
				}
			})
		}

		let rank = -1

		return units
			.map((unit, index) => {
				rank = order.get(unit.ownerId) ?? rank

				return { unit, index, rank }
			})
			.sort((a, b) => (a.rank - b.rank) || (a.index - b.index))
			.map(entry => entry.unit)
	} catch (_e) {
		return units
	}
}

const DEGRADED_ATTR_NAME = 'document'

// T3 — last resort when the tiers above produce nothing: the whole canvas as one read-only unit.
const getDegradedUnits = () => {
	try {
		const root = getElementorRoot()
		if (!root) {
			return []
		}

		const html = normalizeWhitespaces(buildTextStream(root).text)
		if (!html.trim()) {
			return []
		}

		const ownerId = String(window?.elementor?.documents?.getCurrent?.()?.id || '')

		return [ {
			unitId   : `${ownerId}:${DEGRADED_ATTR_NAME}`,
			ownerId,
			attrName : DEGRADED_ATTR_NAME,
			html,
			writable : false,
			getNode  : () => getElementorRoot()
		} ]
	} catch (_e) {
		return []
	}
}

const parseUnitId = (unitId) => {
	const separator = String(unitId ?? '').indexOf(':')
	if (0 >= separator) {
		return null
	}

	return { ownerId: unitId.slice(0, separator), attrName: unitId.slice(separator + 1) }
}

const isAddressableMark = (mark) => !!mark?.unitId && !!mark.range

// decorate() is document-scoped and takes the whole store mark set, so the grouping is the
// adapter's job.
const groupMarksByUnit = (marks) => {
	const grouped = new Map()

	for (const mark of marks.filter(isAddressableMark)) {
		if (!grouped.has(mark.unitId)) {
			grouped.set(mark.unitId, [])
		}

		grouped.get(mark.unitId).push(mark)
	}

	return grouped
}

// A mark that can't be placed is dropped and counted, never guessed at: a guess could highlight
// one occurrence while the fix lands on another.
const decorationDiagnostics = { passes: 0, dropped: 0, suspended: 0, refusedWrites: 0, byReason: {} }

let lastReportedDrops = ''

/**
 * Counters for what the paint and write paths refused, cumulative for the page's lifetime.
 *
 * @returns {{passes: number, dropped: number, suspended: number, refusedWrites: number, byReason: Object}} The diagnostics snapshot.
 */
export const getDecorationDiagnostics = () => ({
	passes        : decorationDiagnostics.passes,
	dropped       : decorationDiagnostics.dropped,
	suspended     : decorationDiagnostics.suspended,
	refusedWrites : decorationDiagnostics.refusedWrites,
	byReason      : { ...decorationDiagnostics.byReason }
})

// Why the last write refused, or null when it landed. Legacy reasons are V3's, forwarded only when
// the combined total across both tiers is zero, so an atomic success isn't reported as a failure.
//
// NOTE: atomic refusals name no reason, so on a mixed page a failed fix surfaces the legacy tier's
// reason; that beats publishing null, which would show no alert at all ({@see editors/types.js}).
const writeRefusal = { reason: null }

/**
 * Reads and clears why the last write attempt refused.
 *
 * @returns {?string} The refusal reason, or null when the write landed or none was recorded.
 */
const consumeWriteRefusal = () => {
	const reason = writeRefusal.reason

	writeRefusal.reason = null

	return reason
}

// Clears both slots, so an atomic refusal can never read a legacy pass's reason as its own.
const beginWrite = () => {
	writeRefusal.reason = null
	elementorV3Adapter.consumeWriteRefusal?.()
}

// A write refused because an open inline editor could not be confirmed dismissed. Warns every
// time: each refusal is a separate failed user action.
const recordRefusedWrite = () => {
	decorationDiagnostics.refusedWrites++

	console.warn('AIOSEO: the TruSEO fix was not applied because the Elementor inline editor stayed open — writing under it would be discarded on the next keystroke.')
}

const recordDroppedMarks = (dropped) => {
	decorationDiagnostics.passes++

	if (!dropped.length) {
		// So a recurrence after a clean pass is reported again.
		lastReportedDrops = ''

		return
	}

	const counts = {}
	for (const entry of dropped) {
		decorationDiagnostics.dropped++
		decorationDiagnostics.byReason[entry.reason] = (decorationDiagnostics.byReason[entry.reason] || 0) + 1
		counts[entry.reason] = (counts[entry.reason] || 0) + 1
	}

	// Repaint runs on every render event, so an unchanged failure would otherwise flood the
	// console. Reported once per distinct outcome, not once per pass.
	const summary = Object.keys(counts).sort().map(reason => `${reason}: ${counts[reason]}`).join(', ')
	if (summary === lastReportedDrops) {
		return
	}

	lastReportedDrops = summary
	console.warn(`AIOSEO: ${dropped.length} TruSEO highlight(s) could not be placed in the Elementor canvas (${summary}).`)
}

// The one place a refusal is recorded. Nulls the mark's node: the pass already detached it, and
// sanitizeHighlightMarks() prunes on `!!hm.node`, not connectedness, so a popover would anchor on it.
const refuseOne = (dropped, mark, reason) => {
	// Skipped rather than thrown: this also runs inside decorate()'s outer catch.
	if (!mark) {
		return
	}

	mark.node = null
	dropped.push({ mark, reason })
}

const refuse = (dropped, marks, reason) => {
	for (const mark of marks) {
		refuseOne(dropped, mark, reason)
	}
}

// Marks skipped because their unit is open in an inline editor — counted apart from `dropped`,
// with the node nulled for refuseOne()'s reason.
const suspendMarks = (marks, suspended) => {
	for (const mark of marks) {
		if (!mark) {
			continue
		}

		mark.node = null
		decorationDiagnostics.suspended++
		suspended?.add(mark)
	}
}

// The stored union for an html-v3 prop, rebuilt the way Elementor's setContentValue does.
//
// NOTE: never hand-assembled — parseHtmlChildren returns the `children` that V4's per-child
// styling is keyed by, and `content` must come from it too since it mutates what it parses.
const buildHtmlValue = (props, html) => {
	const parsed = props.parseHtmlChildren(html)

	return props.htmlV3PropTypeUtil.create({
		content  : parsed.content ? props.stringPropTypeUtil.create(parsed.content) : null,
		children : parsed.children
	})
}

// Projects the stored value, applies the ranges `plan` names in that projection, and returns
// the rebuilt union with a count. Null when nothing applied.
const writeReplacements = (props, value, plan) => {
	if (isHtmlPropValue(value)) {
		// NOTE: buildHtmlValue re-serializes the whole value, so unlike V3 the bytes outside the
		// edit are normalized too.
		const edited = editStoredHtml(value.value.content?.value || '', plan)

		return edited ? { value: buildHtmlValue(props, edited.html), count: edited.count } : null
	}

	// Older Elementor releases do not export this util; without it the write refuses.
	if (isEscapedHtmlPropValue(value)) {
		const edited = 'function' === typeof props.escapedHtmlPropTypeUtil?.create
			? editStoredHtml(value.value || '', plan)
			: null

		return edited ? { value: props.escapedHtmlPropTypeUtil.create(edited.html), count: edited.count } : null
	}

	if (isStringPropValue(value)) {
		const stored    = value.value || ''
		const projected = foldNbsp(stored)
		const edits     = plan(projected)
		if (!edits.length) {
			return null
		}

		// foldNbsp is one character for one, so these offsets index `stored` directly.
		let text = stored

		for (const edit of [ ...edits ].sort((a, b) => b.start - a.start)) {
			text = text.slice(0, edit.start) + edit.text + text.slice(edit.end)
		}

		return { value: props.stringPropTypeUtil.create(text), count: edits.length }
	}

	return null
}

// Where this mark's sentence sits in `projected` now, or null when that's ambiguous.
//
// NOTE: falls back to a search only when the sentence occurs exactly once, so a fix never lands
// on a different occurrence than the one highlighted.
const resolveMarkRange = (projected, mark) => {
	const sentence = 'string' === typeof mark?.sentence && mark.sentence ? mark.sentence : null
	if (!sentence) {
		return null
	}

	const start = mark.range?.start
	const end   = mark.range?.end

	if (0 <= start && start < end && end <= projected.length && projected.slice(start, end) === sentence) {
		return { start, end }
	}

	const found = findSentenceRanges(projected, sentence)

	return 1 === found.length ? found[0] : null
}

const writeProp = (api, ownerId, attrName, value) => {
	// withHistory, so a fix is one undoable step in Elementor's history, like an inline edit.
	api.updateElementSettings({ id: ownerId, props: { [attrName]: value }, withHistory: true })
}

const replaceOneMark = async (mark, replacement) => {
	beginWrite()

	// No fix flow deletes, so an empty replacement is refused.
	if (!mark?.unitId || !replacement || 'string' !== typeof replacement) {
		return false
	}

	const parsed = parseUnitId(mark.unitId)
	if (!parsed) {
		return false
	}

	const api = getUsableElementApi()

	// T2 — a legacy widget, delegated to V3. Gated on the legacy roster and on the model not
	// claiming the element ({@see isAtomicType}), never on the atomic read failing.
	//
	// NOTE: ahead of the props gate (V3 doesn't need editorProps) and of the dismissal, which
	// can't close a legacy pen and would refuse the fix ({@see elementor.js:replaceMark}).
	const claimedByModel = !!api && isAtomicElement(api, parsed.ownerId)

	if (!claimedByModel && findLegacyUnit(mark.unitId)) {
		const written = !!await elementorV3Adapter.replaceMark(mark, replacement)

		// One unit, one tier: the delegate's reason is the whole answer.
		if (!written) {
			writeRefusal.reason = elementorV3Adapter.consumeWriteRefusal?.() || null
		}

		return written
	}

	const props = getUsablePropsApi()
	if (!api || !props) {
		return false
	}

	// Scoped to this unit, and ahead of the read so the plan uses the value the dismissal settled.
	if (!await dismissInlineEditing(() => resolvePaintNode(parsed.ownerId))) {
		recordRefusedWrite()

		return false
	}

	// The same gate getContentUnits() applies.
	const read = readAtomicPropById(api, parsed.ownerId, parsed.attrName)
	if (!read) {
		return false
	}

	const written = writeReplacements(props, read.value, (projected) => {
		const range = resolveMarkRange(projected, mark)

		return range ? [ { start: range.start, end: range.end, text: replacement } ] : []
	})

	if (!written) {
		return false
	}

	// The popover re-analyzes on resolve and the analysis reads the canvas, so wait for the atomic
	// re-render ({@see replaceAllInDocument}).
	const renders = watchElementRenders([
		[ window.top, 'elementor/preview/atomic-widget/render' ],
		[ getPreviewWindow(), 'elementor/element/rendered' ]
	])

	try {
		writeProp(api, parsed.ownerId, parsed.attrName, written.value)
	} catch (error) {
		renders.stop()

		throw error
	}

	// A programmatic settings write does not fire the editor input that drives analysis.
	requestPostUpdate(500)

	await renders.until(new Set([ parsed.ownerId ]))

	return true
}

// T1 — the atomic tier's own write. Gated on both APIs here rather than per document, since the
// legacy tier's write needs neither.
const replaceAtomicOccurrences = (word, replacement, writtenOwners = null) => {
	const api   = getUsableElementApi()
	const props = getUsablePropsApi()
	if (!api || !props) {
		return 0
	}

	let total = 0

	// The whole-canvas fallback appears in no walk, so it is never written.
	eachAtomicProp(api, ({ ownerId, attrName, value }) => {
		try {
			const written = writeReplacements(props, value, (projected) => findSentenceRanges(projected, word)
				.map(range => ({
					start : range.start,
					end   : range.end,

					// The matched text's casing, so a sentence-initial misspelling stays capitalized
					// ({@see elementor.js:replaceInStoredValue}).
					text : matchCase(projected.slice(range.start, range.end), replacement)
				})))

			if (!written) {
				return
			}

			writeProp(api, ownerId, attrName, written.value)
			writtenOwners?.add(ownerId)
			total += written.count
		} catch (_e) {
			// One element whose command is refused must not abandon the elements after it.
		}
	})

	return total
}

/**
 * The paragraph and section scopes' write: delegated for a legacy unit, refused for an atomic one.
 *
 * NOTE: an atomic content prop's allowlist excludes `p` and `h1`-`h6`, so it holds one block by
 * construction and is refused as `not-structural`.
 *
 * @param {Object} mark  The store mark whose block to replace.
 * @param {Array}  parts Structured parts from `parseStructuredReplacement`.
 * @returns {Promise<boolean>} True when the write landed.
 */
const replaceMarkBlocksInV4 = async (mark, parts) => {
	beginWrite()

	if (!mark?.unitId || !Array.isArray(parts) || 2 > parts.length) {
		return false
	}

	const parsed = parseUnitId(mark.unitId)
	if (!parsed) {
		return false
	}

	const api = getUsableElementApi()

	// The same tier precedence `replaceOneMark` applies.
	if (!(!!api && isAtomicElement(api, parsed.ownerId)) && findLegacyUnit(mark.unitId)) {
		const written = !!await elementorV3Adapter.replaceMarkBlocks(mark, parts)

		if (!written) {
			writeRefusal.reason = elementorV3Adapter.consumeWriteRefusal?.() || null
		}

		return written
	}

	writeRefusal.reason = 'not-structural'

	return false
}

// T2 — legacy widgets, written through V3, which addresses the stored setting rather than the
// rendered offsets. No element is in both tiers, so the two passes can't double-apply.
//
// NOTE: triggerReanalysis is passed through, because only V3 can re-arm the analysis behind a
// deferred re-render ({@see elementor.js:reissueDroppedRender}).
const replaceLegacyOccurrences = async (word, replacement, triggerReanalysis) => {
	try {
		return await elementorV3Adapter.replaceAllOccurrences(word, replacement, { triggerReanalysis }) || 0
	} catch (_e) {
		return 0
	}
}

const replaceAllInDocument = async (word, replacement, triggerReanalysis) => {
	beginWrite()

	// An empty word would make the `g` exec loop spin on a zero-length match and hang the editor.
	if (!word || !replacement) {
		return 0
	}

	// Ahead of the dismissal, which can't close a legacy pen and would refuse the whole document
	// ({@see replaceOneMark}).
	const legacy = await replaceLegacyOccurrences(word, replacement, triggerReanalysis)

	// Drained here, before anything else can clear V3's slot.
	const legacyRefusal = elementorV3Adapter.consumeWriteRefusal?.() || null

	// Document-scoped, matching the write.
	let atomic = 0

	if (await dismissInlineEditing(getElementorRoot)) {
		// The analysis reads the canvas, and an atomic element re-renders after the command
		// returns — resolving before that hands a re-analyzing caller the pre-fix text.
		const renders       = watchElementRenders([
			[ window.top, 'elementor/preview/atomic-widget/render' ],
			[ getPreviewWindow(), 'elementor/element/rendered' ]
		])
		const writtenOwners = new Set()

		try {
			atomic = replaceAtomicOccurrences(word, replacement, writtenOwners)
		} finally {
			if (writtenOwners.size) {
				await renders.until(writtenOwners)
			} else {
				renders.stop()
			}
		}
	} else if (!legacy) {
		// Only when neither tier applied the fix.
		recordRefusedWrite()
	}

	const total = atomic + legacy

	if (!total) {
		// V3 judged `not-editable` from legacy markers alone, and an atomic element is editable. Back
		// to what V3 answers without that verdict: silence when it examined no unit.
		const api = 'not-editable' === legacyRefusal ? getUsableElementApi() : null
		const own = api && isWordInUnits(getAtomicUnits(api), word)

		writeRefusal.reason = own ? (getLegacyUnits().some(unit => unit.ownerId) ? 'not-found' : null) : legacyRefusal
	}

	// The legacy tier arms its own re-analysis ({@see replaceLegacyOccurrences}); this covers a fix
	// the atomic tier alone applied.
	if (total && triggerReanalysis && !legacy) {
		requestPostUpdate(500)
	}

	return total
}

// The lifecycle events the atomic views dispatch on the preview iframe's window.
//
// NOTE: `destroy` is the only event a delete dispatches; without it a mark keeps a detached node.
const PREVIEW_RENDER_EVENTS = [
	'elementor/element/render',
	'elementor/element/rendered',
	'elementor/element/destroy'
]

// Leaf atomic widgets dispatch theirs on `window.top` instead, so both windows are subscribed;
// a repaint is idempotent.
const TOP_RENDER_EVENTS = [ 'elementor/preview/atomic-widget/render', 'elementor/preview/atomic-widget/destroy' ]

// Every element view's destroy (atomic ones too), after the element leaves the DOM.
//
// NOTE: covers deleting the last legacy widget, where hasLegacyUnits() already answers false.
// onInvalidated's channel only, never the suspension gate's ({@see onSuspensionChange}).
const LEGACY_DESTROY_EVENT = 'elementor/editor/element-destroyed'

const getPreviewWindow = () => getElementorRoot()?.ownerDocument?.defaultView || null

// The preview `<iframe>` in the main document, reached through the canvas's own window. Null when
// detached, since a detached frame's rect is a stale offset.
const getPreviewFrameElement = () => {
	try {
		const frame = getPreviewWindow()?.frameElement || null

		return frame?.isConnected ? frame : null
	} catch (_e) {
		// A cross-origin preview throws on the property read rather than answering null.
		return null
	}
}

// Every element re-render wipes the marks, so a subscriber repaints unconditionally — never on a
// timer, since an async render can land arbitrarily late. Undebounced; the composable debounces.
//
// NOTE: shared by onInvalidated(), which adds the legacy signals, and the suspension gate, which
// must not have them ({@see onSuspensionChange}).
const subscribeToRenderEvents = (cb, topEvents = TOP_RENDER_EVENTS) => {
	const bound = []

	try {
		const handler = () => {
			try {
				cb()
			} catch (_e) {
				// A subscriber that throws must not take down Elementor's own render.
			}
		}

		const preview = getPreviewWindow()
		if (preview) {
			for (const type of PREVIEW_RENDER_EVENTS) {
				preview.addEventListener(type, handler)
				bound.push([ preview, type, handler ])
			}
		}

		// Subscribed even when top is the preview window: the event types differ. Top listeners
		// also survive a preview reload, which is why the legacy destroy is subscribed here.
		const top = window.top
		if (top) {
			for (const type of topEvents) {
				top.addEventListener(type, handler)
				bound.push([ top, type, handler ])
			}
		}
	} catch (_e) {
		// A cross-origin top window, or a canvas that has not booted.
	}

	return () => {
		for (const [ target, type, handler ] of bound.splice(0)) {
			try {
				target.removeEventListener(type, handler)
			} catch (_e) {
				// The window is gone; its listeners went with it.
			}
		}
	}
}

export default {
	id : 'elementor-v4',

	analyzeOnly : false,

	// This adapter is the builder's own, unlike block/classic resolving under another builder.
	ownsPageBuilder : true,

	// Opts into the composable's contract-driven paint; declaring `decorate` alone isn't the signal.
	paintsViaContract : true,
	suppressNextPenArm,
	revealNode        : revealElementorNode,

	// Ownership only (memoised by resolveAdapter()); isReady() answers usability per call.
	//
	// NOTE: the flag covers the window before the footer packages load, and the packages keep
	// working once Elementor retires the flag.
	isActive () {
		try {
			return !!isElementorEditor() && (isV4Site() || !!getElementApi())
		} catch (_e) {
			return false
		}
	},

	isReady () {
		try {
			return !!getUsableElementApi()
		} catch (_e) {
			return false
		}
	},

	// Three tiers, deduplicated by unitId so no text is analysed twice: atomic units from the
	// props model, legacy widgets scraped the V3 way, and — only if those two together
	// recovered nothing from a canvas that plainly holds text — the canvas root itself.
	getContentUnits () {
		try {
			const api   = getUsableElementApi()
			const seen  = new Set()
			const units = []

			for (const unit of [ ...(api ? getAtomicUnits(api) : []), ...getLegacyUnits() ]) {
				// Text-free units are dropped for every tier, or one empty legacy marker would
				// suppress the degraded read below.
				if (!unit.html?.trim() || seen.has(unit.unitId)) {
					continue
				}

				seen.add(unit.unitId)
				units.push(unit)
			}

			return units.length ? sortUnitsByDocumentPosition(units) : getDegradedUnits()
		} catch (_e) {
			return getDegradedUnits()
		}
	},

	// Document-scoped, with the full store mark set. Clears and repaints rather than reconciling:
	// it runs on every render event, and a wholesale repaint is cheap and idempotent.
	decorate (marks) {
		// Hoisted so the catch knows whether the clear ran and what was already refused.
		const dropped   = []
		const suspended = new Set()
		let cleared     = false

		try {
			const root = getElementorRoot()

			clearDomDecorations(root)
			cleared = true

			if (!Array.isArray(marks) || !marks.length) {
				return
			}

			const api = getUsableElementApi()

			// With the API drifted or the canvas gone, every mark is unreachable.
			if (!api || !root) {
				refuse(dropped, marks, api ? 'no-canvas' : 'no-api')
				recordDroppedMarks(dropped)

				return
			}

			const byUnit = groupMarksByUnit(marks)

			// Counted: this adapter is the only painter, so a unitless mark is a wiring fault.
			refuse(dropped, marks.filter(mark => !isAddressableMark(mark)), 'no-unit')

			if (!byUnit.size) {
				recordDroppedMarks(dropped)

				return
			}

			// One model walk, then one prop read per marked unit — not a full getContentUnits().
			const types     = new Map(walkElements(api).map(element => [ element.id, element.type ]))
			const keyCache  = new Map()
			const analyzers = new Set()

			for (const [ unitId, unitMarks ] of byUnit) {
				// Per unit, so one unresolvable element doesn't cost every other element's marks.
				try {
					const parsed = parseUnitId(unitId)
					const type   = parsed ? types.get(parsed.ownerId) : null

					// T2 — a legacy widget, asked of the marker source ({@see isAtomicType}).
					const legacy = isAtomicType(api, type) ? null : findLegacyUnit(unitId)

					// The whole-canvas unit, or a deleted/unrendered element: in neither tier.
					if (!type && !legacy) {
						refuse(dropped, unitMarks, 'unknown-element')

						continue
					}

					// T2's own refusals ({@see refuseLegacyUnit}).
					const legacyRefusal = legacy ? refuseLegacyUnit(legacy) : null
					if (legacyRefusal) {
						refuse(dropped, unitMarks, legacyRefusal)

						continue
					}

					// Each tier's own coordinate space: T2's `html` is the string these marks were
					// measured in, so it's never re-derived from the node.
					const projected = legacy ? legacy.html : projectAtomicUnit(api, keyCache, type, parsed)

					// T1 only: the prop isn't atomic content, so no write could share its space.
					if (!legacy && !projected) {
						refuse(dropped, unitMarks, 'not-writable')

						continue
					}

					const node = legacy ? legacy.getNode() : resolvePaintNode(parsed.ownerId)
					if (!node) {
						refuse(dropped, unitMarks, 'no-node')

						continue
					}

					// Per-unit suspension: opening an inline editor dispatches nothing, so another
					// element's render can reach an open one.
					//
					// NOTE: for T2 this is the only guard keeping a `<mark>` out of stored content —
					// an open legacy pen persists its innerHTML on every keystroke. Closing it
					// dispatches nothing, so T2 arms V3's close poll ({@see elementor.js:armPenClosePoll});
					// T1 resumes from its render events.
					if (legacy ? isPenOpenAt(node) : isInlineEditingAt(node)) {
						suspendMarks(unitMarks, suspended)

						if (legacy) {
							armPenClosePoll(unitId)
						}

						continue
					}

					const aligned = alignProjectedMarks({
						rendered : foldNbsp(buildTextStream(node).text),
						projected,
						marks    : unitMarks
					})

					// Through refuseOne, so the aligner's refusals clear their node too.
					for (const entry of aligned.dropped) {
						refuseOne(dropped, entry.mark, entry.reason)
					}

					const decorated = decorateNode({ node, marks: aligned.aligned })

					refuse(dropped, decorated.unpainted, 'no-paint')

					for (const painted of decorated.painted) {
						// The store resolves a mark's live node from here and drops marks without one.
						painted.mark.node = painted.node
						analyzers.add(painted.mark.analyzer)
					}
				} catch (_e) {
					refuse(dropped, unitMarks, 'unit-error')
				}
			}

			// The canvas is an iframe, out of reach of the main document's stylesheet.
			ensureDecorationStyles(root.ownerDocument, [ ...analyzers ])

			// T2's: the legacy pen wipes marks on open ({@see elementor.js:ensurePenOpenWatch}).
			ensurePenOpenWatch(root.ownerDocument)

			recordDroppedMarks(dropped)
		} catch (_e) {
			// Never throw into Elementor. After the clear, refuse every mark left holding a
			// detached node, skipping marks still on screen and ones this pass already handled.
			if (cleared && Array.isArray(marks)) {
				const alreadyRefused = new Set([ ...dropped.map(entry => entry.mark), ...suspended ])

				refuse(dropped, marks.filter(mark => !alreadyRefused.has(mark) && !mark?.node?.isConnected), 'pass-error')
				recordDroppedMarks(dropped)
			}
		}
	},

	clearDecorations () {
		try {
			clearDomDecorations(getElementorRoot())
		} catch (_e) {
			// Nothing to clear, or the canvas is already gone.
		}
	},

	// The canvas root inside the preview iframe. Null, never undefined, while it loads: the
	// mount poll treats undefined as found ({@see EditorAdapter}).
	getRootNode () {
		try {
			return getElementorRoot()
		} catch (_e) {
			return null
		}
	},

	// The observer target's child, read off the canvas rather than through getContentUnits().
	// `{}` when the canvas is empty, so the observer setup skips it; null only on a throw.
	getFirstUnitNode () {
		try {
			return getElementorRoot()?.querySelector('[data-id]') || {}
		} catch (_e) {
			return null
		}
	},

	// One unit's live node, without the cost of getContentUnits(). Same tier precedence as
	// decorate() ({@see isAtomicType}); the paint node, since a leaf wrapper has a 0x0 rect.
	getUnitNode (unitId) {
		try {
			const parsed = parseUnitId(unitId)
			if (!parsed) {
				return null
			}

			const api = getUsableElementApi()

			if (api && isAtomicElement(api, parsed.ownerId)) {
				return resolvePaintNode(parsed.ownerId)
			}

			return elementorV3Adapter.getUnitNode?.(unitId) || null
		} catch (_e) {
			return null
		}
	},

	// The main document's body, where the popover's styles live; getMarkRectOffset() lifts the
	// canvas marks' rects into it.
	getPopoverHost () {
		try {
			return document.body
		} catch (_e) {
			return null
		}
	},

	// The preview frame's on-screen box clipped to the viewport, so a mark scrolled out of the
	// canvas hides its popover.
	getViewportBounds () {
		const viewportBottom = window.innerHeight || 0

		try {
			const frame = getPreviewFrameElement()
			if (!frame) {
				return { top: 0, bottom: viewportBottom }
			}

			const rect = frame.getBoundingClientRect()

			return {
				top    : Math.max(0, rect.top),
				bottom : Math.min(viewportBottom, rect.bottom)
			}
		} catch (_e) {
			return { top: 0, bottom: viewportBottom }
		}
	},

	// The frame's offset, lifting canvas-space mark rects into the main document. {0, 0} when the
	// frame is gone; repositionHoverPopover() detects that itself.
	getMarkRectOffset () {
		try {
			const frame = getPreviewFrameElement()
			if (!frame) {
				return { top: 0, left: 0 }
			}

			const rect = frame.getBoundingClientRect()

			return { top: rect.top, left: rect.left }
		} catch (_e) {
			return { top: 0, left: 0 }
		}
	},

	// Rewrites one mark's span, addressed by `unitId` and its sentence: T1 through the stored prop,
	// T2 delegated to V3, T3 refused ({@see replaceOneMark}).
	//
	// NOTE: async because it waits on the inline editor's dismissal ({@see dismissInlineEditing}).
	async replaceMark (mark, replacement) {
		try {
			return await replaceOneMark(mark, replacement)
		} catch (_e) {
			return false
		}
	},

	// False for atomic props, whose block write can only refuse ({@see replaceMarkBlocksInV4}), so
	// the popover doesn't offer it. Fails open.
	canReplaceMarkBlocks (mark) {
		try {
			const parsed = parseUnitId(mark?.unitId)
			if (!parsed) {
				return true
			}

			const api = getUsableElementApi()
			if (!api) {
				return true
			}

			if (isAtomicElement(api, parsed.ownerId)) {
				return false
			}

			return findLegacyUnit(mark.unitId)
				? false !== elementorV3Adapter.canReplaceMarkBlocks?.(mark)
				: true
		} catch (_e) {
			return true
		}
	},

	async replaceMarkBlocks (mark, parts) {
		try {
			return await replaceMarkBlocksInV4(mark, parts)
		} catch (_e) {
			return false
		}
	},

	// The user-visible reason for a refusal ({@see highlighter/spellingReplace.js:consumeWriteRefusal}).
	consumeWriteRefusal,

	// Document-scoped and marks-independent, so the Spelling tab's fix works with highlighting off.
	// Resolves the count written across both tiers.
	async replaceAllOccurrences (word, replacement, { triggerReanalysis = true } = {}) {
		try {
			return await replaceAllInDocument(word, replacement, triggerReanalysis)
		} catch (_e) {
			return 0
		}
	},

	// Selects the element in Elementor's panel and brings it on screen.
	//
	// NOTE: selectElement is not in requiredElementApi; losing it should only cost the selection.
	focus (unitId) {
		try {
			const parsed = parseUnitId(unitId)
			if (!parsed) {
				return
			}

			const api = getElementApi()
			if ('function' === typeof api?.selectElement) {
				api.selectElement(parsed.ownerId)
			}

			// The paint node, not the `display: contents` wrapper with its 0x0 rect.
			const node = resolvePaintNode(parsed.ownerId)
			if (!node) {
				return
			}

			try {
				node.scrollIntoView({ block: 'center', inline: 'nearest' })
			} catch (_e) {
				node.scrollIntoView()
			}
		} catch (_e) {
			// Element already gone, or the canvas cannot be scrolled.
		}
	},

	// Two channels: the atomic render events, and the shared `set-is-modified` hook for legacy
	// widgets, some of which re-render in place without dispatching anything
	// ({@see elementor.js:subscribeToInvalidation}).
	onInvalidated (cb) {
		const stopRenderEvents = subscribeToRenderEvents(cb, [ ...TOP_RENDER_EVENTS, LEGACY_DESTROY_EVENT ])

		// Delivery is gated on legacy units existing ({@see hasLegacyUnits}); the registry entry
		// isn't, since it keeps the legacy close poll alive. The delete that empties the tier is
		// covered by the destroy event ({@see LEGACY_DESTROY_EVENT}).
		const stopInvalidation = subscribeToInvalidation(() => {
			if (hasLegacyUnits()) {
				cb()
			}
		})

		return () => {
			stopRenderEvents()
			stopInvalidation()
		}
	},

	// Empty: atomic views render from the model, so painting the DOM dispatches nothing to
	// suppress; the inline editor is kept out by isSuspended().
	beginPaint () {
		return null
	},

	endPaint () {},

	// Empty: the canvas may not exist yet, so decorate() injects the stylesheet.
	init () {},

	// isSuspended()'s resume channel; without it the gate could only ever close. Closing an inline
	// editor re-renders the widget, so the render events are enough.
	//
	// NOTE: not the `set-is-modified` channel, which fires per keystroke and would suspend the
	// whole editor for the length of every inline edit.
	onSuspensionChange (cb) {
		return subscribeToRenderEvents(cb)
	},

	// Whether an atomic inline editor is open anywhere in the canvas. Fails safe (true).
	//
	// NOTE: asks about the atomic pen only ({@see ATOMIC_INLINE_EDITING_SELECTOR}). The legacy
	// pen's close dispatches nothing, so this gate could never reopen; decorate()'s per-unit
	// isPenOpenAt() check is what keeps a `<mark>` out of a legacy pen's stored content.
	isSuspended () {
		try {
			const root = getElementorRoot()

			return root ? isAtomicInlineEditingAt(root) : true
		} catch (_e) {
			return true
		}
	},

	// The canvas document and window, where the marks live, so mark clicks are heard there.
	//
	// NOTE: the main document/window before the preview boots, so don't cache the answer.
	getDocument () {
		try {
			return getElementorRoot()?.ownerDocument || document
		} catch (_e) {
			return document
		}
	},

	getWindow () {
		try {
			return getPreviewWindow() || window
		} catch (_e) {
			return window
		}
	}
}