import { isElementorEditor } from '@/vue/utils/context'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'
import { ANALYZER_SKIPPED_SELECTOR, buildTextStream, escapeText } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { revealElementorNode } from './elementorReveal'
import { requestPostUpdate } from '@/vue/plugins/tru-seo/spellingSuggestions'
import { findSentenceRanges, matchCase, wordBoundaryPattern } from '@/vue/utils/regex'
import {
	normalize as normalizeQuotes,
	SINGLE_QUOTES_ARRAY,
	SINGLE_QUOTES_REGEX
} from '@/app/tru-seo/languageProcessing/helpers/sanitize/quotes'

import {
	alignProjectedMarks,
	clearDecorations as clearDomDecorations,
	decorateNode,
	ensureDecorationStyles,
	foldNbsp,
	MARK_ATTRIBUTE
} from './domDecorator'

import { buildStructuredMarkup } from '@/vue/plugins/tru-seo/highlighter/structuredMarkup'

import { editStoredBlock, editStoredHtml, parseInert } from './storedHtmlEdit'

// One regex class matching every single-quote codepoint. None of its members is
// regex-special, so it can splice into an escapeRegex'd pattern as-is.
const SINGLE_QUOTES_CLASS = `[${SINGLE_QUOTES_ARRAY.join('')}]`

/**
 * Gets the open Elementor document's root element, inside the preview iframe.
 *
 * @returns {?HTMLElement} The document root element, or null.
 */
export const getElementorRoot = () => window?.elementor?.documents?.getCurrent()?.$element?.[0] || null

const getPreviewWindow = () => getElementorRoot()?.ownerDocument?.defaultView || null

// The `<iframe id="elementor-preview-iframe">` itself, in the MAIN document. Reached through the
// canvas's own window so a canvas rendered in the main document answers null ("no offset needed"),
// and only while connected: a detached frame's rect is stale.
const getPreviewFrameElement = () => {
	try {
		const frame = getPreviewWindow()?.frameElement || null

		return frame?.isConnected ? frame : null
	} catch (_e) {
		// A cross-origin preview throws on the property read rather than answering null.
		return null
	}
}

// The two signals Elementor's inline editor sets on the `[data-elementor-setting-key]` node while
// its pen is open, and removes on destroy. NOT `elementor-inline-editing`: every unit carries that
// class at all times, so a gate on it would refuse the whole document.
const PEN_SELECTOR = '.pen, [contenteditable="true"]'

/**
 * Whether `node` is, contains, or sits inside an open inline-editing pen.
 *
 * NOTE: fails CLOSED — guessing "closed" writes a `<mark>` into stored content, while guessing
 * "open" only leaves the unit unpainted until the pen-close poll ({@see armPenClosePoll}).
 *
 * @param {?Node} node The node to probe.
 * @returns {boolean} True when a pen is open at, inside or around `node`.
 */
export const isPenOpenAt = (node) => {
	try {
		return !!(node?.closest?.(PEN_SELECTOR) || node?.querySelector?.(PEN_SELECTOR))
	} catch (_e) {
		return true
	}
}

// Hook ids must be unique for the page's lifetime — Elementor throws on a duplicate — so each
// subscription takes the next number.
let invalidationCount = 0

// Built lazily, inside a function body, never at module scope: `window.$e.modules.hookUI.Base`
// only exists once Elementor's own scripts have loaded, and this module also loads on editor
// screens (classic, block) where Elementor is absent and `window.$e` is undefined.
const registerInvalidationHook = (cb) => {
	class InvalidationHook extends window.$e.modules.hookUI.Base {
		getCommand () {
			return 'document/save/set-is-modified'
		}

		getId () {
			return `aioseo-truseo-elementor-on-invalidated-${invalidationCount++}`
		}

		apply () {
			try {
				cb()
			} catch (_e) {
				// A subscriber that throws must not reach Elementor's own console as a failed
				// command callback.
			}

			// Never the callback's own return value: Elementor reports a falsy hook result as a failed
			// callback, and the composable's debounced subscriber returns undefined.
			return true
		}
	}

	return window.$e.hooks.registerUIAfter(new InvalidationHook())
}

// Every live repaint subscriber, so the pen-close poll drives the same repaint channel
// onInvalidated() hands the composable. Raw callbacks, so it works where `$e` never appeared and
// both composable instances (metabox and sidebar) are notified.
const invalidationSubscribers = new Set()

const notifyInvalidated = () => {
	// Snapshotted: a subscriber is free to dispose its own composable, which unsubscribes during
	// the walk.
	for (const subscriber of [ ...invalidationSubscribers ]) {
		try {
			subscriber()
		} catch (_e) {
			// One subscriber failing must not cost the others their repaint.
		}
	}
}

// A marker's unit coordinates. `closest('.elementor-widget')`, so a marker inside a NESTED widget
// belongs to that widget rather than to its ancestor.
const identifyUnit = (node) => {
	const ownerId  = node?.closest?.('.elementor-widget')?.getAttribute('data-id') || ''
	const attrName = node?.getAttribute?.('data-elementor-setting-key') || ''

	return { ownerId, attrName, unitId: `${ownerId}:${attrName}` }
}

// One marker's unit. Named on its own so a caller wanting ONE unit does not have to build every
// other unit in the canvas to reach it.
const toContentUnit = (node) => {
	const { ownerId, attrName, unitId } = identifyUnit(node)
	const stream = buildTextStream(node)

	return {
		unitId,
		ownerId,
		attrName,
		// Same projection the analysis measures against elsewhere.
		html          : normalizeWhitespaces(stream.text),
		skipped       : stream.skipped,
		// Every opt-in unit is writable: `data-elementor-setting-key` names the very setting a write
		// targets, so paint and write share one coordinate space. V4's legacy tier reuses these units,
		// so narrowing this switches off both tiers.
		writable      : true,
		// Its markers stay in the roster, so the analysed string is unchanged, but a mark built there
		// would claim a sentence the analyzer found in a later unit.
		highlightable : !node?.closest?.(ANALYZER_SKIPPED_SELECTOR),
		getNode       : () => node
	}
}

const getUnits = () => {
	try {
		const root = getElementorRoot()
		if (!root) {
			return []
		}

		return [ ...root.querySelectorAll('[data-elementor-setting-key]') ].map(toContentUnit)
	} catch (_e) {
		return []
	}
}

// One unit by id, without building a text stream for every marker in the canvas as the roster
// does. Last match wins, matching the roster Map's insertion order for a duplicated unitId.
//
// NOT resolveUnitNode(): an ownerless marker must stay reachable here so decorate() can report it
// as `no-owner`.
const resolveContentUnit = (unitId) => {
	try {
		const root = getElementorRoot()
		if (!root) {
			return null
		}

		let found = null

		for (const node of root.querySelectorAll('[data-elementor-setting-key]')) {
			if (unitId === identifyUnit(node).unitId) {
				found = node
			}
		}

		return found ? toContentUnit(found) : null
	} catch (_e) {
		return null
	}
}

// The pen-close poll. Closing a pen dispatches nothing, and for the default `basic` toolbar nothing
// follows it either; `set-is-modified` only fires while the pen is open, which is exactly when
// decorate() refuses the unit. So a unit the user opened stays unpainted until a poll notices the
// close. One per watched unit, self-clearing, armed from decorate()'s refusal and from the click
// that opens the pen ({@see ensurePenOpenWatch}).
const PEN_POLL_INTERVAL_MS = 100

// Consecutive pen-free observations of the SAME element required before repainting. Closing the
// advanced toolbar's pen re-renders the widget from the server shortly after, so a repaint on the
// first pen-free tick would paint into a node that is about to be replaced.
//
// A later trailing-edge render (a second advanced marker on the same model closing within
// Elementor's render throttle) lands after the poll retires; V3 repaints it through the
// element-render channel ({@see ELEMENT_RENDERED_EVENT}). V4's legacy tier has no such channel.
const PEN_SETTLE_TICKS = 2

// The bound, counted only in ticks that see NO pen — every tick that still finds one resets it. So
// this does not limit how long a pen may stay open; what it stops is an interval outliving the
// thing it watched, in a state where the pen is gone and the node never settles.
const PEN_POLL_MAX_TICKS = 300

const penPolls = new Map()

// The unit's node as the live DOM answers it NOW — closing the advanced pen replaces the node the
// poll was armed with.
//
// NOT through getUnits(), which builds a text stream per unit; this runs ten times a second per
// watched unit and only needs the element.
//
// Same pairing as identifyUnit(), last match wins like decorate()'s unit map. Compared in JS rather
// than spliced into a selector, because `attrName` is attribute text.
const resolveUnitNode = (unitId) => {
	try {
		const root = getElementorRoot()
		const at   = String(unitId).indexOf(':')

		if (!root || 0 >= at) {
			return null
		}

		const ownerId  = unitId.slice(0, at)
		const attrName = unitId.slice(at + 1)

		if (!attrName) {
			return null
		}

		let found = null

		for (const node of root.querySelectorAll('[data-elementor-setting-key]')) {
			if (attrName === node.getAttribute('data-elementor-setting-key') &&
				ownerId === (node.closest('.elementor-widget')?.getAttribute('data-id') || '')) {
				found = node
			}
		}

		return found
	} catch (_e) {
		return null
	}
}

const clearPenPoll = (unitId) => {
	const poll = penPolls.get(unitId)
	if (!poll) {
		return
	}

	penPolls.delete(unitId)

	try {
		window.clearInterval(poll.intervalId)
	} catch (_e) {
		// No timer API left to clear against.
	}
}

const clearAllPenPolls = () => {
	for (const unitId of [ ...penPolls.keys() ]) {
		clearPenPoll(unitId)
	}
}

const pollPenClose = (unitId) => {
	try {
		const poll = penPolls.get(unitId)
		if (!poll) {
			return
		}

		poll.ticks++

		// Retire when over the bound, when the canvas is gone (a reload or document switch re-arms from
		// the next pass), or when no subscriber is left to deliver the repaint to.
		if (poll.ticks > PEN_POLL_MAX_TICKS || !invalidationSubscribers.size || !getElementorRoot()) {
			clearPenPoll(unitId)

			return
		}

		const node = resolveUnitNode(unitId)

		// A missing node is the advanced close mid-flight: the re-render detaches the old node before the
		// new one is in. Counted as unsettled, without refreshing the budget, so a unit that never comes
		// back still retires.
		if (!node) {
			poll.node   = null
			poll.stable = 0

			return
		}

		// Still open, so the budget goes back to full. A pen sitting open and untouched emits nothing
		// to repaint from, so there is no pass to refresh it from either; counting its ticks against
		// the bound would retire the poll mid-edit and leave the eventual close unheard.
		if (isPenOpenAt(node)) {
			poll.ticks  = 0
			poll.node   = null
			poll.stable = 0

			return
		}

		if (node !== poll.node) {
			poll.node   = node
			poll.stable = 0

			return
		}

		poll.stable++

		if (poll.stable < PEN_SETTLE_TICKS) {
			return
		}

		// Retired BEFORE notifying, so the repaint this triggers can arm a fresh poll if the user has
		// reopened the pen meanwhile.
		clearPenPoll(unitId)
		notifyInvalidated()
	} catch (_e) {
		// A tick that throws is a tick that can no longer observe anything; retiring is the fail-
		// closed side, since an interval throwing every 100 ms into Elementor's page is not.
		clearPenPoll(unitId)
	}
}

/**
 * Arms the close poll for one unit whose pen has cost it its marks, or is about to.
 *
 * NOTE: a caller must also join the subscriber registry ({@see subscribeToInvalidation}), or the
 * poll retires on its first tick.
 *
 * @param {string} unitId The unit's id, exactly as getContentUnits() published it.
 * @returns {void}
 */
export const armPenClosePoll = (unitId) => {
	const existing = penPolls.get(unitId)

	// One poll per suspended unit. A second pass while the pen is still open refreshes the bound
	// instead of stacking a second interval: two would each notify on close, and each notification
	// is a whole-document repaint.
	if (existing) {
		existing.ticks = 0

		return
	}

	try {
		const poll = { intervalId: null, ticks: 0, stable: 0, node: null }

		penPolls.set(unitId, poll)

		poll.intervalId = window.setInterval(() => pollPenClose(unitId), PEN_POLL_INTERVAL_MS)
	} catch (_e) {
		// No timer to poll with. The unit stays unpainted until the next invalidation.
		penPolls.delete(unitId)
	}
}

// A pen opens only on a click on the marker, after a short defer, and replaces the marker's
// innerHTML with the stored value — detaching every mark painted there, with nothing dispatched.
// So the click ARMS the close poll rather than repainting: a paint now would land in the unit about
// to become a pen, and the pen would save that `<mark>`.
//
// Suppression for a click the composable swallows to INSPECT a mark rather than edit it: no pen
// opens, so arming would only drive a repaint that flashes every highlight. A deadline rather than
// a boolean, so a click that never arrives cannot leave it standing.
const penArm = { suppressedUntil: 0 }

export const suppressNextPenArm = () => {
	penArm.suppressedUntil = Date.now() + 600
}

const onCanvasPenClick = (event) => {
	try {
		if (Date.now() < penArm.suppressedUntil) {
			penArm.suppressedUntil = 0

			return
		}

		// Nothing to deliver a repaint to. The poll's own first tick answers this too, so this is
		// cost rather than correctness: a composable that has unmounted must not leave clicks
		// arming 100 ms intervals behind it.
		if (!invalidationSubscribers.size) {
			return
		}

		const marker = event?.target?.closest?.('[data-elementor-setting-key]')

		// Paint is the only thing a pen can take away, so a marker with no mark has nothing to resume —
		// otherwise every click inside an opt-in widget would buy a whole-document pass. A click on a
		// painted marker that Elementor then refuses to open (locked widget, request in flight) costs one
		// idempotent repaint.
		if (!marker?.querySelector?.(`[${MARK_ATTRIBUTE}]`)) {
			return
		}

		armPenClosePoll(identifyUnit(marker).unitId)
	} catch (_e) {
		// A click that cannot be attributed to a unit is one no poll could watch.
	}
}

/**
 * Subscribes the canvas document, in capture, to the clicks that open a pen.
 *
 * NOTE: called per paint because a preview reload replaces the canvas document; idempotent since
 * addEventListener discards a duplicate listener.
 *
 * @param {?Document} doc The canvas document.
 * @returns {void}
 */
export const ensurePenOpenWatch = (doc) => {
	try {
		doc?.addEventListener?.('click', onCanvasPenClick, true)
	} catch (_e) {
		// No canvas document to watch.
	}
}

// Released with the last subscriber, beside the polls.
const releasePenOpenWatch = () => {
	try {
		getElementorRoot()?.ownerDocument?.removeEventListener?.('click', onCanvasPenClick, true)
	} catch (_e) {
		// The canvas is already gone; its listeners went with it.
	}
}

/**
 * Subscribes `cb` to every signal that can wipe or unveil paintable canvas DOM.
 *
 * NOTE: two channels — the `set-is-modified` hook every data command runs, and the registry the
 * pen-close poll delivers through, which is joined even when the hook fails to register.
 *
 * @param {Function} cb Called undebounced on every invalidation; its return value is ignored.
 * @returns {Function} Teardown. Idempotent, and safe with no `$e` and no canvas.
 */
export const subscribeToInvalidation = (cb) => {
	if ('function' !== typeof cb) {
		return () => {}
	}

	let registered = null

	try {
		registered = registerInvalidationHook(cb)
	} catch (_e) {
		// Elementor's hook API is absent or drifted. The composable still gets a working
		// unsubscribe, and the poll still has somewhere to deliver its repaint.
	}

	invalidationSubscribers.add(cb)

	return () => {
		invalidationSubscribers.delete(cb)

		// The last subscriber leaving means nothing is left to repaint, so an armed poll would
		// tick on watching for a close nobody would hear. Not on the FIRST unsubscribe —
		// metabox and sidebar mount and unmount independently, and the survivor still needs
		// its polls.
		if (!invalidationSubscribers.size) {
			clearAllPenPolls()
			releasePenOpenWatch()
		}

		try {
			registered?.deactivate?.()
		} catch (_e) {
			// Hook already gone, or Elementor's API changed shape.
		}
	}
}

// Dispatched by a legacy element view once it and its children have rendered, so the reported node
// is already in the document. The only signal for a re-render that changed no data: the advanced
// toolbar's close, a dynamic tag's server render, and the render re-issued after a write
// ({@see reissueDroppedRender}). A `basic`/`none` close re-renders nothing, which is why the close
// poll is still needed.
const ELEMENT_RENDERED_EVENT = 'elementor/editor/element-rendered'

// `window.top` only: the event reaches both windows, and the top one exists before the preview
// iframe and survives its reload. Not folded into subscribeToInvalidation(), which V4's legacy
// tier shares.
const subscribeToElementRenders = (cb) => {
	const handler = () => {
		try {
			cb()
		} catch (_e) {
			// A subscriber that throws must not take down Elementor's own render.
		}
	}

	try {
		window.top?.addEventListener?.(ELEMENT_RENDERED_EVENT, handler)
	} catch (_e) {
		// A cross-origin top window.
	}

	return () => {
		try {
			window.top?.removeEventListener?.(ELEMENT_RENDERED_EVENT, handler)
		} catch (_e) {
			// The window is gone; its listeners went with it.
		}
	}
}

// Bounds the wait for a render that never comes — a render dropped behind a pen and re-issued
// only after a server round-trip ({@see reissueDroppedRender}).
const ELEMENT_RENDER_WAIT_MS = 1000

/**
 * Starts recording element renders, so a write can wait until its widgets are back in the canvas.
 *
 * NOTE: start it BEFORE the write — the render can land before the caller gets to listen.
 *
 * @param   {Array} sources `[ window, eventName ]` pairs to listen on; legacy element renders by default.
 * @returns {{ until: function(Set): Promise<void>, stop: Function }} The waiter and its teardown.
 */
export const watchElementRenders = (sources = [ [ window.top, ELEMENT_RENDERED_EVENT ] ]) => {
	const rendered = new Set()
	let notify = null

	// Legacy views send themselves; atomic views send only their id.
	const handler = (event) => {
		const id = event?.detail?.id || event?.detail?.elementView?.model?.id
		if (id) {
			rendered.add(id)
			notify?.()
		}
	}

	const stop = () => {
		notify = null

		for (const [ target, eventName ] of sources) {
			try {
				target?.removeEventListener?.(eventName, handler)
			} catch (_e) {
				// The window is gone; its listeners went with it.
			}
		}
	}

	for (const [ target, eventName ] of sources) {
		try {
			target?.addEventListener?.(eventName, handler)
		} catch (_e) {
			// A cross-origin window: `until` then resolves on its timeout.
		}
	}

	const until = (ids) => new Promise(resolve => {
		let timer = null

		const done = () => {
			window.clearTimeout(timer)
			stop()
			resolve()
		}

		const check = () => {
			if ([ ...ids ].every(id => rendered.has(id))) {
				done()
			}
		}

		timer  = window.setTimeout(done, ELEMENT_RENDER_WAIT_MS)
		notify = check
		check()
	})

	return { until, stop }
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

// An unreachable target has to SURFACE, as a counted drop. A mark this adapter cannot place is
// dropped, never guessed at — paint and write resolve their target differently (rendered node
// vs stored setting), so a guess highlights one occurrence while a fix lands on another.
const decorationDiagnostics = { passes: 0, dropped: 0, unitGone: 0, suspended: 0, refusedWrites: 0, byReason: {} }

// The last summary warned about — report state rather than a count, so it is deliberately not
// part of the snapshot above.
const dropReport = { lastSummary: '' }

/**
 * Counters for what the paint and write paths refused, cumulative for the page's lifetime.
 *
 * NOTE: `unitGone` and `suspended` are counted apart from `dropped` ({@see recordVanishedMarks}).
 *
 * @returns {{passes: number, dropped: number, unitGone: number, suspended: number, refusedWrites: number, byReason: Object}} The diagnostics snapshot.
 */
export const getDecorationDiagnostics = () => ({
	passes        : decorationDiagnostics.passes,
	dropped       : decorationDiagnostics.dropped,
	unitGone      : decorationDiagnostics.unitGone,
	suspended     : decorationDiagnostics.suspended,
	refusedWrites : decorationDiagnostics.refusedWrites,
	byReason      : { ...decorationDiagnostics.byReason }
})

// Why the most recent write attempt refused, or null when it landed. Cleared by both write entry
// points on the way in. A slot rather than a return value, so the entry points keep the boolean
// and count shapes block and classic also return.
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

// A write this adapter refused, counted under a `write-` prefix so it is never read as a paint
// drop. Warns every time, unlike recordDroppedMarks(): a refused write is one user action that
// visibly did nothing.
//
// The MARK path only: V4's legacy tier delegates replaceAllWordInElementor() unconditionally, so a
// warning there would fire for legacy widgets that simply do not hold the word.
const recordRefusedWrite = (reason) => {
	const key = `write-${reason}`

	decorationDiagnostics.refusedWrites++
	decorationDiagnostics.byReason[key] = (decorationDiagnostics.byReason[key] || 0) + 1
	writeRefusal.reason = reason

	console.warn(`AIOSEO: the TruSEO fix could not be applied to the Elementor canvas (${reason}).`)
}

const recordDroppedMarks = (dropped) => {
	decorationDiagnostics.passes++

	if (!dropped.length) {
		// So a recurrence after a clean pass is reported again instead of being swallowed as
		// a repeat.
		dropReport.lastSummary = ''

		return
	}

	const counts = {}
	for (const entry of dropped) {
		decorationDiagnostics.dropped++
		decorationDiagnostics.byReason[entry.reason] = (decorationDiagnostics.byReason[entry.reason] || 0) + 1
		counts[entry.reason] = (counts[entry.reason] || 0) + 1
	}

	// Repaint runs on every invalidation, so an unchanged failure would otherwise flood the
	// console. Reported once per distinct outcome, not once per pass.
	const summary = Object.keys(counts).sort().map(reason => `${reason}: ${counts[reason]}`).join(', ')
	if (summary === dropReport.lastSummary) {
		return
	}

	dropReport.lastSummary = summary
	console.warn(`AIOSEO: ${dropped.length} TruSEO highlight(s) could not be placed in the Elementor canvas (${summary}).`)
}

// The ONE place a refusal is recorded, so a dropped mark never keeps last pass's node: the clear
// detached it, and sanitizeHighlightMarks() prunes on `!!hm.node` rather than connectedness, so a
// stale node would anchor a popover on a zero rect.
const refuseOne = (dropped, mark, reason) => {
	// A falsy entry in the store's mark set is an upstream wiring fault with nothing to refuse
	// and no node to clear. Skipped rather than left to throw: this runs inside decorate()'s
	// outer catch as well, where a throw would escape the handler that exists to swallow it.
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

// Marks whose unit is no longer in the roster. NOT a refusal, so it never reaches the console
// warning: the marker set depends on the render path (a price-list emits markers on the server
// render and none after a JS re-render), so a unit vanishing is an ordinary outcome of editing.
// The node is still nulled, for refuseOne()'s reason. A WRITE refused for this cause stays loud.
const recordVanishedMarks = (marks, vanished) => {
	for (const mark of marks) {
		if (!mark) {
			continue
		}

		mark.node = null
		decorationDiagnostics.unitGone++
		decorationDiagnostics.byReason['unit-gone'] = (decorationDiagnostics.byReason['unit-gone'] || 0) + 1
		vanished?.add(mark)
	}
}

// Marks skipped because their unit is open in the inline-editing pen. NOT a refusal, for the same
// reason as recordVanishedMarks(): opening the pen is routine editing.
//
// This is the ONE guard between a painted `<mark>` and stored content: the open pen writes its
// innerHTML into the setting on every keystroke, so a mark painted into it is persisted by the
// next character. The node is still nulled, for refuseOne()'s reason.
const suspendMarks = (marks, suspended) => {
	for (const mark of marks) {
		if (!mark) {
			continue
		}

		mark.node = null
		decorationDiagnostics.suspended++
		decorationDiagnostics.byReason['pen-open'] = (decorationDiagnostics.byReason['pen-open'] || 0) + 1
		suspended?.add(mark)
	}
}

// Regions of a stored setting value that are markup rather than prose. The classic adapter
// rewrites text nodes and the block adapter works in rich-text space, so neither can reach
// a tag, a shortcode argument or an entity; here the value is a raw string, so the same
// exclusion has to be explicit or a misspelled slug inside an href gets rewritten too.
const markupPattern = /<!--[\s\S]*?-->|<\/?[a-zA-Z][^>]*>|\[[^\]]*\]|&[a-zA-Z#][0-9a-zA-Z]*;/g

const getMarkupRanges = (value) => {
	const ranges = []
	let match

	markupPattern.lastIndex = 0
	while (null !== (match = markupPattern.exec(value))) {
		ranges.push([ match.index, match.index + match[0].length ])
	}

	return ranges
}

// Null means the word is not in this stored value at all, keeping a miss distinguishable
// from a replacement that produced an identical string. `count` is how many of its
// occurrences were actually replaced, so a caller can total it against how many the
// stored value could ever have had — a single write can carry more than one fix.
const replaceInStoredValue = (value, word, replacement) => {
	// wordBoundaryPattern('') matches at every boundary with zero length, and exec() with `g`
	// never advances lastIndex past a zero-length match — the loop below would spin forever
	// rather than fail. Callers guard this too; a hang is too costly to leave to them.
	if (!word) {
		return null
	}

	// The flagged word comes from RENDERED text and the value searched is STORED: wptexturize can turn
	// U+0027 into U+2019 (or the user typed the curly one), so each single quote becomes a class
	// matching all of them. Other texturize rewrites (`--`, `...`) change length and are not folded
	// ({@see foldQuoteClasses}), so a word carrying one misses. Safe on the whole pattern — the
	// boundary lookarounds hold no quotes.
	const ranges    = getMarkupRanges(value)
	const pattern   = wordBoundaryPattern(word).replace(SINGLE_QUOTES_REGEX, SINGLE_QUOTES_CLASS)
	const wordRegex = new RegExp(pattern, 'gu')
	let result      = '',
		cursor      = 0,
		count       = 0,
		match

	while (null !== (match = wordRegex.exec(value))) {
		const start = match.index
		const end   = start + match[0].length

		if (ranges.some(([ from, to ]) => start < to && end > from)) {
			continue
		}

		// match[0], not `word`: the quote class above lets the needle match a texturized
		// stored value, so the matched text is what is actually being replaced. Escaped because
		// the stored value is markup and the suggestion is text.
		result += value.slice(cursor, start) + escapeText(matchCase(match[0], replacement))
		cursor  = end
		count++
	}

	return count ? { text: result + value.slice(cursor), count } : null
}

/**
 * Checks whether any unit's rendered text holds the word.
 *
 * NOTE: the analysed string carries a widget's whole markup, so a word in none of them was read
 * from text no marker covers (an image caption, an HTML widget, a WordPress widget's output) and
 * no write can reach it.
 *
 * @param {Array}  units The content units.
 * @param {string} word  The word.
 * @returns {boolean}    Whether a unit holds it.
 */
export const isWordInUnits = (units, word) => {
	if (!word) {
		return false
	}

	const wordRegex = new RegExp(wordBoundaryPattern(word).replace(SINGLE_QUOTES_REGEX, SINGLE_QUOTES_CLASS), 'u')

	return (units || []).some(unit => wordRegex.test(foldNbsp(unit?.html || '')))
}

// Mirrors Elementor's onInlineEditingUpdate(): a 3-part key addresses a repeater row, and the
// row's own container owns the bare field.
const resolveSettingTarget = (ownerId, settingKey) => {
	const container = window?.elementor?.getContainer?.(ownerId)
	if (!container) {
		return null
	}

	const parts = String(settingKey).split('.')
	if (3 !== parts.length) {
		return { container, key: settingKey }
	}

	const child = container.repeaters?.[parts[0]]?.children?.[parts[1]]

	return child ? { container: child, key: parts[2] } : null
}

// Elementor's inline editing refuses a dynamic setting outright: its stored value is an
// `[elementor-tag ...]` call that shares nothing with what the tag renders to.
const isDynamicSetting = (container, key) => !!container?.settings?.get?.('__dynamic__')?.[key]

// The same question asked of a UNIT, for the paint path: a unit either has a fix or should not
// carry a mark ({@see decorate}).
//
// Fails OPEN, unlike isPenOpenAt() — do not harmonise them. A wrong "not dynamic" costs one dead
// Fix click; a wrong "dynamic" (getContainer briefly absent during an editor swap) strips every
// mark off the canvas. Exported for V4's legacy tier, which paints these same units.
export const isDynamicUnit = (unit) => {
	try {
		const target = resolveSettingTarget(unit?.ownerId, unit?.attrName)

		return !!target && isDynamicSetting(target.container, target.key)
	} catch (_e) {
		return false
	}
}

// Re-issues the render the settings command asked for, once, when Elementor dropped it. A remote
// render in flight for the same model makes WidgetView.render() return early, and its response
// was serialized before the write, so the widget is redrawn with the pre-fix text and nothing
// later invalidates that cache. The fix flow produces such a request on an `advanced` unit every
// time (the popover's mousedown closes the pen), and closing the panel can too.
//
// Only when a request is actually in flight, to avoid a needless redraw. renderHTML() because it
// is the call Elementor's own renderChanges() makes.
//
// @param {Object}   container The container the write addressed.
// @param {boolean}  reanalyze Whether to re-arm the analysis once the refresh has landed.
// @param {?Set}     armed     Models already armed by this pass, or null for a single write.
const reissueDroppedRender = (container, reanalyze, armed) => {
	try {
		// `renderer` is the container itself for a widget and the widget's for a repeater row — the same
		// indirection container.render() resolves through.
		const view  = container?.renderer?.view
		const model = view?.getEditModel?.() || view?.model

		if (!model?.isRemoteRequestActive?.() || 'function' !== typeof model.once) {
			return
		}

		// Every repeater row shares its widget's model, so a document-wide fix touching three panels would
		// otherwise arm three listeners and three renders off one response.
		if (armed) {
			if (armed.has(model)) {
				return
			}

			armed.add(model)
		}

		// `once`, so an aborted request costs nothing: Elementor issues a replacement whose response
		// already holds the write. A FAILED request leaves this listener dead, but that state already
		// makes the widget unrenderable.
		model.once('remote:render', () => {
			try {
				// renderHTML() bypasses the pen gate container.render() goes through, so check it here.
				// Elementor refuses to open a pen while a request is active, so this is belt and braces.
				if (view.isDestroyed || false === view.allowRender) {
					return
				}

				view.renderHTML?.()

				// The write's own requestPostUpdate(500) can fire before a slow server render lands, and the
				// analysis would read the pre-write text and keep flagging the fixed word. Re-arming here trades
				// a little extra debounce for a correct read. Gated: the popover's spelling fix passes false
				// because it re-analyzes itself, and a late re-analysis would hide the popover it re-opened.
				if (reanalyze) {
					requestPostUpdate(500)
				}
			} catch (_e) {
				// A view that cannot re-render leaves the canvas exactly as the dropped render did.
			}
		})
	} catch (_e) {
		// No renderer to ask. The write itself stands either way.
	}
}

/**
 * Replaces every whole-word occurrence of a word across the document's stored setting values.
 *
 * NOTE: only the stored value is read and written; a word that exists solely in rendered output
 * (shortcodes, oEmbeds) is reported as a miss.
 *
 * @param {string}  word              The word to replace.
 * @param {string}  replacement       The replacement word.
 * @param {boolean} triggerReanalysis Re-run analysis after editing. Pass false when the caller re-analyzes itself.
 * @param {?Set}    writtenOwners     Collects the id of every widget written to, when given.
 * @returns {number}                  The count of occurrences written across the document (0 means none).
 */
const replaceAllWordInElementor = (word, replacement, triggerReanalysis = true, writtenOwners = null) => {
	writeRefusal.reason = null

	if (!word || !replacement || 'function' !== typeof window?.$e?.run) {
		return 0
	}

	const seen = new Set()
	// Per PASS, never module-scoped: two composable instances can each be mid-write, and one's
	// dedupe must not swallow the other's arm.
	const armedModels = new Set()
	// Null until a unit has actually been examined, which is what keeps a V4 page quiet: its
	// legacy tier delegates here unconditionally, and an atomic-only document has no unit for
	// this loop to refuse.
	let refusal      = null,
		sawPlainMiss = false,
		totalCount   = 0

	// No pen refusal here, unlike the paint gate ({@see suspendMarks}). V3 dismisses the pen
	// synchronously on any main-document mousedown, which the click that triggers a write always
	// dispatches, so the pen is already closed by the time this runs. V4's inline editor does not
	// ({@see elementorV4.js:dismissInlineEditing}). Revisit if a write gains an entry point with no
	// main-document mousedown (keyboard activation blurs the pen on a timeout instead).
	// Only what the analyzer read: a word in a skipped widget was never flagged, painted or counted,
	// so rewriting it would change text the user was never shown.
	const units = getUnits().filter(unit => false !== unit.highlightable)

	for (const unit of units) {
		// An empty ownerId means the node has no widget ancestor, so no container addresses it.
		if (!unit.ownerId || seen.has(unit.unitId)) {
			continue
		}

		seen.add(unit.unitId)

		// A plain miss is the baseline reading for a unit; the named causes below overwrite it
		// and then stick, so a document holding one dynamic widget and twenty ordinary ones
		// reports the dynamic binding rather than the miss it also produced.
		refusal = refusal || 'not-found'

		// Scoped to one unit: a widget whose container lookup or settings command throws must
		// not abandon the widgets after it, or "every occurrence" silently means "some".
		try {
			const target = resolveSettingTarget(unit.ownerId, unit.attrName)
			if (!target) {
				refusal = 'no-container'

				continue
			}

			if (isDynamicSetting(target.container, target.key)) {
				refusal = 'dynamic'

				continue
			}

			const stored = target.container.settings?.get?.(target.key)
			if ('string' !== typeof stored) {
				refusal = 'no-value'

				continue
			}

			const outcome = replaceInStoredValue(stored, word, replacement)
			if (!outcome) {
				// Rendered-only text: a word that exists only in a shortcode's output is flagged and held by no
				// stored value. The cause speaks for the whole document, so a plain miss in any unit vetoes it
				// below — naming one widget's shortcode would replace advice that is correct for another.
				const cause = classifyMiss(target, stored, word)

				if ('not-found' === cause) {
					sawPlainMiss = true
				} else if ('not-found' === refusal) {
					refusal = cause
				}

				continue
			}

			window.$e.run('document/elements/settings', {
				container : target.container,
				settings  : { [target.key]: outcome.text },
				options   : { external: true }
			})

			reissueDroppedRender(target.container, triggerReanalysis, armedModels)

			writtenOwners?.add(unit.ownerId)
			totalCount += outcome.count
		} catch (_e) {
			continue
		}
	}

	// Only when the whole document wrote nothing; order-independent, so walk order never decides
	// what the user reads.
	if (!totalCount && refusal) {
		writeRefusal.reason = (sawPlainMiss && 'shortcode-output' === refusal) ? 'not-found' : refusal
	}

	// Overrides every per-unit reason, which would then describe a widget the word is not in.
	if (!totalCount && !isWordInUnits(units, word)) {
		writeRefusal.reason = 'not-editable'
	}

	// A programmatic setting write doesn't fire the editor input that drives analysis.
	if (totalCount && triggerReanalysis) {
		requestPostUpdate(500)
	}

	return totalCount
}

// The zero-width characters a sentence needle must not carry into a search: U+200B ZERO WIDTH
// SPACE and U+FEFF. Not U+200C/U+200D, which are part of words in Persian, Arabic and Indic
// scripts and of emoji sequences, so the stored value holds them too.
//
// The pen leaves a trailing U+200B in the unit's DOM, which `trim()` keeps, so a sentence captured
// after a pen session carries a character the stored value never had. Stripped from the NEEDLE
// only: stripping the haystack would shift offsets relative to the parse the edit lands in.
const ZERO_WIDTH_PATTERN = /[\u200B\uFEFF]/g

const stripZeroWidth = (text) => String(text ?? '').replace(ZERO_WIDTH_PATTERN, '')

// Quote folding on BOTH sides — unlike the word path, which splices a class into its needle
// ({@see replaceInStoredValue}). findSentenceRanges() escapes the needle verbatim, so there is no
// class to splice into. Safe because the folded strings only locate a range and the fold is
// length-preserving; length-changing texturize rewrites are not folded and simply miss.
const foldQuoteClasses = (text) => normalizeQuotes(String(text ?? ''))

// Shortcode markup. `overlapsShortcode` runs it over the PARSED projection, where a shortcode is
// the one piece of markup that survives as literal text; `hasShortcode` over the RAW stored
// string. Never take an offset from one string into the other.
const SHORTCODE_PATTERN = /\[[^\]]*\]/g

const overlapsShortcode = (projected, range) => {
	SHORTCODE_PATTERN.lastIndex = 0
	let match

	while (null !== (match = SHORTCODE_PATTERN.exec(projected))) {
		if (range.start < match.index + match[0].length && range.end > match.index) {
			return true
		}
	}

	return false
}

// A module-scoped `g` regex advances its own lastIndex on test(), so a stale one would skip the
// start of the next value it is asked about.
const hasShortcode = (value) => {
	SHORTCODE_PATTERN.lastIndex = 0

	return SHORTCODE_PATTERN.test(value)
}

// Letter runs, which are exactly what a texturize mismatch cannot account for: wptexturize
// rewrites punctuation — quotes, dashes, ellipses, `(c)` — and never a letter.
const WORD_RUN_PATTERN = /\p{L}+/gu

const wordsSurvive = (projected, needle) => (String(needle).match(WORD_RUN_PATTERN) || [])
	.every(run => projected.includes(run))

// Whether a setting's stored value is content the render expands (shortcodes, wptexturize). Only
// WYSIWYG controls go through Elementor's parse_text_editor(); a heading's `title` prints raw.
// Read from the container's control stack, as Elementor's editor does; an absent stack reads false.
const isRichTextSetting = (container, key) => 'wysiwyg' === container?.controls?.[key]?.type

// The same question with OPPOSITE polarity — do not harmonise. isRichTextSetting gates the WRITE
// and fails closed (a wrong "yes" stores a `<p>` inside a heading); this gates the AI affordance
// and fails open (a wrong "yes" removes the only fix a failing check has).
const knowsSettingCannotHoldBlocks = (container, key) => {
	const type = container?.controls?.[key]?.type

	return !!type && 'wysiwyg' !== type
}

/**
 * Tells a miss's two causes apart: text held under a different spelling, and text not held at all.
 *
 * NOTE: texturize rewrites punctuation only, so a needle whose every letter run is in the stored
 * projection is in the widget; otherwise a shortcode is the likely source. Uncertain cases stay
 * `not-found`, the generic advice.
 *
 * @param {Object} target The resolved `{container, key}` the value was read from.
 * @param {string} stored The stored setting value the sentence was rendered from.
 * @param {string} needle The sentence or word the search did not find in it.
 * @returns {string} The refusal reason.
 */
const classifyMiss = (target, stored, needle) => {
	// Both ahead of the projection, because that is a parse per unit on the document-wide path and
	// between them they answer `not-found` for every value a shortcode could not have expanded in.
	if (!isRichTextSetting(target.container, target.key) || !hasShortcode(stored)) {
		return 'not-found'
	}

	const projected = foldNbsp(buildTextStream(parseInert(stored)).text)

	return wordsSurvive(projected, needle) ? 'not-found' : 'shortcode-output'
}

// The address, the needle and the stored value a mark write needs, or the reason it cannot be had.
// Shared by both mark write paths so they cannot drift on which refusal a state earns.
const resolveMarkWrite = (mark) => {
	const unitId   = String(mark.unitId)
	const at       = unitId.indexOf(':')
	const ownerId  = 0 < at ? unitId.slice(0, at) : ''
	const attrName = 0 < at ? unitId.slice(at + 1) : ''

	// An empty ownerId means the marker had no `.elementor-widget` ancestor, so no container
	// addresses it — the same unit decorate() refuses to paint, so this refusal should be
	// unreachable from a mark the paint produced.
	if (!ownerId || !attrName) {
		return { refusal: 'no-unit' }
	}

	// String-typed as well as non-empty, and the emptiness is tested AFTER the strip so a sentence
	// that is nothing but zero-width residue is caught here rather than reaching
	// findSentenceRanges as an empty pattern.
	const needle = 'string' === typeof mark.sentence ? foldQuoteClasses(stripZeroWidth(mark.sentence)) : ''
	if (!needle) {
		return { refusal: 'no-sentence' }
	}

	const target = resolveSettingTarget(ownerId, attrName)
	if (!target) {
		return { refusal: 'no-container' }
	}

	if (isDynamicSetting(target.container, target.key)) {
		return { refusal: 'dynamic' }
	}

	// settings.get(), never the rendered node. The canvas is texturized and — while a pen is or
	// has been open — carries the pen's own residue; the stored value is the only truth about what
	// a write is editing.
	const stored = target.container.settings?.get?.(target.key)
	if ('string' !== typeof stored) {
		return { refusal: 'no-value' }
	}

	return { needle, stored, target }
}

// The settings command the pen itself uses, plus the two follow-ups every write here owes: a
// re-render Elementor dropped while `allowRender` was false, and the analysis a programmatic
// setting write does not trigger on its own.
const commitSettingWrite = (target, html) => {
	window.$e.run('document/elements/settings', {
		container : target.container,
		settings  : { [target.key]: html },
		options   : { external: true }
	})

	reissueDroppedRender(target.container, true, null)
	requestPostUpdate(500)
}

// Runs a synchronous mark write and resolves once its widget has re-rendered, for the reason
// replaceAllOccurrences() waits: the popover re-analyzes on resolve, and the analysis reads the canvas.
const writeMarkAndAwaitRender = async (mark, write) => {
	const renders = watchElementRenders()
	let written   = false

	try {
		written = !!write()
	} finally {
		if (!written) {
			renders.stop()
		}
	}

	if (written) {
		await renders.until(new Set([ String(mark.unitId).split(':')[0] ]))
	}

	return written
}

/**
 * Rewrites ONE mark's own sentence by editing the stored setting value it was rendered from.
 *
 * NOTE: addressed by the mark's `sentence`, never its `range`, and only when that sentence occurs
 * exactly once — rendered and stored projections can differ in length (shortcodes, `&nbsp;`), so a
 * rendered range can land on the wrong stored occurrence. Do not port V4's range fast path.
 *
 * @param {Object} mark        The store mark to rewrite.
 * @param {string} replacement The text to put in its place.
 * @returns {boolean} True when the stored value was written.
 */
const replaceMarkInElementor = (mark, replacement) => {
	writeRefusal.reason = null

	// An empty replacement is a deletion, which no fix flow makes.
	if (!mark?.unitId || !replacement || 'string' !== typeof replacement) {
		return false
	}

	if ('function' !== typeof window?.$e?.run) {
		return false
	}

	const resolved = resolveMarkWrite(mark)
	if (resolved.refusal) {
		recordRefusedWrite(resolved.refusal)

		return false
	}

	const { needle, stored, target } = resolved

	// The reason the plan below could not produce an edit, so one refusal is recorded with the
	// cause rather than a bare miss. `no-edit` is the state after a range WAS found and the planned
	// rewrites still applied nothing — an element-backed segment inside the range is the only way
	// there, which is an emoji image or a synthetic block boundary.
	let refusal = 'not-found'

	const edited = editStoredHtml(stored, (projected) => {
		const found = findSentenceRanges(foldQuoteClasses(projected), needle)

		// The unique-occurrence rule, and it is the whole guard for the same sentence twice inside
		// one unit. A bare first match would rewrite occurrence 1 while the user watches the
		// highlight on occurrence 2.
		if (1 !== found.length) {
			refusal = found.length ? 'ambiguous' : classifyMiss(target, stored, needle)

			return []
		}

		// A shortcode's arguments are configuration, not prose: an AI rewrite landing inside one
		// silently changes what the shortcode renders. Refused visibly instead.
		if (overlapsShortcode(projected, found[0])) {
			refusal = 'shortcode'

			return []
		}

		refusal = 'no-edit'

		return [ { start: found[0].start, end: found[0].end, text: replacement } ]
	})

	if (!edited) {
		recordRefusedWrite(refusal)

		return false
	}

	commitSettingWrite(target, edited.html)

	return true
}

/**
 * Replaces the BLOCK a mark's sentence lies in with several, from a structured AI suggestion.
 *
 * NOTE: refuses a non-rich-text setting — a `<p>` written into a heading's `title` would nest a
 * block inside the rendered `<h2>`.
 *
 * @param {Object} mark  The store mark whose block to replace.
 * @param {Array}  parts Structured parts from `parseStructuredReplacement`.
 * @returns {boolean} True when the stored value was written.
 */
const replaceMarkBlocksInElementor = (mark, parts) => {
	writeRefusal.reason = null

	// Two parts is the least that needs a block write at all; the funnel keeps one part on the text
	// path, and this guard is what stops a caller from routing it here anyway.
	if (!mark?.unitId || !Array.isArray(parts) || 2 > parts.length) {
		return false
	}

	if ('function' !== typeof window?.$e?.run) {
		return false
	}

	const resolved = resolveMarkWrite(mark)
	if (resolved.refusal) {
		recordRefusedWrite(resolved.refusal)

		return false
	}

	const { needle, stored, target } = resolved

	if (!isRichTextSetting(target.container, target.key)) {
		recordRefusedWrite('not-structural')

		return false
	}

	// Only the causes the plan can see; editStoredBlock names the rest, since only the parse can
	// tell them apart.
	let refusal = 'not-found'

	const edited = editStoredBlock(stored, (projected) => {
		const found = findSentenceRanges(foldQuoteClasses(projected), needle)

		// The same unique-occurrence rule the text write turns on, for the same reason: a bare first
		// match would replace the paragraph the highlight is NOT on.
		if (1 !== found.length) {
			refusal = found.length ? 'ambiguous' : classifyMiss(target, stored, needle)

			return null
		}

		if (overlapsShortcode(projected, found[0])) {
			refusal = 'shortcode'

			return null
		}

		return { start: found[0].start, end: found[0].end, markup: buildStructuredMarkup(parts) }
	})

	if (!edited?.html) {
		recordRefusedWrite(edited?.refusal || refusal)

		return false
	}

	commitSettingWrite(target, edited.html)

	return true
}

export default {
	id : 'elementor-v3',

	// Paired with `paintsViaContract`: this drives the highlight controls and the composable's
	// onMounted wiring, that one drives the paint. Either alone is incoherent — controls over a dead
	// paint, or marks the user has no controls for.
	analyzeOnly : false,

	// Owns the builder it runs inside, unlike block/classic under SiteOrigin or WPBakery.
	// Still orthogonal to analyzeOnly, which is why the highlighting gate reads both rather
	// than deriving one from the other.
	ownsPageBuilder : true,

	// The paint switch: `decorate` becomes the only paint, `onInvalidated` the only repaint channel,
	// and `getContentUnits` the only mark source. No `<mark>` reaching stored content rests on
	// decorate()'s per-unit pen gate alone — there is no save-time strip.
	paintsViaContract : true,
	suppressNextPenArm,
	revealNode        : revealElementorNode,

	// DELIBERATELY ABSENT:
	//   - `isSuspended`/`onSuspensionChange`: Elementor emits no pen open/close event to resume from,
	//     so the pen is a per-unit question instead — isPenOpenAt() in decorate(), resumed by
	//     armPenClosePoll().
	//   - `getFirstUnitNode`: its only consumer is the MutationObserver onInvalidated replaces.

	isActive () {
		try {
			return !!isElementorEditor()
		} catch (_e) {
			return false
		}
	},

	// The preview iframe renders asynchronously long after the editor global exists.
	isReady () {
		try {
			return !!getElementorRoot()
		} catch (_e) {
			return false
		}
	},

	getContentUnits () {
		return getUnits()
	},

	// One unit by id, without the roster's per-marker text streams ({@see resolveContentUnit}).
	getContentUnit (unitId) {
		return resolveContentUnit(unitId)
	},

	// Clears before painting rather than reconciling: a wholesale repaint is the cheapest idempotent
	// option, and decorateNode() refuses to re-wrap a painted mark either way.
	decorate (marks) {
		// Hoisted out of the try because the catch has to know both: whether the clear already
		// detached every painted node, and what the pass had already accounted for when it threw.
		const dropped   = []
		const vanished  = new Set()
		const suspended = new Set()
		let cleared     = false

		try {
			const root = getElementorRoot()

			clearDomDecorations(root)
			cleared = true

			if (!Array.isArray(marks) || !marks.length) {
				return
			}

			// Recorded, not returned silently: with the canvas gone every mark is unreachable.
			if (!root) {
				refuse(dropped, marks, 'no-canvas')
				recordDroppedMarks(dropped)

				return
			}

			const byUnit = groupMarksByUnit(marks)

			// Counted, not skipped: on a V3 page this adapter is the only painter, so a mark
			// with no unit to place it in can only come from a wiring fault.
			refuse(dropped, marks.filter(mark => !isAddressableMark(mark)), 'no-unit')

			if (!byUnit.size) {
				recordDroppedMarks(dropped)

				return
			}

			// Read through the same member the composable built these marks from, so ranges align against
			// the `html` they were measured in. A unit missing from this map has vanished.
			const units     = new Map((this.getContentUnits?.() || []).map(unit => [ unit.unitId, unit ]))
			const analyzers = new Set()

			for (const [ unitId, unitMarks ] of byUnit) {
				// Scoped per unit: one widget that cannot be resolved must not cost the page
				// every other widget's marks.
				try {
					const unit = units.get(unitId)
					if (!unit) {
						recordVanishedMarks(unitMarks, vanished)

						continue
					}

					// An ownerless marker (no `.elementor-widget` ancestor) has no container, so every fix would
					// refuse. Refused here rather than dropped from getUnits(), which also feeds the analysis.
					if (!unit.ownerId) {
						refuse(dropped, unitMarks, 'no-owner')

						continue
					}

					// A `writable: false` unit's `html` is rendered output, so painting it would put the mark and
					// its fix in different coordinate spaces. None of the write paths gates on `writable`, so a
					// degraded tier added to getUnits() also needs a write-side gate.
					if (false === unit.writable) {
						refuse(dropped, unitMarks, 'not-writable')

						continue
					}

					// A dynamic-bound setting stores an `[elementor-tag ...]` call, so every fix refuses, while the
					// widget still renders the tag's resolved text. Not painted, but still analysed — that text is
					// genuine page content.
					if (isDynamicUnit(unit)) {
						refuse(dropped, unitMarks, 'dynamic')

						continue
					}

					const node = unit.getNode()
					if (!node) {
						refuse(dropped, unitMarks, 'no-node')

						continue
					}

					// THE ONLY GUARD keeping a `<mark>` out of stored content: an open pen persists its innerHTML on
					// every keystroke ({@see suspendMarks}). Per unit, not document-wide, so the other marks keep
					// their nodes. armPenClosePoll() is the resume, since opening and closing the pen dispatch nothing.
					if (isPenOpenAt(node)) {
						suspendMarks(unitMarks, suspended)
						armPenClosePoll(unitId)

						continue
					}

					// Projection and paint node are the same node, so the offset is normally 0 — except that `html`
					// collapses a literal `&nbsp;` 6→1 while foldNbsp does not; such text falls through to the
					// occurrence search.
					const stream  = buildTextStream(node)
					const aligned = alignProjectedMarks({
						rendered  : foldNbsp(stream.text),
						projected : unit.html,
						marks     : unitMarks,
						skipped   : stream.skipped
					})

					// Through refuseOne rather than spread straight in, so the aligner's own
					// refusals clear their node like every other refusal does.
					for (const entry of aligned.dropped) {
						refuseOne(dropped, entry.mark, entry.reason)
					}

					const decorated = decorateNode({ node, marks: aligned.aligned })

					refuse(dropped, decorated.unpainted, 'no-paint')

					for (const painted of decorated.painted) {
						// The same hand-off the classic paint makes right after annotating: the
						// store resolves a mark's live node from here, and drops any mark without
						// one (sanitizeHighlightMarks).
						painted.mark.node = painted.node
						analyzers.add(painted.mark.analyzer)
					}
				} catch (_e) {
					refuse(dropped, unitMarks, 'unit-error')
				}
			}

			// The canvas is an iframe, so the composable's main-document stylesheet cannot reach
			// these marks — without this they are in the DOM and invisible.
			ensureDecorationStyles(root.ownerDocument, [ ...analyzers ])

			// Needs the live canvas document, which only a pass holds ({@see ensurePenOpenWatch}).
			ensurePenOpenWatch(root.ownerDocument)

			recordDroppedMarks(dropped)
		} catch (_e) {
			// Never throw into Elementor's editor. Once the clear has run every mark holds a detached node, so
			// refuse those still holding one — only disconnected nodes, and not those this pass already
			// accounted for, so on-screen marks keep theirs and nothing is counted twice.
			if (cleared && Array.isArray(marks)) {
				const alreadyHandled = new Set([ ...dropped.map(entry => entry.mark), ...vanished, ...suspended ])

				refuse(dropped, marks.filter(mark => !alreadyHandled.has(mark) && !mark?.node?.isConnected), 'pass-error')
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

	// REQUIRED, and null — never undefined — while the preview iframe is still loading: the
	// composable's wrapper poll treats `undefined` as found and would suspend highlighting for the
	// page's lifetime.
	getRootNode () {
		try {
			return getElementorRoot()
		} catch (_e) {
			return null
		}
	},

	// One unit's live node, without building the roster's text streams ({@see resolveUnitNode}).
	getUnitNode (unitId) {
		return resolveUnitNode(unitId)
	},

	// The MAIN document's body, never the canvas: the popover is position:fixed there and its
	// scoped styles are loaded there. Marks stay in the canvas and are lifted out of its rect
	// space by getMarkRectOffset() instead.
	getPopoverHost () {
		try {
			return document.body
		} catch (_e) {
			return null
		}
	},

	// The preview frame's on-screen box, clipped to the viewport — the only region a canvas
	// mark can actually be seen in, so a mark scrolled out of the canvas hides its popover
	// instead of pinning it to an edge with nothing in view. Elementor's own chrome (the panel,
	// the responsive bar, the navigator) sits outside that box already.
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

	// Marks are measured in canvas space and the popover is placed in the main document, so its rect
	// is lifted by the frame's offset. {0, 0} for a canvas that never booted or was swapped out;
	// repositionHoverPopover() detects the vanished frame itself.
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

	// Deliberately empty: Elementor's views render from the model, so painting `<mark>`s dispatches
	// nothing to suppress.
	beginPaint () {
		return null
	},

	endPaint () {},

	// Deliberately empty: decorate() injects the canvas stylesheet, since the preview iframe may not
	// exist yet at mount.
	init () {},

	// The data-command hook and the pen-close registry, shared with V4's legacy tier
	// ({@see subscribeToInvalidation}), plus the element render for re-renders no command reports.
	onInvalidated (cb) {
		const stopRenders      = subscribeToElementRenders(cb)
		const stopInvalidation = subscribeToInvalidation(cb)

		return () => {
			stopRenders()
			stopInvalidation()
		}
	},

	// Rewrites ONE mark's own sentence, for the popover's AI-fix scopes; the spelling fix goes through
	// replaceAllOccurrences(). The read and the write stay synchronous: V3's pen dismisses itself on
	// the mousedown that got here, so nothing can change between them. Only the render wait is async.
	async replaceMark (mark, replacement) {
		try {
			return await writeMarkAndAwaitRender(mark, () => replaceMarkInElementor(mark, replacement))
		} catch (_e) {
			return false
		}
	},

	// Whether a block write could land, asked before the AI request is billed. Fails open
	// ({@see knowsSettingCannotHoldBlocks}).
	canReplaceMarkBlocks (mark) {
		try {
			const target = resolveMarkWrite(mark).target

			return !target || !knowsSettingCannotHoldBlocks(target.container, target.key)
		} catch (_e) {
			return true
		}
	},

	// The paragraph and section scopes arrive as structured parts: one string with `\n\n` breaks,
	// written into html where a newline is whitespace, would pull the headings into a run-on
	// paragraph.
	async replaceMarkBlocks (mark, parts) {
		try {
			return await writeMarkAndAwaitRender(mark, () => replaceMarkBlocksInElementor(mark, parts))
		} catch (_e) {
			return false
		}
	},

	// Resolves only once the written widgets have re-rendered. The analysis reads the canvas, not the
	// stored settings, and V3 renders after the command returns — so a caller re-analyzing on
	// resolve would otherwise read the pre-fix text and see nothing change.
	async replaceAllOccurrences (word, replacement, { triggerReanalysis = true } = {}) {
		const renders       = watchElementRenders()
		const writtenOwners = new Set()
		let count = 0

		try {
			count = replaceAllWordInElementor(word, replacement, triggerReanalysis, writtenOwners)
		} catch (_e) {
			renders.stop()

			return 0
		}

		if (!writtenOwners.size) {
			renders.stop()

			return count
		}

		await renders.until(writtenOwners)

		return count
	},

	// Builders refuse for causes the canvas does not show (a dynamic binding, a shortcode, a repeated
	// sentence), so this adapter supplies the reason; block and classic do not.
	consumeWriteRefusal,

	// The canvas document and window, where painted marks live — the composable's own iframe probe is
	// Gutenberg-only. Falls back to the main document/window before the preview boots, so callers
	// must re-ask rather than cache.
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