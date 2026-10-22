<template>
	<div
		v-if="hasMark"
		class="tru-seo-highlight-popover"
		:data-placement="placement"
	>
		<div class="tru-seo-highlight-popover__header">
			<div class="tru-seo-highlight-popover__header-content">
				<div class="tru-seo-highlight-popover__label">
					<span
						class="tru-seo-highlight-popover__dot"
						:style="{ backgroundColor: assessmentColor }"
					/>
					<span class="tru-seo-highlight-popover__title">
						{{ assessmentDisplayName }}
					</span>
				</div>

				<div
					v-if="showPager"
					class="tru-seo-highlight-popover__pager"
				>
					<button
						type="button"
						class="tru-seo-highlight-popover__pager-btn"
						:disabled="!hasPreviousSibling"
						:aria-label="strings.previousIssue"
						@click.prevent.stop="goToPrevious"
					>
						<SvgCaret class="tru-seo-highlight-popover__pager-icon tru-seo-highlight-popover__pager-icon--prev" />
					</button>

					<span class="tru-seo-highlight-popover__pager-count">
						{{ pagerPosition.index + 1 }}/{{ pagerPosition.total }}
					</span>

					<button
						type="button"
						class="tru-seo-highlight-popover__pager-btn"
						:disabled="!hasNextSibling"
						:aria-label="strings.nextIssue"
						@click.prevent.stop="goToNext"
					>
						<SvgCaret class="tru-seo-highlight-popover__pager-icon tru-seo-highlight-popover__pager-icon--next" />
					</button>
				</div>

				<div class="tru-seo-highlight-popover__header-actions">
					<button
						v-if="showSuggestFixButton"
						class="tru-seo-highlight-popover__suggest-fix"
						:disabled="aiBusy"
						:title="aiBusy ? strings.waitForSuggestions : null"
						@click="requestSuggestions"
					>
						<SvgAiContent
							class="tru-seo-highlight-popover__suggest-fix-icon"
						/>
						{{ suggestFixLabel }}
					</button>

					<button
						v-if="isSpellingSuggestable && !suggestionsLoading"
						type="button"
						class="tru-seo-highlight-popover__add-to-dictionary"
						:disabled="addToSafeWordsLoading"
						@click.prevent.stop="addToDictionary"
					>
						<svg-plus class="tru-seo-highlight-popover__add-icon" />

						{{ addToSafeWordsLoading ? strings.addingToDictionary : strings.addToDictionary }}
					</button>

					<div
						v-if="canGetSuggestions && suggestionsLoading"
						class="tru-seo-highlight-popover__spinner"
					/>
				</div>
			</div>

			<div
				v-if="addToSafeWordsError"
				class="tru-seo-highlight-popover__error"
				role="alert"
			>
				{{ addToSafeWordsError }}
			</div>

			<div
				v-if="showBusyHint && !showFooter"
				class="tru-seo-highlight-popover__busy-hint"
				role="status"
			>
				<span class="tru-seo-highlight-popover__busy-icon" />
				{{ strings.waitForSuggestions }}
			</div>

			<hr
				v-if="spellingSuggestions.length || aiSuggestions.length"
				class="tru-seo-highlight-popover__separator"
			>
		</div>

		<div
			v-if="showDescription"
			class="tru-seo-highlight-popover__description"
		>
			<hr class="tru-seo-highlight-popover__separator">

			<div class="tru-seo-highlight-popover__description-body">
				<SvgInfo class="tru-seo-highlight-popover__hint-icon" />

				<span v-html="assessmentText" />
			</div>
		</div>

		<div
			v-if="isAiSuggestable && aiError"
			class="tru-seo-highlight-popover__error"
		>
			{{ aiError }}
			<button
				class="tru-seo-highlight-popover__retry"
				:disabled="aiBusy"
				:title="aiBusy ? strings.waitForSuggestions : null"
				@click="requestSuggestions"
			>
				{{ strings.retry }}
			</button>
		</div>

		<div
			v-if="isSpellingSuggestable && hasCachedSpellingSuggestions && !suggestionsLoading"
			class="tru-seo-highlight-popover__suggestions"
		>
			<template v-if="spellingSuggestions.length">
				<button
					v-for="(suggestion, index) in spellingSuggestions"
					:key="index"
					type="button"
					class="tru-seo-highlight-popover__suggestion"
					@mousedown="onFixPointerDown(() => applySpellingSuggestion(suggestion))"
					@click.prevent.stop="onFixClick(() => applySpellingSuggestion(suggestion))"
				>
					{{ suggestion }}
				</button>
			</template>

			<div
				v-else
				class="tru-seo-highlight-popover__no-suggestions"
			>
				{{ strings.noSuggestions }}
			</div>
		</div>

		<div
			v-if="isAiSuggestable && hasCachedAiSuggestions && !suggestionsLoading"
			class="tru-seo-highlight-popover__suggestions"
		>
			<template v-if="aiSuggestions.length">
				<button
					v-for="(suggestion, index) in aiSuggestions"
					:key="index"
					class="tru-seo-highlight-popover__suggestion tru-seo-highlight-popover__suggestion--ai"
					@mousedown="onFixPointerDown(() => applyAiSuggestion(suggestion))"
					@click="onFixClick(() => applyAiSuggestion(suggestion))"
				>
					<span
						class="tru-seo-highlight-popover__suggestion-text"
						v-html="formatSuggestionText(suggestion.text)"
					/>
					<span
						v-if="suggestion.rationale"
						class="tru-seo-highlight-popover__suggestion-rationale"
					>
						{{ suggestion.rationale }}
					</span>
				</button>
			</template>

			<div
				v-else
				class="tru-seo-highlight-popover__no-suggestions"
			>
				{{ strings.noSuggestions }}
			</div>
		</div>

		<div
			v-if="showFooter"
			class="tru-seo-highlight-popover__footer"
		>
			<hr class="tru-seo-highlight-popover__separator">

			<div class="tru-seo-highlight-popover__footer-content">
				<div
					v-if="showBusyHint"
					class="tru-seo-highlight-popover__busy-hint tru-seo-highlight-popover__busy-hint--inline"
					role="status"
				>
					<span class="tru-seo-highlight-popover__busy-icon" />
					{{ strings.waitForSuggestions }}
				</div>

				<button
					class="tru-seo-highlight-popover__regenerate"
					:disabled="aiBusy"
					:title="aiBusy ? strings.waitForSuggestions : null"
					@click="regenerateAiSuggestions"
				>
					<SvgAiContent class="tru-seo-highlight-popover__regenerate-icon" />
					{{ regenerateLabel }}
				</button>
			</div>
		</div>

		<div
			v-if="fixRefusal"
			class="tru-seo-highlight-popover__error"
			role="alert"
		>
			{{ fixRefusalMessage }}
		</div>

		<div class="tru-seo-highlight-popover__manage">
			<hr class="tru-seo-highlight-popover__separator">

			<div
				class="tru-seo-highlight-popover__manage-text"
				@click="onManageClick"
				v-html="strings.dontWantToSee"
			/>
		</div>
		<out-of-credits-modal
			:show="showCreditsUpsell"
			:feature="upsellFeature"
			@close="showCreditsUpsell = false"
		/>

	</div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

import {
	useRootStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import {
	AI_SUGGESTABLE_ANALYZERS,
	ANALYZER_SCOPE_MAP
} from '@/vue/stores/TruSeoHighlighterStore'

import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'

import { marked } from 'marked'
import DOMPurify from 'dompurify'

import { getAssessmentColor, getAssessmentName } from '@/vue/plugins/tru-seo/helpers/assessmentColors'
import { consumeWriteRefusal, replaceBlockText, replaceText, replaceWordInContent } from '@/vue/plugins/tru-seo/highlighter/spellingReplace'
import { useAiContent } from '@/vue/composables/AiContent'
import { usePointerDownFix } from '@/vue/composables/PointerDownFix'

import OutOfCreditsModal from '@/vue/components/common/ai/OutOfCreditsModal'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import SvgAiRephrase from '@/vue/components/common/svg/ai/Rephrase'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgInfo from '@/vue/components/common/svg/Info'
import SvgPlus from '@/vue/components/common/svg/Plus'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const SPELLING_ANALYZERS = [ 'spellingChecker' ]
const AI_FEATURE_KEY     = 'truseoSuggest'

defineProps({
	placement : {
		type    : String,
		default : 'above'
	}
})

const truSeoHighlighterStore = useTruSeoHighlighterStore()
const rootStore              = useRootStore()
const aiContent              = useAiContent()

const showCreditsUpsell = ref(false)

const upsellFeature = {
	slug        : 'truseo-suggest-fix',
	costKey     : AI_FEATURE_KEY,
	icon        : SvgAiRephrase,
	title       : __('Suggest Fix', td),
	description : __('Let AI rewrite the flagged text so you can accept the fix inline, without leaving the editor.', td)
}

const contentOptimizationUrl = (rootStore.aioseo?.urls?.aio?.settings || '#') + '#/content-optimization'

const strings = {
	noSuggestions       : __('No suggestions yet. Try editing the word and re-running the check.', td),
	suggestFix          : __('Suggest a fix', td),
	regenerate          : __('Regenerate', td),
	retry               : __('Retry', td),
	waitForSuggestions  : __('Another suggestion is being generated. Try again when it\'s done.', td),
	addToDictionary     : __('Add to dictionary', td),
	addingToDictionary  : __('Adding…', td),
	previousIssue       : __('Previous issue', td),
	nextIssue           : __('Next issue', td),
	fixFailed           : __('AIOSEO couldn\'t apply this fix here. Try editing the text directly in the content.', td),
	fixFailedDynamic    : __('AIOSEO couldn\'t apply this fix here. This text comes from a dynamic field, so it has to be changed at its source.', td),
	fixFailedShortcode  : __('AIOSEO couldn\'t apply this fix here. This text is produced by a shortcode, so it has to be changed where the shortcode gets it from.', td),
	fixFailedEditorOpen : __('AIOSEO couldn\'t apply this fix while this element\'s settings panel is open. Close the panel and try again.', td),
	fixFailedLocked     : __('AIOSEO couldn\'t apply this fix because this module is locked in the builder. Unlock the module and try again.', td),
	fixFailedNoEditor   : __('AIOSEO couldn\'t apply this fix because the module isn\'t open for editing. Click the highlighted word in the builder again, then pick the suggestion.', td),
	dontWantToSee       : sprintf(
		// Translators: 1 - "Disable for this post" action link, 2 - "Content Optimization" settings link.
		__('Don\'t want to see this? %1$s or under %2$s settings for all posts.', td),
		'<a href="#" class="tru-seo-highlight-popover__manage-link" data-aioseo-highlight-disable="1">' + __('Disable the highlighter for this post', td) + '</a>',
		'<a href="' + contentOptimizationUrl + '" class="tru-seo-highlight-popover__manage-link" target="_blank" rel="noopener noreferrer">' + __('Content Optimization', td) + '</a>'
	)
}

// Renders only while the store can still resolve the mark. Elementor's inline pen replaces the
// editing area's innerHTML when it opens, which drops the mark's `node` and would leave an empty
// box. Gated here rather than closed from the composable, whose `popoverPinned` is per-instance
// while the store is shared.
const hasMark = computed(() => !!truSeoHighlighterStore.hoveredMark)

const assessmentColor = computed(() => {
	const analyzer = truSeoHighlighterStore.hoveredMark?.analyzer
	return analyzer ? getAssessmentColor(analyzer) : '#64748B'
})

const assessmentDisplayName = computed(() => {
	const analyzer = truSeoHighlighterStore.hoveredMark?.analyzer
	return analyzer ? getAssessmentName(analyzer) : ''
})

const assessmentText = computed(() => {
	return truSeoHighlighterStore.hoveredMarkAssessmentText
})

const pagerPosition = computed(() => {
	return truSeoHighlighterStore.hoveredMarkPosition
})

// Whether a fix needs the user's own click on the mark first ({@see plugins/tru-seo/editors/divi4.js}).
// The pager and the advance-after-fix both re-open the popover on a mark nobody clicked, so every
// suggestion offered there could only refuse; such an editor gets neither.
const fixesRequireMarkClick = computed(() => true === resolveAdapter()?.fixesRequireMarkClick)

const showPager = computed(() => {
	return 1 < pagerPosition.value.total && !fixesRequireMarkClick.value
})

const hasPreviousSibling = computed(() => {
	return truSeoHighlighterStore.hasPreviousHoveredSibling
})

const hasNextSibling = computed(() => {
	return truSeoHighlighterStore.hasNextHoveredSibling
})

const goToPrevious = () => {
	truSeoHighlighterStore.navigateHoveredMark(-1)
}

const goToNext = () => {
	truSeoHighlighterStore.navigateHoveredMark(1)
}

const isSpellingSuggestable = computed(() => {
	const analyzer = truSeoHighlighterStore.hoveredMark?.analyzer
	return !!analyzer && SPELLING_ANALYZERS.includes(analyzer)
})

// Scopes whose answer is several paragraphs, so an editor may be unable to store it at all
// ({@see stores/TruSeoHighlighterStore.js:ANALYZER_SCOPE_MAP}).
const BLOCK_SCOPES = [ 'paragraph', 'section' ]

// Gated on the editor too, because the request is billed on click: a target that cannot hold
// blocks (an Elementor heading title, a V4 atomic prop) refuses any multi-part answer.
// NOTE: fails open — an adapter without the probe keeps the button.
const isAiSuggestable = computed(() => {
	const mark     = truSeoHighlighterStore.hoveredMark
	const analyzer = mark?.analyzer

	if (!analyzer || !AI_SUGGESTABLE_ANALYZERS.includes(analyzer)) {
		return false
	}

	if (!BLOCK_SCOPES.includes(ANALYZER_SCOPE_MAP[analyzer])) {
		return true
	}

	// Only re-evaluated when `hoveredMark` changes: the probe reads editor state Vue can't track.
	return false !== resolveAdapter()?.canReplaceMarkBlocks?.(mark)
})

const canGetSuggestions = computed(() => {
	return isSpellingSuggestable.value || isAiSuggestable.value
})

const aiFeatureCost = computed(() => {
	return aiContent.getFeatureCost(AI_FEATURE_KEY)
})

const hasCreditsForAi = computed(() => {
	return aiContent.hasEnoughCredits(aiFeatureCost.value)
})

const suggestFixLabel = computed(() => {
	if (isAiSuggestable.value) {
		return __('Suggest a fix', td)
	}

	return strings.suggestFix
})

const regenerateLabel = computed(() => {
	return __('Regenerate', td)
})

const hasCachedSpellingSuggestions = computed(() => {
	return truSeoHighlighterStore.hoveredMarkHasCachedSuggestions
})

const hasCachedAiSuggestions = computed(() => {
	return truSeoHighlighterStore.hoveredMarkHasCachedAiSuggestions
})

const spellingSuggestions = computed(() => {
	return truSeoHighlighterStore.hoveredMarkSuggestions
})

const aiSuggestions = computed(() => {
	return truSeoHighlighterStore.hoveredMarkAiSuggestions
})

const suggestionsLoading = computed(() => {
	return truSeoHighlighterStore.suggestionsLoading || truSeoHighlighterStore.aiSuggestionsLoading
})

// The plain-language description is context for before the user acts. Once
// suggestions are loading or shown, hide it so the popover isn't cluttered.
const showDescription = computed(() => {
	return !!assessmentText.value &&
		!isSpellingSuggestable.value &&
		!suggestionsLoading.value &&
		!hasCachedAiSuggestions.value
})

// Another issue's suggestions are generating; only one request runs at a time.
const aiBusy = computed(() => truSeoHighlighterStore.aiSuggestionsBusy)

// Says why Suggest a fix, Regenerate and Retry are disabled, so it doesn't read as a credits problem.
const showBusyHint = computed(() => isAiSuggestable.value && aiBusy.value && !suggestionsLoading.value)

const showFooter = computed(() => isAiSuggestable.value && hasCachedAiSuggestions.value && !suggestionsLoading.value)

const aiError = computed(() => {
	return truSeoHighlighterStore.aiSuggestionsError
})

const addToSafeWordsLoading = computed(() => {
	return 0 < truSeoHighlighterStore.addToSafeWordsLoadingFor.length
})

const addToSafeWordsError = computed(() => {
	return truSeoHighlighterStore.addToSafeWordsError
})

// Why the last fix on this mark refused, or null. Holds the reason, not the message.
const fixRefusal = ref(null)

// The only reasons where the text is not in the stored content (a dynamic tag's resolved text, or
// a shortcode's output), where retrying is the fix (an open settings panel overwrites the write),
// where editing is exactly what the builder blocks (a locked module), or where the module's editor
// has to be opened by a click on the mark first (Divi 4), so the generic "edit it in the content"
// advice would mislead. Every other refusal, `shortcode` included, describes text the user can edit.
const REFUSAL_MESSAGES = {
	dynamic               : 'fixFailedDynamic',
	'shortcode-output'    : 'fixFailedShortcode',
	'editor-open'         : 'fixFailedEditorOpen',
	'locked-module'       : 'fixFailedLocked',
	'needs-inline-editor' : 'fixFailedNoEditor'
}

const fixRefusalMessage = computed(() => strings[REFUSAL_MESSAGES[fixRefusal.value] || 'fixFailed'])

const showSuggestFixButton = computed(() => {
	if (!isAiSuggestable.value) {
		return false
	}

	if (suggestionsLoading.value) {
		return false
	}

	if (hasCachedAiSuggestions.value) {
		return false
	}

	if (aiError.value) {
		return false
	}

	return true
})

const requestSuggestions = () => {
	const mark = truSeoHighlighterStore.hoveredMark
	if (!mark || !isAiSuggestable.value) {
		return
	}

	// The button stays enabled without credits so the click can sell the upgrade
	// instead of dead-ending.
	if (!hasCreditsForAi.value) {
		showCreditsUpsell.value = true

		return
	}

	// Batch every uncached issue of this type so switching to a sibling is instant.
	truSeoHighlighterStore.fetchAiSuggestionsForType(mark.analyzer)
}

const regenerateAiSuggestions = () => {
	const mark = truSeoHighlighterStore.hoveredMark
	if (!mark || !isAiSuggestable.value) {
		return
	}

	if (!hasCreditsForAi.value) {
		showCreditsUpsell.value = true

		return
	}

	// Regenerate only the current item, leaving its siblings' suggestions intact.
	truSeoHighlighterStore.fetchAiSuggestionsForType(mark.analyzer, { rephrase: true, marks: [ mark ] })
}

const formatSuggestionText = (text) => {
	if (!text) {
		return ''
	}

	return DOMPurify.sanitize(marked.parse(String(text), { async: false }))
}

// Close after the fix instead of advancing where the next mark would need its own click first.
const nextIssueKeys = (mark) => fixesRequireMarkClick.value ? [] : truSeoHighlighterStore.nextIssueAdvanceKeys(mark)

const addToDictionary = async () => {
	const mark = truSeoHighlighterStore.hoveredMark
	if (!mark?.sentence || addToSafeWordsLoading.value) {
		return
	}

	// Capture the next issues and arm the advance before the fix — addToSafeWords
	// re-analyzes and rebuilds every mark, so a mark reference can't survive it.
	const nextKeys = nextIssueKeys(mark)
	if (nextKeys.length) {
		truSeoHighlighterStore.armAdvance(nextKeys)
	}

	const added = await truSeoHighlighterStore.addToSafeWords(mark.sentence)

	if (!added) {
		// Fix failed — keep the popover on the current word (with its error).
		truSeoHighlighterStore.cancelAdvance()

		return
	}

	if (!nextKeys.length) {
		// Nothing to advance to — close as before.
		truSeoHighlighterStore.dismissAfterFix(mark)
	}
}

const { onFixClick, onFixPointerDown } = usePointerDownFix()

const applySpellingSuggestion = async (suggestion) => {
	const mark = truSeoHighlighterStore.hoveredMark
	if (!mark) {
		return
	}

	fixRefusal.value = null

	const nextKeys = nextIssueKeys(mark)

	// Fix every occurrence of the word across the whole post — not just the painted
	// marks — so the same misspelling is never left flagged elsewhere in the content.
	// Pass triggerReanalysis=false; the advance flow's own reanalyzeNow() rebuilds
	// the marks, and a second (debounced) re-analysis would tear down the popover.
	const success = await replaceWordInContent(mark.sentence, suggestion, false)

	if (!success) {
		// Only an editor that knows why it refused supplies a reason, so block and classic stay
		// silent without a branch ({@see spellingReplace.js:consumeWriteRefusal}).
		fixRefusal.value = consumeWriteRefusal()

		return
	}

	advanceOrDismiss(mark, nextKeys)

	// Rebuild the marks now so the target issue repaints promptly, instead of
	// waiting on the debounced re-analysis; pendingAdvanceMark then re-opens the
	// popover on it.
	await truSeoHighlighterStore.reanalyzeNow()
}

const applyAiSuggestion = async (suggestion) => {
	const mark = truSeoHighlighterStore.hoveredMark
	if (!mark || !suggestion?.text) {
		return
	}

	fixRefusal.value = null

	const nextKeys = nextIssueKeys(mark)

	const strategy = suggestion.replaceStrategy
	let applied = false

	// Awaited: an unawaited promise is always truthy and would advance past a fix that failed.
	if ('paragraph' === strategy || 'section' === strategy) {
		applied = await replaceBlockText(mark, suggestion.text)
	} else {
		applied = await replaceText(mark, suggestion.text)
	}

	if (!applied) {
		// The only route to `ambiguous` and `shortcode`, neither visible in the canvas
		// ({@see spellingReplace.js:consumeWriteRefusal}).
		fixRefusal.value = consumeWriteRefusal()

		return
	}

	advanceOrDismiss(mark, nextKeys)

	await truSeoHighlighterStore.reanalyzeNow()
}

// Shared close-then-reopen for a suggestion accept: hide the popover at once so
// it doesn't flicker with emptied content, drop the fixed issue's highlight, and
// arm the advance so the rebuild reopens on the next surviving issue. With no next
// issue, just close.
const advanceOrDismiss = (mark, nextKeys) => {
	if (nextKeys.length) {
		truSeoHighlighterStore.armAdvance(nextKeys)
		// Drop the fixed issue's highlight right away so it isn't briefly shown as
		// still flagged while the rebuild is pending.
		truSeoHighlighterStore.pruneMarksForIssue(mark)
		truSeoHighlighterStore.requestPopoverHide()
	} else {
		truSeoHighlighterStore.dismissAfterFix(mark)
	}
}

const disableForThisPost = () => {
	if (truSeoHighlighterStore.highlightingEnabled) {
		truSeoHighlighterStore.toggleGlobalHighlighting()
	}

	truSeoHighlighterStore.clearAll()
}

const onManageClick = (event) => {
	const disableLink = event.target?.closest?.('[data-aioseo-highlight-disable]')
	if (!disableLink) {
		return
	}

	event.preventDefault()
	event.stopPropagation()

	disableForThisPost()
}

watch(
	() => truSeoHighlighterStore.hoveredMark,
	(mark) => {
		truSeoHighlighterStore.clearAddToSafeWordsError()
		// A fresh mark gets a fresh popover — never carry a previous mark's AI error
		// (it would also suppress this mark's "Suggest a fix" button).
		truSeoHighlighterStore.clearAiSuggestionsError()
		fixRefusal.value = null

		if (!mark?.sentence || !SPELLING_ANALYZERS.includes(mark.analyzer)) {
			return
		}

		truSeoHighlighterStore.fetchSpellingSuggestions(mark.sentence)
	},
	{ immediate: true }
)
</script>

<style lang="scss" scoped>
.tru-seo-highlight-popover {
	width: 100%;
	background-color: $white;
	border: 1px solid $border;
	border-radius: 8px;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
	box-sizing: border-box;
	font-family: $font-family;
	font-size: 13px;
	line-height: 1.5;
	overflow-wrap: break-word;
	padding: 12px 16px;
	position: relative;
	user-select: none;

	// WP's editor-region styles add a border-bottom to <a>; the popover underlines
	// its own links via text-decoration, so reset it to avoid a double underline.
	:deep(a) {
		border-bottom: 0;
	}

	&::before,
	&::after {
		content: '';
		height: 0;
		left: 50%;
		position: absolute;
		width: 0;
	}

	&[data-placement="above"] {
		&::before {
			border-color: $border transparent transparent transparent;
			border-style: solid;
			border-width: 7px 7px 0 7px;
			bottom: -7px;
			transform: translateX(-50%);
		}

		&::after {
			border-color: $white transparent transparent transparent;
			border-style: solid;
			border-width: 6px 6px 0 6px;
			bottom: -6px;
			transform: translateX(-50%);
		}
	}

	&[data-placement="below"] {
		&::before {
			border-color: transparent transparent $border transparent;
			border-style: solid;
			border-width: 0 7px 7px 7px;
			top: -7px;
			transform: translateX(-50%);
		}

		&::after {
			border-color: transparent transparent $white transparent;
			border-style: solid;
			border-width: 0 6px 6px 6px;
			top: -6px;
			transform: translateX(-50%);
		}
	}

	&__header {
		width: 100%;
		display: flex;
		flex-direction: column;

		&-content {
			width: 100%;
			align-items: center;
			display: flex;
			gap: 12px;
		}

		&-actions {
			align-items: center;
			display: flex;
			flex-shrink: 0;
			gap: 8px;
			margin-left: auto;
		}
	}

	&__pager {
		align-items: center;
		display: flex;
		flex-shrink: 0;
		gap: 2px;
	}

	&__pager-btn {
		align-items: center;
		background: none;
		border: none;
		border-radius: 4px;
		color: $black2;
		cursor: pointer;
		display: inline-flex;
		justify-content: center;
		padding: 2px;

		&:hover:not(:disabled) {
			background-color: $inline-background;
			color: $blue;
		}

		&:disabled {
			color: $placeholder-color;
			cursor: not-allowed;
		}
	}

	&__pager-icon {
		height: 16px;
		width: 16px;

		&--prev {
			transform: rotate(90deg);
		}

		&--next {
			transform: rotate(-90deg);
		}
	}

	&__pager-count {
		color: $placeholder-color;
		font-size: $font-sm;
		font-variant-numeric: tabular-nums;
		min-width: 28px;
		text-align: center;
		white-space: nowrap;
	}

	&__label {
		align-items: center;
		display: flex;
		gap: 8px;
		min-width: 0;
	}

	&__dot {
		border-radius: 50%;
		flex-shrink: 0;
		height: 8px;
		width: 8px;
	}

	&__title {
		color: $black;
		font-weight: $font-bold;
		min-width: 0;
		overflow-wrap: break-word;
		word-break: break-word;
	}

	&__suggest-fix {
		background: none;
		border: none;
		color: $blue;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		font-size: 13px;
		font-weight: $font-bold;
		gap: 6px;
		padding: 0;

		&:hover {
			color: $blue3;
		}

		&:disabled {
			color: $placeholder-color;
			cursor: not-allowed;
		}
	}

	&__suggest-fix-icon {
		height: 16px;
		width: 16px;
	}

	&__spinner {
		animation: tru-seo-popover-spin 0.8s linear infinite;
		border: 2px solid $border;
		border-radius: 50%;
		border-top-color: $black2;
		height: 16px;
		width: 16px;
	}

	&__description-body {
		align-items: flex-start;
		color: $placeholder-color;
		display: flex;
		font-size: $font-sm;
		gap: 6px;
		line-height: 1.5;
		overflow-wrap: break-word;
		white-space: normal;
		word-break: break-word;

		:deep(a) {
			color: $blue2;
			text-decoration: underline;
		}

		:deep(strong) {
			font-weight: $font-bold;
		}
	}

	&__busy-hint {
		align-items: flex-start;
		color: $orange3;
		display: flex;
		font-size: $font-sm;
		gap: 6px;
		line-height: 1.5;
		margin-top: 4px;

		&--inline {
			margin-top: 0;
			min-width: 0;
		}
	}

	&__busy-icon {
		animation: tru-seo-popover-spin 0.8s linear infinite;
		border: 1.5px solid currentColor;
		border-radius: 50%;
		border-right-color: transparent;
		box-sizing: border-box;
		flex-shrink: 0;
		height: 12px;
		margin-top: 3px;
		width: 12px;
	}

	&__error {
		align-items: center;
		color: $red;
		display: flex;
		font-size: $font-sm;
		gap: 8px;
		justify-content: space-between;
		margin-top: 8px;
	}

	&__retry {
		background: none;
		border: 1px solid $border;
		border-radius: 4px;
		color: $blue;
		cursor: pointer;
		font-size: $font-sm;
		padding: 2px 8px;

		&:hover {
			color: $blue3;
		}

		&:disabled {
			color: $placeholder-color;
			cursor: not-allowed;
		}
	}

	&__add-to-dictionary {
		display: inline-flex;
		align-items: center;
		gap: 5px;
		flex-shrink: 0;
		background-color: $inline-background;
		border: none;
		border-radius: 4px;
		color: $blue;
		cursor: pointer;
		font-size: 13px;
		font-weight: $font-bold;
		padding: 4px 10px;
		white-space: nowrap;

		&:hover {
			background-color: rgba(0, 90, 224, 0.12);
			color: $blue3;
		}

		&:disabled {
			background-color: $background;
			color: $placeholder-color;
			cursor: not-allowed;
		}
	}

	&__add-icon {
		width: 9px;
		height: 9px;
		flex-shrink: 0;
	}

	&__suggestions {
		display: flex;
		flex-direction: column;
		gap: 0;
		max-height: 260px;
		overflow-x: hidden;
		overflow-y: auto;
		overscroll-behavior: contain;
	}

	&__separator {
		border: none;
		border-top: 1px solid $border;
		margin: 8px 0;
	}

	&__suggestion {
		background: none;
		border: none;
		border-radius: 4px;
		color: $black;
		cursor: pointer;
		font-size: 13px;
		line-height: 1.5;
		max-width: 100%;
		min-width: 0;
		overflow-wrap: break-word;
		padding: 4px 8px;
		text-align: left;
		white-space: normal;
		word-break: break-word;

		&:hover {
			background-color: $inline-background;
		}

		&--ai {
			display: flex;
			flex-direction: column;
			gap: 2px;
			padding: 6px 8px;
		}

		& + & {
			border-top: 1px solid $background;
		}
	}

	&__suggestion-text {
		color: $black;
		overflow-wrap: break-word;
		word-break: break-word;
		white-space: normal;

		:deep(h2),
		:deep(h3) {
			color: $blue;
			font-size: $font-sm;
			font-weight: $font-bold;
			margin: 6px 0 2px;
			white-space: normal;
		}

		:deep(h2) {
			font-size: 13px;
		}

		:deep(p) {
			margin: 0;
			white-space: normal;

			& + p {
				margin-top: 6px;
			}
		}

		:deep(p:first-child) {
			margin-top: 0;
		}
	}

	&__suggestion-rationale {
		color: $placeholder-color;
		font-size: $font-sm;
		font-style: italic;
		overflow-wrap: break-word;
		word-break: break-word;
	}

	&__no-suggestions {
		color: $placeholder-color;
		font-style: italic;
		padding: 4px 8px;
	}

	&__hint {
		align-items: center;
		color: $placeholder-color;
		display: flex;
		font-size: $font-sm;
		gap: 6px;
	}

	&__hint-icon {
		flex-shrink: 0;
		height: 14px;
		width: 14px;
	}

	&__footer {
		display: flex;
		flex-direction: column;

		&-content {
			display: flex;
			align-items: center;
			gap: 16px;
			justify-content: flex-end;
		}
	}

	&__manage {
		display: flex;
		flex-direction: column;
	}

	&__manage-text {
		color: $placeholder-color;
		font-size: $font-sm;
		line-height: 1.5;

		:deep(a) {
			// main.scss's `.aioseo-app a` ties with this rule, so without the override the color
			// follows stylesheet load order (blue in the block editor, grey in WPBakery).
			color: $blue !important;
			font-weight: inherit;
			text-decoration: underline;
			cursor: pointer;

			&:hover {
				text-decoration: none;
			}
		}
	}

	&__regenerate {
		align-items: center;
		background: none;
		border: none;
		color: $blue;
		cursor: pointer;
		display: inline-flex;
		font-size: $font-sm;
		font-weight: $font-bold;
		flex-shrink: 0;
		gap: 6px;
		margin-left: auto;
		padding: 2px 4px;

		&:hover {
			color: $blue3;
		}

		&:disabled {
			color: $placeholder-color;
			cursor: not-allowed;
		}
	}

	&__regenerate-icon {
		height: 14px;
		width: 14px;
	}
}

@keyframes tru-seo-popover-spin {
	to {
		transform: rotate(360deg);
	}
}
</style>

<style lang="scss">
.aioseo-highlight-popover-root {
	z-index: 100000 !important;
	width: 100%;
	max-width: 420px;
}
</style>