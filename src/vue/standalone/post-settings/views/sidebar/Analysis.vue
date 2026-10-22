<template>
	<div
		id="aioseo-post-content-analysis"
		class="aioseo-tab-content aioseo-post-general sidebar"
	>
		<core-sidebar-card
			v-if="displayContentAnalysisCard"
			slug="contentAnalysis"
			class="card-content-analysis"
			:toggles="false"
			hide-header
			no-slide
		>
			<template #before-tabs>
				<page-builder-notice />
			</template>

			<div
				v-if="displayTruSeoSidebarKeyphraseCard"
				class="content-analysis-toolbar"
			>
				<tru-seo-locale-control variant="row" />

				<div class="content-analysis-toolbar__actions">
					<tru-seo-highlight-control variant="pill" />

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

				</div>
			</div>

			<div class="content-analysis-sections">
				<div
					v-if="displayTruSeoSidebarKeyphraseCard"
					class="content-analysis-section card-keywords"
				>
					<button
						type="button"
						class="content-analysis-section__header"
						@click="toggleSection('keywords')"
					>
						<span class="content-analysis-section__title">
							{{ strings.keywordsAnalysis }}
						</span>

						<core-tooltip
							class="content-analysis-section__help"
							@click.stop
						>
							<svg-circle-question-mark width="14" />

							<template #tooltip>
								{{ strings.keywordsDescription }}
							</template>
						</core-tooltip>

						<svg-caret
							class="content-analysis-section__caret"
							:class="{ rotated: !sections.keywords }"
						/>
					</button>

					<transition-slide
						:active="sections.keywords"
						:duration="300"
						class="content-analysis-section__body"
					>
						<div class="content-analysis-section__body-inner">
							<core-sidebar-keywords ref="keywordsSidebarRef" />
						</div>
					</transition-slide>
				</div>

				<div
					v-if="displayTruSeoSidebarKeyphraseCard"
					class="content-analysis-section card-basic-seo"
				>
					<button
						type="button"
						class="content-analysis-section__header"
						@click="toggleSection('basics')"
					>
						<span class="content-analysis-section__title">{{ strings.basicsTitle }}</span>

						<core-tooltip
							class="content-analysis-section__help"
							@click.stop
						>
							<svg-circle-question-mark width="14" />

							<template #tooltip>
								{{ strings.basicsDescription }}
							</template>
						</core-tooltip>

						<span
							v-if="isTruSeoDataReady && getTabErrorsCount('basic')"
							class="content-analysis-tab-issues"
						>
							<span class="content-analysis-tab-issues__dot" />

							{{ issueCountLabel(getTabErrorsCount('basic')) }}
						</span>

						<span
							v-else-if="isTruSeoDataReady"
							class="content-analysis-tab-issues content-analysis-tab-issues--good"
							:aria-label="strings.basicsAllGood"
						>
							<span class="content-analysis-tab-issues__dot" />
						</span>

						<svg-caret
							class="content-analysis-section__caret"
							:class="{ rotated: !sections.basics }"
						/>
					</button>

					<transition-slide
						:active="sections.basics"
						:duration="300"
						class="content-analysis-section__body"
					>
						<div class="content-analysis-section__body-inner">
							<metabox-analysis-detail
								v-if="isTruSeoDataReady"
								:analysisItems="postEditorStore.truseoData?.truseo?.general?.basic"
								tab="basic"
								:hide-good="true"
								:hide-issues-header="true"
							/>

							<p
								v-else
								class="content-analysis-empty"
							>
								{{ strings.analysisEmpty }}
							</p>
						</div>
					</transition-slide>
				</div>

				<div
					v-if="displayTruSeoSidebarKeyphraseCard && spellingTabVisible"
					class="content-analysis-section card-spelling-seo"
				>
					<button
						type="button"
						class="content-analysis-section__header"
						@click="toggleSection('spelling')"
					>
						<span class="content-analysis-section__title">{{ strings.spellingTitle }}</span>

						<core-tooltip
							class="content-analysis-section__help"
							@click.stop
						>
							<svg-circle-question-mark width="14" />

							<template #tooltip>
								{{ strings.spellingDescription }}

								<button
									v-if="canManageDictionary"
									type="button"
									class="aioseo-spelling-manage-link"
									@click.stop="truSeoHighlighterStore.openSafeWordsModal()"
								>
									{{ strings.manageDictionary }}
								</button>
							</template>
						</core-tooltip>

						<span
							v-if="isTruSeoDataReady && getSpellingCount()"
							class="content-analysis-tab-issues content-analysis-tab-issues--red"
						>
							<span class="content-analysis-tab-issues__dot" />

							{{ misspellingCountLabel(getSpellingCount()) }}
						</span>

						<span
							v-else-if="isTruSeoDataReady && spellingAnalysisRan"
							class="content-analysis-tab-issues content-analysis-tab-issues--good"
							:aria-label="strings.spellingAllGood"
						>
							<span class="content-analysis-tab-issues__dot" />
						</span>

						<svg-caret
							class="content-analysis-section__caret"
							:class="{ rotated: !sections.spelling }"
						/>
					</button>

					<transition-slide
						:active="sections.spelling"
						:duration="300"
						class="content-analysis-section__body"
					>
						<div class="content-analysis-section__body-inner">
							<spelling-analysis hide-description />
						</div>
					</transition-slide>
				</div>

				<div
					v-if="displayTruSeoSidebarKeyphraseCard"
					class="content-analysis-section card-readability-seo"
				>
					<button
						type="button"
						class="content-analysis-section__header"
						@click="toggleSection('readability')"
					>
						<span class="content-analysis-section__title">{{ strings.readability }}</span>

						<core-tooltip
							class="content-analysis-section__help"
							@click.stop
						>
							<svg-circle-question-mark width="14" />

							<template #tooltip>
								{{ strings.readabilityDescription }}
							</template>
						</core-tooltip>

						<span
							v-if="hasReadabilityData"
							class="readability-score-pill"
							:class="getReadabilityScoreClass(readabilityScore)"
						>
							{{ readabilityScore }}
						</span>

						<svg-caret
							class="content-analysis-section__caret"
							:class="{ rotated: !sections.readability }"
						/>
					</button>

					<transition-slide
						:active="sections.readability"
						:duration="300"
						class="content-analysis-section__body"
					>
						<div class="content-analysis-section__body-inner">
							<metabox-analysis-detail
								v-if="hasReadabilityData"
								:analysisItems="postEditorStore.truseoData?.truseo?.general?.readability"
								tab="readability"
								:hide-good="true"
							/>

							<p
								v-else
								class="content-analysis-empty"
							>
								{{ strings.analysisEmpty }}
							</p>
						</div>
					</transition-slide>
				</div>

				<div
					v-if="headlineAnalyzerEnabled"
					class="content-analysis-section card-headline-seo"
				>
					<button
						type="button"
						class="content-analysis-section__header"
						@click="toggleSection('headline')"
					>
						<span class="content-analysis-section__title">{{ strings.headlineTitle }}</span>

						<core-tooltip
							class="content-analysis-section__help"
							@click.stop
						>
							<svg-circle-question-mark width="14" />

							<template #tooltip>
								{{ strings.headlineDescription }}
							</template>
						</core-tooltip>

						<span
							v-if="null !== headlineScore"
							class="readability-score-pill"
							:class="getHeadlineScoreClass(headlineScore)"
						>
							{{ headlineScore }}
						</span>

						<svg-caret
							class="content-analysis-section__caret"
							:class="{ rotated: !sections.headline }"
						/>
					</button>

					<transition-slide
						:active="sections.headline"
						:duration="300"
						class="content-analysis-section__body"
					>
						<div class="content-analysis-section__body-inner">
							<p class="content-analysis-headline-description">{{ strings.headlineDescription }}</p>

							<base-button
								class="gray small aioseo-open-headline-analyzer"
								@click="openHeadlineAnalyzer"
							>
								{{ strings.openHeadlineAnalyzer }}
							</base-button>
						</div>
					</transition-slide>
				</div>
			</div>
		</core-sidebar-card>
	</div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, watch } from 'vue'

import {
	usePostEditorStore,
	useRootStore,
	useSettingsStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { __, _n, sprintf } from '@/vue/plugins/translations'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { merge } from 'lodash-es'
import { useHeadlineAnalyzer } from '@/vue/composables/HeadlineAnalyzer'
import { useTruSeoScore } from '@/vue/composables/TruSeoScore'
import { useMainSidebarIntent } from '../../composables/MainSidebarIntent'
import { useTruSeoLocale } from '../../composables/TruSeoLocale'
import { truSeoShouldAnalyze, supportsPageAnalysis } from '@/vue/plugins/tru-seo/components/helpers'
import { isOptimizeSupported } from '@/vue/plugins/tru-seo/optimize-post/editorAdapter'
import { isAnalyzeCapableEditor } from '@/vue/plugins/tru-seo/editors'

import BaseButton from '@/vue/components/common/base/Button'
import CoreSidebarCard from '@/vue/components/common/core/SidebarCard'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreSidebarKeywords from '../partials/general/SidebarKeywords'
import MetaboxAnalysisDetail from '../partials/general/MetaboxAnalysisDetail'
import PageBuilderNotice from '../partials/general/tru-seo/PageBuilderNotice'
import SpellingAnalysis from '../partials/general/tru-seo/SpellingAnalysis'
import TruSeoLocaleControl from '../partials/general/TruSeoLocaleControl'
import TruSeoHighlightControl from '../partials/general/TruSeoHighlightControl'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import TransitionSlide from '@/vue/components/common/transition/Slide'

const td = import.meta.env.VITE_TEXTDOMAIN

const postEditorStore        = usePostEditorStore()
const rootStore              = useRootStore()
const settingsStore          = useSettingsStore()
const truSeoHighlighterStore = useTruSeoHighlighterStore()

const selectedKeyphrase  = ref(0)
const keywordsSidebarRef = ref(null)

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
	headlineScore,
	openHeadlineAnalyzer
} = useHeadlineAnalyzer()
const { getSelectedOption } = useTruSeoLocale()
const { getIntent, clearIntent } = useMainSidebarIntent()

const strings = merge(composableStrings, {
	contentAnalysisTooltip : __('Analyze your content for keyword usage, readability and on-page SEO best practices.', td),
	keywordsAnalysis       : __('Keywords', td),
	basicsTitle            : __('Basics', td),
	readability            : __('Readability', td),
	headlineTitle          : __('Headline', td),
	headlineDescription    : __('This score rates how likely your headline is to earn clicks and shares. Open the Headline Analyzer panel for a full breakdown and suggestions on how to improve your headline.', td),
	openHeadlineAnalyzer   : __('Open Headline Analyzer', td),
	spellingTitle          : __('Spelling', td),
	spellingDescription    : __('Fix spelling mistakes flagged in your content. Words you add to your dictionary won\'t be flagged again.', td),
	manageDictionary       : __('Manage Dictionary', td),
	analysisEmpty          : __('You must first add some content to the page before it can be analyzed.', td),
	basicsAllGood          : __('No improvements needed', td),
	spellingAllGood        : __('No misspellings found', td),
	optimizePost           : __('Optimize', td)
})

const sections = ref({
	keywords    : true,
	basics      : true,
	readability : true,
	headline    : true,
	spelling    : true
})

const toggleSection = (section) => {
	sections.value[section] = !sections.value[section]
}

const sectionSelectors = {
	keywords    : '.card-keywords',
	basics      : '.card-basic-seo',
	readability : '.card-readability-seo'
}

// Deep-link target for the pre-publish panel's fix rows: expand + scroll to the
// requested section, and expand the focus-keyword row.
const applyPendingSection = () => {
	const intent = getIntent()
	if ('analysis' !== intent.tab || !intent.section) {
		return
	}

	const { section, expandKeyword } = intent

	if (undefined !== sections.value[section]) {
		sections.value[section] = true
	}

	if ('keywords' === section && expandKeyword) {
		keywordsSidebarRef.value?.expandFocusKeyword()
	}

	nextTick(() => {
		const selector = sectionSelectors[section]
		if (selector) {
			document.querySelector(selector)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
		}
	})

	clearIntent()
}

watch(() => settingsStore.metaBoxTabs.mainSidebar, applyPendingSection, { deep: true })

const spellCheckerEnabled = computed(() => !!window.aioseo?.spellChecker?.enabled && isAnalyzeCapableEditor())

// Also require a dictionary for the selected analysis language — some supported
// languages (e.g. Arabic, Japanese) have no spell-check dictionary.
const spellingTabVisible = computed(() => spellCheckerEnabled.value && !!getSelectedOption()?.hasSpellChecker)

const canManageDictionary = computed(() => {
	if (!allowed('aioseo_page_analysis')) {
		return false
	}

	if (!spellCheckerEnabled.value) {
		return false
	}

	return !!postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker
})

const getSpellingCount = () => {
	const sentences = postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker?.highlightSentences || []

	return new Set(sentences.map(s => (s || '').trim()).filter(Boolean)).size
}

// The spelling assessment has run (key exists whether the result is issue or good),
// so a zero count means "all clear" rather than "not analyzed yet".
const spellingAnalysisRan = computed(() => !!postEditorStore.truseoData?.truseo?.general?.spelling?.spellingChecker)

const displayTruSeoSidebarKeyphraseCard = computed(() => {
	return truSeoShouldAnalyze() && allowed('aioseo_page_analysis') && !isForum.value
})

// The card also hosts the Headline Analyzer section, so render it whenever either
// feature is on — but only on page types that support analysis. TruSEO
// sections/toolbar still gate on displayTruSeoSidebarKeyphraseCard.
const displayContentAnalysisCard = computed(() => {
	return supportsPageAnalysis() && (truSeoShouldAnalyze() || headlineAnalyzerEnabled.value) && allowed('aioseo_page_analysis') && !isForum.value
})

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

// Auto-Optimize requires a focus keyword — the server optimizes against it.

// The sidebar only mounts in the block editor, so isOptimizeSupported() is
// stable here (no classic Visual/Text observer needed). Optimize also needs AI
// Content access — hide the button rather than 403 on click when it's missing.
const showOptimizePost = computed(() =>
	isTruSeoDataReady.value &&
	isOptimizeSupported() &&
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

onMounted(() => {
	if ('post' === postEditorStore.currentPost.context && postEditorStore.truseoData?.focusKeyword && !postEditorStore.truseoData?.additionalKeywords?.length) {
		selectedKeyphrase.value = -1
	}

	ensureHeadlineScore()

	applyPendingSection()
})
</script>
<style lang="scss">
// Sidebar-specific styles
.edit-post-sidebar,
.editor-sidebar {
	.card-content-analysis {
		margin: 0 -1rem;
		box-shadow: none;
		border: none;

		> .header {
			height: auto;
			min-height: 46px;
			padding: 1rem;
			border-bottom: none;
			font-size: 14px;
			font-weight: $font-bold;

			// Let the score wrap below the heading when a locale title is too long.
			.text {
				flex-wrap: wrap;
				gap: 8px;

				.score-badge {
					margin-left: auto;
				}
			}
		}

		.content {
			padding: 0;
			font-size: 14px;
			// No border-top: the sticky tab-title above already draws the single
			// divider, so a border here just doubles it (and brackets the empty
			// before-tabs band above the toolbar).
		}

		// The page-builder notice fills the before-tabs slot, which the card always
		// wraps in a .content div — even when the notice renders nothing (any
		// non-page-builder post). Collapse the empty wrapper so it can't add height.
		// :empty ignores the v-if comment node.
		.content:empty {
			display: none;
		}

		// The card zeroes .content padding, so the page-builder notice would sit
		// flush against the panel edges. Inset it to match the toolbar's rhythm.
		.aioseo-page-builder-notice {
			margin: 12px 16px;
		}
	}

	.content-analysis-toolbar {
		// The sidebar is too narrow to fit the language selector alongside the
		// highlight/optimize row, so the language row stacks full-width above them.
		display: flex;
		flex-direction: column;
		align-items: stretch;
		gap: 8px;
		padding: 12px 16px;
		border-bottom: 1px solid $border;

		.aioseo-truseo-highlight-control {
			width: 100%;
			justify-content: space-between;
		}

		// Highlight toggle and Optimize button share this row 50/50. When Optimize
		// is hidden, flex-grow lets the highlight toggle fill the whole row.
		&__actions {
			display: flex;
			align-items: stretch;
			gap: 8px;

			> * {
				flex: 1 1 0;
				min-width: 0;
			}

			.aioseo-optimize-post {
				display: flex;

				// Stretch the tooltip wrapper (disabled state) across its half.
				.aioseo-tooltip {
					margin-left: 0;
					width: 100%;

					> :last-child {
						width: 100%;
					}
				}

				.aioseo-button.small.aioseo-optimize-post-button {
					width: 100%;
					height: 34px;

					svg {
						margin-right: 6px;
					}
				}
			}
		}
	}

	.content-analysis-section {
		border-top: 1px solid $border;

		&:first-child {
			border-top: none;
		}

		&__header {
			appearance: none;
			// !important (plus the :hover guard below) keeps page-builder themes
			// (e.g. Divi) from painting a grey button fill that bleeds onto the
			// Add-keyword button just below.
			background: none !important;
			border: 0;
			font: inherit;
			display: flex;
			align-items: center;
			gap: 8px;
			width: 100%;
			padding: 14px 16px;
			font-size: 14px;
			font-weight: $font-bold;
			color: $black;
			cursor: pointer;
			text-align: left;

			&:hover {
				background: none !important;
			}
		}

		&__title {
			flex-shrink: 0;
		}

		&__help {
			display: inline-flex;
			align-items: center;
			margin-left: 0;
			margin-right: auto;
			color: $placeholder-color;

			svg {
				color: inherit;
			}
		}

		&__caret {
			width: 20px;
			height: 20px;
			color: $placeholder-color;
			transform: rotate(-180deg);
			transition: transform 0.3s;
			flex-shrink: 0;

			&.rotated {
				transform: rotate(-90deg);
			}
		}

		// The height slide itself is handled by <transition-slide> (same component
		// the other sidebar cards use); this just carries the body's padding.
		&__body-inner {
			padding: 0 16px 16px;
			font-size: 14px;
		}

		.content-analysis-empty {
			margin: 0;
			padding: 4px 0;
			color: $placeholder-color;
			font-size: 14px;
			line-height: 22px;
		}

		.aioseo-analysis-detail:last-of-type {
			margin-bottom: 0;

			.title {
				margin-bottom: 0;
			}
		}

		.aioseo-analysis-detail {
			margin-top: 0;
		}

		.aioseo-spelling-analysis {
			padding: 0;

			// Only the size steps down for the narrower panel — the weight stays as the
			// metabox has it, matching the analysis item titles in the cards above.
			&__word {
				font-size: 13px;
			}
		}
	}

	.card-readability-seo {
		.readability-description {
			p {
				color: $black;
				font-size: 14px;
				line-height: 22px;
				margin: 0 0 20px;
			}

			&__divider {
				width: 100%;
				margin-top: 20px;
				background: $gray;
				height: 1px;
			}
		}
	}

	.card-headline-seo {
		.content-analysis-headline-description {
			color: $black;
			font-size: 13px;
			line-height: 20px;
			margin: 0 0 16px;
		}

		.aioseo-open-headline-analyzer {
			display: inline-flex;
		}
	}

	.readability-score-pill {
		border-radius: 100px;
		color: #fff;
		font-size: 12px;
		font-weight: 700;
		line-height: 1;
		margin-left: 12px;
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

	.analysis-wrapper {
		border-top: none;
	}
}

// The section-help tooltip is teleported to <body>, so this link is styled
// globally rather than under the sidebar wrapper.
.aioseo-spelling-manage-link {
	display: inline;
	padding: 0;
	border: 0;
	background: none;
	font: inherit;
	color: $blue;
	text-decoration: underline;
	cursor: pointer;

	&:hover {
		text-decoration: none;
	}
}
</style>