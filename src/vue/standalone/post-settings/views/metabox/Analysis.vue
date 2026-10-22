<template>
	<div
		id="aioseo-post-content-analysis"
		class="aioseo-tab-content aioseo-post-general"
	>
		<core-card
			v-if="displayContentAnalysisCard"
			slug="postSettingsGeneralContentAnalysis"
			:header-text="strings.contentAnalysis"
			class="content-analysis-card"
			:toggles="false"
			no-slide
		>
			<template #header>
				<span>{{ strings.contentAnalysis }}</span>

				<core-tooltip
					v-if="isTruSeoDataReady"
					:placement="'bottom'"
				>
					<base-score-badge
						:score="postEditorStore.currentPost.seo_score"
						:loading="postEditorStore.currentPost.loading.score"
					/>

					<template #tooltip>
						{{ strings.seoScoreTooltip }}
					</template>
				</core-tooltip>
			</template>

			<template #header-extra>
				<div
					v-if="displayTruSeoMetaboxCard"
					class="content-analysis-header-extra"
					@click.stop
				>
					<div
						v-if="showOptimizePost"
						class="aioseo-optimize-post"
					>
						<base-button
							class="aioseo-optimize-post-button"
							size="small"
							type="blue"
							:loading="truSeoHighlighterStore.optimizingPost"
							:disabled="truSeoHighlighterStore.optimizingPost"
							@click.stop="truSeoHighlighterStore.openOptimizeModal()"
						>
							<svg-ai-content
								v-if="!truSeoHighlighterStore.optimizingPost"
								width="16"
								height="16"
							/>
							{{ strings.optimizePost }}
						</base-button>
					</div>

					<tru-seo-highlight-control variant="pill" />

					<tru-seo-locale-control variant="pill" />
				</div>
			</template>

			<template #before-tabs>
				<page-builder-notice />
			</template>

			<template #tabs>
				<content-analysis-tabs
					v-model="activeTab"
					:tabs="tabs"
				>
					<template #readability>
						<span
							v-if="isTruSeoDataReady"
							class="content-analysis-tab-pill"
							:class="getReadabilityScoreClass(readabilityScore)"
						>
							{{ readabilityScore }}
						</span>
					</template>

					<template #headline>
						<span
							v-if="null !== headlineScore"
							class="content-analysis-tab-pill"
							:class="getHeadlineScoreClass(headlineScore)"
						>
							{{ headlineScore }}
						</span>
					</template>

					<template #basics>
						<span
							v-if="hasBasicData && getTabErrorsCount('basic')"
							class="content-analysis-tab-issues"
							:aria-label="issueCountLabel(getTabErrorsCount('basic'))"
						>
							<span class="content-analysis-tab-issues__dot" />

							{{ getTabErrorsCount('basic') }}
						</span>

						<span
							v-else-if="hasBasicData"
							class="content-analysis-tab-issues content-analysis-tab-issues--good"
							:aria-label="strings.basicsAllGood"
						>
							<span class="content-analysis-tab-issues__dot" />
						</span>
					</template>

					<template #spelling>
						<span
							v-if="hasBasicData && getSpellingCount()"
							class="content-analysis-tab-issues content-analysis-tab-issues--red"
							:aria-label="misspellingCountLabel(getSpellingCount())"
						>
							<span class="content-analysis-tab-issues__dot" />

							{{ getSpellingCount() }}
						</span>

						<span
							v-else-if="hasBasicData && spellingAnalysisRan"
							class="content-analysis-tab-issues content-analysis-tab-issues--good"
							:aria-label="strings.spellingAllGood"
						>
							<span class="content-analysis-tab-issues__dot" />
						</span>
					</template>
				</content-analysis-tabs>

				<transition
					name="route-fade"
					mode="out-in"
				>
					<div
						:key="activeTab"
						class="content-analysis-panels"
					>
						<div
							v-if="'keywords' === activeTab"
							class="content-analysis-panel content-analysis-panel--keywords"
						>
							<p class="content-analysis-panel__description">{{ strings.keywordsDescription }}</p>

							<hr class="content-analysis-panel__divider" />

							<div v-if="optionsStore.options.searchAppearance.advanced.useKeywords && optionsStore.options.searchAppearance.advanced.keywordsLooking">
								<core-alert
									class="meta-keywords-alert"
									type="blue"
									show-close
									@close-alert="hideKeywordsLooking"
								>
									{{ strings.lookingForMetaKeywords }}

									<a
										href="#"
										@click.prevent="$emit('changeTab', 'advanced')"
									>
										{{ strings.goToAdvancedTab }}
									</a>

									<a
										class="no-underline"
										href="#"
										@click.prevent="$emit('changeTab', 'advanced')"
									>
										→
									</a>
								</core-alert>
							</div>

							<keywords-table />
						</div>

						<div
							v-else-if="'basics' === activeTab"
							class="content-analysis-panel content-analysis-panel--basics seo-analysis-card"
						>
							<seo-analysis v-if="hasBasicData" />

							<p
								v-else
								class="content-analysis-empty"
							>
								{{ strings.analysisEmpty }}
							</p>
						</div>

						<div
							v-else-if="'readability' === activeTab"
							class="content-analysis-panel content-analysis-panel--readability readability-analysis-card"
						>
							<readability-analysis v-if="hasReadabilityData" />

							<p
								v-else
								class="content-analysis-empty"
							>
								{{ strings.analysisEmpty }}
							</p>
						</div>

						<div
							v-else-if="headlineAnalyzerEnabled && 'headline' === activeTab"
							class="content-analysis-panel content-analysis-panel--headline aioseo-headline-analyzer"
						>
							<p class="content-analysis-panel__description">{{ strings.headlineDescription }}</p>

							<hr class="content-analysis-panel__divider" />

							<headline-analysis />
						</div>

						<div
							v-else-if="spellingTabVisible && 'spelling' === activeTab"
							class="content-analysis-panel content-analysis-panel--spelling"
						>
							<spelling-analysis
								:show-manage-dictionary="canManageDictionary"
								@manage-dictionary="truSeoHighlighterStore.openSafeWordsModal()"
							/>
						</div>
					</div>
				</transition>
			</template>
		</core-card>
	</div>
</template>

<script setup>
import { ref, computed, watch, defineEmits, onMounted, onBeforeUnmount } from 'vue'

import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { __, _n, sprintf } from '@/vue/plugins/translations'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { merge } from 'lodash-es'
import { useHeadlineAnalyzer } from '@/vue/composables/HeadlineAnalyzer'
import { useTruSeoScore } from '@/vue/composables/TruSeoScore'
import { useTruSeoLocale } from '../../composables/TruSeoLocale'
import { truSeoShouldAnalyze, supportsPageAnalysis } from '@/vue/plugins/tru-seo/components/helpers'
import { isOptimizeSupported } from '@/vue/plugins/tru-seo/optimize-post/editorAdapter'
import { isAnalyzeCapableEditor } from '@/vue/plugins/tru-seo/editors'

import BaseButton from '@/vue/components/common/base/Button'
import BaseScoreBadge from '@/vue/components/common/base/ScoreBadge'
import ContentAnalysisTabs from '../partials/general/tru-seo/ContentAnalysisTabs'
import HeadlineAnalysis from '../partials/general/tru-seo/HeadlineAnalysis'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import KeywordsTable from '../partials/general/KeywordsTable'
import PageBuilderNotice from '../partials/general/tru-seo/PageBuilderNotice'
import ReadabilityAnalysis from '../partials/general/tru-seo/ReadabilityAnalysis'
import SeoAnalysis from '../partials/general/tru-seo/SeoAnalysis'
import SpellingAnalysis from '../partials/general/tru-seo/SpellingAnalysis'
import TruSeoLocaleControl from '../partials/general/TruSeoLocaleControl'
import TruSeoHighlightControl from '../partials/general/TruSeoHighlightControl'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'

defineEmits([ 'changeTab' ])

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	disabled : {
		type : Boolean,
		default () {
			return false
		}
	},
	parentComponentContext : String
})

const optionsStore           = useOptionsStore()
const postEditorStore        = usePostEditorStore()
const rootStore              = useRootStore()
const truSeoHighlighterStore = useTruSeoHighlighterStore()

const selectedKeyphrase = ref(0)

// Whole-post optimize is unavailable in page-builder contexts, and in the classic editor only on
// the Visual tab (not the Text/HTML tab). Kept reactive so toggling the classic Visual/Text tab
// — which mutates the class on #wp-content-wrap — correctly shows/hides the button.
const optimizeSupported = ref(isOptimizeSupported())
let editorModeObserver = null

const {
	getReadabilityScoreClass,
	hasEnoughContent,
	readabilityScore,
	strings : composableStrings
} = useTruSeoScore()
const {
	ensureHeadlineScore,
	getHeadlineScoreClass,
	headlineAnalyzerEnabled,
	headlineScore
} = useHeadlineAnalyzer()
const { getSelectedOption } = useTruSeoLocale()

const strings = merge(composableStrings, {
	contentAnalysis        : __('Content Optimization', td),
	contentAnalysisTooltip : __('Analyze your content for keyword usage, readability and on-page SEO best practices.', td),
	keywordsAnalysis       : __('Keywords', td),
	basicsTitle            : __('Basics', td),
	readabilityTitle       : __('Readability', td),
	headlineTitle          : __('Headline', td),
	headlineDescription    : __('This score rates how likely your headline is to earn clicks and shares. Aim for 70 or above — 40–69 has room to improve, and below 40 needs work.', td),
	spellingTitle          : __('Spelling', td),
	manageDictionary       : __('Manage Dictionary', td),
	analysisEmpty          : __('You must first add some content to the page before it can be analyzed.', td),
	basicsAllGood          : __('No improvements needed', td),
	spellingAllGood        : __('No misspellings found', td),
	optimizePost           : __('Optimize with AI', td),
	lookingForMetaKeywords : __('Looking for meta keywords?', td),
	goToAdvancedTab        : __('Go to the Advanced tab to add/edit meta keywords', td)
})

const spellCheckerEnabled = computed(() => !!window.aioseo?.spellChecker?.enabled && isAnalyzeCapableEditor())

// Also require a dictionary for the selected analysis language — some supported
// languages (e.g. Arabic, Japanese) have no spell-check dictionary.
const spellingTabVisible = computed(() => spellCheckerEnabled.value && !!getSelectedOption()?.hasSpellChecker)

const tabs = computed(() => {
	const list = []

	// TruSEO sub-tabs only apply when TruSEO analysis is on; with only the Headline
	// Analyzer enabled, the card shows just the Headline tab.
	if (truSeoShouldAnalyze()) {
		list.push({ slug: 'keywords', label: strings.keywordsAnalysis })
		list.push({ slug: 'basics', label: strings.basicsTitle })

		if (spellingTabVisible.value) {
			list.push({ slug: 'spelling', label: strings.spellingTitle })
		}

		// Readability assumes prose with paragraphs and subheadings. A term description is a
		// single short block, so the assessments return misleading passes.
		if ('term' !== postEditorStore.currentPost.context) {
			list.push({ slug: 'readability', label: strings.readabilityTitle })
		}
	}

	if (headlineAnalyzerEnabled.value) {
		list.push({ slug: 'headline', label: strings.headlineTitle })
	}

	return list
})

const activeTab = ref(tabs.value[0]?.slug || 'keywords')

// Sub-tabs can disappear when their feature or language gate flips (e.g. TruSEO
// off leaves only Headline); fall back to the first tab so the card never shows
// a blank panel. Guard the empty list — the card itself is hidden then.
watch(tabs, (list) => {
	if (list.length && !list.some(tab => tab.slug === activeTab.value)) {
		activeTab.value = list[0].slug
	}
})

const countIssues = (items) => {
	return Object.values(items || {}).filter(item => 7 > item?.score && '' !== item?.title).length
}

const getTabErrorsCount = (tab) => {
	if ('keywords' === tab) {
		let count = countIssues(postEditorStore.truseoData?.truseo?.focus_keyword?.items)

		postEditorStore.truseoData?.additionalKeywords?.forEach(keyword => {
			count += countIssues(keyword?.items)
		})

		return count
	}

	return countIssues(postEditorStore.truseoData?.truseo?.general?.[tab])
}

const issueCountLabel = (count) => sprintf(
	// Translators: %1$d - The number of improvements found.
	_n('%1$d improvement', '%1$d improvements', count, td),
	count
)

const misspellingCountLabel = (count) => sprintf(
	// Translators: %1$d - The number of misspellings found.
	_n('%1$d misspelling', '%1$d misspellings', count, td),
	count
)

// Counts distinct misspelled words (not total occurrences) so the tab badge
// matches the per-word rows shown in the Spelling panel.
const getSpellingCount = () => {
	const sentences = postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker?.highlightSentences || []

	return new Set(sentences.map(s => (s || '').trim()).filter(Boolean)).size
}

// The spelling assessment has run (key exists whether the result is issue or good),
// so a zero count means "all clear" rather than "not analyzed yet".
const spellingAnalysisRan = computed(() => !!postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker)

// Shown only when:
// - The user can manage the dictionary (same cap as the REST routes).
// - The spell checker is enabled site-wide (Settings → Advanced).
// - The spelling assessment has run (key exists in spelling results,
//   whether the score is "issue" or "good").
const canManageDictionary = computed(() => {
	if (!allowed('aioseo_page_analysis')) {
		return false
	}

	if (!spellCheckerEnabled.value) {
		return false
	}

	return !!postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker
})

// Base gate shared by TruSEO and the Headline Analyzer: a real post (not a term),
// outside the snippet-editor modal, with permission, and not a bbPress forum type.
const canShowAnalysisCard = computed(() => {
	// Terms are allowed through on the strength of `supportsPageAnalysis`, which PHP only sets
	// for TruSEO-eligible taxonomies.
	const isAnalyzableContext = [ 'post', 'term' ].includes(postEditorStore.currentPost.context)

	return isAnalyzableContext && 'modal' !== props.parentComponentContext && allowed('aioseo_page_analysis') && !isForum.value
})

const displayTruSeoMetaboxCard = computed(() => canShowAnalysisCard.value && truSeoShouldAnalyze())

// The card also hosts the Headline Analyzer, so render it whenever either feature
// is on — but only on page types that support analysis. TruSEO-specific
// chrome/sub-tabs still gate on displayTruSeoMetaboxCard.
const displayContentAnalysisCard = computed(() => canShowAnalysisCard.value && supportsPageAnalysis() && (truSeoShouldAnalyze() || headlineAnalyzerEnabled.value))

const isTruSeoDataReady = computed(() => {
	// Too little content to analyze reads as "not ready", so the whole card (score
	// badge, tab pills, Basics/Spelling, Optimize) falls back to the empty state.
	if (!hasEnoughContent.value) {
		return false
	}

	const basic = postEditorStore.truseoData?.truseo?.general?.basic
	if (!basic || 'object' !== typeof basic) {
		return false
	}

	// Verify that there are actual analysis items with valid data
	const basicResults = Object.values(basic)
	return 0 < basicResults.length && basicResults.some(item => item && item.title)
})

// Whether the Basics/Readability groups have analysis items yet. The card is
// always shown (so Keywords is reachable), but these tabs render an empty
// state until the post has content to analyze.
const hasBasicData = computed(() => isTruSeoDataReady.value)

// Auto-Optimize requires a focus keyword — the server optimizes against it. The
// live value is reactive Pinia state (same source the SERP preview reads above).

// Optimize also needs AI Content access — the generate call is gated on it, so
// hide the button (rather than 403 on click) when the user lacks that cap.
const showOptimizePost = computed(() =>
	isTruSeoDataReady.value &&
	optimizeSupported.value &&
	allowed('aioseo_page_ai_content_settings')
)

const hasReadabilityData = computed(() => {
	if (!hasEnoughContent.value) {
		return false
	}

	const readability = postEditorStore.truseoData?.truseo?.general?.readability
	if (!readability || 'object' !== typeof readability) {
		return false
	}

	return Object.values(readability).some(item => item && item.title)
})

const isForum = computed(() => {
	return rootStore.aioseo.data.isBBPressActive &&
		(
			'forum' === postEditorStore.currentPost.postType ||
			'topic' === postEditorStore.currentPost.postType ||
			'reply' === postEditorStore.currentPost.postType
		)
})

const hideKeywordsLooking = () => {
	optionsStore.options.searchAppearance.advanced.keywordsLooking = false
	optionsStore.saveChanges()
}

onMounted(() => {
	if ('post' === postEditorStore.currentPost.context && postEditorStore.truseoData?.focusKeyword && !postEditorStore.truseoData?.additionalKeywords?.length) {
		selectedKeyphrase.value = -1
	}

	ensureHeadlineScore()

	// isOptimizeSupported() reads the DOM, so it is not reactive on its own. The classic editor's
	// Visual/Text toggle flips the class on #wp-content-wrap client-side; observe it and re-evaluate.
	const contentWrap = document.getElementById('wp-content-wrap')
	if (contentWrap && 'undefined' !== typeof MutationObserver) {
		editorModeObserver = new MutationObserver(() => {
			optimizeSupported.value = isOptimizeSupported()
		})
		editorModeObserver.observe(contentWrap, { attributes: true, attributeFilter: [ 'class' ] })
	}
})

onBeforeUnmount(() => {
	if (editorModeObserver) {
		editorModeObserver.disconnect()
		editorModeObserver = null
	}
})
</script>
<style lang="scss">
.content-analysis-card {
	// The page-builder notice fills the before-tabs slot, which the card always
	// wraps in a padded .content div — even when the notice renders nothing (any
	// non-page-builder post). Collapse the empty wrapper so it doesn't leave a
	// blank band above the tabs. :empty ignores the notice's v-if comment node.
	.content:empty {
		display: none;
	}

	// When the notice does render, the wrapper's bottom padding stacks with the
	// tab bar's own top padding, leaving a wide gap. Drop it so the notice sits
	// snug above the tabs. Scoped via :has so the header locale control's own
	// card .content isn't affected.
	.content:has(> .aioseo-page-builder-notice) {
		padding-bottom: 0;
	}

	.header .header-extra {
		margin-left: auto;
	}

	.content-analysis-header-extra {
		align-items: center;
		display: flex;
		gap: 12px;

		// Match the score badge and language control to the same height.
		.score-badge-text.is-bordered,
		.aioseo-truseo-locale-trigger {
			box-sizing: border-box;
			height: 34px;
		}

		.score-badge-text.is-bordered {
			display: inline-flex;
			align-items: center;
		}
	}

	.aioseo-optimize-post {
		display: flex;
		align-items: center;

		// The tooltip wrapper has a default left margin; the header row's gap
		// already spaces the button, so zero it to avoid a doubled gap.
		.aioseo-tooltip {
			margin-left: 0;
		}

		.aioseo-optimize-post-button svg {
			margin-right: 6px;
		}
	}

	.content-analysis-panel {
		min-height: 320px;

		&--keywords {
			padding: 16px $gutter $gutter;
		}

		&--headline {
			padding: 16px $gutter $gutter;
		}

		&__description {
			margin: 0 0 16px;
			color: $black;
			font-size: 14px;
			line-height: 22px;
		}

		&__divider {
			width: 100%;
			height: 1px;
			margin: 0 0 16px;
			border: 0;
			background: $gray;
		}
	}

	.content-analysis-empty {
		margin: 0;
		padding: 24px $gutter;
		color: $placeholder-color;
		font-size: 14px;
		line-height: 22px;
		text-align: center;
	}

	// Keep the bulk-actions Apply button the same height as the select beside it.
	.aioseo-wp-bulk-actions {
		select,
		.button.action {
			height: 32px;
			box-sizing: border-box;
			vertical-align: middle;
		}
	}
}

.content-analysis-tab-pill {
	border-radius: 100px;
	color: #fff;
	font-size: 12px;
	font-weight: 700;
	line-height: 1;
	padding: 4px 12px;

	&.score--green {
		background-color: $green;
	}

	&.score--orange {
		background-color: $orange;
	}

	&.score--red {
		background-color: $red;
	}
}

.aioseo-content-analysis-tab .content-analysis-tab-issues {
	margin-left: 4px;
}

.content-analysis-tab-issues {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	font-size: 12px;
	font-weight: 700;
	line-height: 1;
	color: #e8730c;

	&__dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background-color: #e8730c;
		flex-shrink: 0;
	}

	&--red {
		color: #DC2626;

		.content-analysis-tab-issues__dot {
			background-color: #DC2626;
		}
	}

	&--good {
		color: $green;

		.content-analysis-tab-issues__dot {
			background-color: $green;
		}
	}
}
</style>