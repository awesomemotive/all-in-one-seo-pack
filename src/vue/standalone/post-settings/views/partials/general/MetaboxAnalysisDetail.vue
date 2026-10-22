<template>
	<div class="aioseo-analysis-detail">
		<core-alert
			v-if="isBlockCodeEditor"
			type="yellow"
		>
			{{ strings.switchToVisualEditor }}
		</core-alert>

		<template v-else>
			<div
				v-if="issueItems.length"
				class="analysis-section"
			>
				<div
					v-if="!hideIssuesHeader"
					class="section-header section-header--issues"
				>
					<svg-circle-exclamation width="16" />

					<span class="section-header__title">{{ strings.issues }} ({{ issueItems.length }})</span>
				</div>

				<div class="section-content section-content--issues">
					<template
						v-for="(item, index) in issueItems"
						:key="index"
					>
						<div class="analysis-item">
							<div class="analysis-item__content">
								<div class="analysis-item__header">
									<template v-if="showHighlightControls">
										<template v-if="getInstanceCount(item) > 0">
											<tru-seo-toggle-highlighter
												:analyzer="item.key"
											/>
										</template>

										<template v-else>
											<core-tooltip
												placement="right"
												class="analysis-item__not-highlightable"
											>
												<svg-circle-question-mark width="16" />

												<template #tooltip>
													{{ strings.notHighlightable }}
												</template>
											</core-tooltip>
										</template>
									</template>

									<template v-else>
										<span
											class="analysis-item__color-dot"
											:style="{ backgroundColor: '#e8730c' }"
										/>
									</template>

									<span class="analysis-item__title">{{ item.title }}</span>

									<button
										v-if="showHighlightControls && getInstanceCount(item) > 0 && isCountClickable(item)"
										type="button"
										class="analysis-item__count analysis-item__count--link"
										:aria-label="strings.scrollToFirstInstance(item.title)"
										@click="scrollToFirstInstance(item.key)"
									>
										<span class="analysis-item__count-label">
											{{ strings.instancesFound(getInstanceCount(item)) }}
										</span>

										<svg-right-arrow-short class="analysis-item__count-arrow" />
									</button>

									<span
										v-else-if="showHighlightControls && getInstanceCount(item) > 0"
										class="analysis-item__count"
									>
										{{ strings.instancesFound(getInstanceCount(item)) }}
									</span>

								</div>

								<hidden-in-view-note
									class="analysis-item__hidden-in-view"
									:count="getHiddenCount(item)"
								/>

								<p class="analysis-item__description">{{ item.text }}</p>
							</div>
						</div>

						<div
							v-if="index < issueItems.length - 1"
							class="analysis-divider"
						/>
					</template>
				</div>
			</div>

			<div
				v-if="goodItems.length && !hideGood && issueItems.length"
				class="analysis-section"
			>
				<button
					type="button"
					class="section-header section-header--good"
					:class="{ 'section-header--collapsed': goodCollapsed }"
					:aria-expanded="!goodCollapsed"
					aria-controls="aioseo-analysis-good-content"
					@click="goodCollapsed = !goodCollapsed"
				>
					<svg-circle-check width="16" />

					<span class="section-header__title">{{ strings.good }} ({{ goodItems.length }})</span>

					<svg-caret class="section-header__caret" />
				</button>

				<transition-slide
					:active="!goodCollapsed"
					:duration="300"
					id="aioseo-analysis-good-content"
					class="section-content section-content--good"
				>
					<div class="section-content__inner">
						<template
							v-for="(item, index) in goodItems"
							:key="index"
						>
							<div class="analysis-item">
								<div class="analysis-item__content">
									<div class="analysis-item__header">
										<span class="analysis-item__color-dot analysis-item__color-dot--good" />

										<span class="analysis-item__title">{{ item.title }}</span>
									</div>

									<p class="analysis-item__description">{{ item.text }}</p>
								</div>
							</div>

							<div
								v-if="index < goodItems.length - 1"
								class="analysis-divider"
							/>
						</template>
					</div>
				</transition-slide>
			</div>

			<div
				v-if="!issueItems.length && goodItems.length"
				class="analysis-all-good"
			>
				<svg-circle-check width="16" />

				<span>{{ strings.allGood }}</span>
			</div>
		</template>
	</div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { __, sprintf } from '@/vue/plugins/translations'
import { useEditorMode } from '@/vue/composables/EditorMode'

import {
	useTruSeoHighlighterStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgRightArrowShort from '@/vue/components/common/svg/right-arrow/Short'
import TransitionSlide from '@/vue/components/common/transition/Slide'
import HiddenInViewNote from './tru-seo/HiddenInViewNote'
import TruSeoToggleHighlighter from './tru-seo/ToggleHighlighter'
import { isBadResult } from '@/app/tru-seo/scoring/interpreters'
import { announceEditorJump, findPageBuilderUnitNode, getEditorStickyOffset, getVisibleAnchor, revealInEditor } from '@/vue/plugins/tru-seo/highlighter/spellingJump'
import { getFormatClassName } from '@/vue/plugins/tru-seo/highlighter/wpDataStore'
import { countLocatableSentences, countHiddenSentences } from '@/vue/plugins/tru-seo/highlighter/sentences'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'

const td      = import.meta.env.VITE_TEXTDOMAIN
const strings = {
	switchToVisualEditor : __('TruSEO can\'t analyze your post in the Code Editor. Switch to the Visual Editor to see your results.', td),
	issues               : __('Improvements', td),
	good                 : __('Good', td),
	allGood              : __('Everything looks good — no improvements needed.', td),
	// Translators: Tooltip text explaining that this assessment cannot be highlighted in the editor.
	notHighlightable     : __('This check looks at your whole post, so there\'s no specific text to highlight.', td),
	instancesFound       : (count) => sprintf(
		// Translators: 1 - The number of instances found.
		__('%1$d found', td),
		count
	),
	scrollToFirstInstance : (title) => sprintf(
		// Translators: 1 - The title of the readability improvement.
		__('Jump to the first %1$s improvement in the editor', td),
		title
	)
}

const truSeoHighlighterStore = useTruSeoHighlighterStore()

const { isBlockCodeEditor } = useEditorMode()

const props = defineProps({
	analysisItems : {
		type : Object
	},
	tab : {
		type    : String,
		default : 'basic'
	},
	hideGood : {
		type    : Boolean,
		default : false
	},
	hideIssuesHeader : {
		type    : Boolean,
		default : false
	}
})

const isReadabilityTab = computed(() => 'readability' === props.tab)

// The controls only mean something where marks paint: `highlightingActive` is the store's
// `enabled` and the user's toggle. Not `isPageBuilderEditor()`, which would hide them on builders
// that paint into their own canvas.
const showHighlightControls = computed(() =>
	isReadabilityTab.value &&
	truSeoHighlighterStore.highlightingActive
)

const goodCollapsed = ref(true)

const issueItems = computed(() => {
	if (!props.analysisItems) {
		return []
	}

	return Object.entries(props.analysisItems)
		.filter(([ , item ]) => item.title && isBadResult(item.score))
		.map(([ key, item ]) => ({ ...item, key }))
})

const goodItems = computed(() => {
	if (!props.analysisItems) {
		return []
	}
	return Object.entries(props.analysisItems)
		.filter(([ , item ]) => item.title && !isBadResult(item.score))
		.map(([ key, item ]) => ({ ...item, key }))
})

// Auto-open the Good section only when every check passes (no improvements), so
// an all-green tab isn't just a collapsed bar. Fires on the all-good transition,
// so it doesn't fight a manual toggle while issues remain.
watch(
	() => 0 === issueItems.value.length && 0 < goodItems.value.length,
	(allGood) => {
		goodCollapsed.value = !allGood
	},
	{ immediate: true }
)

// Also needs a mark to jump to: a builder analyzes text it can't highlight (button labels, tab
// titles), so a check can count instances that never paint.
const isCountClickable = (item) => {
	return truSeoHighlighterStore.highlightingEnabled &&
		truSeoHighlighterStore.highlightAnalyzers.includes(item.key) &&
		truSeoHighlighterStore.highlightMarks.some(hm => hm.analyzer === item.key)
}

// "N found" counts the marks the flagged sentences get, through the rule that builds them, so it
// equals the popover's total. It must not depend on the highlight checkbox or the painted marks, or
// the number changes between the checked and unchecked states and flickers as marks paint in.
const instanceCounts = computed(() => {
	const adapter = resolveAdapter()
	const units   = adapter?.getContentUnits?.() || []
	const counts  = new Map()

	// An editor not ready yet has nothing to match, which is not nothing found. A ready one can
	// have no units at all, e.g. a block post that is a single Classic block shown as a preview.
	const canMatch = 'function' === typeof adapter?.isReady ? adapter.isReady() : 0 < units.length

	for (const item of issueItems.value) {
		const sentences = item.highlightSentences || []

		counts.set(item.key, canMatch ? countLocatableSentences(sentences, units) : sentences.length)
	}

	return counts
})

const getInstanceCount = (item) => instanceCounts.value.get(item.key) || 0

// How many of those instances are in text the editor's view doesn't show, so they never paint.
// Recomputed with the issues, like the counts above.
const hiddenCounts = computed(() => {
	const units  = resolveAdapter()?.getContentUnits?.() || []
	const counts = new Map()

	if (units.some(unit => unit.hiddenInView)) {
		for (const item of issueItems.value) {
			counts.set(item.key, countHiddenSentences(item.highlightSentences || [], units))
		}
	}

	return counts
})

const getHiddenCount = (item) => hiddenCounts.value.get(item.key) || 0

// Resolves a highlight mark's DOM node, falling back to a live DOM query when
// `pollForBlockHighlightNodes` hasn't linked nodes yet (race window right
// after toggling highlights on or after a locale switch).
const findScrollTarget = (analyzerKey) => {
	const marks = truSeoHighlighterStore.highlightMarks.filter(hm => hm.analyzer === analyzerKey)
	const className = getFormatClassName(analyzerKey)

	// Prefer a store-tracked node still attached to the DOM. Stored refs go stale
	// when the editor re-renders a block, and scrollIntoView on a detached node no-ops.
	const live = marks.find(hm => hm.node && hm.node.isConnected)
	if (live) {
		return live.node
	}

	// Re-query the current <mark> from a still-attached parent block.
	for (const hm of marks) {
		if (!hm.parent || !hm.parent.isConnected) {
			continue
		}

		const candidate = hm.parent.querySelector(`mark.${className}`)
		if (candidate) {
			return candidate
		}
	}

	// Store refs are frequently left unlinked (node/parent null) even though the
	// <mark> elements are painted, so query the live editor DOM directly. The block
	// editor renders inside an iframe; the classic editor renders in the main document.
	const docs = [ document ]
	const canvas = document.querySelector('iframe[name="editor-canvas"]')
	if (canvas && canvas.contentDocument) {
		docs.push(canvas.contentDocument)
	}

	for (const doc of docs) {
		const liveMark = doc.querySelector(`mark.${className}`)
		if (liveMark) {
			return liveMark
		}
	}

	// A builder can leave a mark that neither painted nor kept its `parent`, so re-resolve the
	// unit id through the adapter. Last and gated on ownership, as in
	// {@see spellingJump.js:getEditorContentRoots}.
	const unitNode = findPageBuilderUnitNode(marks.map(hm => hm.unitId).filter(Boolean))
	if (unitNode) {
		return unitNode
	}

	const withParent = marks.find(hm => hm.parent && hm.parent.isConnected)
	return withParent?.parent || null
}

// Extra top gap so a jump target isn't hidden behind (or flush against) the
// editor's sticky chrome. The Classic editor pins its media/Visual-Code bar and
// format toolbar over the content, so add their heights (stable whether or not
// currently pinned); the Block editor's canvas already renders below its fixed
// header, so the trailing gap alone suffices there. A builder canvas adds the theme
// header it renders fixed inside itself. The fixed WP admin bar is handled
// separately by WP's `scroll-padding-top` on the document.
const getStickyEditorOffset = () => {
	let height = 0
	for (const selector of [ '.wp-editor-tools', '.mce-toolbar-grp' ]) {
		const el = document.querySelector(selector)
		if (el) {
			height += el.getBoundingClientRect().height
		}
	}

	return Math.round(height) + getEditorStickyOffset() + 16
}

const scrollToFirstInstance = (analyzerKey) => {
	const node = findScrollTarget(analyzerKey)
	if (!node) {
		return
	}

	// `scrollIntoView` crosses the same-origin editor-iframe boundary to scroll the
	// main window, and WP's `scroll-padding-top` already clears the fixed admin bar.
	// The Classic editor's TinyMCE toolbar sticks over the content though, and
	// scroll-padding doesn't know about it — so offset the target by the toolbar's
	// live height, or `block: 'start'` lands the mark hidden just beneath it.
	revealInEditor(node)

	const target = getVisibleAnchor(node)
	target.style.scrollMarginTop = `${getStickyEditorOffset()}px`
	target.scrollIntoView({ behavior: 'smooth', block: 'start' })

	announceEditorJump(target)
}

</script>

<style lang="scss">
.aioseo-app .aioseo-post-general .aioseo-analysis-detail {
	display: flex;
	flex-direction: column;
	gap: 20px;
	padding: 12px 20px;
	font-size: 14px;
	line-height: 22px;
	margin: 0;

	.aioseo-alert {
		margin: 0;
	}

	.analysis-section {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	.section-header {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 12px 8px;
		border-radius: 4px;
		flex-wrap: wrap;

		&--issues {
			background-color: #fff4e5;

			svg {
				color: #e8730c;
			}

			.section-header__title {
				color: #e8730c;
				font-weight: 700;
			}
		}

		&--good {
			background-color: #ecfdf5;
			border: 0;
			cursor: pointer;
			font: inherit;
			text-align: left;
			width: 100%;

			svg {
				color: #00aa63;
			}

			.section-header__title {
				color: #00aa63;
				font-weight: 700;
			}

			.section-header__caret {
				margin-left: auto;
				transition: transform 0.2s ease;
				width: 22px;
			}

			&.section-header--collapsed .section-header__caret {
				transform: rotate(-90deg);
			}
		}

		&__title {
			font-size: 14px;
			line-height: 22px;
		}
	}

	.section-content {
		display: flex;
		flex-direction: column;

		&--issues {
			.analysis-item__color-dot {
				opacity: 1;
			}
		}

		// The height slide is handled by <transition-slide>; this carries the
		// items' column layout.
		&__inner {
			display: flex;
			flex-direction: column;
		}
	}

	.analysis-item {
		display: flex;
		gap: 20px;
		padding-left: 20px;
		align-items: flex-start;

		&__content {
			display: flex;
			flex: 1;
			flex-direction: column;
			gap: 4px;
		}

		&__header {
			display: flex;
			align-items: center;
			gap: 8px;
		}

		&__color-dot {
			border-radius: 50%;
			display: inline-block;
			flex-shrink: 0;
			height: 8px;
			width: 8px;

			&--good {
				background-color: $green;
			}
		}

		&__title {
			font-weight: 700;
			color: #141b38;
			font-size: 14px;
			line-height: 22px;
		}

		&__count {
			color: $blue;
			font-size: 14px;
			font-weight: 400;
			line-height: 22px;
			margin-left: auto;

			&--link {
				background: transparent;
				border: 0;
				cursor: pointer;
				display: inline-flex;
				align-items: center;
				min-height: 24px;
				font: inherit;
				padding: 0;

				.analysis-item__count-label {
					text-decoration: underline;
				}

				&:hover .analysis-item__count-label,
				&:focus-visible .analysis-item__count-label {
					text-decoration: none;
				}
			}
		}

		&__count-arrow {
			color: inherit;
			display: inline-block;
			flex-shrink: 0;
			height: 12px;
			margin-left: 4px;
			transform: rotate(-90deg);
			vertical-align: middle;
			width: 12px;
		}

		&__not-highlightable {
			display: flex;
			flex-shrink: 0;
			margin: 0;

			svg {
				color: #e8730c !important;
			}
		}

		&__hidden-in-view {
			padding-left: 16px;
		}

		&__description {
			margin: 0;
			padding-left: 16px;
			color: #141b38;
			font-size: 14px;
			line-height: 22px;
			font-weight: 400;
		}

		&__manage-dictionary {
			display: inline-flex;
			align-items: center;
			min-height: 24px;
			gap: 4px;
			width: fit-content;
			margin: 4px 0 0 16px;
			padding: 0;
			border: 0;
			background: none;
			font: inherit;
			font-size: 13px;
			font-weight: $font-bold;
			color: $blue;
			cursor: pointer;
			text-align: left;

			&:hover {
				text-decoration: underline;
			}

			svg {
				width: 11px;
				height: 11px;
				flex-shrink: 0;
			}
		}

		.aioseo-tooltip {
			display: flex;
			margin: 0;
			color: #8c8f9a;

			svg {
				color: inherit;
			}
		}
	}

	.analysis-divider {
		height: 1px;
		background-color: #dcdde1;
		margin: 12px 0;
	}

	.analysis-all-good {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 12px 14px;
		border-radius: 6px;
		background-color: #ecfdf5;
		color: #00aa63;
		font-size: 13px;
		font-weight: 600;
		line-height: 1.4;

		svg {
			flex-shrink: 0;
			color: #00aa63;
		}
	}
}

.aioseo-app .aioseo-post-general.sidebar {
	.aioseo-analysis-detail {
		padding: 0;
	}

	.section-header--issues {
		padding: 8px;
	}

	.section-header__title {
		font-size: 13px;
	}

	.section-header__toggle-label {
		&--full {
			display: none;
		}

		&--short {
			display: inline;
		}
	}

	.analysis-item {
		padding: 0 !important;
		flex-direction: column;

		// Let the header wrap so the "N found" jump link never overflows the narrow sidebar.
		&__header {
			flex-wrap: wrap;
		}

		&__count {
			font-size: 13px;
		}

		&__title {
			font-size: 13px;
		}

		&__hidden-in-view {
			padding: 0;
		}

		&__description {
			padding: 0;
			font-size: 13px;
		}
	}
}
</style>