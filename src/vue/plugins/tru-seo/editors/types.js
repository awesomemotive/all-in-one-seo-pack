/**
 * @typedef {Object} ContentUnit
 * @property {string}                 unitId   `${ownerId}:${attrName}`. Stable within one pass
 *                                             only — classic ownerIds are positional, so an
 *                                             inserted paragraph renumbers every later unit.
 * @property {string}                 ownerId  The editor's id for the owning element/block.
 * @property {string}                 attrName The attribute/setting holding this text.
 * @property {string}                 html     Text projection of the unit, for measuring and
 *                                             matching only. Its relationship to the editor's
 *                                             stored value is adapter-specific: block and
 *                                             classic derive it from the value they write, but
 *                                             Elementor derives it from the RENDERED preview
 *                                             node, which diverges from the stored setting for
 *                                             shortcodes and oEmbeds. A write path must read
 *                                             the stored value and replace inside that — never
 *                                             reuse an offset into `html`, and never write
 *                                             `html` back.
 * @property {boolean}                [writable] Whether a write path can reach the SOURCE this
 *                                             unit's text came from. Optional, default TRUE
 *                                             when absent — the same convention analyzeOnly
 *                                             and isSupported use.
 *                                             NOT a claim that `html` IS that source. Both
 *                                             Elementor adapters' marker units project the
 *                                             rendered node while the write edits a stored
 *                                             setting, and they are writable anyway because
 *                                             the write takes `ownerId`/`attrName` as an
 *                                             ADDRESS and re-resolves its target inside the
 *                                             stored value — never by reusing an offset into
 *                                             `html`, which the entry above forbids outright.
 *                                             False marks a unit with no addressable source
 *                                             at all, recovered by a DEGRADED read: Elementor
 *                                             V4's whole-canvas fallback, whose unitId names
 *                                             the DOCUMENT rather than an element. Analyse
 *                                             those, never write them — `decorate`,
 *                                             `replaceMark` and `replaceAllOccurrences` must
 *                                             each refuse a unit that declares it false,
 *                                             because a mark on one can only ever offer a fix
 *                                             that refuses. The composable skips them while
 *                                             BUILDING MARKS ({@see TruSeoHighlighter.js}), which
 *                                             covers `decorate` and, through the mark it never
 *                                             built, `replaceMark`. It does NOT cover
 *                                             `replaceAllOccurrences`: that one is
 *                                             document-scoped and marks-independent, called
 *                                             straight off the resolved adapter
 *                                             ({@see highlighter/spellingReplace.js}), so an
 *                                             adapter's own write-side gate is the only one it
 *                                             has ({@see elementor.js:replaceMark}).
 * @property {boolean}                [highlightable] Whether the analyzer reads this unit's text.
 *                                             Optional, default TRUE when absent. False keeps
 *                                             the unit in the analysed string but out of mark
 *                                             building, so it cannot claim a sentence the
 *                                             analyzer found in another unit.
 * @property {Array}                  [skipped] `{start, end}` ranges of `html` the analyzer
 *                                             drops, such as `<code>` or `<blockquote>` text
 *                                             ({@see highlighter/textStream.js}). Optional,
 *                                             default none. No mark is built inside one.
 * @property {boolean}                [offCanvas] Whether the editor's view shows this unit's
 *                                             text only as a summary label, or not at all.
 *                                             Optional, default FALSE. The unit is still
 *                                             analysed, marked and writable; `decorate` skips
 *                                             painting it.
 * @property {boolean}                [hiddenInView] Whether the view shows this unit's text
 *                                             nowhere, not even in a label. Optional, default
 *                                             FALSE. The panels say how many of an issue's
 *                                             instances are in such units
 *                                             ({@see highlighter/sentences.js:countHiddenSentences}).
 * @property {function(): ?HTMLElement} getNode Resolves the live DOM node, or null if not rendered.
 */

/**
 * One painted span, as an adapter hands it to its editor's own highlight store.
 * NOT the input to `decorate` — that takes whole store marks.
 *
 * @typedef {Object} HighlightRange
 * @property {string} id    Mark id.
 * @property {number} start Absolute character offset into the unit's projection.
 * @property {number} end   Absolute character offset into the unit's projection.
 */

/**
 * What an editor adapter provides for TruSEO highlighting.
 *
 * READ THIS BEFORE WRITING A NEW ADAPTER. The members below are split into two groups by
 * whether anything in this plugin calls them **through this contract** today:
 *
 *   1. CORE — read by `resolveAdapter()`/`pickAdapter()` (`editors/index.js`), the
 *      highlighter composable (`composables/TruSeoHighlighter.js`), or
 *      `highlighter/spellingReplace.js`. Implement all of these.
 *   2. NOT WIRED — a documented shape with **no caller through this contract**. Implementing
 *      one changes nothing on its own, and omitting one breaks nothing. They exist because
 *      live-highlighting work is expected to consume them; treat them as a reserved shape to
 *      match, not as work you must do to ship an adapter.
 *
 * An adapter is the default export of a plain object literal in this directory, and it only
 * runs once it is added to `getRegistry()` in `editors/index.js`. Registry order is
 * precedence — most specific editor first — and the registry is built when called, not at
 * module body, to survive the `spellingReplace.js` import cycle.
 *
 * An omitted CORE member never throws — it degrades silently, because every call site either
 * optional-chains the member or is guarded by an earlier `!!adapter` check. `getRootNode` is
 * the one where that silence is actively harmful; read its entry before skipping anything.
 *
 * Implementations must never throw out of any method. A method that cannot do its job
 * returns a falsy result so the caller degrades to "no highlighting".
 *
 * Members are always called as methods on the adapter object (`adapter.isReady()`) and never
 * destructured off it — `blockEditor`'s `isReady()` delegates through `this`, so a detached
 * reference loses its binding.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CORE — consumed today
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * @typedef {Object} EditorAdapter
 * @property {string}                                    id  Adapter identity. Still consulted
 *           directly by the composable in four places ({@see TruSeoHighlighter.js}): the two
 *           paint calls, and the two classic-only TinyMCE handle-lifecycle sites. Anything a
 *           new adapter needs must be expressed as a capability below, not by adding a fifth
 *           id test.
 * @property {boolean}                                   ownsPageBuilder  Whether this adapter
 *           owns the page builder it is running inside. Optional, default false — and false is
 *           the answer that matters: a builder with no adapter of its own runs ON TOP of a live
 *           classic or block editor, so those adapters resolve there while owning only the
 *           editor underneath, not the builder painted over it. Read by
 *           `isHighlightableEditor()` ({@see TruSeoHighlighter.js}), which is the distinction
 *           isPageBuilderEditor() cannot make: it answers "a builder is present", not "the
 *           resolved adapter is its adapter". Orthogonal to analyzeOnly, and the gate reads both
 *           because they answer different questions — NOT because a shipped adapter currently
 *           makes them disagree. None does: both Elementor adapters own their builder and both
 *           highlight. The analyzeOnly operand is the guard for the next adapter that cannot.
 * @property {boolean}                                   paintsViaContract  Whether the
 *           highlighter should paint this editor through `decorate` below. Optional, default
 *           false, and the default is the safe one — an adapter that omits it gets no paint,
 *           which is visible immediately, rather than a silent one.
 *           NOT derived from `decorate` being present: ALL FOUR registered adapters declare one
 *           and only the two Elementor adapters paint through this contract (block dispatches to
 *           a wp.data store through its own paint module; classic's is a bare `decorate () {}`
 *           stub satisfying this typedef). Same explicit-opt-in reasoning as analyzeOnly below.
 *           Also gates the onInvalidated subscription,
 *           where a mis-scoped test is worse than a missing paint: block's onInvalidated is a
 *           bare `wp.data.subscribe`, so a repaint driven off it that itself dispatches to a
 *           wp.data store feeds itself forever.
 *           SETTING THIS TRADES ONE REPAINT CHANNEL FOR ANOTHER — declare `onInvalidated` in
 *           the same breath. It gates the invalidation subscription AND `observeEditor()`'s
 *           early return, so it switches the composable's MutationObserver off; the
 *           subscription it switches on is optional-chained, so an adapter carrying this flag
 *           and no onInvalidated ends up with neither. Marks then vanish on every re-render and
 *           stay gone until analysis next runs. Stated here because onInvalidated's own entry is
 *           the one such an author never reaches.
 * @property {boolean}                                   analyzeOnly   Whether this editor never
 *           activates live highlighting — analysed and written through its own adapter, with no
 *           painted surface to point a user at. Explicit per-adapter opt-in — not derived from
 *           isActive or id — so a new adapter defaults to false (ordinary, highlighting-capable)
 *           until it deliberately claims otherwise.
 *           NO SHIPPED ADAPTER DECLARES THIS TRUE. It is half of the highlighting gate
 *           ({@see ownsPageBuilder}), for a future analyse-only builder; it is NOT read by
 *           isAnalyzeCapableEditor(), which keys on ownsPageBuilder instead.
 *           SETTING THIS TRUE ON A PAINTING ADAPTER SILENTLY KILLS ITS PAINT'S CONTROLS, not its
 *           paint: it drives the store's `enabled`, which is the v-if on the highlight controls
 *           and onMounted's early return, while `usesAdapterPaint()` still reaches `decorate`
 *           from reset(). Pair it with paintsViaContract's absence or the two disagree.
 * @property {function(): boolean}                       isActive  Does this editor own the page?
 *           Ownership only — never readiness. resolveAdapter() memoises the answer, so a
 *           capability probe here caches "no adapter" for the page's lifetime whenever the
 *           editor's own globals land a tick after the asking app mounts.
 * @property {function(): boolean}                       isSupported Can this editor EVER
 *           highlight on this page? Sampled once, at mount, to point
 *           truSeoHighlighterStore.enabled (the v-if on the highlight controls). Optional in
 *           the literal sense: an adapter that omits it is treated as supported
 *           (`adapter.isSupported?.() ?? true`), the same convention analyzeOnly uses, so a new
 *           adapter behaves like an ordinary editor until it opts out. Classic is a real
 *           instance of that default — it declares none, because its Code tab is a suspension
 *           handled downstream of `enabled`, not an incapacity.
 * @property {function(string, string, Object=): Promise<number>} replaceAllOccurrences
 *           Document-scoped, marks-independent whole-word replace. Resolves to the count
 *           of occurrences written across the document (0 means none), so a caller can
 *           tell a partial write from a complete one rather than a bare pass/fail. Required
 *           because the Spelling tab fixes words with highlighting off, when no marks exist
 *           at all. The options argument carries `triggerReanalysis`, honoured by every adapter
 *           whose write is too programmatic to fire the editor's own analysis input — classic
 *           and both Elementor adapters. The block editor has no re-analysis step to trigger,
 *           so it takes the two required arguments and ignores the rest. Called from
 *           `highlighter/spellingReplace.js`, not the composable.
 *           A builder adapter must enumerate only units it can WRITE: `writable: false` marks
 *           a degraded read, and a document-scoped fix that skips those returns a count lower
 *           than the assessment found — a partial fix, which the count is what makes visible.
 * @property {function(Object, string): Promise<boolean>} replaceMark  Takes a store mark, not a
 *           range. Address the mark by `unitId` and its own `sentence` — never by `parent`,
 *           which a builder mark can carry as null, and never by the offsets alone: `range`
 *           indexes the projection the mark was BUILT against, so an element edited since then
 *           leaves the offsets pointing at unrelated characters. Resolve false rather than
 *           approximating.
 *           Called from `highlighter/spellingReplace.js:replaceWord()`, which the popover's
 *           AI-suggestion path reaches and its spelling fix does NOT — that one is document-wide
 *           and goes through replaceAllOccurrences above. Its block/classic branches remain
 *           as the fallback for a page no adapter claims and call the same two functions those
 *           adapters wrap — so an adapter that omits this member degrades to exactly the
 *           pre-adapter behaviour, which for a page builder means no write at all.
 *           A builder adapter must refuse a `writable: false` unit here: with no addressable
 *           source there is nothing to write, and no delegate can invent one — Elementor V4's
 *           whole-canvas unit is the shipped instance. A WRITABLE unit whose `html` is a rendered
 *           projection is a different case and is written, not refused, as long as the write
 *           re-finds its target in the stored value and refuses when that is not unique: V4
 *           delegates its legacy tier to V3's implementation, which does exactly that. What may
 *           never happen is resolving a range in one string and applying it to the other.
 * @property {function(Object, Array): Promise<boolean>} replaceMarkBlocks  The paragraph and
 *           section scopes' write: takes the AI suggestion already parsed into `{type, level,
 *           content}` PARTS and stores real block markup for them. Optional, and omitting it means
 *           the fix falls to the plain-text write — which is exactly the defect this member exists
 *           to remove, so omit it only where the storage genuinely cannot hold a block.
 *           WHY PARTS AND NOT A STRING. A plain string can carry a paragraph break only as a
 *           newline, and html collapses a newline to whitespace: the flattened write reached V3, V4
 *           and classic alike and every break vanished at render, leaving one run-on paragraph with
 *           the `## ` headings inlined into it. So an adapter storing HTML must build tags, and the
 *           builder is shared ({@see highlighter/structuredMarkup.js:buildStructuredMarkup}).
 *           REFUSE, NEVER APPROXIMATE, where the target cannot hold blocks. A setting that stores
 *           inline text prints a `<p>` inside whatever element the widget wraps it in, and the
 *           browser then tears the two apart — Elementor V3 gates on the control being `wysiwyg`
 *           and V4's atomic tier refuses outright, because its own prop allowlist excludes `p` and
 *           `h1`-`h6`. Both answer `'not-structural'`, which prints the generic advice, and that
 *           advice is true for it: the text IS in the widget.
 *           The block editor is deliberately NOT routed through this member: it replaces the block
 *           outright via `wp.blocks`, upstream of any adapter, and that arm is untouched.
 * @property {function(Object): boolean}                canReplaceMarkBlocks  Whether a block write
 *           could land for this mark, asked BEFORE the AI request is billed. Optional.
 *           It exists because the AI suggestion is paid for on click: a paragraph-scope request for a
 *           target that cannot hold a block is refused for every answer with two or more parts —
 *           which is what a paragraph-SPLIT returns — so the credit bought a refusal. Only the
 *           `paragraph` and `section` scopes consult it; every other scope lands through the
 *           plain-text write, which every adapter can do.
 *           MUST FAIL OPEN, and its polarity is deliberately the INVERSE of the write's own gate.
 *           Answer false only for a target the adapter POSITIVELY knows cannot hold a block; anything
 *           unresolved answers true. A wrong false removes the only fix affordance a genuinely
 *           failing check has, while a wrong true costs one request. The write gate fails closed for
 *           the opposite reason — a wrong yes there stores a `<p>` inside a heading — so the two must
 *           not be harmonised, the same asymmetry `isDynamicUnit` keeps against `isPenOpenAt`.
 * @property {function(): ?string}                      consumeWriteRefusal  Why the LAST write
 *           refused, read-once. Optional, and OMITTING IT MEANS SILENCE — which is the whole of
 *           how block and classic stay quiet. `highlighter/spellingReplace.js` asks this of the
 *           resolved adapter after a falsy `replaceMark` or a zero `replaceAllOccurrences`, and
 *           the two surfaces that alarm (the Spelling tab's "fix failed" alert and the
 *           popover's) gate on the answer being non-null. So an adapter that does not declare
 *           this member produces no alert anywhere, with no per-editor branch existing to keep
 *           in sync — the mechanism, not a convention.
 *           DECLARE IT ONLY FOR REFUSALS THE USER CAN ACT ON. Block and classic deliberately do
 *           not: their zeros come from attributes the writer cannot always address (a button's
 *           label, a table cell, a second rich-text attribute), where a fix that does nothing is
 *           common and alarming on it would cry wolf. A builder refuses for causes it KNOWS — a
 *           dynamic binding, a shortcode, a sentence occurring twice — none of which show
 *           anything in its canvas, which is what makes the alert worth raising there.
 *           READ-ONCE, and the reason it must be: the writes it describes return a boolean and a
 *           count, so the reason travels beside them rather than in them. Clear it on the way IN
 *           to every write, so a stale reason can never be read as the current attempt's.
 *           RESERVED STRINGS, `'dynamic'` and `'shortcode-output'` (plus `'editor-open'` and the
 *           panel-only pair below), are the only reason strings with a contract. Both alerting
 *           surfaces branch on each by literal
 *           (`HighlightPopover.vue`, `SpellingAnalysis.vue`) to name where the text really lives
 *           instead of offering the generic "edit it in the content", which cannot be done for a
 *           value the widget renders from a tag or from a shortcode's output. Every other reason is
 *           free-form and prints the generic wording. So an adapter refusing either cause must
 *           answer exactly that literal: naming it anything else — a delegation forwarding
 *           `'dynamic-binding'`, say — silently restores the wrong advice with nothing failing.
 *           The set is small on purpose. `'shortcode'` is deliberately NOT in it: it means
 *           the sentence lies ON a shortcode call the STORED value holds, which is text the user
 *           can edit, so the generic wording is right for it. Reserve a reason only when the
 *           generic advice is FALSE, never merely imprecise.
 *           `'editor-open'` is reserved on both surfaces too: the element's settings panel is open and
 *           would overwrite the write on close, so the advice is to close it and retry.
 *           `'locked-module'` is reserved on both surfaces as well: the builder's own lock blocks the
 *           edit, so the advice is to unlock the module, not to edit it directly.
 *           TWO PANEL-ONLY STRINGS, `'not-editable'` and `'post-title'`. `'not-editable'`: the word is
 *           in no unit's `html` at all, only in widget markup no unit covers (an Elementor image
 *           caption, an HTML widget). `'post-title'`: the unit renders the post title (WPBakery's
 *           `vc_custom_heading` with `source="post_title"`), so the title is what to change. Only the
 *           Spelling tab branches on them: neither text is painted, so the popover meets them only
 *           on a stale mark, where the generic wording is right.
 * @property {function(): ?HTMLElement}                  getRootNode        The editor's content root.
 *           REQUIRED, AND ITS ABSENCE FAILS SILENTLY. `getEditorNode('wrapper')` returns
 *           whatever this answers, so an adapter that omits it yields `undefined` — and the
 *           mount-time wrapper poll's `if (null !== getEditorNode('wrapper'))` is TRUE for
 *           `undefined`. The poll clears itself and calls `watchEditorMode()` as though the
 *           editor had been found, after which `evaluateHighlightingAllowed()` reads the same
 *           `undefined` and returns false forever. Result: the highlight controls are visible,
 *           highlighting never engages, and nothing is logged. Return a real element or null;
 *           never leave this off an adapter that highlights.
 * @property {function(string): ?HTMLElement}           getUnitNode  ONE unit's live node, by the
 *           `unitId` a mark carries. Optional; only a builder adapter needs it, and block and
 *           classic declare none because their marks carry no unitId to ask about. Read by the
 *           Readability tab's "N found" jump for marks that neither painted nor kept a `parent`.
 *           MUST NOT use the roster: `getContentUnits()` is quadratic in element count, and this
 *           runs on a click. Returns the widget, so a caller must prefer any painted mark.
 * @property {function(): ?(HTMLElement|Object)}         getFirstUnitNode   First content unit's node.
 *           Its parent is the MutationObserver target that watches for a removed content node
 *           and calls `reset()` — not anchor/scroll math, which lives in `getMarkRectOffset`
 *           instead. NOTE the return is not simply `?HTMLElement`: all three adapters that answer
 *           it — block, classic and Elementor V4 — return a bare `{}` when the lookup runs but
 *           finds no node, and null only when it throws.
 *           `{}` is deliberate — `{}.parentElement` is undefined, which the observer setup reads
 *           as "nothing to watch" and skips. A caller must not assume DOM methods on this.
 *           NOT CONSULTED AT ALL FOR A `paintsViaContract` ADAPTER: its one consumer is
 *           `getEditorNode('first-block')`, called only from `observeEditor()`, which returns
 *           before that line for a contract painter because `onInvalidated` replaces the
 *           observer. Elementor V4 implements it anyway and the method is dead there. Implement
 *           it only if your adapter also paints through one of the built-in paint modules.
 * @property {function(): ?HTMLElement}                  getPopoverHost     Element the popover mounts
 *           into. Classic mounts OUTSIDE TinyMCE's `.mce-edit-area`, whose DOM churn on
 *           caret/selection changes would otherwise tear the popover down. NOTE no shipping
 *           adapter's EXPRESSION can answer null — block, Elementor V3 and Elementor V4 share one
 *           byte-identical `return document.body`, and classic's fallback runs through
 *           `getClosestNodeByPropertyValue()`, which itself falls back to
 *           `document.documentElement`. All four are nonetheless wrapped in a try whose catch
 *           returns null, so all four can. There is no block-vs-Elementor asymmetry here; the
 *           caller null-guards regardless.
 * @property {function(): {top: number, bottom: number}} getViewportBounds  Top/bottom the popover may not exceed.
 * @property {function(): {top: number, left: number}}   getMarkRectOffset  Offset from mark-rect space to main-viewport space.
 *           Non-zero when marks live in an iframe — classic's TinyMCE body, the block editor's
 *           canvas, and both Elementor adapters' `#elementor-preview-iframe`.
 *           NOTE: a vanished or not-yet-usable frame must also suppress painting (hide the
 *           popover, not just position at a stale spot) — this return shape has no channel
 *           for that signal, since {0, 0} means both "no offset needed" and "the frame is
 *           gone". Every one of those editors can lose its frame under a live instance: the block
 *           canvas on a device-preview switch, code-editor toggle or distraction-free mode,
 *           classic's `#content_ifr` on a TinyMCE re-init or builder swap, and Elementor's
 *           preview on a reload or document switch. A caller that needs to hide must detect the
 *           missing frame itself.
 * @property {function(HTMLElement): void}               revealNode  Opens any collapsed container
 *           (toggle, accordion) hiding `node`, so a jump can scroll to it. Optional; called right
 *           before every jump's `scrollIntoView` ({@see spellingJump.js:revealInEditor}). Without
 *           it a word inside a closed toggle has no box, so the jump and the popover fall back to
 *           its nearest visible ancestor ({@see spellingJump.js:getVisibleAnchor}).
 * @property {function(): number}                       getStickyTopOffset  Height of chrome fixed over
 *           the top of the editor's own content, such as a theme header a builder canvas renders,
 *           added to a jump's scroll margin. Optional; 0 without it.
 * @property {function(Element, Element): boolean}       isPointerOverlay  Whether an element at a
 *           point is the editor's own transparent click overlay over that mark, rather than
 *           something drawn on top of it. Optional; read only with `marksIgnorePointer`.
 * @property {function(Function): Function}              onSuspensionChange Fires when the editor's mode changes. DISTINCT from
 *           onInvalidated, which fires on content change.
 *           NOTE: an adapter passes cb through undebounced; the caller debounces.
 * @property {function(): boolean}                       isSuspended
 *           Editor-level mode (code editor / Text tab). A complete implementation also
 *           covers per-element suspension — e.g. the block editor's per-block "Edit as
 *           HTML" — since editing there is equally unsafe to highlight. Until every
 *           adapter covers both, the composable applies its own gate for the per-element
 *           case on top of this method.
 *           DECLARING THIS WITHOUT `onSuspensionChange` BUILDS A TRAPDOOR. `allowHighlighting`
 *           defaults true, so a gate sampled once can only ever CLOSE: one sample landing while
 *           an inline editor is focused suspends highlighting, wipes the user's analyzer
 *           selection, and nothing re-evaluates for the life of that app instance — silently,
 *           recoverable only by remounting the app. If you declare this, declare the resume
 *           channel with it ({@see elementorV4.js:onSuspensionChange}, whose comment argues why
 *           the channel is not optional).
 *           NOTE the composable's gate is sampled at mount and then only when
 *           `onSuspensionChange` fires, so it is NOT a paint-path guard. An adapter whose editor
 *           owns a live contenteditable should apply its own test inside `decorate` too — a
 *           repaint driven by an unrelated element's render otherwise paints into an editor the
 *           gate was never re-asked about. Scope that refusal to the affected UNIT, not the
 *           document: refusing everything nulls every mark's node, and `sanitizeHighlightMarks()`
 *           prunes on `!!node`, so one popover open inside that window empties the store until
 *           the next analysis. Answering "suspended" when the editor cannot be inspected is the
 *           safe side of the call.
 * @property {function(*=): ?Object}                     beginPaint  Suppress editor events around a
 *           paint; returns a token for endPaint (null when there is nothing to suppress).
 *           Receives one optional, opaque `context` argument: the handle the composable is about
 *           to paint with, when it has one. An adapter whose paint target is resolvable only
 *           live must PREFER this over its own lookup — a live lookup can answer a newer
 *           instance than the one being painted, which leaves the painted target's events
 *           unsuppressed and makes endPaint restore an object this paint never touched. Fall
 *           back to a live lookup when the argument is absent or unrecognised, so the adapter
 *           still works when driven standalone. Match it on shape, or `toRaw()` it first —
 *           never on identity: the composable holds its handle in a deep `ref`, so what
 *           arrives here is a reactive wrapper that fails `===` against the raw instance.
 *           Adapters that own their target outright may ignore it.
 * @property {function(?Object): void}                   endPaint    Restore what beginPaint
 *           suppressed. Takes beginPaint's token and nothing else, so it can only ever act on
 *           the target beginPaint chose.
 * @property {function(): void}                          init               One-time setup (e.g. registering RichText formats).
 *           Must be idempotent: the composable calls it on every mount as a safety net.
 * @property {function(): Document}                      getDocument  The document this
 *           editor's content — and therefore every mark painted into it — lives in. Read by
 *           the composable's own `getEditorDocument()`, which feeds `getListenerDocuments()`,
 *           the delegated mark-click listener, the click-outside/Escape handlers and the
 *           iframe stylesheet target. CONSULTED ONLY FOR AN ADAPTER THAT SETS
 *           `paintsViaContract`: the composable resolves block's canvas through its own WP
 *           7.0 probe, and classic's marks are reached through `window.tinymce.editors ->
 *           getDoc()` instead, so pointing that path at this method would move classic's
 *           copy/keyup listeners and stylesheet into TinyMCE's document for no gain.
 *           Answer the MAIN document, not null, when the editor's own document does not
 *           exist yet — a fallback, not the right answer, so nothing may cache it.
 * @property {function(): Window}                        getWindow    The window matching
 *           getDocument(). Same gating, same fallback. Used for the copy/keyup listeners and
 *           as the scroll target when the editor has no scrollable ancestor of its own.
 *           NOTE all four adapters answer these — block through `utils/editor.js`'s
 *           `getEditorDocument`/`getEditorWindow`, classic, and both Elementor ones — and every
 *           one of them can hand back another realm's object: block's once the canvas is iframed
 *           (WP 6.3+), classic's once TinyMCE has booted `#content_ifr`, and Elementor's whenever
 *           the preview exists. Each also falls back to the MAIN document before its own is up, so
 *           the realm is not knowable from the call site. A caller must therefore never
 *           `instanceof` what comes back — this window's `Document`/`Window` constructors do not
 *           match another realm's. Compare `nodeType` or duck-type the method you need.
 *           (Only the two Elementor answers are ever consulted, per the gating above.)
 * @property {function(): ContentUnit[]}                 getContentUnits  The editor's text, cut
 *           into the units every other coordinate in this contract is expressed against.
 *           REQUIRED FOR A CONTRACT PAINTER, AND ITS ABSENCE FAILS SILENTLY: it is the ONLY
 *           mark source that reaches `decorate`. The highlighter's contract arm builds the whole
 *           set by looping this ({@see TruSeoHighlighter.js:highlightAdapterEditor}), so an
 *           adapter declaring `paintsViaContract` and `decorate` but not this loops over `[]`,
 *           hands `decorate` nothing, and leaves the highlight toggle visible, clickable and
 *           inert with zero console output — the failure `paintsViaContract`'s explicit opt-in
 *           exists to prevent.
 *           Its second consumer is `getContent()` in `page-builders/elementor/helpers.js`, which
 *           reads it off the RESOLVED adapter to build the analysed content string. Reading it
 *           off a concrete adapter is the bug that pairing exists to prevent — the V4 experiment
 *           is a site-level fact, so an Elementor site with it on resolves every page, legacy
 *           ones included, to the V4 adapter.
 * @property {function(Array): void}                     decorate  Full store mark set,
 *           document-scoped — the grouping belongs to each adapter, not the contract.
 *           A builder adapter groups by `mark.unitId`, so a mark it is expected to paint has to
 *           carry one; the block and classic paints identify a mark's owner by `mark.block` /
 *           `mark.parent` instead and never reach this method. Must refuse any unit declaring
 *           `writable: false` (see ContentUnit above), must skip the units an editor currently
 *           has open (see `isSuspended()`'s NOTE), and must be safe to call repeatedly:
 *           Elementor V4 repaints on every render event, including no-op ones.
 *           Reached only behind `paintsViaContract` — declaring it alone changes nothing.
 * @property {function(): void}                          clearDecorations  Undoes decorate's
 *           paint. Same `paintsViaContract` gate: called before each repaint and on the
 *           analyzers-all-off bail, so the canvas is left clean rather than frozen.
 * @property {function(Function): Function}              onInvalidated   Returns an
 *           unsubscribe. Fires on CONTENT change, distinct from onSuspensionChange. Delivered
 *           undebounced, possibly once per element; the composable debounces it. It must be the
 *           editor's own signal, since a contract painter gets no `observeEditor()` observer.
 *           Same `paintsViaContract` gate.
 * @property {function(): string[]}                      getPartialAnalysisElements  Optional.
 *           Display names of the elements on the page whose text this view can't fully analyse,
 *           in page order. The TruSEO panel lists them in a note
 *           ({@see post-settings/views/partials/general/tru-seo/PageBuilderNotice.vue}), so
 *           name an element only when another view really does analyse that text.
 * @property {function(): ?string}                       getFullViewUrl  Optional. Where the
 *           same post opens in a view that shows and analyses all of its text, or null when
 *           this view is that one or the user can't open it. The panels link to it from the
 *           note above and from issues found in `hiddenInView` units.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * NOT WIRED — documented shape, no caller through this contract today
 *
 * Nothing below is reached via `resolveAdapter()`.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * @property {function(): boolean}                       isReady   NOT WIRED. Can the editor be
 *           used right now? Distinct from isActive, which answers page ownership and is
 *           memoised; readiness is per-call, because an editor whose DOM boots after the asking
 *           app mounts (TinyMCE, an Elementor V4 preview iframe) is owned long before it is
 *           usable. Kept deliberately as the readiness channel for adapters that need one —
 *           but nothing consults it today, so implementing it neither enables nor gates
 *           anything on its own.
 * @property {function(string): void}                    focus  NOT WIRED. Scrolls the unit into view.
 */

export {}