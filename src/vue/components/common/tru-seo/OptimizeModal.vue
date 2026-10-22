<template>
	<out-of-credits-modal
		v-if="showUpsellInsteadOfIntro"
		:show="isOpen"
		:feature="upsellFeature"
		@close="close"
	/>

	<core-modal
		v-else
		:modal-name="modalName"
		:show="isOpen"
		:classes="[ 'aioseo-optimize-modal-wrap' ]"
		:allow-bg-close="!isProcessing"
		@close="handleClose"
	>
		<template #header>
			<span class="aioseo-optimize-modal__header-title">
				{{ headerTitle }}
			</span>

			<button
				v-if="!isProcessing"
				class="close"
				type="button"
				@click.stop="close"
			>
				<svg-close
					width="14"
					height="14"
				/>
			</button>
		</template>

		<template #body>
			<div class="aioseo-optimize-modal">
				<div
					v-if="isIntro"
					class="aioseo-optimize-modal__start"
				>
					<div
						v-if="hasEnoughCreditsToOptimize"
						class="aioseo-optimize-modal__preview"
						aria-hidden="true"
					>
						<div class="aioseo-optimize-modal__preview-card aioseo-optimize-modal__preview-card--before">
							<div class="aioseo-optimize-modal__ring aioseo-optimize-modal__ring--before">
								<svg
									viewBox="0 0 40 40"
									class="aioseo-optimize-modal__ring-svg"
								>
									<circle
										class="aioseo-optimize-modal__ring-track"
										cx="20"
										cy="20"
										r="16"
									/>
									<circle
										class="aioseo-optimize-modal__ring-value"
										cx="20"
										cy="20"
										r="16"
									/>
								</svg>

								<span class="aioseo-optimize-modal__ring-score">48</span>
							</div>

							<ul class="aioseo-optimize-modal__preview-lines">
								<li
									v-for="n in 3"
									:key="n"
								>
									<span class="aioseo-optimize-modal__preview-dot" />
									<span class="aioseo-optimize-modal__preview-bar" />
								</li>
							</ul>
						</div>

						<svg-ai-content class="aioseo-optimize-modal__preview-glyph" />

						<div class="aioseo-optimize-modal__preview-card aioseo-optimize-modal__preview-card--after">
							<div class="aioseo-optimize-modal__ring aioseo-optimize-modal__ring--after">
								<svg
									viewBox="0 0 40 40"
									class="aioseo-optimize-modal__ring-svg"
								>
									<circle
										class="aioseo-optimize-modal__ring-track"
										cx="20"
										cy="20"
										r="16"
									/>
									<circle
										class="aioseo-optimize-modal__ring-value"
										cx="20"
										cy="20"
										r="16"
									/>
								</svg>

								<span class="aioseo-optimize-modal__ring-score">92</span>
							</div>

							<ul class="aioseo-optimize-modal__preview-lines aioseo-optimize-modal__preview-lines--resolved">
								<li
									v-for="n in 3"
									:key="n"
								>
									<span class="aioseo-optimize-modal__preview-check">
										<svg viewBox="0 0 12 12">
											<path d="M2.5 6.4 L5 8.8 L9.5 3.6" />
										</svg>
									</span>
									<span class="aioseo-optimize-modal__preview-bar" />
								</li>
							</ul>
						</div>
					</div>

					<p class="aioseo-optimize-modal__start-description">
						{{ needsFocusKeyword ? strings.keywordDescription : startDescription }}
					</p>

					<core-alert
						v-if="truSeoHighlighterStore.optimizePostError"
						class="aioseo-optimize-modal__error"
						type="red"
					>
						{{ truSeoHighlighterStore.optimizePostError }}
					</core-alert>

					<template v-if="needsFocusKeyword">
						<div class="aioseo-optimize-modal__keyword-field">
							<base-input
								v-model="keywordInput"
								size="medium"
								:placeholder="strings.keywordPlaceholder"
								@keydown.enter="saveKeyword"
							/>

							<p
								v-if="keywordError"
								class="aioseo-optimize-modal__keyword-error"
							>
								{{ keywordError }}
							</p>
						</div>

						<div class="aioseo-optimize-modal__actions">
							<base-button
								type="blue"
								size="medium"
								:disabled="!keywordInput.trim()"
								@click="saveKeyword"
							>
								{{ strings.keywordContinue }}
							</base-button>
						</div>
					</template>

					<div
						v-else
						class="aioseo-optimize-modal__actions"
					>
						<base-button
							type="blue"
							size="medium"
							@click="startOptimize"
						>
							{{ strings.optimize }}

							<credit-badge :cost="optimizeCost" />
						</base-button>
					</div>
				</div>

				<div
					v-else-if="isProcessing"
					class="aioseo-optimize-modal__processing"
				>
					<lottie
						v-if="loadedAnimation"
						class="aioseo-optimize-modal__processing-lottie"
						:options="{ animationData: loadedAnimation }"
						:width="140"
						:height="140"
					/>

					<loading-bar
						:percent="percent"
						:show-number="false"
					>
						{{ phaseText }}
					</loading-bar>
				</div>

				<div
					v-else-if="isDone"
					class="aioseo-optimize-modal__complete"
				>
					<div class="aioseo-optimize-modal__score-hero">
						<div class="aioseo-optimize-modal__score-rings">
							<div :class="[ 'aioseo-optimize-modal__ring', 'aioseo-optimize-modal__ring--hero', ringColorClass(scoreAfter) ]">
								<svg
									viewBox="0 0 40 40"
									class="aioseo-optimize-modal__ring-svg"
								>
									<circle
										class="aioseo-optimize-modal__ring-track"
										cx="20"
										cy="20"
										r="16"
									/>
									<circle
										class="aioseo-optimize-modal__ring-value"
										cx="20"
										cy="20"
										r="16"
										:style="ringStyle(shownScore)"
									/>
								</svg>

								<span class="aioseo-optimize-modal__ring-score">{{ Math.round(shownScore) }}</span>
							</div>

							<span
								v-if="scoreChanged"
								class="aioseo-optimize-modal__score-delta"
								:class="{ 'aioseo-optimize-modal__score-delta--positive': scoreImproved }"
							>
								{{ deltaText }}
							</span>
						</div>

						<p class="aioseo-optimize-modal__score-caption">
							{{ scoreCaption }}
						</p>
					</div>

					<core-alert
						v-if="skippedBlocksText"
						class="aioseo-optimize-modal__skipped"
						type="yellow"
					>
						{{ skippedBlocksText }}
					</core-alert>

					<div
						v-if="resolvedItems.length"
						class="aioseo-optimize-modal__resolved"
					>
						<div class="aioseo-optimize-modal__section-title">
							{{ strings.resolvedTitle }}
						</div>

						<ul class="aioseo-optimize-modal__resolved-list">
							<li
								v-for="(check, index) in visibleResolved"
								:key="index"
							>
								<span class="aioseo-optimize-modal__resolved-check">
									<svg viewBox="0 0 12 12">
										<path d="M2.5 6.4 L5 8.8 L9.5 3.6" />
									</svg>
								</span>

								<span class="aioseo-optimize-modal__resolved-text">
									{{ check.title }}<span
										v-if="check.points"
										class="aioseo-optimize-modal__resolved-points"
									>+{{ check.points }}</span>
								</span>
							</li>

							<li
								v-if="hiddenResolvedCount"
								class="aioseo-optimize-modal__resolved-more"
							>
								{{ moreChecksText }}
							</li>
						</ul>
					</div>

					<div
						v-if="fieldCards.length"
						class="aioseo-optimize-modal__diffs"
					>
						<div
							v-for="card in fieldCards"
							:key="card.field"
							class="aioseo-optimize-modal__diff"
							:class="{ 'aioseo-optimize-modal__diff--reverted': revertedCards[card.field] }"
						>
							<div class="aioseo-optimize-modal__diff-header">
								<span class="aioseo-optimize-modal__diff-label">
									{{ card.label }}
								</span>

								<button
									type="button"
									class="aioseo-optimize-modal__diff-revert"
									:disabled="revertedCards[card.field]"
									@click="revert(card)"
								>
									<svg-refresh
										width="12"
										height="12"
									/>

									{{ revertedCards[card.field] ? strings.reverted : strings.revert }}
								</button>
							</div>

							<div
								v-if="card.diffBlocks"
								class="aioseo-optimize-modal__diff-content"
							>
								<div
									v-for="(block, blockIndex) in card.diffBlocks"
									:key="blockIndex"
									class="aioseo-optimize-modal__diff-block"
								>
									<template
										v-for="(part, partIndex) in block"
										:key="partIndex"
									>
										<ins v-if="part.added">{{ part.value }}</ins>
										<del v-else-if="part.removed">{{ part.value }}</del>
										<span v-else>{{ part.value }}</span>
									</template>
								</div>
							</div>

							<div
								v-else-if="card.summary"
								class="aioseo-optimize-modal__diff-summary"
							>
								{{ card.summary }}
							</div>

							<div
								v-else
								class="aioseo-optimize-modal__diff-values"
							>
								<span class="aioseo-optimize-modal__diff-before">
									{{ card.before || '—' }}
								</span>

								<span class="aioseo-optimize-modal__diff-arrow">→</span>

								<span class="aioseo-optimize-modal__diff-after">
									{{ card.after }}
								</span>
							</div>

							<div
								v-if="card.metric"
								class="aioseo-optimize-modal__diff-metric"
							>
								{{ card.metric }}
							</div>
						</div>
					</div>

					<p
						v-if="!hasChanges"
						class="aioseo-optimize-modal__empty"
					>
						{{ strings.noChanges }}
					</p>

					<div
						v-if="visibleNextSteps.length"
						class="aioseo-optimize-modal__next"
					>
						<div class="aioseo-optimize-modal__section-title">
							{{ strings.nextTitle }}
						</div>

						<p class="aioseo-optimize-modal__next-description">
							{{ strings.nextDescription }}
						</p>

						<ul class="aioseo-optimize-modal__next-list">
							<li
								v-for="step in visibleNextSteps"
								:key="step.id"
							>
								<span class="aioseo-optimize-modal__next-check">
									<svg viewBox="0 0 12 12">
										<path d="M2.5 6.4 L5 8.8 L9.5 3.6" />
									</svg>
								</span>

								<span class="aioseo-optimize-modal__next-text">
									<span class="aioseo-optimize-modal__next-title">
										{{ step.title }}<span
											v-if="step.points"
											class="aioseo-optimize-modal__next-points"
										>+{{ step.points }}</span>
									</span>

									<span class="aioseo-optimize-modal__next-hint">{{ step.text }}</span>

									<div
										v-if="step.canGenerateImage"
										class="aioseo-optimize-modal__next-cta"
									>
										<span class="aioseo-optimize-modal__next-cta-icon">
											<svg-image-generator />
										</span>

										<span class="aioseo-optimize-modal__next-cta-text">
											<span class="aioseo-optimize-modal__next-cta-title">{{ strings.imageCtaTitle }}</span>
											{{ strings.imageCtaBody }}
										</span>

										<base-button
											type="blue"
											size="small"
											@click="openImageGenerator"
										>
											<svg-ai-content
												width="14"
												height="14"
											/>

											{{ strings.imageCtaButton }}
										</base-button>
									</div>
								</span>
							</li>

							<li
								v-if="hiddenNextCount"
								class="aioseo-optimize-modal__next-more"
							>
								{{ moreNextText }}
							</li>
						</ul>
					</div>

				</div>
			</div>
		</template>

		<template
			v-if="isIntro || isDone"
			#footer
		>
			<div class="footer-left">
				<credit-counter parent-component-context="modal" />
			</div>

			<div
				v-if="isDone"
				class="footer-right"
			>
				<base-button
					v-if="hasEnoughCreditsToOptimize"
					type="gray"
					size="medium"
					@click="startOptimize"
				>
					{{ strings.reOptimize }}

					<credit-badge :cost="optimizeCost" />
				</base-button>

				<base-button
					type="blue"
					size="medium"
					@click="close"
				>
					{{ strings.done }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import {
	useAiImageGeneratorStore,
	useAiStore,
	usePostEditorStore,
	useRootStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { isBadResult } from '@/app/tru-seo/scoring/interpreters'
import { getFocusKeywordResults } from '@/app/tru-seo/helpers/resultsFilter'
import { keyphraseExists } from '@/vue/utils/keyphraseUtils'
import { potentialSeoScoreGain } from '@/vue/plugins/tru-seo/helpers/resultsHelper'
import { useKeywords } from '@/vue/standalone/post-settings/composables/Keywords'
import { getTruSeoInstance } from '@/vue/plugins/tru-seo/TruSeoSingleton'

import { useAiContent } from '@/vue/composables/AiContent'
import { __, _n, sprintf } from '@/vue/plugins/translations'
import { measureTextWidth } from '@/vue/utils/measureTextWidth'
import { sanitizeString } from '@/vue/utils/strings'
import { diffWords } from '@/vue/utils/wordDiff'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import BaseButton from '@/vue/components/common/base/Button'
import BaseInput from '@/vue/components/common/base/Input'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CreditBadge from '@/vue/components/common/ai/CreditBadge'
import OutOfCreditsModal from '@/vue/components/common/ai/OutOfCreditsModal'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'
import LoadingBar from '@/vue/components/common/core/LoadingBar'
import Lottie from '@/vue/components/common/core/Lottie'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgImageGenerator from '@/vue/components/common/svg/ai/ImageGenerator'
import SvgRefresh from '@/vue/components/common/svg/Refresh'

const td = import.meta.env.VITE_TEXTDOMAIN

const modalName = 'aioseo-optimize-post'

const aiImageGeneratorStore  = useAiImageGeneratorStore()
const aiStore                = useAiStore()
const postEditorStore        = usePostEditorStore()
const rootStore              = useRootStore()
const truSeoHighlighterStore = useTruSeoHighlighterStore()

const { hasEnoughCredits } = useAiContent()

const optimizeCost = computed(() => truSeoHighlighterStore.optimizeCreditCost)

// Out of credits shows the upsell in place of the intro copy and the run button, so the
// modal always explains itself rather than presenting a button that can't work.
const hasEnoughCreditsToOptimize = computed(() => hasEnoughCredits(optimizeCost.value))

// Swap the whole modal for the upsell rather than nesting it, so the user doesn't get an
// Optimize header and a credit counter wrapped around a message about having no credits.
// Intro only: a run that just finished must keep showing its results even at zero credits.
const showUpsellInsteadOfIntro = computed(() => isIntro.value && !hasEnoughCreditsToOptimize.value)

// Optimize needs something to optimize for, so the keyword is collected here rather than
// sending the user off to the Keywords table and back. Credits are checked first, since
// there's no point asking for a keyword the user can't spend anything on.
// addKeyword re-runs the analysis through this ref, so it has to hold the real instance —
// left null, the new keyword would never get scored.
const truSeo = ref(null)
const { addKeyword } = useKeywords(truSeo)

onMounted(async () => {
	truSeo.value = await getTruSeoInstance()
})

const keywordInput = ref('')
const keywordError = ref('')

const needsFocusKeyword = computed(() => !postEditorStore.truseoData?.focusKeyword)

const saveKeyword = () => {
	const value = keywordInput.value.trim()
	if (!value) {
		return
	}

	if (keyphraseExists(postEditorStore, value)) {
		keywordError.value = strings.keywordDuplicate

		return
	}

	keywordError.value = ''
	addKeyword(value)
	keywordInput.value = ''
}

const upsellFeature = computed(() => ({
	slug        : 'optimize-post',
	cost        : optimizeCost.value,
	icon        : SvgAiContent,
	title       : __('Optimize with AI', td),
	description : __('Rewrite this post\'s SEO title, meta description and content in one click, then review every change before keeping it.', td)
}))

const strings = {
	introTitle         : __('Optimize with AIOSEO Copilot', td),
	introDescription   : __('AIOSEO Copilot rewrites your post for search in one click — it optimizes your SEO title, meta description, and content, and fixes spelling. Your current version is saved as a revision first, so you can roll back anytime.', td),
	// Shown when the user can't edit the SEO title/description, so Optimize only
	// touches the body content — the copy must not promise a meta rewrite.
	introDescNoMeta    : __('AIOSEO Copilot rewrites your post for search in one click — it optimizes your content and fixes spelling. Your current version is saved as a revision first, so you can roll back anytime.', td),
	optimize           : __('Optimize', td),
	reOptimize         : __('Re-optimize', td),
	processingTitle    : __('Optimizing with Copilot', td),
	doneTitle          : __('Optimization complete', td),
	phaseOptimizing    : __('Optimizing your content…', td),
	phaseSpelling      : __('Fixing spelling mistakes…', td),
	scoreImproved      : __('Your TruSEO score improved!', td),
	scoreFinal         : __('Your TruSEO score', td),
	resolvedTitle      : __('Improvements resolved', td),
	seoTitle           : __('SEO Title', td),
	metaDescription    : __('Meta Description', td),
	headline           : __('Headline', td),
	content            : __('Content', td),
	revert             : __('Revert', td),
	reverted           : __('Reverted', td),
	noChanges          : __('Your content already looks great — no changes were needed.', td),
	done               : __('Done', td),
	nextTitle          : __('What\'s next', td),
	nextDescription    : __('Here are some other items you can work on to further optimize your post.', td),
	imageCtaTitle      : __('Skip the stock photo hunt', td),
	imageCtaBody       : __('Describe what you want and Copilot creates a custom image for this post.', td),
	imageCtaButton     : __('Generate an Image', td),
	keywordDescription : __('Copilot optimizes your post for the keyword you want it to rank for. Add one to continue.', td),
	keywordPlaceholder : __('e.g. cold brew coffee', td),
	keywordContinue    : __('Continue', td),
	keywordDuplicate   : __('You\'re already targeting that keyword on this post.', td)
}

const animationImports = [
	() => import('@/vue/assets/lottie/cute-bear-dancing-animation.json'),
	() => import('@/vue/assets/lottie/enjoying-sloth-animation.json'),
	() => import('@/vue/assets/lottie/koala-eats-leaves.json'),
	() => import('@/vue/assets/lottie/panda-sleeping-animation.json'),
	() => import('@/vue/assets/lottie/cat-playing-animation.json')
]

const loadedAnimation = ref(null)

// Picks and loads one random loader animation per run (once, when processing starts).
const loadRandomAnimation = async () => {
	const index  = Math.floor(Math.random() * animationImports.length)
	const module = await animationImports[index]()
	loadedAnimation.value = module.default || module
}

// Reverted flags live on the persisted result so they survive a close/reopen of
// the modal; a fresh run resets them by replacing optimizeResult.
const revertedCards = computed(() => truSeoHighlighterStore.optimizeResult?.reverted || {})

const revert = (card) => {
	truSeoHighlighterStore.revertOptimizeField(card.field)
}

const isIntro      = computed(() => 'intro' === truSeoHighlighterStore.optimizePhase)
const isProcessing = computed(() => [ 'optimizing', 'spelling' ].includes(truSeoHighlighterStore.optimizePhase))
const isDone       = computed(() => 'done' === truSeoHighlighterStore.optimizePhase)
const isOpen       = computed(() => isIntro.value || isProcessing.value || isDone.value)

// Optimize only rewrites the SEO title/description for users allowed to edit
// them; otherwise it changes body content (and spelling) only. Match the intro
// copy to what will actually happen so the result isn't a surprise.
const startDescription = computed(() =>
	allowed('aioseo_page_general_settings') ? strings.introDescription : strings.introDescNoMeta
)

const headerTitle = computed(() => {
	if (isDone.value) {
		return strings.doneTitle
	}

	if (isIntro.value) {
		return strings.introTitle
	}

	return strings.processingTitle
})
const phaseText   = computed(() => 'spelling' === truSeoHighlighterStore.optimizePhase ? strings.phaseSpelling : strings.phaseOptimizing)

const result = computed(() => truSeoHighlighterStore.optimizeResult || {})

const scoreBefore   = computed(() => result.value.score?.before ?? 0)
const scoreAfter    = computed(() => result.value.score?.after ?? 0)
const scoreDelta    = computed(() => scoreAfter.value - scoreBefore.value)
const scoreChanged  = computed(() => 0 !== scoreDelta.value)
const scoreImproved = computed(() => 0 < scoreDelta.value)
const deltaText     = computed(() => (scoreImproved.value ? '+' : '') + scoreDelta.value)
const scoreCaption  = computed(() => scoreImproved.value ? strings.scoreImproved : strings.scoreFinal)

const MAX_VISIBLE_CHECKS = 6
const fixedChecks        = computed(() => result.value.fixedChecks || [])

// "Nnpx → Mpx" for the SEO title, matching how search engines gauge title width
// (the same measurement the field counter uses).
const pixelMetric = (before, after) => sprintf(
	// Translators: 1 - Pixel width before, 2 - Pixel width after.
	__('%1$dpx → %2$dpx', td),
	Math.round(measureTextWidth(before || '')),
	Math.round(measureTextWidth(after || ''))
)

// "N → M characters" for the meta description, which is gauged by character count.
const charMetric = (before, after) => sprintf(
	// Translators: 1 - Character count before, 2 - Character count after.
	__('%1$d → %2$d characters', td),
	(before || '').length,
	(after || '').length
)

// The AI response and stored SEO fields can carry HTML entities (&amp;, &#039;,
// …); render them as their characters so the diff never shows encoded junk.
const decodeText = value => sanitizeString(value, true)

const contentDiff = computed(() => {
	const blocks = result.value.contentBlocks || []

	// Drop blocks whose only difference was entity encoding — after decoding they
	// diff as unchanged, so there's nothing to show.
	return blocks
		.map(block => diffWords(decodeText(block.before), decodeText(block.after)))
		.filter(diff => diff.some(part => part.added || part.removed))
})

const fieldCards = computed(() => {
	const data  = result.value
	const cards = []

	const seoTitleBefore = decodeText(data.seoTitle?.before)
	const seoTitleAfter  = decodeText(data.seoTitle?.after)
	if (data.seoTitle && seoTitleBefore !== seoTitleAfter) {
		cards.push({
			field  : 'seoTitle',
			label  : strings.seoTitle,
			before : seoTitleBefore,
			after  : seoTitleAfter,
			metric : pixelMetric(seoTitleBefore, seoTitleAfter)
		})
	}

	const metaDescriptionBefore = decodeText(data.metaDescription?.before)
	const metaDescriptionAfter  = decodeText(data.metaDescription?.after)
	if (data.metaDescription && metaDescriptionBefore !== metaDescriptionAfter) {
		cards.push({
			field  : 'metaDescription',
			label  : strings.metaDescription,
			before : metaDescriptionBefore,
			after  : metaDescriptionAfter,
			metric : charMetric(metaDescriptionBefore, metaDescriptionAfter)
		})
	}

	const headlineBefore = decodeText(data.headline?.before)
	const headlineAfter  = decodeText(data.headline?.after)
	if (data.headline && headlineBefore !== headlineAfter) {
		cards.push({
			field  : 'headline',
			label  : strings.headline,
			before : headlineBefore,
			after  : headlineAfter
		})
	}

	if (contentDiff.value.length) {
		cards.push({
			field      : 'content',
			label      : strings.content,
			diffBlocks : contentDiff.value,
			metric     : sprintf(
				// Translators: 1 - Word count before, 2 - Word count after.
				__('%1$d → %2$d words', td),
				data.words?.before || 0,
				data.words?.after || 0
			)
		})
	}

	return cards
})

const spelling      = computed(() => truSeoHighlighterStore.optimizeResult?.spelling || null)
const spellingFixed = computed(() => spelling.value?.fixed || 0)
const dictWords     = computed(() => spelling.value?.addedToDictionary || [])

// Resolved improvements = fixed checks + spelling fixes + dictionary additions,
// shown together in the list at the top.
const resolvedItems = computed(() => {
	const items = fixedChecks.value.map(check => ({ title: check.title, points: check.points }))

	if (0 < spellingFixed.value) {
		items.push({
			title : sprintf(
				// Translators: %1$d - The number of spelling mistakes fixed.
				_n('Fixed %1$d spelling mistake', 'Fixed %1$d spelling mistakes', spellingFixed.value, td),
				spellingFixed.value
			)
		})
	}

	if (dictWords.value.length) {
		items.push({
			title : sprintf(
				// Translators: %1$d - The number of words added to the dictionary.
				_n('Added %1$d word to the dictionary', 'Added %1$d words to the dictionary', dictWords.value.length, td),
				dictWords.value.length
			)
		})
	}

	return items
})
const visibleResolved     = computed(() => resolvedItems.value.slice(0, MAX_VISIBLE_CHECKS))
const hiddenResolvedCount = computed(() => Math.max(0, resolvedItems.value.length - MAX_VISIBLE_CHECKS))
const skippedBlocksText   = computed(() => {
	const skipped = result.value.skippedBlocks || 0
	if (!skipped) {
		return ''
	}

	return sprintf(
		// Translators: %1$d - The number of paragraphs and headings left out of the optimization.
		_n('%1$d paragraph or heading was too long to optimize and was left as written. Split it into shorter ones to include it next time.', '%1$d paragraphs or headings were too long to optimize and were left as written. Split them into shorter ones to include them next time.', skipped, td),
		skipped
	)
})
const moreChecksText      = computed(() => sprintf(
	// Translators: %1$d - The number of additional resolved improvements not shown.
	_n('+%1$d more', '+%1$d more', hiddenResolvedCount.value, td),
	hiddenResolvedCount.value
))

const hasChanges = computed(() => fieldCards.value.length || resolvedItems.value.length)

// What's next = the Basics checks still failing after the run. Optimize persists a
// re-analysis, so anything it fixed has already scored its way out of this list. The
// store's grouping is reused so checks shown under the keyword rows aren't repeated.
const nextSteps = computed(() => {
	const basic   = postEditorStore.truseoData?.truseo?.general?.basic || {}
	const general = postEditorStore.currentPost.truseo?.general || {}

	// With a focus keyword set, the store already keeps the keyword checks out of this
	// group. Without one they land here and every last one reads "add a focus keyword",
	// which is a single action rather than six list items.
	const skip = postEditorStore.truseoData?.focusKeyword
		? new Set()
		: new Set(Object.keys(getFocusKeywordResults(general)))

	return Object.entries(basic)
		.filter(([ id, item ]) => !skip.has(id) && item?.title && isBadResult(item.score ?? 0))
		.map(([ id, item ]) => ({
			id,
			title            : item.title,
			text             : item.text,
			points           : potentialSeoScoreGain(general, item.score ?? 0),
			canGenerateImage : 'images' === id && aiImageGeneratorStore.canUploadFiles
		}))
})
const visibleNextSteps = computed(() => nextSteps.value.slice(0, MAX_VISIBLE_CHECKS))
const hiddenNextCount  = computed(() => Math.max(0, nextSteps.value.length - MAX_VISIBLE_CHECKS))
const moreNextText     = computed(() => sprintf(
	// Translators: %1$d - The number of additional improvements not shown.
	_n('+%1$d more improvement', '+%1$d more improvements', hiddenNextCount.value, td),
	hiddenNextCount.value
))

// Simulated progress: no server sub-progress, so the bar eases toward a
// phase-driven cap. The phase TEXT comes from real phase, never this timer.
const PHASE_CAPS    = { optimizing: 65, spelling: 92 }
const reducedMotion = !!window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches

// r=16 → circumference ≈ 100.53; the value arc's dashoffset sets the visible fill.
const RING_CIRCUMFERENCE = 100.53

const ringStyle = (score) => ({
	strokeDashoffset : RING_CIRCUMFERENCE * (1 - Math.max(0, Math.min(100, score)) / 100)
})

const ringColorClass = (score) => {
	if (79 < score) {
		return 'aioseo-optimize-modal__ring--green'
	}

	if (49 < score) {
		return 'aioseo-optimize-modal__ring--orange'
	}

	return 'aioseo-optimize-modal__ring--red'
}

// Score-jump count-up. Drives both the after-ring fill and the number, so one
// tween animates the whole payoff. Reduced motion / no improvement → final state.
const displayScore = ref(0)
const tweening     = ref(false)

// During the reveal count-up show the animated value; once settled — including
// after a revert recomputes the score — fall through to the live after-score, so
// reverting a change snaps the hero to the recalculated score.
const shownScore = computed(() => tweening.value ? displayScore.value : scoreAfter.value)

let scoreRaf  = null,
	rampTimer = null

const stopScoreTween = () => {
	if (scoreRaf) {
		cancelAnimationFrame(scoreRaf)
		scoreRaf = null
	}

	tweening.value = false
}

const runScoreTween = () => {
	stopScoreTween()

	const from = scoreBefore.value
	const to   = scoreAfter.value

	if (reducedMotion || !scoreImproved.value) {
		displayScore.value = to

		return
	}

	tweening.value = true

	const duration = 1100
	const start    = performance.now()

	const step = (now) => {
		const progress = Math.min(1, (now - start) / duration)
		const eased    = 1 - Math.pow(1 - progress, 3)
		displayScore.value = from + (to - from) * eased

		if (1 > progress) {
			scoreRaf = requestAnimationFrame(step)

			return
		}

		displayScore.value = to
		scoreRaf = null
		tweening.value = false
	}

	scoreRaf = requestAnimationFrame(step)
}

const percent = ref(0)

const clearRamp = () => {
	if (rampTimer) {
		clearInterval(rampTimer)
		rampTimer = null
	}
}

const rampTo = (cap) => {
	clearRamp()

	if (reducedMotion) {
		percent.value = cap

		return
	}

	rampTimer = setInterval(() => {
		const remaining = cap - percent.value
		if (0.5 > remaining) {
			clearRamp()

			return
		}

		percent.value = Math.round((percent.value + remaining * 0.08) * 10) / 10
	}, 200)
}

watch(() => truSeoHighlighterStore.optimizePhase, (phase) => {
	switch (phase) {
		case 'intro':
			clearRamp()
			percent.value = 0
			// Re-arm as active modal so ESC / backdrop dismiss the intro view — needed
			// when a failed run returns here after an ESC unset it while processing.
			rootStore.setActiveModal(modalName)
			break
		case 'optimizing':
			percent.value = 0
			// One random loader animation per run, chosen when processing starts.
			loadRandomAnimation()
			rampTo(PHASE_CAPS.optimizing)
			break
		case 'spelling':
			rampTo(PHASE_CAPS.spelling)
			break
		case 'done':
			clearRamp()
			percent.value = 100
			runScoreTween()
			// Re-arm as active modal: an ESC during processing unsets it, which would
			// otherwise leave the result view undismissable by ESC / backdrop.
			rootStore.setActiveModal(modalName)
			break
		default:
			clearRamp()
			stopScoreTween()
			percent.value = 0
	}
}, { immediate: true })

const startOptimize = () => truSeoHighlighterStore.optimizePost()

const close = () => truSeoHighlighterStore.closeOptimizeModal()

// The Image Generator lives in the AI Content tab, so switching there is part of opening
// it — same sequence extend-block-editor.js uses, including the delay that lets this modal
// finish closing before the next one animates in.
const openImageGenerator = () => {
	close()

	window.aioseoBus.$emit('do-post-settings-main-tab-change', { name: 'aiContent' })

	setTimeout(() => {
		aiImageGeneratorStore.resetInitiator()
		aiStore.isModalOpened = 'image-generator'
	}, 300)
}

// The modal is not dismissable while processing: ESC/backdrop close events are
// ignored until the run finishes.
const handleClose = () => {
	if (isProcessing.value) {
		return
	}

	truSeoHighlighterStore.closeOptimizeModal()
}

onBeforeUnmount(() => {
	clearRamp()
	stopScoreTween()
})
</script>

<style lang="scss">
.aioseo-optimize-modal {
	&__header-title {
		font-weight: $font-bold;
	}

	&__start {
		padding: 32px 40px;
		text-align: center;

		.aioseo-optimize-modal__actions {
			justify-content: center;
		}
	}

	&__preview {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 16px;
		margin-bottom: 24px;
	}

	&__preview-card {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 14px 16px;
		border: 1px solid $border;
		border-radius: 8px;
		background: #fff;

		&--after {
			border-color: rgba($green, 0.4);
			box-shadow: 0 2px 10px rgba($green, 0.15);
		}
	}

	&__ring {
		position: relative;
		width: 56px;
		height: 56px;
		flex-shrink: 0;
	}

	&__ring-svg {
		width: 100%;
		height: 100%;
		transform: rotate(-90deg);
	}

	&__ring-track {
		fill: none;
		stroke: $border;
		stroke-width: 4;
	}

	&__ring-value {
		fill: none;
		stroke-width: 4;
		stroke-linecap: round;
		// r=16 → circumference ≈ 100.53; dashoffset sets the visible arc length.
		stroke-dasharray: 100.53;
	}

	&__ring--before &__ring-value {
		stroke: $orange;
		stroke-dashoffset: 52.3;
	}

	&__ring--after &__ring-value {
		stroke: $green;
		stroke-dashoffset: 8;
	}

	&__ring-score {
		position: absolute;
		inset: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 16px;
		font-weight: $font-bold;
	}

	&__ring--before &__ring-score {
		color: $orange;
	}

	&__ring--after &__ring-score {
		color: $green;
	}

	// Score-driven color modifiers (done-view hero rings).
	&__ring--green &__ring-value {
		stroke: $green;
	}

	&__ring--orange &__ring-value {
		stroke: $orange;
	}

	&__ring--red &__ring-value {
		stroke: $red;
	}

	&__ring--green &__ring-score {
		color: $green;
	}

	&__ring--orange &__ring-score {
		color: $orange;
	}

	&__ring--red &__ring-score {
		color: $red;
	}

	&__ring--hero {
		width: 72px;
		height: 72px;

		.aioseo-optimize-modal__ring-score {
			font-size: 20px;
		}
	}

	&__preview-lines {
		list-style: none;
		margin: 0;
		padding: 0;
		display: flex;
		flex-direction: column;
		gap: 8px;

		li {
			display: flex;
			align-items: center;
			gap: 6px;
		}
	}

	&__preview-dot {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background: $red;
		flex-shrink: 0;
	}

	&__preview-bar {
		width: 44px;
		height: 6px;
		border-radius: 100px;
		background: $gray;
	}

	&__preview-lines--resolved &__preview-bar {
		background: rgba($green, 0.25);
	}

	&__preview-check {
		width: 14px;
		height: 14px;
		border-radius: 50%;
		background: $green;
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;

		svg {
			width: 9px;
			height: 9px;
			fill: none;
			stroke: currentColor;
			stroke-width: 2;
			stroke-linecap: round;
			stroke-linejoin: round;
		}
	}

	&__preview-glyph {
		width: 24px;
		height: 24px;
		color: $blue;
		flex-shrink: 0;
	}

	// Entrance animation: the intro "after" ring fills in, its resolved rows pop,
	// and the done-view checklist pops. The done-view score ring + count-up are
	// JS-driven (see runScoreTween). Gated to no-preference so reduced-motion users
	// get the final static state with zero animation.
	@media (prefers-reduced-motion: no-preference) {
		&__ring--after &__ring-value {
			animation: aioseo-optimize-ring 900ms ease-out;
		}

		&__preview-lines--resolved li,
		&__resolved-list li {
			animation: aioseo-optimize-pop 360ms ease-out backwards;

			&:nth-child(1) { animation-delay: 300ms; }
			&:nth-child(2) { animation-delay: 400ms; }
			&:nth-child(3) { animation-delay: 500ms; }
			&:nth-child(4) { animation-delay: 600ms; }
			&:nth-child(5) { animation-delay: 700ms; }
			&:nth-child(6) { animation-delay: 800ms; }
			&:nth-child(7) { animation-delay: 900ms; }
		}
	}

	&__start-description {
		color: $black2;
		font-size: 14px;
		line-height: 1.6;
		margin: 0 0 24px;
	}

	&__error,
	&__skipped {
		text-align: left;
		margin-bottom: 20px;
	}

	&__processing {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		gap: 20px;
		padding: 56px 40px;

		.aioseo-loading-bar {
			max-width: 400px;
		}

		// The JS ramp already jumps to the cap under reduced motion; kill the
		// shared bar's width transition so the jump doesn't slide (this modal only).
		@media (prefers-reduced-motion: reduce) {
			.aioseo-loading-bar__progress {
				transition: none;
			}
		}
	}

	&__processing-lottie {
		margin: 0 auto;
	}

	&__complete {
		padding: 24px 40px 32px;
	}

	&__score-hero {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 12px;
		margin-bottom: 24px;
	}

	&__score-rings {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 16px;
	}

	&__score-delta {
		display: inline-flex;
		align-items: center;
		padding: 4px 10px;
		border-radius: 100px;
		font-size: 14px;
		font-weight: $font-bold;
		background: $background;
		color: $black2;

		&--positive {
			background: rgba($green, 0.12);
			color: $green;
		}
	}

	&__score-caption {
		margin: 0;
		color: $black2;
		font-size: 14px;
		font-weight: $font-bold;
	}

	&__section-title {
		color: $black;
		font-size: 13px;
		font-weight: $font-bold;
		margin-bottom: 10px;
	}

	&__resolved {
		margin-bottom: 24px;
	}

	&__resolved-list {
		list-style: none;
		margin: 0;
		padding: 0;
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 10px 20px;

		li {
			display: flex;
			align-items: flex-start;
			gap: 8px;
			color: $black2;
			font-size: 13px;
			line-height: 1.4;
		}
	}

	&__resolved-check {
		width: 16px;
		height: 16px;
		border-radius: 50%;
		background: $green;
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		margin-top: 1px;

		svg {
			width: 10px;
			height: 10px;
			fill: none;
			stroke: currentColor;
			stroke-width: 2;
			stroke-linecap: round;
			stroke-linejoin: round;
		}
	}

	&__resolved-more {
		grid-column: 1 / -1;
		color: $placeholder-color;
		font-style: italic;
	}

	&__resolved-text {
		flex: 1;
	}

	&__keyword-field {
		max-width: 340px;
		margin: 0 auto 20px;
		text-align: left;
	}

	&__keyword-error {
		margin: 6px 0 0;
		color: $red;
		font-size: 12.5px;
	}

	&__next {
		margin-top: 24px;
		padding-top: 20px;
		border-top: 1px solid $border;
	}

	&__next-description {
		margin: -4px 0 12px;
		color: $black2;
		font-size: 12.5px;
		line-height: 1.45;
	}

	&__next-list {
		list-style: none;
		margin: 0;
		padding: 0;
		display: grid;
		gap: 12px;

		li {
			display: flex;
			align-items: flex-start;
			gap: 8px;
		}
	}

	// The resolved list's tick, greyed out: same shape, not yet earned.
	&__next-check {
		width: 16px;
		height: 16px;
		border-radius: 50%;
		background: $placeholder-color;
		color: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		margin-top: 1px;

		svg {
			width: 10px;
			height: 10px;
			fill: none;
			stroke: currentColor;
			stroke-width: 2;
			stroke-linecap: round;
			stroke-linejoin: round;
		}
	}

	// Neutral rather than the resolved list's green — these points are still on the table.
	&__next-points {
		margin-left: 8px;
		padding: 1px 7px;
		border-radius: 100px;
		background: rgba($black2, 0.08);
		color: $black2;
		font-size: 11px;
		font-weight: $font-bold;
	}

	&__next-text {
		flex: 1;
	}

	&__next-title {
		display: block;
		color: $black;
		font-size: 13px;
		font-weight: $font-bold;
		line-height: 1.4;
	}

	&__next-hint {
		display: block;
		margin-top: 2px;
		color: $black2;
		font-size: 12.5px;
		line-height: 1.45;
	}

	// Styled as a feature prompt rather than a notice — it offers something rather than
	// warning about something, and mirrors the out-of-credits upsell's feature strip.
	&__next-cta {
		display: flex;
		align-items: center;
		gap: 12px;
		margin-top: 10px;
		padding: 12px 14px;
		border: 1px solid rgba($blue, 0.25);
		border-radius: 6px;
		background: linear-gradient(135deg, $blue4, #E4EDFF);
		font-size: 12.5px;
		line-height: 1.45;
		color: $black2;

		.aioseo-button svg {
			margin-right: 6px;
		}
	}

	&__next-cta-icon {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		width: 34px;
		height: 34px;
		border-radius: 6px;
		background: #fff;
		box-shadow: 0 1px 2px rgba($black, 0.08);

		svg {
			width: 18px;
			height: 18px;
			color: $blue;
		}
	}

	&__next-cta-text {
		flex: 1;
	}

	&__next-cta-title {
		display: block;
		color: $black;
		font-weight: $font-bold;
	}

	&__next-more {
		color: $placeholder-color;
		font-style: italic;
		font-size: 13px;
	}

	&__resolved-points {
		margin-left: 8px;
		padding: 1px 7px;
		border-radius: 100px;
		background: rgba($green, 0.12);
		color: $green;
		font-size: 11px;
		font-weight: $font-bold;
	}

	&__diffs {
		display: flex;
		flex-direction: column;
		gap: 16px;
		margin-bottom: 20px;
	}

	&__diff {
		border: 1px solid $border;
		border-radius: 4px;
		padding: 12px 16px;

		&--reverted {
			opacity: 0.55;
		}

		&-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 8px;
		}

		&-label {
			color: $black;
			font-size: 13px;
			font-weight: $font-bold;
		}

		&-revert {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			padding: 0;
			border: 0;
			background: none;
			color: $blue;
			font-size: 12px;
			font-weight: $font-bold;
			cursor: pointer;
			flex-shrink: 0;

			svg {
				width: 12px;
				height: 12px;
			}

			&:disabled {
				color: $placeholder-color;
				cursor: default;
			}
		}

		&-values {
			display: flex;
			align-items: flex-start;
			gap: 10px;
			font-size: 13px;
			line-height: 1.5;
		}

		&-before {
			color: $placeholder-color;
			text-decoration: line-through;
			flex: 1;
			word-break: break-word;
			// Clamp to 2 lines so a very long value can't blow up the modal.
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}

		&-arrow {
			color: $placeholder-color;
			flex-shrink: 0;
		}

		&-after {
			color: $green;
			flex: 1;
			word-break: break-word;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}

		&-block {
			& + & {
				margin-top: 10px;
				padding-top: 10px;
				border-top: 1px dashed $border;
			}
		}

		&-content {
			font-size: 13px;
			line-height: 1.6;
			color: $black2;
			max-height: 220px;
			overflow-y: auto;

			ins {
				background: rgba($green, 0.14);
				color: $green;
				text-decoration: none;
			}

			del {
				background: rgba($red, 0.1);
				color: $red;
			}
		}

		&-summary {
			font-size: 13px;
			line-height: 1.5;
			color: $black2;
		}

		&-metric {
			margin-top: 8px;
			font-size: 12px;
			color: $placeholder-color;
		}
	}

	&__summary {
		margin: 0 0 20px;
		padding: 16px;
		background: $background;
		border-radius: 4px;
		color: $black2;
		font-size: 13px;
		line-height: 1.6;
	}

	&__dict-words {
		display: block;
		margin-top: 4px;
		color: $placeholder-color;
		font-style: italic;
	}

	&__empty {
		color: $black2;
		font-size: 14px;
		line-height: 1.6;
		margin: 0 0 20px;
	}

	&__actions {
		display: flex;
		justify-content: flex-end;
		gap: 12px;
	}
}

.aioseo-optimize-modal-wrap {
	.modal-container__footer {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: flex-start;
		padding: 12px 20px;
	}

	.footer-left {
		display: flex;
		flex-direction: row;
		align-items: center;
		gap: 12px;
	}

	.footer-right {
		display: flex;
		flex-direction: row;
		align-items: center;
		gap: 12px;
		margin-left: auto;
	}
}

@keyframes aioseo-optimize-ring {
	from {
		stroke-dashoffset: 100.53;
	}

	to {
		stroke-dashoffset: 8;
	}
}

@keyframes aioseo-optimize-pop {
	from {
		opacity: 0;
		transform: translateY(4px) scale(0.9);
	}

	to {
		opacity: 1;
		transform: none;
	}
}
</style>