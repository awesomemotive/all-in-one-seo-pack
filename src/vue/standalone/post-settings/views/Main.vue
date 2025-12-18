<template>
	<div class="aioseo-app aioseo-post-settings">
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
					<span>{{ getTabName(activeTab) }}</span>

					<svg-close @click="processChangeTab(null)"/>
				</div>

				<alert v-if="'sidebar' === screenContext" />

				<component
					:is="turnSlugIntoComponent(activeTab)"
					:parent-component-context="'sidebar' === screenContext ? 'sidebar' : 'metabox'"
					@changeTab="newTab => processChangeTab(newTab)"
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
	</div>
</template>

<script setup>
import { computed, getCurrentInstance, nextTick, onBeforeMount, onBeforeUnmount, onMounted, ref, watch } from 'vue'

import {
	useAiAssistantStore,
	useKeywordRankTrackerStore,
	useLicenseStore,
	usePostEditorStore,
	useRedirectsStore,
	useRootStore,
	useSeoRevisionsStore,
	useSettingsStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { getParams, removeParam } from '@/vue/utils/params'
import { debounceContext } from '@/vue/utils/debounce'
import { isBlockEditor, isPageBuilderEditor } from '@/vue/utils/context'
import { maybeUpdateTaxonomies } from '@/vue/plugins/tru-seo/components/taxonomies'
import {
	extendParagraphPlaceholder,
	checkAiAssistantShortcut,
	extendBlockEditorInserterButton
} from '@/vue/standalone/blocks/extend-paragraph-block'

import Advanced from './Advanced'
import AiContent from './AiContent'
import Alert from './partials/Alert'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModal from '@/vue/components/common/core/modal/Index'
import General from './General'
import KeywordRankTracker from './KeywordRankTracker'
import LinkAssistant from './Links'
import ModalContent from './ModalContent'
import Redirects from './Redirects'
import Schema from './Schema'
import SeoRevisions from './SeoRevisions'
import SeoRevisionsCountBadge from './pro/partials-seo-revisions/CountBadge'
import Social from './Social'
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
import SvgShare from '@/vue/components/common/svg/Share'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiAssistantStore        = useAiAssistantStore()
const keywordRankTrackerStore = useKeywordRankTrackerStore()
const licenseStore            = useLicenseStore()
const postEditorStore         = usePostEditorStore()
const redirectsStore          = useRedirectsStore()
const rootStore               = useRootStore()
const seoRevisionsStore       = useSeoRevisionsStore()
const settingsStore           = useSettingsStore()

const updatingSeoRevisions = ref(false)
const activeTab            = ref('general')
const modal                = ref(false)
const activeMainSidebarTab = ref('')
const watchBlockEditor     = ref(null)

const strings = {
	pageName   : 'General',
	modalTitle : __('Preview Snippet Editor', td),
	new        : __('NEW!', td)
}

const screenContext = computed(() => {
	return getCurrentInstance().root.data.screenContext
})

const tabs = computed(() => {
	const tabs = [
		{
			slug       : 'general',
			icon       : 'svg-settings',
			name       : __('General', td),
			permission : 'aioseo_page_general_settings'
		},
		{
			slug       : 'social',
			icon       : 'svg-share',
			name       : __('Social', td),
			permission : 'aioseo_page_social_settings'
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
			name       : __('AI Content', td),
			permission : 'aioseo_page_ai_content_settings',
			label      : 'new'
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

const getTabs = computed(() => {
	if ('term' === postEditorStore.currentPost.context || postEditorStore.currentPost.isWooCommercePageWithoutSchema) {
		return tabs.value.filter((tab) => {
			const excludedTabs = [ 'aiContent', 'schema' ]
			return !excludedTabs.includes(tab.slug) && allowed(getTabPermission(tab.slug), true)
		})
	}

	return tabs.value.filter(tab => {
		if (allowed(getTabPermission(tab.slug), true)) {
			return true
		}

		return (
			'general' === tab.slug &&
			(
				allowed('aioseo_page_analysis') ||
				allowed(getTabPermission(tab.slug), true)
			)
		)
	})
})

const initTab = computed(() => {
	return getTabs.value[0].slug
})

const turnSlugIntoComponent = (slug) => {
	const map = {
		general                       : General,
		social                        : Social,
		schema                        : Schema,
		redirects                     : Redirects,
		seoRevisions                  : SeoRevisions,
		advanced                      : Advanced,
		aiContent                     : AiContent,
		linkAssistant                 : LinkAssistant,
		'seo-revisions-count-badge'   : SeoRevisionsCountBadge,
		'svg-settings'                : SvgSettings,
		'svg-share'                   : SvgShare,
		'svg-receipt'                 : SvgReceipt,
		'svg-redirect-crossed-arrows' : SvgRedirectCrossedArrows,
		'svg-backup'                  : SvgBackup,
		'svg-build'                   : SvgBuild,
		'svg-ai-content'              : SvgAiContent,
		'svg-link-suggestion'         : SvgLinkSuggestion
	}

	return map[slug]
}

const updateSeoRevisions = () => {
	if (
		window.wp.data.select('core/editor').isSavingPost() &&
		!window.wp.data.select('core/editor').isAutosavingPost()
	) {
		updatingSeoRevisions.value = true

		setTimeout(() => {
			seoRevisionsStore.fetch().finally(() => {
				updatingSeoRevisions.value = false
			})
		}, 2500)
	}
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
		case 'social':
			if (!postEditorStore.currentPost.modalOpen) {
				settingsStore.changeTabSettings({ setting: 'modal', value: 'social' })
				postEditorStore.currentPost.modalOpen = true
			}
			break
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

watch(() => settingsStore.metaBoxTabs.mainSidebar, (mainSidebar) => {
	if ('sidebar' !== screenContext.value) {
		return
	}

	if (activeMainSidebarTab.value === mainSidebar.tab) {
		return
	}

	activeMainSidebarTab.value = mainSidebar.tab

	processChangeTab(mainSidebar.tab)
}, { deep: true })

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

onMounted(async () => {
	window.aioseoBus.$on('do-post-settings-main-tab-change', ({ name, context }) => {
		processChangeTab(name, context)
	})

	if (isBlockEditor()) {
		const {
			extendImageBlockToolbar,
			extendImageBlockPlaceholder,
			extendFeaturedImageButton
		} = await import('@/vue/standalone/ai-image-generator/extend-block-editor')

		const hasAiContentTab = getTabs.value.some(tab => 'aiContent' === tab.slug)

		if (hasAiContentTab) {
			extendImageBlockToolbar()
		}

		if (aiAssistantStore.hasPermission) {
			extendParagraphPlaceholder()
		}

		watchBlockEditor.value = window.wp.data.subscribe(() => {
			if (!licenseStore.isUnlicensed && !updatingSeoRevisions.value) {
				updateSeoRevisions()
			}

			if (hasAiContentTab) {
				extendImageBlockPlaceholder()
				extendFeaturedImageButton()
			}

			if (aiAssistantStore.hasPermission) {
				checkAiAssistantShortcut()
				extendBlockEditorInserterButton({ aiAssistantStore })
			}
		})
	}
})

onBeforeUnmount(() => {
	window.aioseoBus.$off('do-post-settings-main-tab-change')

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

nextTick(() => {
	if (settingsStore.metaBoxTabs.mainSidebar.tab) {
		processChangeTab(settingsStore.metaBoxTabs.mainSidebar.tab)
	}
})

window.aioseoBus.$on('standalone-update-post', (param) => {
	Object.keys(param).forEach(option => {
		postEditorStore.currentPost[option] = param[option]
	})

	if (param?.primary_term) {
		maybeUpdateTaxonomies()
	}
})

switch (screenContext.value) {
	case 'sidebar' :
		activeTab.value = null
		break
	default :
		activeTab.value = getParams()['aioseo-tab'] || settingsStore.metaBoxTabs.main || initTab.value
		settingsStore.changeTabSettings({ setting: 'main', value: activeTab.value })
		setTimeout(() => {
			removeParam('aioseo-tab')
		}, 500)
		break
}
</script>

<style lang="scss">
.aioseo-app.aioseo-post-settings,
.aioseo-metabox .aioseo-app.aioseo-post-settings {
	background: #fff;
	color: $black;

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

		svg {
			margin-left: auto;
			width: 10px;
			height: 10px;
			cursor: pointer;
		}
	}

	.aioseo-tab-content {
		background: #fff;
		border-top: 0;
		padding: var(--aioseo-gutter);
		font-size: 14px;
		position: relative;
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
			padding: 16px;
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