import {
	useAiStore,
	useOptionsStore,
	usePostEditorStore,
	useSpellCheckerDictionaryStore
} from '@/vue/stores'

import { defineStore } from 'pinia'
import { merge } from 'lodash-es'
import { markRaw } from 'vue'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { cyrb53 } from '@/app/tru-seo/helpers/hash'

import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'
import {
	getSharedWorker,
	requestAddSafeWord,
	requestAnalysisRefresh,
	requestAnalysisRun,
	requestSpellingCheck,
	requestSpellingSuggestions
} from '@/vue/plugins/tru-seo/spellingSuggestions'
import { applyOptimizedBlocks, applySpellingCorrections, gatherSpellingTargets, getPostContentBlocks, isOptimizeSupported } from '@/vue/plugins/tru-seo/optimize-post/editorAdapter'
import { isBadResult } from '@/app/tru-seo/scoring/interpreters'
import { getAssessmentDescription } from '@/vue/plugins/tru-seo/helpers/assessmentColors'

import {
	STORE_NAME as HIGHLIGHT_STORE_NAME
} from '@/vue/plugins/tru-seo/highlighter/wpDataStore'

import {
	clearTinyMceAnnotations
} from '@/vue/plugins/tru-seo/highlighter/tinymce'
import { dispatchBlockHighlights, recomputeNativeBlockMarkRanges } from '@/vue/plugins/tru-seo/highlighter/blockEditor'
import { toHighlightTexts } from '@/vue/plugins/tru-seo/highlighter/sentences'
import { useTags } from '@/vue/composables/Tags'
import { getFeatureCost } from '@/vue/composables/AiContent'
import { useHeadlineAnalyzer } from '@/vue/composables/HeadlineAnalyzer'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

// Analyzers that support AI-backed suggestions via the remote AI Generator API.
// Note: 'spellingChecker' is intentionally excluded — it uses a local Hunspell worker.
// 'singleH1', 'textAlignment' and 'subheadingsTooLong' are excluded — structural fixes
// (add a subheading, change alignment) have no text rewrite for the LLM to return.
export const AI_SUGGESTABLE_ANALYZERS = [
	'passiveVoice',
	'textSentenceLength',
	'textParagraphTooLong',
	'sentenceBeginnings',
	'textTransitionWords',
	'wordComplexity',
	'textCompetingLinks',
	'keyphraseDistribution',
	'keyphraseDensity'
]

// Maps each AI-suggestable analyzer to the scope the remote service expects.
export const ANALYZER_SCOPE_MAP = {
	passiveVoice          : 'sentence',
	textSentenceLength    : 'sentence',
	textParagraphTooLong  : 'paragraph',
	sentenceBeginnings    : 'sentence',
	textTransitionWords   : 'sentence',
	wordComplexity        : 'word',
	textCompetingLinks    : 'anchor',
	keyphraseDistribution : 'sentence',
	keyphraseDensity      : 'sentence'
}

// Max issues sent per suggestion request. One request is one LLM call at a flat
// credit cost, so this only bounds prompt size / latency — the remaining marks of
// the same type are fetched by the next request. Coupled with the PHP forward timeout.
export const AI_SUGGEST_BATCH_MAX = 25

const AI_CONTEXT_MAX_CHARS = 1500

// Returns a bounded local context for the given mark: typically the containing
// paragraph's text. Never the full post. Caps at AI_CONTEXT_MAX_CHARS.
const getContextForMark = (mark) => {
	if (!mark) {
		return ''
	}

	const parentText = mark.parent?.textContent
		? normalizeWhitespaces(mark.parent.textContent).trim()
		: ''

	if (!parentText || parentText === mark.sentence) {
		return ''
	}

	if (parentText.length <= AI_CONTEXT_MAX_CHARS) {
		return parentText
	}

	// Keep the section of context around the target text when possible.
	const targetIndex = parentText.indexOf(mark.sentence)
	if (-1 === targetIndex) {
		return parentText.slice(0, AI_CONTEXT_MAX_CHARS) + '…'
	}

	const half  = Math.floor(AI_CONTEXT_MAX_CHARS / 2)
	const start = Math.max(0, targetIndex - half)
	const end   = Math.min(parentText.length, start + AI_CONTEXT_MAX_CHARS)

	let slice = parentText.slice(start, end)
	if (0 < start) {
		slice = '…' + slice
	}
	if (end < parentText.length) {
		slice = slice + '…'
	}

	return slice
}

// Resolves the density direction ("over" or "under") for the keyphraseDensity
// analyzer based on the stored assessment score.
const getKeyphraseDensityDirection = (truseo) => {
	const result = truseo?.keyphraseDensity
	if (!result) {
		return 'under'
	}

	// Scores -10 (overMaximum) and -50 (wayOverMaximum) mean the keyphrase
	// appears too often. Anything else (4 = underMinimum) means too rarely.
	return -10 >= result.score ? 'over' : 'under'
}

// Builds analyzer-specific metadata for the remote AI Generator service.
const buildAiMetadata = (mark) => {
	const postEditorStore = usePostEditorStore()
	const truseo = postEditorStore.currentPost?.truseo?.general || {}
	const analyzer = mark?.analyzer

	if ('keyphraseDensity' === analyzer) {
		return {
			direction : getKeyphraseDensityDirection(truseo)
		}
	}

	return {}
}

// Build the batch payload the remote AI Generator API expects for a set of marks
// of the same analyzer. Shared context lives at the top level; each mark becomes
// one entry in `issues`, correlated back to its cache by `id`.
const buildAiBatchPayload = (marks, { rephrase = false } = {}) => {
	const postEditorStore = usePostEditorStore()
	const optionsStore    = useOptionsStore()

	const analyzer  = marks[0]?.analyzer
	const focusKeyphrase = postEditorStore.truseoData?.focusKeyword || ''
	const focusSynonyms  = postEditorStore.truseoData?.focusKeywordSynonyms || ''
	const locale    = postEditorStore.currentPost?.truseo_locale || window.aioseo?.user?.locale || 'en_US'
	const tone      = optionsStore.options?.aiContent?.tone || ''
	const audience  = optionsStore.options?.aiContent?.audience || ''

	return {
		analyzer,
		scope   : ANALYZER_SCOPE_MAP[analyzer] || '',
		focusKeyphrase,
		focusSynonyms,
		postId  : postEditorStore.currentPost?.id || 0,
		locale,
		options : { tone, audience },
		rephrase,
		issues  : marks.map(mark => ({
			id          : mark.id,
			targetText  : mark?.sentence || '',
			contextText : getContextForMark(mark),
			metadata    : buildAiMetadata(mark)
		}))
	}
}

// Analyzers whose suggestion depends on the neighbouring sentences, not just the
// flagged text. Their cache key folds in the surrounding context so a fresh request
// for a genuinely changed paragraph is stored under a new key. Lookup and pruning
// still match on analyzer + flagged text ({@see findAiCacheEntry}, pruneSuggestionsCache),
// so fixing one sentence in a paragraph doesn't drop the still-valid suggestions of the
// other flagged sentences it happens to rekey.
const CONTEXT_DEPENDENT_ANALYZERS = [ 'sentenceBeginnings', 'textTransitionWords' ]

// Stable, fixed-size cache key for a mark's AI suggestions. Keyed by everything
// that shapes the suggestion — analyzer, scope, the flagged text, and the request
// context (keyphrase/synonyms/tone/audience/locale/metadata), plus the surrounding
// context for context-dependent analyzers — so suggestions are reused across
// re-analyses while their inputs are unchanged, and regenerated when any change.
// Independent of the mark's ephemeral random id (regenerated on every re-analysis).
const getMarkAiCacheKey = (mark) => {
	if (!mark?.analyzer || !mark?.id) {
		return null
	}

	const postEditorStore = usePostEditorStore()
	const optionsStore    = useOptionsStore()

	const context = [
		mark.analyzer,
		ANALYZER_SCOPE_MAP[mark.analyzer] || '',
		mark.sentence || '',
		postEditorStore.truseoData?.focusKeyword || '',
		postEditorStore.truseoData?.focusKeywordSynonyms || '',
		optionsStore.options?.aiContent?.tone || '',
		optionsStore.options?.aiContent?.audience || '',
		postEditorStore.currentPost?.truseo_locale || window.aioseo?.user?.locale || 'en_US',
		JSON.stringify(buildAiMetadata(mark)),
		CONTEXT_DEPENDENT_ANALYZERS.includes(mark.analyzer) ? getContextForMark(mark) : ''
	].join('\x1f')

	return cyrb53(context).toString(36)
}

// The block a mark lives in ('' for the classic editor, which has no blocks). Stable
// across intra-block edits — fixing one sentence keeps its block's id — so it tells two
// occurrences of an identical sentence apart without churning like the surrounding text.
const markBlockId = (mark) => mark?.block?.clientId || ''

// Content identity of a cached AI suggestion — analyzer + flagged text + block. Stable
// across the paragraph-context churn folded into the cache key, so a suggestion stays
// matchable to its mark even after a sibling fix rekeys it. The block keeps two identical
// sentences in different blocks distinct, so fixing one doesn't collapse the other's entry.
const aiCacheContentId = (analyzer, sentence, blockId) => `${analyzer || ''}\x1f${sentence || ''}\x1f${blockId || ''}`

// Resolves a mark's cached AI suggestion entry. Tries the exact (context-inclusive)
// key first, then falls back to any entry for the same analyzer + flagged text + block:
// fixing one sibling in a paragraph rekeys the other context-dependent marks, but their
// already generated suggestions are still valid, so they must stay reachable under the new
// key. Cache entries store their block id, so identical sentences in different blocks
// resolve to their own entry rather than sharing one.
const findAiCacheEntry = (cache, mark) => {
	if (!mark?.analyzer) {
		return null
	}

	const key = getMarkAiCacheKey(mark)
	if (key && cache[key]) {
		return cache[key]
	}

	const contentId = aiCacheContentId(mark.analyzer, mark.sentence, markBlockId(mark))

	return Object.values(cache).find(entry => entry && aiCacheContentId(entry.analyzer, entry.sentence, entry.block) === contentId) || null
}

// Identity of a mark for sibling navigation. Spelling paints one mark per
// occurrence of a word; since a fix replaces every occurrence at once, all
// occurrences of a word share one key so navigation treats them as one issue.
const markSiblingKey = (mark) => {
	if (!mark) {
		return null
	}

	return 'spellingChecker' === mark.analyzer
		? `w:${(mark.sentence || '').toLowerCase()}`
		: `id:${mark.id}`
}

// A key that identifies a mark's issue stably across a re-analysis rebuild (which
// assigns fresh random ids). Spelling keys by word text; other analyzers key by
// analyzer + block + flagged text, so the advance flow can re-find the next issue
// after the fix. Deliberately separate from markSiblingKey (the pager's identity).
const advanceMatchKey = (mark) => {
	if (!mark) {
		return null
	}

	if ('spellingChecker' === mark.analyzer) {
		return `w:${(mark.sentence || '').toLowerCase()}`
	}

	return `a:${mark.analyzer}|b:${mark.block?.clientId || ''}|s:${mark.sentence || ''}`
}

// Painted marks of the same analyzer as `mark`, in reading order. For spelling,
// repeat occurrences of a word collapse to its first (reading-order) mark so the
// pager steps between distinct words. Shared by the sibling getter and the
// after-fix advance logic so both order issues identically.
const orderedSiblings = (marks, mark) => {
	if (!mark) {
		return []
	}

	const ordered = marks
		.filter(hm => hm.analyzer === mark.analyzer && hm.node)
		.sort((a, b) => {
			if (a.node === b.node) {
				return 0
			}

			return (a.node.compareDocumentPosition(b.node) & Node.DOCUMENT_POSITION_FOLLOWING) ? -1 : 1
		})

	// Non-spelling analyzers have one mark per issue already.
	if ('spellingChecker' !== mark.analyzer) {
		return ordered
	}

	const seen = new Set()

	return ordered.filter(hm => {
		const key = markSiblingKey(hm)
		if (seen.has(key)) {
			return false
		}
		seen.add(key)

		return true
	})
}

// Flattens an analysis run's results into a Map of failing checks keyed by their
// identifier, value = title. Used to diff which checks the optimize pass resolved.
const collectBadChecks = (analysis) => {
	const bad = new Map()
	if (!analysis?.allResults?.forEach) {
		return bad
	}

	analysis.allResults.forEach((result, id) => {
		if (result?.title && isBadResult(result.score)) {
			bad.set(id, result.title)
		}
	})

	return bad
}

// Flagged spelling words in an analysis run, occurrence-level (one entry per
// highlighted word, duplicates included) — matches the Spelling tab badge count.
const spellingFlaggedWords = (analysis) =>
	(analysis?.allResults?.get?.('spellingChecker')?.highlightSentences || [])
		.map(word => (word || '').trim())
		.filter(Boolean)

// Whitespace-delimited word count across the body content blocks.
const countContentWords = (blocks) => {
	const text = (blocks || []).map(block => block?.text || '').join(' ').trim()
	if (!text) {
		return 0
	}

	const words = text.match(/\S+/g)

	return words ? words.length : 0
}

// Timers backing the advance-after-fix flow. `advanceReady` is normally flipped by
// the first re-analysis rebuild's clearAll, but rapid successive fixes can coalesce
// the debounced rebuild away (its final fire sees no net change, so reset() is
// skipped), leaving the advance armed-but-never-ready → the popover stalls empty.
// The ready timer flips it anyway against the current (already prune-updated) marks;
// the expiry timer drops a never-resolved advance so a later unrelated rebuild can't
// reopen the popover on it. Module-scoped — there is a single store instance.
//
// `advanceResume` keeps a resolved advance for as long as its popover stays on that issue. A fix
// is followed by more than one rebuild — a second highlighter instance, or the debounced content
// analysis once a builder's canvas finishes re-rendering — and each would otherwise drop the
// reopened popover. Untimed: how late that rebuild lands depends on the page.
let advanceReadyTimer = null,
	advanceExpiryTimer = null,
	advanceResume = null
const ADVANCE_READY_FALLBACK_MS = 600
const ADVANCE_EXPIRY_MS         = 4000

// Spelling-suggestion requests in flight, by word. The analysis worker answers one message at a
// time, so a duplicate request queues ahead of the re-analysis that follows a fix. Non-reactive on
// purpose: the Spelling tab's prefetch effect must only re-run on cache writes.
const spellingSuggestionRequests = new Map()

const clearAdvanceTimers = () => {
	if (advanceReadyTimer) {
		clearTimeout(advanceReadyTimer)
		advanceReadyTimer = null
	}
	if (advanceExpiryTimer) {
		clearTimeout(advanceExpiryTimer)
		advanceExpiryTimer = null
	}
}

// Read from the localized payload rather than the post-editor store: the state initializer runs
// before any store can be used, and the value never changes within a page load.
const isTermEditor = () => 'term' === window.aioseo?.currentPost?.context

export const useTruSeoHighlighterStore = defineStore('TruSeoHighlighterStore', {
	state : () => ({
		allowHighlighting            : true,
		// A term's description is a plain <textarea>, which cannot host the <mark> elements the
		// highlighter paints. Offering the toggle there would be a control that visibly does nothing,
		// so the subsystem stays off — the Spelling tab reads and rewrites the textarea directly.
		enabled                      : !isTermEditor(),
		highlightingEnabled          : !isTermEditor() && (window.aioseo?.options?.advanced?.highlighter ?? true),
		highlightAnalyzers           : [],
		rememberedHighlightAnalyzers : null,
		deselectedHighlightAnalyzers : [],
		highlightMarks               : [],
		highlightPopover             : {},
		hoveredMarkId                : null,
		source                       : 'aioseo-tru-seo-highlighter',
		markNavigationSeq            : 0,
		popoverHideSeq               : 0,
		advanceToKeys                : [],
		advanceReady                 : false,
		suggestionsCache             : {},
		suggestionsLoadingFor        : null,
		// Set for the length of a language switch: until the new locale's dictionary is loaded,
		// the worker answers suggestion requests from the previous one.
		suggestionsPaused            : false,
		languageSwitchId             : 0,
		aiSuggestionsCache           : {},
		aiSuggestionsLoadingKeys     : {},
		aiSuggestionsError           : null,
		addToSafeWordsLoadingFor     : [],
		addToSafeWordsError          : null,
		safeWordsModalOpen           : false,
		editorModeUnsubscribe        : null,
		// Which composable instance holds `editorModeUnsubscribe`. It lives HERE, beside the
		// handle, because the slot is shared between the metabox and sidebar instances and a
		// per-instance mirror cannot be kept honest: the instance that takes the slot over has
		// no way to clear the claim held in the closure of the one it took it from.
		editorModeOwner              : null,
		editorModeSuspended          : null,
		awaitingFreshHighlights      : false,
		optimizePhase                : 'idle',
		optimizeResult               : null,
		optimizeOriginalBlocks       : null,
		optimizePostError            : null
	}),
	getters : {
		// Actively running; drives the button's loading/disabled state. The 'done'
		// phase re-enables the button while the result modal stays open.
		optimizingPost () {
			return [ 'optimizing', 'spelling' ].includes(this.optimizePhase)
		},
		// Whether a run will make the spelling call, which the service bills on top of
		// the rewrite. It only fires for a post that already has misspellings, so a
		// clean post is never charged for it.
		optimizeIncludesSpelling () {
			const postEditorStore = usePostEditorStore()
			const flagged         = postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker?.highlightSentences || []

			return 0 < flagged.length && this.shouldRunSpelling()
		},
		// Credits one Optimize run costs. The rewrite and the spelling correction are
		// two separately billed service calls, so the quote has to cover both.
		optimizeCreditCost () {
			return getFeatureCost('truseoOptimizePost') +
				(this.optimizeIncludesSpelling ? getFeatureCost('truseoSpelling') : 0)
		},
		// `highlightingEnabled` is only the user's preference and stays true where `enabled` is
		// forced off (a builder without its own adapter), so live highlighting needs both.
		highlightingActive () {
			return this.enabled && this.highlightingEnabled
		},
		availableHighlightAnalyzers () {
			// Merge readability + spelling groups: both surfaces (readability tab
			// and spelling tab) offer highlighting, and `spellingChecker` now lives
			// in the `spelling` group after the score split.
			const postEditorStore = usePostEditorStore()
			const general         = postEditorStore.truseoData?.truseo?.general || {}
			const highlightable   = { ...general.readability, ...general.spelling }

			return Object.entries(highlightable)
				.filter(([ , assessment ]) => {
					return assessment?.title &&
						isBadResult(assessment.score) &&
						assessment.highlightSentences?.length
				})
				.map(([ key ]) => key)
		},
		allHighlightSentences () {
			const postEditorStore = usePostEditorStore()
			const highlightData = postEditorStore.currentPost?.truseo?.general || {}
			const result = {}

			for (const analyzer of this.highlightAnalyzers) {
				const assessment = highlightData[analyzer]
				if (!assessment?.highlightSentences?.length) {
					continue
				}

				// Assessments keep returning marks for a check they now pass — passive
				// voice marks every passive sentence even at a green percentage. The
				// analysis lists only show failing checks, so painting those would leave
				// a highlight with no row to explain it and no checkbox to switch it off.
				// Mirrors the gate in {@see availableHighlightAnalyzers}.
				if (!isBadResult(assessment.score)) {
					continue
				}

				const sentences = toHighlightTexts(assessment.highlightSentences)

				if (sentences.length) {
					result[analyzer] = sentences
				}
			}

			return result
		},
		highlightSentences () {
			const all = this.allHighlightSentences
			const flat = Object.values(all).flat()

			return flat.length ? flat : null
		},
		highlightAnalyzerHasError () {
			const postEditorStore = usePostEditorStore()
			const truseo = postEditorStore.currentPost?.truseo?.general || {}

			return this.highlightAnalyzers.some(analyzer => !!(truseo[analyzer]?.error))
		},
		activeMark () {
			if (!this.highlightMarks.length) {
				return null
			}

			return this.highlightMarks.find(hm => hm.active && hm.node)
		},
		hoveredMark () {
			if (!this.hoveredMarkId) {
				return null
			}

			return this.highlightMarks.find(hm => hm.id === this.hoveredMarkId && hm.node) || null
		},
		hoveredMarkAssessmentText () {
			const mark = this.hoveredMark
			if (!mark) {
				return ''
			}

			// Plain-language explanation of the check, not the post's live stats —
			// the dynamic check text (e.g. "13.8% of your sentences…") is meaningless
			// on a single highlighted instance.
			return getAssessmentDescription(mark.analyzer)
		},
		hoveredMarkSuggestions () {
			const word = this.hoveredMark?.sentence
			if (!word || !this.suggestionsCache[word]) {
				return []
			}

			return this.suggestionsCache[word]
		},
		hoveredMarkHasCachedSuggestions () {
			const word = this.hoveredMark?.sentence
			return !!word && word in this.suggestionsCache
		},
		suggestionsLoading () {
			const word = this.hoveredMark?.sentence
			return !!word && this.suggestionsLoadingFor === word
		},
		hoveredMarkAiCacheKey () {
			return getMarkAiCacheKey(this.hoveredMark)
		},
		hoveredMarkAiSuggestions () {
			const entry = findAiCacheEntry(this.aiSuggestionsCache, this.hoveredMark)

			return entry?.suggestions || []
		},
		hoveredMarkHasCachedAiSuggestions () {
			return !!findAiCacheEntry(this.aiSuggestionsCache, this.hoveredMark)
		},
		aiSuggestionsLoading () {
			const key = this.hoveredMarkAiCacheKey
			return !!key && !!this.aiSuggestionsLoadingKeys[key]
		},
		// Whether any suggestion request is running, for any analyzer.
		aiSuggestionsBusy () {
			return 0 < Object.keys(this.aiSuggestionsLoadingKeys).length
		},
		// Painted marks of the same type as the open one, in reading order, so the
		// popover's prev/next pager can step between issues of that analyzer.
		hoveredMarkSiblings () {
			return orderedSiblings(this.highlightMarks, this.hoveredMark)
		},
		hoveredMarkPosition () {
			const siblings = this.hoveredMarkSiblings
			if (!siblings.length) {
				return { index: -1, total: 0 }
			}

			const key = markSiblingKey(this.hoveredMark)

			return { index: siblings.findIndex(hm => markSiblingKey(hm) === key), total: siblings.length }
		},
		// While an advance-after-fix is pending, the id of the target issue's mark
		// once the re-analysis rebuild has painted it (given it a DOM node), else
		// null. `advanceToKeys` is the fixed issue's remaining siblings in preference
		// order (next-in-reading-order first, then earlier ones); the first one that
		// repaints wins. A fix can resolve more than the issue you clicked — the
		// immediate next sibling may vanish too — so we fall through to the next
		// surviving issue instead of stalling on a target that's no longer flagged.
		// Gated on `advanceReady` — set only once the fix's teardown has run — so it
		// never matches the stale pre-rebuild marks (which still hold the old issues).
		// Recomputes as rebuilt marks get their nodes, so the composable can re-open
		// the popover on the target the moment it's paintable.
		pendingAdvanceMark () {
			if (!this.advanceToKeys.length || !this.advanceReady) {
				return null
			}

			for (const key of this.advanceToKeys) {
				const painted = this.highlightMarks.find(
					hm => hm.node && advanceMatchKey(hm) === key
				)

				if (painted) {
					return painted.id
				}
			}

			return null
		},
		hasPreviousHoveredSibling () {
			return 0 < this.hoveredMarkPosition.index
		},
		hasNextHoveredSibling () {
			const { index, total } = this.hoveredMarkPosition

			return -1 !== index && index < total - 1
		}
	},
	actions : {
		clearHighlightPopover () {
			if (this.highlightPopover?.observer) {
				this.highlightPopover.observer.disconnect()
			}

			if (this.highlightPopover?.node) {
				this.highlightPopover.node.remove()
			}

			if (this.highlightPopover?.app) {
				this.highlightPopover.app.unmount()
			}

			this.highlightPopover = {}
		},
		clearAnnotations () {
			// Clear custom format type highlight data (block editor).
			if (window?.wp?.data?.dispatch) {
				const dispatch = window.wp.data.dispatch(HIGHLIGHT_STORE_NAME)
				if (dispatch) {
					dispatch.clearAll()
				}
			}

			// Clear TinyMCE annotations (classic editor and freeform blocks).
			clearTinyMceAnnotations()

			this.highlightMarks = []
		},
		clearAll () {
			// The first rebuild after arming an advance flips it ready, so pendingAdvanceMark
			// resolves against fresh marks. Skipped rebuilds and never-resolved advances are
			// covered by armAdvance's timers, so clearAll doesn't drop the advance itself.
			if (this.advanceToKeys.length && !this.advanceReady) {
				this.advanceReady = true
			}

			// A rebuild while the popover is still on the issue an advance resolved to re-arms it.
			if (!this.advanceToKeys.length && advanceResume && advanceResume.key === advanceMatchKey(this.hoveredMark)) {
				this.advanceToKeys = [ advanceResume.key ]
				this.advanceReady  = true

				advanceExpiryTimer = setTimeout(() => {
					advanceExpiryTimer = null
					this.cancelAdvance()
				}, ADVANCE_EXPIRY_MS)
			}

			advanceResume = null

			this.hoveredMarkId = null
			this.suggestionsLoadingFor = null
			// Not the AI loading keys: they are content-based, so they still name the marks a rebuild
			// repaints, and the request clears them itself. Dropping them here let a second request
			// start while the first was still running.
			this.aiSuggestionsError = null
			this.pruneSuggestionsCache()
			this.clearHighlightPopover()
			this.clearAnnotations()
		},
		// Moves the open popover to the previous/next issue of the same type by
		// pointing hoveredMarkId at the target sibling. The composable watches
		// hoveredMarkId to scroll it into view and re-anchor the popover.
		navigateHoveredMark (direction) {
			const siblings = this.hoveredMarkSiblings
			if (!siblings.length) {
				return
			}

			const key          = markSiblingKey(this.hoveredMark)
			const currentIndex = siblings.findIndex(hm => markSiblingKey(hm) === key)
			if (-1 === currentIndex) {
				return
			}

			const target = siblings[currentIndex + (0 > direction ? -1 : 1)]
			if (!target) {
				return
			}

			this.hoveredMarkId = target.id
			// Signals the composable to scroll the new mark into view and re-anchor
			// the popover — a plain open (clicking a mark) must not trigger a scroll.
			this.markNavigationSeq++
		},
		// The issues to fall through once `fixedMark` is resolved, as `advanceMatchKey`s
		// (stable across the re-analysis rebuild, which assigns fresh ids) in preference
		// order: every later sibling in reading order first, then the earlier ones
		// (nearest first). Empty when it was the only issue. A single fix can clear more
		// than the clicked issue — the immediate next sibling may vanish too — so we hand
		// `pendingAdvanceMark` the whole ordered fallback list rather than one target, and
		// it advances to the first that's still flagged after the rebuild.
		nextIssueAdvanceKeys (fixedMark) {
			const siblings = orderedSiblings(this.highlightMarks, fixedMark)
			if (2 > siblings.length) {
				return []
			}

			const key   = markSiblingKey(fixedMark)
			const index = siblings.findIndex(hm => markSiblingKey(hm) === key)
			if (-1 === index) {
				return []
			}

			// Later siblings in reading order, then earlier ones nearest-first.
			const forward  = siblings.slice(index + 1)
			const backward = siblings.slice(0, index).reverse()

			return [ ...forward, ...backward ].map(advanceMatchKey)
		},
		// Arms the advance-to-next-issue flow before a fix. The fix's re-analysis tears
		// down and rebuilds every mark, so we can't hold a mark reference across it —
		// instead we remember the target issues' (stable) keys and let
		// `pendingAdvanceMark` resolve the first surviving one once the rebuild repaints.
		// Called only when there IS a next issue; otherwise the caller closes as before.
		armAdvance (nextKeys) {
			clearAdvanceTimers()
			advanceResume = null
			this.advanceToKeys = Array.isArray(nextKeys) ? nextKeys : (nextKeys ? [ nextKeys ] : [])
			this.advanceReady = false

			if (!this.advanceToKeys.length) {
				return
			}

			// Fallback: if no rebuild's clearAll flips advanceReady shortly (the
			// debounced re-analysis can coalesce away under rapid fixes), flip it here
			// so the advance resolves against the current stable marks instead of
			// hanging. Fires after the debounce window so a real rebuild wins first.
			advanceReadyTimer = setTimeout(() => {
				advanceReadyTimer = null
				if (this.advanceToKeys.length && !this.advanceReady) {
					this.advanceReady = true
				}
			}, ADVANCE_READY_FALLBACK_MS)

			// Drop an advance that never resolves (e.g. the fix cleared every issue of
			// its type), so a later unrelated rebuild can't reopen the popover on it.
			advanceExpiryTimer = setTimeout(() => {
				advanceExpiryTimer = null
				this.cancelAdvance()
			}, ADVANCE_EXPIRY_MS)
		},
		// Completes a pending advance: points the popover at the repainted target
		// mark and signals the composable to (re)open and re-anchor it.
		resolveAdvance (markId) {
			const mark = this.highlightMarks.find(hm => hm.id === markId)

			clearAdvanceTimers()
			advanceResume = mark ? { key: advanceMatchKey(mark) } : null
			this.advanceToKeys = []
			this.advanceReady = false
			this.hoveredMarkId = markId
			this.markNavigationSeq++
		},
		// Abandons a pending advance (e.g. the fix failed, or the user closed the
		// popover before the rebuild finished), so it doesn't reopen unexpectedly.
		cancelAdvance () {
			clearAdvanceTimers()
			advanceResume = null
			this.advanceToKeys = []
			this.advanceReady = false
		},
		// Signals the composable to hide the popover at once. Used on accepting a
		// fix so it vanishes cleanly instead of lingering (empty) while the rebuild
		// runs; the advance then reopens a fresh popover on the next issue.
		requestPopoverHide () {
			this.popoverHideSeq++
		},
		// Optimistically drops the just-fixed issue's marks and atomically repaints
		// the rest (block editor), so its highlight disappears immediately and the
		// others don't flash off while the debounced re-analysis rebuild runs.
		pruneMarksForIssue (fixedMark) {
			if (!fixedMark) {
				return
			}

			// Spelling replaces every occurrence of the word at once, so drop them all.
			// Every other analyzer fixes a single sentence in a single block, so scope the
			// removal to that block — an identical sentence in another block is untouched
			// and must stay flagged (and keep its own AI suggestion).
			const sameBlockOnly = 'spellingChecker' !== fixedMark.analyzer
			const fixedBlockId  = markBlockId(fixedMark)

			this.highlightMarks = this.highlightMarks.filter(hm => {
				if (hm.analyzer !== fixedMark.analyzer || hm.sentence !== fixedMark.sentence) {
					return true
				}

				return sameBlockOnly && markBlockId(hm) !== fixedBlockId
			})

			// The fix shifted every character after it, so the surviving marks in
			// that block now hold stale offsets. Re-anchor them to the live content
			// before dispatch — otherwise the RichText format paints partial,
			// unclickable highlights until the debounced re-analysis lands.
			recomputeNativeBlockMarkRanges(this.highlightMarks)

			dispatchBlockHighlights(this.highlightMarks)
		},
		// Cleanup after a fix is applied from the popover or panel. Removes only
		// the marks for the just-fixed issue and closes the popover.
		dismissAfterFix (appliedMark) {
			clearAdvanceTimers()
			advanceResume = null
			this.advanceToKeys = []
			this.advanceReady = false
			this.hoveredMarkId = null
			this.suggestionsLoadingFor = null
			this.aiSuggestionsLoadingFor = null
			this.aiSuggestionsError = null
			this.clearHighlightPopover()
			this.pruneMarksForIssue(appliedMark)
			this.pruneSuggestionsCache()
		},
		pruneSuggestionsCache () {
			// Marks are transiently empty between clearAnnotations() and the re-highlight
			// rebuild (e.g. reset() right after applying a suggestion). Pruning against an
			// empty mark set would wipe every cached suggestion — including untouched
			// sentences — so skip until marks reflect the current content again.
			if (!this.highlightMarks.length) {
				return
			}

			// Keep every word the Spelling tab still lists, not only the painted ones: a
			// listed word can have no mark (e.g. text a page builder doesn't let us paint),
			// and dropping its suggestions blanks its row and refetches the whole list.
			const postEditorStore = usePostEditorStore()
			const listedWords     = postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker?.highlightSentences || []
			const activeWords     = new Set([
				...listedWords.map(word => (word || '').trim()),
				...this.highlightMarks
					.filter(hm => 'spellingChecker' === hm.analyzer)
					.map(hm => hm.sentence)
			])

			for (const word of Object.keys(this.suggestionsCache)) {
				if (!activeWords.has(word)) {
					delete this.suggestionsCache[word]
				}
			}

			// Retain by content (analyzer + flagged text + block), not by exact cache key:
			// the key folds in the surrounding paragraph for context-dependent analyzers, so
			// fixing one sentence rekeys the still-flagged siblings in that paragraph. Matching
			// on their (unchanged) analyzer + text + block keeps their suggestions alive while
			// still telling identical sentences in different blocks apart.
			const activeAiContentIds = new Set(
				this.highlightMarks
					.filter(hm => AI_SUGGESTABLE_ANALYZERS.includes(hm.analyzer))
					.map(hm => aiCacheContentId(hm.analyzer, hm.sentence, markBlockId(hm)))
			)

			for (const [ key, entry ] of Object.entries(this.aiSuggestionsCache)) {
				if (!entry || !activeAiContentIds.has(aiCacheContentId(entry.analyzer, entry.sentence, entry.block))) {
					delete this.aiSuggestionsCache[key]
				}
			}
		},
		// Drops every cached AI-suggestion entry for a mark's content (analyzer + text +
		// block), across any context key it may have been stored under.
		removeAiSuggestionsForMark (mark) {
			if (!mark?.analyzer) {
				return
			}

			const contentId = aiCacheContentId(mark.analyzer, mark.sentence, markBlockId(mark))
			for (const [ key, entry ] of Object.entries(this.aiSuggestionsCache)) {
				if (entry && aiCacheContentId(entry.analyzer, entry.sentence, entry.block) === contentId) {
					delete this.aiSuggestionsCache[key]
				}
			}
		},
		isSpellingWorkerReady () {
			return !!getSharedWorker()
		},
		async fetchSpellingSuggestions (word) {
			if (!word) {
				return
			}

			if (word in this.suggestionsCache || this.suggestionsPaused) {
				return
			}

			if (spellingSuggestionRequests.has(word)) {
				return spellingSuggestionRequests.get(word)
			}

			this.suggestionsLoadingFor = word

			const request = (async () => {
				const [ suggestions ] = await Promise.all([
					requestSpellingSuggestions(word),
					new Promise(resolve => setTimeout(resolve, 300))
				])

				// A request dropped by an invalidation or a language switch must not refill the cache.
				if (spellingSuggestionRequests.get(word) === request) {
					spellingSuggestionRequests.delete(word)
					this.suggestionsCache[word] = suggestions
				}

				// Clear loading state only if still loading for this word.
				if (this.suggestionsLoadingFor === word) {
					this.suggestionsLoadingFor = null
				}
			})()

			spellingSuggestionRequests.set(word, request)

			return request
		},
		async fetchAiSuggestionsForType (analyzer, { rephrase = false, marks = null } = {}) {
			// One request at a time: each one bills credits, and parallel ones raced to fill the same
			// popover.
			if (!analyzer || !AI_SUGGESTABLE_ANALYZERS.includes(analyzer) || this.aiSuggestionsBusy) {
				return
			}

			const explicitMarks = Array.isArray(marks) && marks.length ? marks : null

			// Regenerate targets the passed mark(s); a normal request batches every
			// uncached mark of this type so switching to a sibling is instant.
			let targetMarks = explicitMarks || this.highlightMarks.filter(hm =>
				hm.analyzer === analyzer &&
				hm.id &&
				hm.node &&
				!findAiCacheEntry(this.aiSuggestionsCache, hm)
			)

			if (!explicitMarks) {
				// Start the capped window at the issue the user opened. In plain document
				// order an issue past the cap is left out of the very request its own
				// button fired — so it gets no loading state, and the still-active button
				// invites a second, duplicate request. Wrapping around keeps its following
				// siblings, where the pager goes next, first in line for the prefetch.
				const openedIndex = targetMarks.findIndex(hm => hm.id === this.hoveredMarkId)
				if (0 < openedIndex) {
					targetMarks = [ ...targetMarks.slice(openedIndex), ...targetMarks.slice(0, openedIndex) ]
				}
			}

			if (rephrase) {
				// Drop every cached entry for the target text so it re-fetches fresh; a
				// prior sibling fix may have stored it under a now-stale context key.
				for (const mark of targetMarks) {
					this.removeAiSuggestionsForMark(mark)
				}
			}

			// Cap the batch; leftovers are fetched by the next request.
			targetMarks = targetMarks.slice(0, AI_SUGGEST_BATCH_MAX)

			if (!targetMarks.length) {
				return
			}

			const loadingKeys = targetMarks.map(mark => getMarkAiCacheKey(mark))
			const marksById = new Map(targetMarks.map(mark => [ String(mark.id), mark ]))

			this.aiSuggestionsError = null
			for (const key of loadingKeys) {
				this.aiSuggestionsLoadingKeys[key] = true
			}

			try {
				const payload  = buildAiBatchPayload(targetMarks, { rephrase })
				const response = await http.post(links.restUrl('ai/truseo/suggest')).send(payload)

				if (response.body?.success) {
					const results = Array.isArray(response.body.results) ? response.body.results : []
					for (const result of results) {
						// Map the returned id back to its mark, then cache under the
						// mark's content-based key (not the ephemeral id).
						const mark = result?.id ? marksById.get(String(result.id)) : null
						if (!mark) {
							continue
						}

						const suggestions = Array.isArray(result.suggestions) ? result.suggestions : []

						// Leave items the model couldn't fix uncached so they keep offering
						// "Suggest a fix" (retryable) instead of a dead "No suggestions" state.
						if (!suggestions.length) {
							continue
						}

						// Drop any prior entry for this text (e.g. an old-context key left
						// after a sibling fix) so a mark never carries two cache entries.
						this.removeAiSuggestionsForMark(mark)

						this.aiSuggestionsCache[getMarkAiCacheKey(mark)] = {
							analyzer : mark.analyzer,
							sentence : mark.sentence,
							block    : markBlockId(mark),
							suggestions
						}
					}

					if (response.body.aiOptions) {
						const optionsStore = useOptionsStore()
						optionsStore.internalOptions.internal.ai = merge(
							{},
							optionsStore.internalOptions.internal.ai,
							response.body.aiOptions
						)
					}
				} else {
					this.aiSuggestionsError = response.body?.message || __('Couldn\'t generate suggestions. Please try again.', td)
				}
			} catch (error) {
				const body = error?.response?.body
				if (body?.code && body.aiOptions) {
					const optionsStore = useOptionsStore()
					optionsStore.internalOptions.internal.ai = merge(
						{},
						optionsStore.internalOptions.internal.ai,
						body.aiOptions
					)
				}
				this.aiSuggestionsError = body?.message || __('Couldn\'t generate suggestions. Please try again.', td)
			} finally {
				for (const key of loadingKeys) {
					delete this.aiSuggestionsLoadingKeys[key]
				}
			}
		},
		/**
		 * Opens the optimize modal. Resumes the last completed result if one exists
		 * (so reopening after a close still shows it); otherwise shows the intro,
		 * where the user starts a run.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		openOptimizeModal () {
			this.optimizePostError = null
			// Resume the last completed result on reopen (e.g. after an accidental
			// backdrop close); only start fresh on the intro when there's none.
			this.optimizePhase = this.optimizeResult ? 'done' : 'intro'
		},

		/**
		 * Reverts a single optimized field back to its pre-optimize value from the
		 * done view, then re-runs analysis so the toolbar badge and the popup's
		 * after-score both reflect the reverted content.
		 *
		 * @since 5.0.0
		 *
		 * @param {string} field One of 'seoTitle', 'metaDescription', 'headline', 'content'.
		 * @returns {Promise<void>}
		 */
		async revertOptimizeField (field) {
			const postEditorStore = usePostEditorStore()
			const result          = this.optimizeResult
			if (!result) {
				return
			}

			switch (field) {
				case 'seoTitle':
					postEditorStore.updateTitle(result.seoTitle?.before ?? '')
					break
				case 'metaDescription':
					postEditorStore.updateDescription(result.metaDescription?.before ?? '')
					break
				case 'headline':
					window.wp?.data?.dispatch?.('core/editor')?.editPost?.({ title: result.headline?.before ?? '' })
					break
				case 'content':
					if (this.optimizeOriginalBlocks) {
						window.wp?.data?.dispatch?.('core/block-editor')?.resetBlocks?.(this.optimizeOriginalBlocks)
					}
					break
				default:
					return
			}

			// Persist the reverted flag on the result so reopening the modal still
			// shows this field as reverted (the done view reads it from here).
			result.reverted = { ...(result.reverted || {}), [field]: true }

			// Immediate run: updates the toolbar badge and returns the fresh score
			// so the popup hero drops to reflect the reverted content.
			const analysis = await requestAnalysisRun()

			// Bail if a new run (or close) replaced the result while we awaited.
			if (this.optimizeResult !== result) {
				return
			}

			const score = analysis?.seoScore
			if ('number' === typeof score) {
				result.score = { ...result.score, after: score }
			}
		},
		async optimizePost () {
			if (this.optimizingPost || !isOptimizeSupported()) {
				return
			}

			const postEditorStore = usePostEditorStore()
			const optionsStore    = useOptionsStore()

			// Resolve smart tags (e.g. #post_title, #separator_sa, #post_excerpt) to
			// their live values so the service receives real text, not the templates.
			// `separator: undefined` lets parseTags resolve #separator_sa from the
			// tags store, matching the SEO preview in metabox/sidebar General.vue.
			const { parseTags } = useTags({ separator: undefined })

			// The body is sent as an ordered list of text blocks, each keyed by its
			// clientId. The optimizer rewrites the blocks that need work and returns
			// the replacement block(s) per id, which we apply back below.
			const allBlocks = getPostContentBlocks()
			if (!allBlocks.length) {
				// Reachable from the intro CTA when the post has a title/meta but no
				// optimizable body blocks — surface it in the intro's inline alert.
				this.optimizePostError = __('Add some content to this post before optimizing.', td)

				return
			}

			// Left out rather than cut: the rewrite replaces the whole block, so a cut block
			// would lose everything past the cut. The server applies the same limit.
			const maxBlockLength = useAiStore().options?.maxOptimizeBlockLength || 10000
			const content        = allBlocks.filter(block => [ ...block.text ].length <= maxBlockLength)
			const skippedBlocks  = allBlocks.length - content.length
			if (!content.length) {
				this.optimizePostError = __('Every paragraph and heading in this post is too long to optimize. Split them into shorter ones and try again.', td)

				return
			}

			// Lock in the quote the user just accepted. The spelling pass runs only if
			// it was part of that quote, so the charge can never exceed the number the
			// button showed — and a post with no misspellings is never billed for it.
			const billedForSpelling = this.optimizeIncludesSpelling

			// Sent so the server optimizes against the user's chosen keyword. The
			// button is gated on the post already having one, so this is never empty.
			const providedKeyword = postEditorStore.truseoData?.focusKeyword || ''

			// Snapshot the live "before" values up front, before any mutation, so the
			// completion modal can show an accurate before/after diff.
			const beforeSeoTitle       = parseTags(postEditorStore.currentPost?.title || postEditorStore.currentPost?.tags?.title || '')
			const beforeSeoDescription = parseTags(postEditorStore.currentPost?.description || postEditorStore.currentPost?.tags?.description || '')
			const wordsBefore          = countContentWords(allBlocks)

			// Hold the full pre-optimize block tree so the done view can revert the body
			// wholesale. markRaw keeps Vue from proxying the raw WP block objects.
			const originalBlocks = window.wp?.data?.select?.('core/block-editor')?.getBlocks?.()
			this.optimizeOriginalBlocks = Array.isArray(originalBlocks) ? markRaw(originalBlocks) : null

			// The WP H1 (document title) is distinct from the SEO meta title above. Only
			// optimize it when the Headline Analyzer is enabled for this post (English +
			// block editor + feature on) — that's the analyzer that scores the result.
			const { headlineAnalyzerEnabled } = useHeadlineAnalyzer()
			const canOptimizeHeadline         = headlineAnalyzerEnabled.value
			const getEditedTitle              = () => window.wp?.data?.select?.('core/editor')?.getEditedPostAttribute?.('title') || ''
			const sentTitle                   = canOptimizeHeadline ? getEditedTitle() : ''

			// The SEO meta title & description live under the general post settings
			// capability (same gate as the manual fields and the REST save). Users
			// without it may run Optimize for the body/headline, so only apply the
			// optimized meta when they're actually allowed to edit it.
			const canOptimizeMeta = allowed('aioseo_page_general_settings')

			const payload = {
				postId          : postEditorStore.currentPost?.id || 0,
				seo_title       : parseTags(postEditorStore.currentPost?.title || postEditorStore.currentPost?.tags?.title || ''),
				seo_description : parseTags(postEditorStore.currentPost?.description || postEditorStore.currentPost?.tags?.description || ''),
				focus_keyword   : providedKeyword,
				locale          : postEditorStore.currentPost?.truseo_locale || window.aioseo?.user?.locale || 'en_US',
				options         : {
					tone     : optionsStore.options?.aiContent?.tone || '',
					audience : optionsStore.options?.aiContent?.audience || ''
				},
				content
			}

			if (canOptimizeHeadline) {
				payload.post_title = sentTitle
			}

			this.optimizePhase     = 'optimizing'
			this.optimizeResult    = null
			this.optimizePostError = null

			// Snapshot the pre-optimize score + failing checks from a fresh local
			// analysis (same source as the score badge) so the done view can show the
			// real score jump and which checks were resolved.
			const beforeAnalysis = await requestAnalysisRun()
			const scoreBefore    = beforeAnalysis?.seoScore ?? (postEditorStore.currentPost?.seo_score || 0)
			const badBefore      = collectBadChecks(beforeAnalysis)

			try {
				// Save first so the pre-optimization content is captured as a revision
				// the user can restore if they don't want the AI's changes.
				try {
					await window.wp?.data?.dispatch?.('core/editor')?.savePost?.()
				} catch (e) {
					// Best-effort: proceed with the optimization even if the pre-save fails.
				}

				const response = await http.post(links.restUrl('ai/truseo/optimize-post')).send(payload)

				if (response.body?.success) {
					// Meta fields only — same setters the manual SEO title / meta
					// description inputs use (TitlesDescriptions.vue). Gated on the
					// general post settings capability so we never overwrite meta the
					// user isn't permitted to edit.
					if (canOptimizeMeta) {
						postEditorStore.updateTitle(response.body.seo_title)
						postEditorStore.updateDescription(response.body.seo_description)
					}

					if (response.body.aiOptions) {
						optionsStore.internalOptions.internal.ai = merge(
							{},
							optionsStore.internalOptions.internal.ai,
							response.body.aiOptions
						)
					}

					// Apply the optimized H1. Skip-if-changed: only write when the live
					// title still matches what we sent, so a title the user edited while
					// the request was in flight is never clobbered. Also skip empty
					// results (non-English/unchanged) and no-op rewrites.
					const liveTitle       = canOptimizeHeadline ? getEditedTitle() : ''
					const headlineApplied = canOptimizeHeadline &&
						!!response.body.headline &&
						liveTitle === sentTitle &&
						response.body.headline !== liveTitle

					if (headlineApplied) {
						window.wp?.data?.dispatch?.('core/editor')?.editPost?.({ title: response.body.headline })
					}

					// Apply the rewritten body blocks in place (replace each block by
					// its clientId with the returned block(s)). Returns the count actually
					// applied — smaller than the response length when entries are empty or
					// their block was removed mid-flight.
					const blocksRewritten = applyOptimizedBlocks(response.body.content)

					// Re-run TruSEO analysis so the score badge refreshes. The spelling
					// pass below reads misspellings from this refreshed (post-optimize)
					// analysis, never the pre-optimize content.
					await requestAnalysisRefresh()

					// Auto-correct spelling in the freshly rewritten body. Best-effort:
					// a spelling failure must never undo the optimize-post result that's
					// already applied above.
					let spelling = null
					if (billedForSpelling) {
						this.optimizePhase = 'spelling'
						try {
							spelling = await this.runSpellingPass(payload.locale, payload.options)
						} catch (e) {
							// Swallow — optimize-post already succeeded and was applied.
						}
					}

					// Read the final state after all applies + refresh + spelling have
					// settled: the score, word count, and which failing checks are now
					// resolved. Same analysis source as the score badge, so they agree.
					const afterAnalysis    = await requestAnalysisRun()
					const scoreAfter       = afterAnalysis?.seoScore ?? (postEditorStore.currentPost?.seo_score || 0)
					const afterBlocks      = getPostContentBlocks()
					const wordsAfter       = countContentWords(afterBlocks)

					// The body rewrite fixes most misspellings itself, so runSpellingPass's
					// own tally (only the leftovers it corrected) badly undercounts what the
					// user sees resolved. Report the real reduction across the whole optimize:
					// flagged-before minus flagged-after. Dictionary additions also drop out
					// of the after-count, so attribute those to the dictionary line — not
					// "fixed" — to keep the two from double-counting.
					if (spelling) {
						const beforeSpellingWords = spellingFlaggedWords(beforeAnalysis)
						const afterSpellingCount  = spellingFlaggedWords(afterAnalysis).length
						const dictSet             = new Set((spelling.addedToDictionary || []).map(word => (word || '').toLowerCase()))
						const dictOccurrences     = beforeSpellingWords.filter(word => dictSet.has(word.toLowerCase())).length

						spelling.fixed = Math.max(0, beforeSpellingWords.length - afterSpellingCount - dictOccurrences)
					}

					const fixedChecks = []
					badBefore.forEach((title, id) => {
						// Spelling resolution is reported by the dedicated "Fixed N spelling
						// mistakes" / dictionary lines; skip it here so it isn't listed twice.
						if (spelling && 'spellingChecker' === id) {
							return
						}

						const result = afterAnalysis?.allResults?.get?.(id)
						if (!result || !isBadResult(result.score)) {
							fixedChecks.push({ title })
						}
					})

					// Spread the overall score gain evenly across the resolved checks so each
					// shows a rough "+N" contribution; the parts sum to the headline delta.
					const scoreGain = Math.max(0, scoreAfter - scoreBefore)
					if (scoreGain && fixedChecks.length) {
						const base = Math.floor(scoreGain / fixedChecks.length)
						const rem  = scoreGain - (base * fixedChecks.length)
						fixedChecks.forEach((check, index) => {
							check.points = base + (index < rem ? 1 : 0)
						})
					}

					// Per-block before/after text (paired by original block id) so the
					// completion modal can diff each rewritten block on its own.
					const contentBlocks = (response.body.content || [])
						.map(res => {
							const beforeBlock = content.find(block => block.id === res.id)

							return {
								before : beforeBlock?.text || '',
								after  : Array.isArray(res.blocks) ? res.blocks.map(block => block.text || '').join('\n\n') : ''
							}
						})
						.filter(block => block.before !== block.after)

					// Capture the before/after result for the completion modal, then
					// switch to the 'done' phase (keeps the modal open until dismissed).
					this.optimizeResult = {
						seoTitle        : canOptimizeMeta ? { before: beforeSeoTitle, after: response.body.seo_title || '' } : null,
						metaDescription : canOptimizeMeta ? { before: beforeSeoDescription, after: response.body.seo_description || '' } : null,
						headline        : headlineApplied ? { before: sentTitle, after: response.body.headline } : null,
						blocksRewritten,
						skippedBlocks,
						score           : { before: scoreBefore, after: scoreAfter },
						words           : { before: wordsBefore, after: wordsAfter },
						contentBlocks,
						fixedChecks,
						spelling,
						reverted        : {}
					}
					this.optimizePhase = 'done'

					return true
				}

				this.optimizePostError = response.body?.message || __('Couldn\'t optimize this post. Please try again.', td)
				this.optimizePhase     = 'intro'

				return false
			} catch (error) {
				const body = error?.response?.body
				if (body?.code && body.aiOptions) {
					optionsStore.internalOptions.internal.ai = merge(
						{},
						optionsStore.internalOptions.internal.ai,
						body.aiOptions
					)
				}
				this.optimizePostError = body?.message || __('Couldn\'t optimize this post. Please try again.', td)
				this.optimizePhase     = 'intro'

				return false
			}
		},

		/**
		 * Hides the optimize modal without discarding the completed result, so
		 * reopening it (e.g. after an accidental backdrop close) still shows the
		 * last optimization. Clears only the transient error.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		closeOptimizeModal () {
			this.optimizePhase     = 'idle'
			this.optimizePostError = null
		},

		/**
		 * Opens the Manage Dictionary (safe words) modal. The metabox and the sidebar
		 * each mount their own modal instance but share this store, so the flag lets
		 * whichever instance actually rendered open no matter which surface triggered it.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		openSafeWordsModal () {
			this.safeWordsModalOpen = true
		},

		/**
		 * Closes the Manage Dictionary (safe words) modal.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		closeSafeWordsModal () {
			this.safeWordsModalOpen = false
		},

		/**
		 * Whether the spelling auto-correction pass should run: the spell checker
		 * is enabled site-wide, the worker is ready, and the analysis locale has a
		 * Hunspell dictionary. Mirrors the Spelling tab gate.
		 *
		 * @since 5.0.0
		 *
		 * @returns {boolean} True if the spelling pass can run.
		 */
		shouldRunSpelling () {
			if (!window.aioseo?.spellChecker?.enabled || !this.isSpellingWorkerReady()) {
				return false
			}

			const postEditorStore = usePostEditorStore()
			const locale          = postEditorStore.currentPost?.truseo_locale || window.aioseo?.user?.locale || 'en_US'

			return useSpellCheckerDictionaryStore().isLocaleSpellCheckable(locale)
		},

		/**
		 * Runs the spelling auto-correction pass after the optimized body has been
		 * applied and re-analyzed. Reads the post-optimize misspellings from the
		 * assessment (not highlight marks — those only exist when highlighting is
		 * toggled on), fetches each word's Hunspell suggestions, asks the AI service
		 * for the best correction per word, applies corrections by whole-word
		 * replacement, and adds flagged brand/legit words to the dictionary. Ends
		 * with exactly one re-analysis for the whole pass.
		 *
		 * @since 5.0.0
		 *
		 * @param {string} locale  The analysis locale.
		 * @param {Object} options The tone/audience options.
		 * @returns {Promise<{fixed: number, addedToDictionary: string[]}>} The applied
		 *                                                                  spelling fixes and dictionary additions.
		 */
		async runSpellingPass (locale, options) {
			const empty = { fixed: 0, addedToDictionary: [] }

			const postEditorStore = usePostEditorStore()
			const optionsStore    = useOptionsStore()

			// Read the flagged words from a fresh, non-debounced analysis of the
			// applied body — not the store. applyOptimizedBlocks triggers a debounced
			// content-change analysis that shares one timer with the plain refresher
			// and can supersede it (resolving null without applying), which would
			// leave the store on pre-optimize spelling and miss any misspelling the
			// rewrite introduced. requestAnalysisRun executes immediately and returns
			// its own results, so this always reflects the post-optimize body.
			const analysis = await requestAnalysisRun()
			const spelling = analysis?.allResults?.get?.('spellingChecker')
			const flagged  = [ ...new Set((spelling?.highlightSentences || []).map(s => (s || '').trim()).filter(Boolean)) ]
			if (!flagged.length) {
				return empty
			}

			// Keep only words that live in a block we can actually correct, each
			// paired with its surrounding sentence as context. Drops words that only
			// appear in uncorrectable blocks (tables, embeds, ...) so we never spend
			// AI credits on a fix we couldn't apply.
			const targets = gatherSpellingTargets(flagged)
			if (!targets.length) {
				return empty
			}

			// Fetch every word's Hunspell suggestions in one parallel wave, then
			// attach the suggestions + context so the service can pick the best fit.
			const suggestionLists = await Promise.all(
				targets.map(target => requestSpellingSuggestions(target.word))
			)

			const wordPayload = targets.map((target, index) => ({
				word        : target.word,
				suggestions : Array.isArray(suggestionLists[index]) ? suggestionLists[index] : [],
				context     : target.context
			}))

			let results = []
			try {
				const response = await http.post(links.restUrl('ai/truseo/spelling')).send({
					postId : postEditorStore.currentPost?.id || 0,
					locale,
					options,
					words  : wordPayload
				})

				if (response.body?.aiOptions) {
					optionsStore.internalOptions.internal.ai = merge(
						{},
						optionsStore.internalOptions.internal.ai,
						response.body.aiOptions
					)
				}

				if (!response.body?.success) {
					return empty
				}

				results = Array.isArray(response.body.results) ? response.body.results : []
			} catch (error) {
				const body = error?.response?.body
				if (body?.aiOptions) {
					optionsStore.internalOptions.internal.ai = merge(
						{},
						optionsStore.internalOptions.internal.ai,
						body.aiOptions
					)
				}

				return empty
			}

			const dictWords = [ ...new Set(results.filter(r => r?.addToDictionary && r?.word).map(r => r.word)) ]

			// Guard: drop any "correction" the live Hunspell worker still flags as a
			// non-word, so a mangled coined word (e.g. "Zylorq"→"Zyloq") is never applied.
			const candidates  = results.filter(r => r?.word && (r?.correction || '').trim())
			const validity    = await Promise.all(
				candidates.map(candidate => requestSpellingCheck((candidate.correction || '').trim()))
			)
			const corrections = candidates.filter((candidate, index) => validity[index])

			// Nothing actionable (every word left as-is): content is unchanged, so
			// the post-optimize analysis already reflects reality — no refresh needed.
			if (!corrections.length && !dictWords.length) {
				return empty
			}

			applySpellingCorrections(corrections)

			// addSafeWordsBatch runs the single trailing re-analysis when it adds any
			// word; otherwise repaint here so the corrected words drop off the highlights.
			const added = await this.addSafeWordsBatch(dictWords)
			if (!added.length) {
				await requestAnalysisRefresh()
			}

			return { fixed: corrections.length, addedToDictionary: added }
		},

		/**
		 * Persists a batch of words to the dictionary and adds them to the live
		 * worker instance, then triggers exactly ONE re-analysis at the end. Unlike
		 * looping addToSafeWords (which re-analyzes per word), this keeps the whole
		 * batch to a single analysis pass.
		 *
		 * @since 5.0.0
		 *
		 * @param {string[]} words The words to add to the dictionary.
		 * @returns {Promise<string[]>} The words that were successfully persisted.
		 */
		async addSafeWordsBatch (words) {
			const list = [ ...new Set((words || []).map(w => (w || '').trim()).filter(Boolean)) ]
			if (!list.length) {
				return []
			}

			this.addToSafeWordsError = null
			const added = []

			for (const word of list) {
				try {
					const response = await http.post(links.restUrl('spell-checker/safe-words/add')).send({ word })
					if (!response.body?.success) {
						continue
					}

					const persistedWord = response.body.word || word
					await requestAddSafeWord(persistedWord)
					this.invalidateSpellingSuggestions(persistedWord)
					added.push(persistedWord)
				} catch (error) {
					// Best-effort per word; keep going so one failure doesn't abort the batch.
				}
			}

			if (added.length) {
				await requestAnalysisRefresh()
			}

			return added
		},
		async addToSafeWords (word) {
			if (!word) {
				return false
			}

			this.addToSafeWordsError = null
			this.addToSafeWordsLoadingFor.push(word)

			try {
				const response = await http.post(links.restUrl('spell-checker/safe-words/add')).send({ word })

				if (!response.body?.success) {
					this.addToSafeWordsError = response.body?.message || 'Failed to add word to your dictionary.'

					return false
				}

				const persistedWord = response.body.word || word
				await requestAddSafeWord(persistedWord)
				this.invalidateSpellingSuggestions(persistedWord)

				// Force the canonical analysis path so the store receives the
				// post-safe-word assessment and the highlight watch in General.vue
				// repaints. Without this, the worker re-analyzes but its result
				// never reaches `postEditorStore.currentPost.truseo.general` and
				// the highlights stay cleared after the popover's `clearAll()`.
				await this.refreshAnalysisAfterDictionaryChange()

				return true
			} catch (error) {
				const body = error?.response?.body
				this.addToSafeWordsError = body?.message || 'Failed to add word to your dictionary.'

				return false
			} finally {
				this.addToSafeWordsLoadingFor = this.addToSafeWordsLoadingFor.filter(w => w !== word)
			}
		},
		async refreshAnalysisAfterDictionaryChange () {
			// Immediate, non-debounced canonical analysis so the just-added safe word
			// drops off the Spelling tab list at once. requestAnalysisRefresh shares
			// the worker's debounce timer with the ambient content-change flow and can
			// be superseded (resolving without applying), which left the fixed word
			// listed. requestAnalysisRun bypasses that timer. Delegated to the shared
			// bridge for the same worker-in-addon-bundles reason as [[reanalyzeNow]].
			await requestAnalysisRun()
		},
		async reanalyzeNow () {
			// Immediate, non-debounced canonical analysis + store apply, so the
			// Spelling tab list reflects the corrected content right away instead of
			// waiting on the race-prone ambient re-analysis (which left a fixed word
			// listed, or let it reappear from a late/superseded worker result). The
			// store can't call the worker directly — the static import chain pulls
			// `@/app/tru-seo/index.js?worker` into addon IIFE bundles that can't emit
			// its code-split chunks — so this goes through the shared bridge, a no-op
			// in addon builds (matching the [[setSharedWorker]] pattern).
			await requestAnalysisRun()
		},
		invalidateSpellingSuggestions (word) {
			if (!word) {
				return
			}

			const lower = word.toLowerCase()
			for (const cached of Object.keys(this.suggestionsCache)) {
				if (cached.toLowerCase() === lower) {
					delete this.suggestionsCache[cached]
				}
			}

			for (const pending of [ ...spellingSuggestionRequests.keys() ]) {
				if (pending.toLowerCase() === lower) {
					spellingSuggestionRequests.delete(pending)
				}
			}
		},
		clearAddToSafeWordsError () {
			this.addToSafeWordsError = null
		},
		clearAiSuggestionsError () {
			this.aiSuggestionsError = null
		},
		sanitizeHighlightMarks () {
			this.highlightMarks = this.highlightMarks.filter(hm => !!hm.node)
			if (this.highlightMarks.length && !this.highlightMarks.find(hm => !!hm.active)) {
				this.highlightMarks[0].active = true
			}
		},
		toggleHighlightAnalyzer (analyzer) {
			// Any manual interaction overrides the language-switch auto-select.
			this.awaitingFreshHighlights = false

			if (!analyzer) {
				this.highlightAnalyzers = []
				this.highlightingEnabled = false

				return false
			}

			const index = this.highlightAnalyzers.indexOf(analyzer)
			if (-1 !== index) {
				this.highlightAnalyzers.splice(index, 1)
				// Remember the explicit uncheck so the auto-sync won't re-add this
				// analyzer when it (or a new issue of its type) appears again.
				if (!this.deselectedHighlightAnalyzers.includes(analyzer)) {
					this.deselectedHighlightAnalyzers.push(analyzer)
				}
			} else {
				this.highlightAnalyzers.push(analyzer)
				this.deselectedHighlightAnalyzers = this.deselectedHighlightAnalyzers.filter(a => a !== analyzer)
			}

			// Checking a box re-enables the master toggle for convenience,
			// but unchecking the last box must NOT close the master toggle —
			// the user may want to leave it on while inspecting issues.
			if (0 < this.highlightAnalyzers.length) {
				this.highlightingEnabled = true
			}
		},
		toggleGlobalHighlighting () {
			this.awaitingFreshHighlights = false
			this.highlightingEnabled = !this.highlightingEnabled

			// Persist the master on/off as a per-post preference so a reload keeps it.
			usePostEditorStore().saveTruSeoHighlighting(this.highlightingEnabled)

			if (!this.highlightingEnabled) {
				// Remember the current selection so it can be restored next time the toggle is turned on.
				this.rememberedHighlightAnalyzers = [ ...this.highlightAnalyzers ]
				this.highlightAnalyzers = []

				return
			}

			const available  = this.availableHighlightAnalyzers
			const defaultSet = [ ...available ]

			if (null === this.rememberedHighlightAnalyzers) {
				this.highlightAnalyzers = defaultSet

				return
			}

			const restored = this.rememberedHighlightAnalyzers.filter(a => available.includes(a))

			this.highlightAnalyzers = restored.length ? restored : defaultSet
		},

		/**
		 * Suspends highlighting when the editor can no longer paint — the user
		 * switched the Block Editor to the Code Editor, or the editor was hidden.
		 * Snapshots the master toggle and analyzer selection so resumeHighlighting()
		 * can restore them verbatim, then clears them so the highlightSentences
		 * watcher tears the marks down. Never persists the toggle, so the per-post
		 * preference survives the round trip. No-op if already suspended.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		suspendHighlighting () {
			this.allowHighlighting = false

			if (this.editorModeSuspended) {
				return
			}

			this.editorModeSuspended = {
				highlightingEnabled : this.highlightingEnabled,
				highlightAnalyzers  : [ ...this.highlightAnalyzers ]
			}

			this.highlightAnalyzers  = []
			this.highlightingEnabled = false
		},

		/**
		 * Restores the master toggle and analyzer selection saved by
		 * suspendHighlighting() once the editor can paint again (e.g. switched
		 * back to the Visual Editor), so highlights that were on before the switch
		 * reappear. Re-runs the additive seeders afterwards to cover the case where
		 * nothing was selected yet at suspend time (post opened directly in the Code
		 * Editor); both are gated on highlightingEnabled, so a toggle that was off
		 * stays off. No selection to restore is a no-op beyond clearing the gate.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		resumeHighlighting () {
			this.allowHighlighting = true

			if (!this.editorModeSuspended) {
				return
			}

			this.highlightingEnabled = this.editorModeSuspended.highlightingEnabled
			this.highlightAnalyzers  = [ ...this.editorModeSuspended.highlightAnalyzers ]
			this.editorModeSuspended = null

			this.syncNewHighlightAnalyzers()
			this.ensureSpellingHighlightPainted()
		},

		/**
		 * Seeds highlightingEnabled from the per-post preference when one was saved,
		 * otherwise leaves the site-wide default (set in state). Run once on mount,
		 * before the user can toggle.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		initTruSeoHighlighting () {
			// Terms have nothing to paint into, so a preference carried over from a post must not
			// switch the subsystem back on.
			if (!this.enabled) {
				return
			}

			const perPost = usePostEditorStore().currentPost?.options?.truSeo?.highlightingEnabled
			if ('boolean' === typeof perPost) {
				this.highlightingEnabled = perPost
			}
		},
		// While highlighting is on, check any readability analyzer that newly has
		// issues (adding it to the painted set) unless the user explicitly unchecked
		// it. Additive and idempotent — never drops the user's current selection.
		// Spelling keeps its own dictionary-timed path ({@see ensureSpellingHighlightPainted}).
		syncNewHighlightAnalyzers () {
			if (!this.highlightingEnabled) {
				return
			}

			const additions = this.availableHighlightAnalyzers.filter(analyzer =>
				'spellingChecker' !== analyzer &&
				!this.highlightAnalyzers.includes(analyzer) &&
				!this.deselectedHighlightAnalyzers.includes(analyzer)
			)

			if (additions.length) {
				this.highlightAnalyzers = [ ...this.highlightAnalyzers, ...additions ]
			}
		},

		/**
		 * Adds spellingChecker to the painted analyzer set when highlighting is on
		 * and spelling has results. The default painted set is chosen the first time
		 * analysis lands, but the Hunspell dictionary usually isn't ready then, so
		 * spellingChecker is excluded; this re-adds it once the dictionary loads and
		 * spelling results reach the store. Additive (never drops other analyzers)
		 * and idempotent; respects the master toggle so it never overrides
		 * highlighting the user turned off.
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		ensureSpellingHighlightPainted () {
			if (!this.highlightingEnabled || this.highlightAnalyzers.includes('spellingChecker')) {
				return
			}

			if (!this.availableHighlightAnalyzers.includes('spellingChecker')) {
				return
			}

			this.highlightAnalyzers = [ ...this.highlightAnalyzers, 'spellingChecker' ]
		},

		/**
		 * Prepares the highlighter for a language switch: wipes any marks left
		 * over from the previous locale and arms the auto-enable flag so the
		 * first analysis to land for the new locale will turn highlighting on
		 * with every available analyzer checked.
		 *
		 * @since 5.0.0
		 *
		 * @returns {number} The switch's id, to pass to {@see endLanguageSwitch}.
		 */
		beginLanguageSwitch () {
			this.languageSwitchId++
			this.suggestionsPaused            = true
			this.suggestionsLoadingFor        = null
			this.awaitingFreshHighlights      = true
			this.rememberedHighlightAnalyzers = null
			this.deselectedHighlightAnalyzers = []
			this.highlightAnalyzers           = []
			this.highlightingEnabled          = false
			// Suggestions came from the previous locale's dictionary.
			this.suggestionsCache             = {}
			spellingSuggestionRequests.clear()
			this.clearAnnotations()

			return this.languageSwitchId
		},

		/**
		 * Lets spelling suggestions be fetched again once the new locale's dictionary is loaded.
		 *
		 * NOTE: a no-op for a switch a later one has superseded, which is still in progress.
		 *
		 * @param {number} languageSwitchId The id {@see beginLanguageSwitch} returned.
		 * @returns {void}
		 */
		endLanguageSwitch (languageSwitchId) {
			if (languageSwitchId !== this.languageSwitchId) {
				return
			}

			this.suggestionsPaused = false
			// A fresh object, so the Spelling tab's prefetch runs again for every listed word.
			this.suggestionsCache  = {}
		},

		/**
		 * Turns highlighting on and selects every analyzer that currently
		 * has highlight sentences. Used right after a locale switch — both
		 * after the first analysis returns and again once the spell-check
		 * dictionary becomes ready (so `spellingChecker` is picked up too).
		 *
		 * @since 5.0.0
		 *
		 * @returns {void}
		 */
		enableAllAvailableHighlights () {
			const available = this.availableHighlightAnalyzers
			if (!available.length) {
				return
			}

			this.highlightingEnabled = true
			this.highlightAnalyzers  = [ ...available ]
		}
	}
})