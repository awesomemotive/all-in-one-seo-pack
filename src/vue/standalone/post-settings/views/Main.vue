<template>
	<div class="aioseo-app aioseo-post-settings">
		<core-main-tabs
			:tabs="getTabs"
			:showSaveButton="false"
			:active="activeTab"
			internal
			disableMobile
			@changed="value => processChangeTab(value)"
			v-if="'sidebar' !== $root._data.screenContext"
		>
			<template #var-tab-icon="{ tab }">
				<component
					:class="[
						{ warning: tab.warning }
					]"
					:is="tab.icon"
				/>

				<component :is="tab.badge" />
			</template>
		</core-main-tabs>

		<transition name="route-fade" mode="out-in">
			<div
				v-if="'sidebar' === $root._data.screenContext && null === activeTab"
				class="aioseo-sidepanel"
			>
				<a
					v-for="(tab, index) in getTabs"
					:key="index"
					class="aioseo-sidepanel-button"
					href="#"
					@click.prevent="processChangeTab(tab.slug)"
				>
					<component class="icon" :is="tab.icon"/>

					<div class="name">{{ tab.name }}</div>

					<component :is="tab.badge" />

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
					v-if="'sidebar' === $root._data.screenContext"
					class="aioseo-tab-title"
				>
					<span>{{ getTabName(activeTab) }}</span>

					<svg-close @click="processChangeTab(null)"/>
				</div>

				<alert v-if="'sidebar' === this.$root._data.screenContext" />

				<component
					:is="activeTab"
					parentComponentContext="metabox"
					@changeTab="newTab => processChangeTab(newTab)"
				/>
			</div>
		</transition>

		<core-modal
			:show="postEditorStore.currentPost.modalOpen && 'sidebar' === $root._data.screenContext"
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
	</div>
</template>

<script>
import {
	useLicenseStore,
	usePostEditorStore,
	useRedirectsStore,
	useRootStore,
	useSettingsStore,
	useSeoRevisionsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import license from '@/vue/utils/license'

import { getParams, removeParam } from '@/vue/utils/params'
import { debounceContext } from '@/vue/utils/debounce'
import { isBlockEditor, isPageBuilderEditor } from '@/vue/utils/context'
import { ObjectRevisions } from '@/vue/mixins/seo-revisions/SeoRevisions'
import Advanced from './Advanced'
import Alert from './partials/Alert'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModal from '@/vue/components/common/core/modal/Index'
import General from './General'
import LinkAssistant from './Links'
import ModalContent from './ModalContent'
import Redirects from './Redirects'
import Schema from './Schema'
import SeoRevisions from './SeoRevisions'
import SeoRevisionsCountBadge from './pro/partials-seo-revisions/CountBadge'
import Social from './Social'
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

export default {
	setup () {
		return {
			licenseStore      : useLicenseStore(),
			postEditorStore   : usePostEditorStore(),
			redirectsStore    : useRedirectsStore(),
			rootStore         : useRootStore(),
			settingsStore     : useSettingsStore(),
			seoRevisionsStore : useSeoRevisionsStore()
		}
	},
	components : {
		Advanced,
		Alert,
		CoreMainTabs,
		CoreModal,
		General,
		LinkAssistant,
		ModalContent,
		Redirects,
		Schema,
		SeoRevisions,
		SeoRevisionsCountBadge,
		Social,
		SvgBackup,
		SvgBuild,
		SvgCaret,
		SvgCircleInformationSolid,
		SvgClose,
		SvgLinkSuggestion,
		SvgReceipt,
		SvgRedirectCrossedArrows,
		SvgSettings,
		SvgShare
	},
	data () {
		return {
			activeTab : 'general',
			modal     : false,
			strings   : {
				pageName   : 'General',
				modalTitle : this.$t.__('Preview Snippet Editor', this.$td)
			},
			activeMainSidebarTab : '',
			isPageBuilderEditor
		}
	},
	watch : {
		'postEditorStore.currentPost' : {
			deep : true,
			handler () {
				debounceContext(this.postEditorStore.savePostState, 250)
			}
		},
		'postEditorStore.currentPost.modalOpen' (isModalOpen) {
			if ('general' !== this.activeTab) {
				this.maybeResetActiveTab(isModalOpen)
			}
		},
		'postEditorStore.currentPost.linkAssistant.modalOpen' (isModalOpen) {
			this.maybeResetActiveTab(isModalOpen)
		},
		'postEditorStore.currentPost.redirects.modalOpen' (isModalOpen) {
			this.maybeResetActiveTab(isModalOpen)
		},
		'seoRevisionsStore.modalOpenSidebar' (isModalOpen) {
			this.maybeResetActiveTab(isModalOpen)
		},
		'settingsStore.metaBoxTabs.mainSidebar' : {
			deep : true,
			handler (mainSidebar) {
				if ('sidebar' !== this.$root._data.screenContext) {
					return
				}

				if (this.activeMainSidebarTab === mainSidebar.tab) {
					return
				}

				this.activeMainSidebarTab = mainSidebar.tab

				this.processChangeTab(mainSidebar.tab)
			}
		}
	},
	computed : {
		tabs () {
			const tabs = [
				{
					slug : 'general',
					icon : 'svg-settings',
					name : this.$t.__('General', this.$td)
				},
				{
					slug : 'social',
					icon : 'svg-share',
					name : this.$t.__('Social', this.$td)
				},
				{
					slug : 'schema',
					icon : 'svg-receipt',
					name : this.$t.__('Schema', this.$td)
				},
				{
					slug       : 'redirects',
					icon       : 'svg-redirect-crossed-arrows',
					name       : this.$t.__('Redirects', this.$td),
					warning    : (0 < this.redirectsStore.rows.filter(row => !!row.enabled).length),
					permission : 'aioseo_page_redirects_manage'
				},
				{
					slug  : 'seoRevisions',
					icon  : 'svg-backup',
					name  : this.$t.__('SEO Revisions', this.$td),
					badge : 'seo-revisions-count-badge'
				},
				{
					slug : 'advanced',
					icon : 'svg-build',
					name : this.$t.__('Advanced', this.$td)
				}
			]

			if (
				!this.rootStore.aioseo.integration &&
				!isPageBuilderEditor() &&
				'post' === this.postEditorStore.currentPost.context &&
				!this.postEditorStore.currentPost.linkAssistant?.isExcludedPost
			) {
				tabs.splice(3, 0, {
					slug : 'linkAssistant',
					icon : 'svg-link-suggestion',
					name : this.$t.__('Link Assistant', this.$td)
				})
			}

			return tabs
		},
		initTab () {
			return this.getTabs[0].slug
		},
		getTabs () {
			if ('term' === this.postEditorStore.currentPost.context || this.postEditorStore.currentPost.isWooCommercePageWithoutSchema) {
				return this.tabs.filter((tab) => {
					return 'schema' !== tab.slug && allowed(this.getTabPermission(tab.slug), true)
				})
			}

			return this.tabs.filter(tab => {
				if (allowed(this.getTabPermission(tab.slug), true)) {
					return true
				}

				return (
					'general' === tab.slug &&
					(
						allowed('aioseo_page_analysis') ||
						allowed(this.getTabPermission(tab.slug), true)
					)
				)
			})
		}
	},
	methods : {
		async processChangeTab (newTabValue) {
			// We need to check for null here explicitly because null values identify themselves as objects.
			if (null !== newTabValue && 'object' === typeof newTabValue) {
				this.processChangeTab(newTabValue.main)
				this.$nextTick(() => {
					this.settingsStore.changeTabSettings({ setting: newTabValue.main, value: newTabValue.sub })
				})
				return
			}

			switch (this.$root._data.screenContext) {
				case 'sidebar' :
					// Change the WordPress components panel header to static if there's a tab open.
					document.querySelectorAll('.components-panel__header').forEach(el => {
						const position = null === newTabValue ? 'sticky' : 'static'
						el.style.position = position
					})
					break
				default :
					this.activeTab = newTabValue
					this.settingsStore.changeTabSettings({ setting: 'main', value: newTabValue })
					break
			}

			if ('sidebar' !== this.$root._data.screenContext) {
				return
			}

			if (this.activeTab === newTabValue) {
				return
			}

			this.activeTab = newTabValue

			await this.$nextTick()

			switch (newTabValue) {
				case 'social':
					if (!this.postEditorStore.currentPost.modalOpen) {
						this.settingsStore.changeTabSettings({ setting: 'modal', value: 'social' })
						this.postEditorStore.currentPost.modalOpen = true
					}
					break
				case 'linkAssistant':
					if (this.postEditorStore.currentPost.linkAssistant && !this.postEditorStore.currentPost.linkAssistant.modalOpen) {
						this.postEditorStore.currentPost.linkAssistant.modalOpen = true
					}
					break
				case 'redirects':
					if (this.postEditorStore.currentPost.redirects && !this.postEditorStore.currentPost.redirects.modalOpen) {
						this.postEditorStore.currentPost.redirects.modalOpen = true
					}
					break
				case 'seoRevisions':
					await this.$nextTick()
					if (!this.seoRevisionsStore.modalOpenSidebar && (this.licenseStore.isUnlicensed || !license.hasCoreFeature('seo-revisions'))) {
						this.seoRevisionsStore.modalOpenSidebar = true
					}
					break
				default:
					break
			}
		},
		maybeResetActiveTab (isModalOpen) {
			if (isModalOpen) {
				return
			}

			if ('sidebar' !== this.$root._data.screenContext) {
				return
			}

			this.$nextTick(() => {
				this.processChangeTab(null)
			})
		},
		closeModal () {
			this.postEditorStore.currentPost.modalOpen = false
		},
		getTabPermission (slug) {
			const tab = this.tabs.find(t => t.slug === slug)
			return 'undefined' !== typeof tab.permission ? tab.permission : `aioseo_page_${tab.slug}_settings`
		},
		getTabName (slug) {
			const tab = this.tabs.find(t => t.slug === slug)
			return tab?.name
		}
	},
	mixins : [ ObjectRevisions ],
	created () {
		this.modal = getParams()['aioseo-modaltab'] || this.modal
		if (this.modal) {
			this.settingsStore.changeTabSettings({ setting: 'modal', value: this.modal })
			this.postEditorStore.currentPost.modalOpen = true
			setTimeout(() => {
				removeParam('aioseo-modaltab')
			}, 500)
		}

		this.$nextTick(() => {
			if (this.settingsStore.metaBoxTabs.mainSidebar.tab) {
				this.processChangeTab(this.settingsStore.metaBoxTabs.mainSidebar.tab)
			}
		})

		window.aioseoBus.$on('standalone-update-post', (param) => {
			Object.keys(param).forEach(option => {
				this.postEditorStore.currentPost[option] = param[option]
			})
		})

		switch (this.$root._data.screenContext) {
			case 'sidebar' :
				this.activeTab = null
				break
			default :
				this.activeTab = getParams()['aioseo-tab'] || this.initTab
				this.settingsStore.changeTabSettings({ setting: 'main', value: this.activeTab })
				setTimeout(() => {
					removeParam('aioseo-tab')
				}, 500)
				break
		}
	},
	beforeMount () {
		/**
		 * Make unpin button visible.
		 * @link all-in-one-seo-pack-pro/src/react/headline-analyzer/index.jsx:151
		 */
		const unpinButton = document.querySelector('.interface-complementary-area__pin-unpin-item')
		if (unpinButton && null !== unpinButton) {
			unpinButton.style.display = 'block'
		}
	},
	mounted () {
		if (
			isBlockEditor() &&
			!this.licenseStore.isUnlicensed
		) {
			this.watchObjectRevisionsOnSavePost()
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-settings,
.aioseo-metabox .aioseo-post-settings {
	background: #fff;
	color: $black;

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
		.aioseo-sidepanel-button {
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

.edit-post-sidebar {
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

		.aioseo-tab:not(.is-page-builder) {
			position: relative;
			top: -45px;
		}

		.aioseo-textarea-autosize {
			border: 1px solid $input-border;
		}

		.aioseo-tab-content {
			padding: 16px;
			border: none;
		}

		.aioseo-description {
			margin: 0;
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

.aioseo-redirects.modal {
	.bd {
		padding: 20px;
	}

	.modal-wrapper .modal-container {
		max-width: 1000px;
	}
}
</style>