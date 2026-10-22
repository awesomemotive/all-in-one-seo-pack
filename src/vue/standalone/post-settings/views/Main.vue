<template>
	<div class="aioseo-app aioseo-post-settings" v-if="getTabs.length">
		<core-main-tabs
			:tabs="getTabs"
			:showSaveButton="false"
			:active="activeTab"
			internal
			disableMobile
			@changed="value => processChangeTab(value)"
			v-if="'sidebar' !== screenContext"
		>
			<template #var-tab-icon="{ tab }">
				<component
					:class="{ warning: tab.warning }"
					:is="turnSlugIntoComponent(tab.icon)"
				/>

				<component :is="turnSlugIntoComponent(tab.badge)" />
			</template>
		</core-main-tabs>

		<transition name="route-fade" mode="out-in">
			<div
				v-if="'sidebar' === screenContext && null === activeTab"
				class="aioseo-sidepanel"
			>
				<a
					v-for="(tab, index) in getTabs"
					:key="index"
					class="aioseo-sidepanel-button"
					href="#"
					@click.prevent="processChangeTab(tab.slug)"
				>
					<component class="icon" :is="turnSlugIntoComponent(tab.icon)"/>

					<div class="name">
						{{ tab.name }}
						<span
							v-if="tab.label === 'new'"
							class="label new"
						>
							{{ strings.new }}
						</span>
					</div>

					<component :is="turnSlugIntoComponent(tab.badge)" />

					<svg-circle-information-solid
						v-if="tab.warning"
						width="15"
						height="15"
					/>

					<svg-caret />
				</a>
			</div>
		</transition>

		<transition name="route-fade" mode="out-in">
			<div
				v-if="activeTab"
				:key="activeTab"
				class="aioseo-tab"
				:class="{ 'is-page-builder': !!rootStore.aioseo.integration }"
			>
				<div
					v-if="'sidebar' === screenContext"
					class="aioseo-tab-title"
				>
					<template v-if="showHeadlinePanel">
						<span>{{ strings.headlineAnalyzer }}</span>

						<svg-close @click="closeHeadlineAnalyzer" />
					</template>

					<template v-else>
						<span>{{ getTabName(activeTab) }}</span>

						<base-score-badge
							v-if="'analysis' === activeTab && isTruSeoDataReady"
							class="aioseo-tab-title__score"
							:score="postEditorStore.currentPost.seo_score"
							:loading="postEditorStore.currentPost.loading.score"
						/>

						<svg-close @click="processChangeTab(null)" />
					</template>
				</div>

				<alert v-if="'sidebar' === screenContext && !showHeadlinePanel" />

				<headline-analyzer-panel v-if="showHeadlinePanel" />

				<div v-show="!showHeadlinePanel">
					<Suspense>
						<template #default>
							<component
								:is="turnSlugIntoComponent(activeTab)"
								:parent-component-context="'sidebar' === screenContext ? 'sidebar' : 'metabox'"
								@changeTab="newTab => processChangeTab(newTab)"
							/>
						</template>
						<template #fallback>
							<div class="aioseo-loading-placeholder">
								<core-loader dark />
							</div>
						</template>
					</Suspense>
				</div>

				<review-cta
					v-if="postEditorStore.currentPost.showReviewCta && !showHeadlinePanel"
					:screen-context="screenContext"
				/>
			</div>
		</transition>

		<core-modal
			:show="postEditorStore.currentPost.modalOpen"
			@close="closeModal"
			:classes="[ 'aioseo-post-settings-modal' ]"
			modal-name="preview-snippet-editor"
		>
			<template #headerTitle>
				{{ strings.modalTitle }}
			</template>

			<template #body>
				<modal-content />
			</template>
		</core-modal>

		<keyword-rank-tracker
			:modal-open="keywordRankTrackerStore.modalOpenPostEdit"
			@update:modal-open="keywordRankTrackerStore.toggleModal({modal:'modalOpenPostEdit', open: $event})"
			modal-name="keyword-rank-tracker-modal"
		/>

		<optimize-modal />

		<safe-words-modal
			:show="truSeoHighlighterStore.safeWordsModalOpen"
			@close="handleSafeWordsClose"
		/>
	</div>
</template>

<script setup>
import { computed, defineAsyncComponent, getCurrentInstance, nextTick, onBeforeMount, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import {
	useAiAssistantStore,
	useKeywordRankTrackerStore,
	useLicenseStore,
	usePostEditorStore,
	useRedirectsStore,
	useRootStore,
	useSeoRevisionsStore,
	useSettingsStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { getParams, removeParam } from '@/vue/utils/params'
import { useMainSidebarIntent } from '../composables/MainSidebarIntent'
import { useScrollTo } from '@/vue/composables/ScrollTo'
import { useTruSeoHighlighter } from '@/vue/composables/TruSeoHighlighter'
import { useHeadlineAnalyzer } from '@/vue/composables/HeadlineAnalyzer'
import { createDebounce, debounceContext } from '@/vue/utils/debounce'
import { preloadOnIdle } from '@/vue/utils/preload'
import { isBlockEditor, isElementorEditor, isPageBuilderEditor } from '@/vue/utils/context'
import { truSeoShouldAnalyze, supportsPageAnalysis } from '@/vue/utils/postData/helpers'
import { maybeUpdateTaxonomies } from '@/vue/plugins/tru-seo/components/taxonomies'
import {
	extendParagraphPlaceholder,
	checkAiAssistantShortcut,
	extendBlockEditorInserterButton
} from '@/vue/standalone/blocks/extend-paragraph-block'

import Alert from './partials/Alert'
import ReviewCta from './partials/ReviewCta'
import BaseScoreBadge from '@/vue/components/common/base/ScoreBadge'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModal from '@/vue/components/common/core/modal/Index'
import ModalContent from './ModalContent'
import OptimizeModal from '@/vue/components/common/tru-seo/OptimizeModal'
import SafeWordsModal from '@/vue/components/common/tru-seo/SafeWordsModal'
import HeadlineAnalyzerPanel from '@/vue/standalone/headline-analyzer/components/HeadlineAnalyzerPanel'
import SeoRevisionsCountBadge from './pro/partials-seo-revisions/CountBadge'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import SvgBackup from '@/vue/components/common/svg/Backup'
import SvgBuild from '@/vue/components/common/svg/Build'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleInformationSolid from '@/vue/components/common/svg/circle/InformationSolid'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgLinkSuggestion from '@/vue/components/common/svg/link/Suggestion'
import SvgReceipt from '@/vue/components/common/svg/Receipt'
import SvgRedirectCrossedArrows from '@/vue/components/common/svg/redirect/CrossedArrows'
import SvgSettings from '@/vue/components/common/svg/Settings'
import SvgSpeed from '@/vue/components/common/svg/Speed'

const Advanced            = defineAsyncComponent(() => import('./Advanced'))
const KeywordRankTracker  = defineAsyncComponent(() => import('./KeywordRankTracker'))
const AiContent           = defineAsyncComponent(() => import('./AiContent'))
const Analysis            = defineAsyncComponent(() => import('./Analysis'))
const General             = defineAsyncComponent(() => import('./General'))
const LinkAssistant       = defineAsyncComponent(() => import('./Links'))
const Redirects           = defineAsyncComponent(() => import('./Redirects'))
const Schema              = defineAsyncComponent(() => import('./Schema'))
const SeoRevisions        = defineAsyncComponent(() => import('./SeoRevisions'))

const td = import.meta.env.VITE_TEXTDOMAIN

const aiAssistantStore        = useAiAssistantStore()
const keywordRankTrackerStore = useKeywordRankTrackerStore()
const licenseStore            = useLicenseStore()
const postEditorStore         = usePostEditorStore()
const redirectsStore          = useRedirectsStore()
const rootStore               = useRootStore()
const seoRevisionsStore       = useSeoRevisionsStore()
const settingsStore           = useSettingsStore()
const truSeoHighlighterStore  = useTruSeoHighlighterStore()

const { scrollTo } = useScrollTo()
const { headlineAnalyzerEnabled, headlineAnalyzerOpen, closeHeadlineAnalyzer } = useHeadlineAnalyzer()
const { getIntent: getMainSidebarIntent, clearIntent: clearMainSidebarIntent } = useMainSidebarIntent()

// Mirror Analysis.vue's readiness gate so the tab-header score only appears once
// real TruSEO analysis data exists.
const isTruSeoDataReady = computed(() => {
	const basic = postEditorStore.truseoData?.truseo?.general?.basic
	if (!basic || 'object' !== typeof basic) {
		return false
	}

	const basicResults = Object.values(basic)

	return 0 < basicResults.length && basicResults.some(item => item && item.title)
})

// The highlighter must work regardless of the active tab, so its highlight-sentence
// watcher lives here (Main is always mounted) rather than in the Optimization tab,
// which unmounts when another tab is active. Elementor mounts its own host at load
// ({@see HighlighterHost}), and a second instance would repaint every mark twice.
if (!isElementorEditor()) {
	const { watchHighlightSentences } = useTruSeoHighlighter()
	const debouncedWatchHighlightSentences = createDebounce((value, oldValue) => watchHighlightSentences(value, oldValue), 300)
	watch(() => truSeoHighlighterStore.allHighlightSentences, (value, oldValue) => {
		debouncedWatchHighlightSentences(value, oldValue)
	}, { deep: true, immediate: true })
}

// Seed the painted analyzer set as soon as analysis lands, regardless of the
// active tab, so highlights show on page load when highlighting is on instead
// of only after opening the Optimization tab. Both seeders are additive,
// idempotent, and gated on the master toggle; spelling is picked up once its
// dictionary finishes loading and the store gains its results.
watch(() => truSeoHighlighterStore.availableHighlightAnalyzers, () => {
	truSeoHighlighterStore.syncNewHighlightAnalyzers()
	truSeoHighlighterStore.ensureSpellingHighlightPainted()
}, { immediate: true })

const activeTab            = ref('general')
const modal                = ref(false)
const watchBlockEditor     = ref(null)

const strings = {
	pageName         : 'Appearance',
	modalTitle       : __('Preview Snippet Editor', td),
	new              : __('NEW!', td),
	headlineAnalyzer : __('Headline Analyzer', td)
}

const screenContext = computed(() => {
	return getCurrentInstance().root.data.screenContext
})

// The Headline Analyzer replaces the Optimization tab content in place (the tab
// header shows its title + a close). Scoped to the Optimization tab so switching
// tabs while it's open reveals the new tab instead of the analyzer.
const showHeadlinePanel = computed(() => headlineAnalyzerOpen.value && 'sidebar' === screenContext.value && 'analysis' === activeTab.value)

const tabs = computed(() => {
	const tabs = [
		{
			slug       : 'general',
			icon       : 'svg-settings',
			name       : __('Appearance', td),
			// The Social section lives on this tab too, so show it for either cap;
			// each section inside gates on its own general/social permission.
			permission : [ 'aioseo_page_general_settings', 'aioseo_page_social_settings' ]
		},
		{
			slug       : 'analysis',
			icon       : 'svg-speed',
			name       : __('Optimization', td),
			permission : 'aioseo_page_analysis',
			label      : 'new'
		},
		{
			slug       : 'schema',
			icon       : 'svg-receipt',
			name       : __('Schema', td),
			permission : 'aioseo_page_schema_settings'
		},
		{
			slug       : 'aiContent',
			icon       : 'svg-ai-content',
			name       : __('AI Copilot', td),
			permission : 'aioseo_page_ai_content_settings'
		},
		{
			slug       : 'redirects',
			icon       : 'svg-redirect-crossed-arrows',
			name       : __('Redirects', td),
			warning    : (0 < redirectsStore.rows.filter(row => !!row.enabled).length),
			permission : 'aioseo_page_redirects_manage'
		},
		{
			slug       : 'seoRevisions',
			icon       : 'svg-backup',
			name       : __('SEO Revisions', td),
			badge      : 'seo-revisions-count-badge',
			permission : 'aioseo_page_seo_revisions_settings'
		},
		{
			slug       : 'advanced',
			icon       : 'svg-build',
			name       : __('Advanced', td),
			permission : 'aioseo_page_advanced_settings'
		}
	]

	if (
		!rootStore.aioseo.integration &&
		!isPageBuilderEditor() &&
		'post' === postEditorStore.currentPost?.context &&
		!postEditorStore.currentPost?.linkAssistant?.isExcludedPost &&
		'attachment' !== postEditorStore.currentPost?.postType
	) {
		tabs.splice(4, 0,
			{
				slug       : 'linkAssistant',
				icon       : 'svg-link-suggestion',
				name       : __('Link Assistant', td),
				permission : 'aioseo_page_link_assistant_settings'
			})
	}

	return tabs
})

// The Optimization tab hosts TruSEO analysis and the Headline Analyzer. It's
// hidden on page types that support neither (e.g. media, special pages), and
// otherwise stays hidden only when both features are off.
const showOptimizationTab = computed(() => supportsPageAnalysis() && (truSeoShouldAnalyze() || headlineAnalyzerEnabled.value))

const getTabs = computed(() => {
	if ('term' === postEditorStore.currentPost.context || postEditorStore.currentPost.isWooCommercePageWithoutSchema) {
		return tabs.value.filter((tab) => {
			const excludedTabs = [ 'aiContent', 'schema' ]
			if (excludedTabs.includes(tab.slug)) {
				return false
			}

			// Eligibility is decided in PHP — terms only qualify on WooCommerce collection
			// taxonomies, and schema-less WooCommerce pages never do.
			if ('analysis' === tab.slug && !showOptimizationTab.value) {
				return false
			}

			return allowed(getTabPermission(tab.slug), true)
		})
	}

	return tabs.value.filter(tab => {
		if ('analysis' === tab.slug && !showOptimizationTab.value) {
			return false
		}

		// AI Content isn't applicable to media/attachment pages.
		if ('aiContent' === tab.slug && 'attachment' === postEditorStore.currentPost?.postType) {
			return false
		}

		return allowed(getTabPermission(tab.slug), true)
	})
})

const initTab = computed(() => {
	return getTabs.value[0]?.slug || null
})

const turnSlugIntoComponent = (slug) => {
	const map = {
		general                       : General,
		analysis                      : Analysis,
		schema                        : Schema,
		redirects                     : Redirects,
		seoRevisions                  : SeoRevisions,
		advanced                      : Advanced,
		aiContent                     : AiContent,
		linkAssistant                 : LinkAssistant,
		'seo-revisions-count-badge'   : SeoRevisionsCountBadge,
		'svg-settings'                : SvgSettings,
		'svg-speed'                   : SvgSpeed,
		'svg-receipt'                 : SvgReceipt,
		'svg-redirect-crossed-arrows' : SvgRedirectCrossedArrows,
		'svg-backup'                  : SvgBackup,
		'svg-build'                   : SvgBuild,
		'svg-ai-content'              : SvgAiContent,
		'svg-link-suggestion'         : SvgLinkSuggestion
	}

	return map[slug]
}

const processChangeTab = async (newTabValue, contextOverride = null) => {
	// We need to check for null here explicitly because null values identify themselves as objects.
	if (null !== newTabValue && 'object' === typeof newTabValue) {
		await processChangeTab(newTabValue.main)
		await nextTick(() => {
			settingsStore.changeTabSettings({ setting: newTabValue.main, value: newTabValue.sub })
		})

		return
	}

	// Social is now a section inside the Appearance (general) tab; redirect legacy callers.
	if ('social' === newTabValue) {
		newTabValue = 'general'
	}

	const newScreenContext = contextOverride || screenContext.value
	if ('sidebar' === newScreenContext) {
		// Change the WordPress components panel header to static if there's a tab open.
		document.querySelectorAll('.components-panel__header').forEach(el => {
			el.style.position = null === newTabValue ? 'sticky' : 'static'
		})
	} else {
		activeTab.value = newTabValue
		settingsStore.changeTabSettings({ setting: 'main', value: newTabValue })
	}

	if ('sidebar' !== newScreenContext) {
		return
	}

	if (activeTab.value === newTabValue) {
		return
	}

	activeTab.value = newTabValue

	await nextTick()

	switch (newTabValue) {
		case 'linkAssistant':
			if (postEditorStore.currentPost.linkAssistant && !postEditorStore.currentPost.linkAssistant.modalOpen) {
				postEditorStore.currentPost.linkAssistant.modalOpen = true
			}
			break
		case 'redirects':
			if (postEditorStore.currentPost.redirects && !postEditorStore.currentPost.redirects.modalOpen) {
				postEditorStore.currentPost.redirects.modalOpen = true
			}
			break
		case 'seoRevisions':
			await nextTick()
			if (!seoRevisionsStore.modalOpenSidebar && (licenseStore.isUnlicensed || 0 === seoRevisionsStore.itemsLimit)) {
				seoRevisionsStore.modalOpenSidebar = true
			}
			break
		default:
			break
	}
}

const maybeResetActiveTab = (isModalOpen) => {
	if (isModalOpen) {
		return
	}

	if ('sidebar' !== screenContext.value) {
		return
	}

	nextTick(() => {
		processChangeTab(null)
	})
}

const closeModal = () => {
	postEditorStore.currentPost.modalOpen = false
}

const handleSafeWordsClose = ({ dirty } = {}) => {
	truSeoHighlighterStore.closeSafeWordsModal()

	if (dirty) {
		truSeoHighlighterStore.refreshAnalysisAfterDictionaryChange()
	}
}

const getTabPermission = (slug) => {
	const tab = tabs.value.find(t => t.slug === slug)
	return 'undefined' !== typeof tab.permission ? tab.permission : `aioseo_page_${tab.slug}_settings`
}

const getTabName = (slug) => {
	const tab = tabs.value.find(t => t.slug === slug)
	return tab?.name
}

watch(() => postEditorStore.currentPost, () => {
	debounceContext(postEditorStore.savePostState, 250)
}, { deep: true })

watch(() => postEditorStore.currentPost.modalOpen, (isModalOpen) => {
	if ('general' !== activeTab.value) {
		maybeResetActiveTab(isModalOpen)
	}
})

watch(() => postEditorStore.currentPost.linkAssistant?.modalOpen, (isModalOpen) => {
	maybeResetActiveTab(isModalOpen)
})

watch(() => postEditorStore.currentPost.redirects?.modalOpen, (isModalOpen) => {
	maybeResetActiveTab(isModalOpen)
})

watch(() => seoRevisionsStore.modalOpenSidebar, (isModalOpen) => {
	maybeResetActiveTab(isModalOpen)
})

// Auto-Optimize is triggered from the Analysis tab but its results (SEO title,
// description, SERP preview) render on the Appearance tab — switch there and
// scroll the refreshed Search Appearance into view once the run completes.
watch(() => truSeoHighlighterStore.optimizePhase, async (phase) => {
	if ('done' !== phase || 'sidebar' === screenContext.value) {
		return
	}

	if ('general' !== activeTab.value) {
		processChangeTab('general')
	}

	await nextTick()

	// The card is collapsible, so open it before scrolling — otherwise the results land out of view.
	settingsStore.openCard('postSettingsSearchAppearance')

	setTimeout(() => {
		scrollTo('aioseo-card-postSettingsSearchAppearance', { block: 'start' })
	}, 500)
})

const applyMainSidebarIntent = () => {
	const intent = getMainSidebarIntent()

	if ('sidebar' !== screenContext.value || !intent.tab) {
		return
	}

	processChangeTab(intent.tab)

	// The Optimization tab clears intents that also carry a section, once it has scrolled there.
	if (!intent.section) {
		clearMainSidebarIntent()
	}
}

watch(() => settingsStore.metaBoxTabs.mainSidebar, applyMainSidebarIntent, { deep: true })

const isAioseoSidebarOpen = () => 'aioseo-post-settings-sidebar/aioseo-post-settings-sidebar' ===
	window.wp.data.select('core/interface')?.getActiveComplementaryArea?.('core')

// Re-opening the sidebar before its close animation ends hands back this app's DOM instead of a
// fresh mount, so land on the tab list here. A Fix click sets its intent after this runs.
const watchSidebarReopen = () => {
	let wasOpen = isAioseoSidebarOpen()

	return window.wp.data.subscribe(() => {
		const isOpen = isAioseoSidebarOpen()
		if (isOpen && !wasOpen) {
			processChangeTab(null)
		}

		wasOpen = isOpen
	})
}

let unwatchSidebarReopen = null

onBeforeMount(() => {
	/**
	 * Make unpin button visible.
	 * @link all-in-one-seo-pack-pro/src/react/headline-analyzer/index.jsx:151
	 */
	const unpinButton = document.querySelector('.interface-complementary-area__pin-unpin-item')
	if (unpinButton) {
		unpinButton.style.display = 'block'
	}
})

onMounted(() => {
	window.aioseoBus.$on('do-post-settings-main-tab-change', ({ name, context }) => {
		processChangeTab(name, context)
	})

	if (isBlockEditor() && 'sidebar' === screenContext.value) {
		unwatchSidebarReopen = watchSidebarReopen()
	}

	if (isBlockEditor()) {
		// Defer the AI image generator setup to idle so the dynamic import
		// does not suspend `onMounted` and gate the tab-view `preloadOnIdle` below.
		const schedule = window.requestIdleCallback || (cb => setTimeout(cb, 1))
		schedule(async () => {
			const {
				extendImageBlockToolbar,
				extendImageBlockPlaceholder,
				extendFeaturedImageButton
			} = await import('@/vue/standalone/ai-image-generator/extend-block-editor')

			const hasAiContentTab = getTabs.value.some(tab => 'aiContent' === tab.slug)

			if (hasAiContentTab) {
				extendImageBlockToolbar()
			}

			// Initialize and keep track of user's block visibility preference.
			aiAssistantStore.updateBlockHiddenByUser()

			if (aiAssistantStore.hasPermission) {
				extendParagraphPlaceholder({ aiAssistantStore })
			}

			watchBlockEditor.value = window.wp.data.subscribe(() => {
				if (hasAiContentTab) {
					extendImageBlockPlaceholder()
					extendFeaturedImageButton()
				}

				// Update hidden state (only triggers reactivity if value changed).
				aiAssistantStore.updateBlockHiddenByUser()

				if (aiAssistantStore.isBlockAvailable && aiAssistantStore.hasPermission) {
					checkAiAssistantShortcut({ aiAssistantStore })
					extendBlockEditorInserterButton({ aiAssistantStore })
				}
			})
		})
	}

	preloadOnIdle([
		() => import('./General'),
		() => import('./Analysis'),
		() => import('./Schema'),
		() => import('./Advanced'),
		() => import('./AiContent'),
		() => import('./Links'),
		() => import('./Redirects'),
		() => import('./SeoRevisions'),
		() => import('./KeywordRankTracker')
	])
})

onBeforeUnmount(() => {
	window.aioseoBus.$off('do-post-settings-main-tab-change')

	unwatchSidebarReopen?.()

	if (null !== watchBlockEditor.value) {
		watchBlockEditor.value()
	}
})

// Everything below runs as it would on the old `create` lifecycle.
modal.value = getParams()['aioseo-modaltab'] || modal.value
if (modal.value) {
	settingsStore.changeTabSettings({ setting: 'modal', value: modal.value })
	postEditorStore.currentPost.modalOpen = true

	setTimeout(() => {
		removeParam('aioseo-modaltab')
	}, 500)
}

// The intent is set before the sidebar app mounts, so the watcher above never sees it.
nextTick(applyMainSidebarIntent)

window.aioseoBus.$on('standalone-update-post', (param) => {
	Object.keys(param).forEach(option => {
		postEditorStore.currentPost[option] = param[option]
	})

	if (param?.primary_term) {
		maybeUpdateTaxonomies()
	}
})

// Read the deep-linked Social sub-tab (Facebook/X) synchronously here — the editor
// strips query params shortly after load, so metabox/Social's mounted hook runs too late.
const preferredSocialTab = getParams()['social-tab']
if (preferredSocialTab) {
	settingsStore.changeTabSettings({ setting: 'social', value: preferredSocialTab })
	settingsStore.changeTabSettings({ setting: 'socialModal', value: preferredSocialTab })
	setTimeout(() => {
		removeParam('social-tab')
	}, 500)
}

switch (screenContext.value) {
	case 'sidebar' :
		activeTab.value = null
		break
	default : {
		const preferredTab = getParams()['aioseo-tab'] || settingsStore.metaBoxTabs.main
		const isAccessible = preferredTab && getTabs.value.some(tab => tab.slug === preferredTab)
		activeTab.value = isAccessible ? preferredTab : initTab.value
		settingsStore.changeTabSettings({ setting: 'main', value: activeTab.value })
		setTimeout(() => {
			removeParam('aioseo-tab')
		}, 500)
		break
	}
}
</script>

<style lang="scss">
.aioseo-loading-placeholder {
	display: flex;
	justify-content: center;
	align-items: center;
	min-height: 300px;
	padding: 40px;
}

.aioseo-app.aioseo-post-settings,
.aioseo-metabox .aioseo-app.aioseo-post-settings {
	background: #fff;
	color: $black;
	position: relative;

	&:has(.route-fade-enter-active) {
		overflow: hidden;
	}

	.aioseo-tabs {
		--tabs-item-horizontal-height: 50px;
		--tab-font-size: 14px;
		--tab-inactive-color: #{$black2};
		background: $background;

		.var-tab {
			.icon {
				display: none;
			}
		}

		svg {
			display: none;

			&.aioseo-caret {
				display: inline;
			}
		}
	}

	.aioseo-sidepanel {
		a.aioseo-sidepanel-button {
			display: flex;
			align-items: center;
			padding: 12px;
			color: $black2-hover;
			text-decoration: none;

			&:not(:last-child) {
				border-bottom: 1px solid #DDDDDD;
			}

			&:focus {
				box-shadow: none;
			}

			.icon {
				display: inline;
				width: 20px;
				height: 20px;
				margin-right: 10px;
			}

			.name {
				font-weight: $font-bold;
			}

			.aioseo-circle-information-solid {
				margin-left: 8px;
				color: $orange;
			}

			.aioseo-caret {
				margin-left: auto;
				width: 24px;
				height: 24px;
				cursor: pointer;
				transform: rotate(-90deg);
			}

			.new {
				color: #df2a4a;
				vertical-align: super;
				font-size: 10px;
				display: inline-block;
				margin-top: -5px;
			}
		}
	}

	.aioseo-tab-title {
		display: flex;
		align-items: center;
		color: $black2-hover;
		font-weight: $font-bold;
		padding: 12px;
		border-bottom: 1px solid #DDDDDD;
		background: #fff;
		position: sticky;
		z-index: 1;
		top: 0;

		&__score {
			margin-left: 8px;
		}

		> svg {
			margin-left: auto;
			width: 10px;
			height: 10px;
			cursor: pointer;
		}
	}

	.aioseo-tab-content {
		background: $background;
		border-top: 0;
		padding: 0 var(--aioseo-gutter);
		font-size: 14px;
		position: relative;
		overflow: hidden;

		&.sidebar {
			background: #fff;
			padding: var(--aioseo-gutter);
		}

		.aioseo-tab-content {
			padding: var(--aioseo-gutter);
		}
	}

	.aioseo-sidebar-content-title {
		font-weight: bold;
		font-size: 14px;
		padding-bottom: 5px;
	}
}

.edit-post-sidebar,
.editor-sidebar {
	.col-xs-12,
	.col-sm-6,
	.col-md-4,
	.col-md-3 {
		width: 100%;
		flex-basis: 100% !important;
		max-width: 100% !important;
	}

	.components-panel {
		border-bottom: none;
	}

	.aioseo-mobile-tabs {
		display: none;
	}

	.aioseo-app {
		--aioseo-gutter: 12px;

		input {
			border: 1px solid $input-border;

			&:focus {
				border-color: $blue;
				box-shadow: 0 0 0 1px $blue;
			}

			&::placeholder {
				color: $placeholder-color;
			}
		}

		.aioseo-textarea-autosize {
			border: 1px solid $input-border;
		}

		.aioseo-tab-content {
			padding: 0 var(--aioseo-gutter);
			border: none;
		}

		.route-fade {
			&-enter-active,
			&-leave-active {
				transition: opacity 0.2s, transform 0.2s;
			}
			&-enter-from,
			&-leave-active {
				position: absolute;
				top: 0;
			}
		}
	}

	.aioseo-settings-row {
		margin-bottom: 16px;
		padding-bottom: 16px;

		&:last-of-type {
			border-bottom: 0;
			margin-bottom: 0 !important;
			padding-bottom: 0 !important;
		}

		> .aioseo-col {
			padding-top: 0;
		}

		.settings-name .name {
			font-size: 14px;
			font-weight: bold;
			margin-bottom: 0;
		}
	}
}

.aioseo-post-settings-modal {
	.aioseo-modal-content {
		.aioseo-tabs {
			--tabs-item-horizontal-height: 50px;
			--tab-font-size: 14px;
			--tab-inactive-color: #{$black2};
			background: $background;

			.var-tab {
				.icon {
					display: none;
				}
			}

			svg {
				display: none;

				&.aioseo-caret {
					display: inline;
				}
			}
		}

		.aioseo-tabs.internal {
			border-bottom-width: 1px !important;

			@media screen and (max-width: 520px) {
				padding-left: 20px !important;
			}
		}

		@media only screen and (min-width: 782px) {
			.col-md-4 {
				-ms-flex-preferred-size: 33.33333333% !important;
				flex-basis: 33.33333333% !important;
				max-width: 33.33333333% !important;
			}

			.col-md-5 {
				-ms-flex-preferred-size: 41.66666667% !important;
				flex-basis: 41.66666667% !important;
				max-width: 41.66666667% !important;
			}

			.col-md-7 {
				-ms-flex-preferred-size: 58.33333333% !important;
				flex-basis: 58.33333333% !important;
				max-width: 58.33333333% !important;
			}

			.col-md-8 {
				-ms-flex-preferred-size: 66.66666667% !important;
				flex-basis: 66.66666667% !important;
				max-width: 66.66666667% !important;
			}
		}
	}
}

.aioseo-redirects.aioseo-modal {
	.bd {
		padding: 20px;
	}

	.modal-wrapper .modal-container {
		max-width: 1000px;
	}
}

#editor {
	.block-editor-inserter:has(.aioseo-ai-assistant-inserter-btn) {
		width: max-content;

		.block-editor-inserter__toggle {
			display: inline-flex;
		}
	}
}
</style>