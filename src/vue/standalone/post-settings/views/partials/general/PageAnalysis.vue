<template>
	<div class="page-analysis-panel">
		<core-alert
			v-if="pageBuilderAlert"
			type="yellow"
		>
			<span v-html="pageBuilderAlert" />
		</core-alert>

		<core-main-tabs
			:tabs="parseTabs"
			:showSaveButton="false"
			:active="initTab"
			internal
			@changed="value => processChangeTab(value)"
		>
			<template #after-label="{ tab }">
				<span
					v-if="tab.errorCount >= 0"
					class="tab-score"
					:class="getErrorClass(postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors)"
				>
					<svg-ellipse
						v-if="0 < postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors"
						width="6"
					/>
					<svg-circle-check
						v-if="0 === postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors"
						width="12"
					/>
					{{ getErrorDisplay(postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors) }}
				</span>
			</template>
		</core-main-tabs>
		<transition mode="out-in">
			<metaboxAnalysisDetail
				v-if="postEditorStore.currentPost.page_analysis"
				:analysisItems="postEditorStore.currentPost.page_analysis.analysis[initTab]"
			/>
		</transition>
	</div>
</template>

<script>
import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { isBlockEditor } from '@/vue/utils/context'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import metaboxAnalysisDetail from './MetaboxAnalysisDetail'
import SvgEllipse from '@/vue/components/common/svg/Ellipse'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import { useTruSeoScore } from '@/vue/composables/TruSeoScore'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getErrorClass,
			getErrorDisplay,
			strings
		} = useTruSeoScore()

		return {
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore(),
			getErrorClass,
			getErrorDisplay,
			strings
		}
	},
	components : {
		CoreAlert,
		CoreMainTabs,
		metaboxAnalysisDetail,
		SvgEllipse,
		SvgCircleCheck
	},
	data () {
		return {
			initTab : 'basic',
			tabs    : [
				{
					slug : 'basic',
					name : __('Basic SEO', td)
				},
				{
					slug : 'title',
					name : __('Title', td)
				},
				{
					slug : 'readability',
					name : __('Readability', td)
				}
			]
		}
	},
	computed : {
		parseTabs () {
			return this.tabs.map(tab => {
				tab.errorCount = this.postEditorStore.currentPost.page_analysis.analysis[tab.slug].errors

				return tab
			})
		},
		pageBuilderAlert () {
			if (!isBlockEditor() || ![ 'elementor', 'divi' ].includes(this.rootStore.aioseo.integration)) {
				return false
			}

			const pageBuilderName = this.rootStore.aioseo.integration.charAt(0).toUpperCase() + this.rootStore.aioseo.integration.slice(1)

			return sprintf(
				// Translators: 1 - The Page Builder name, 2 - HTML code opening tag, 3 - HTML code closing tag.
				__('We have detected that you are currently using the %1$s Page Builder. Please click %2$shere%3$s to use the %1$s editor for a most accurate result.', td),
				pageBuilderName,
				'<a href="' + this.postEditorStore.currentPost.editlink + '">',
				'</a>'
			)
		}
	},
	methods : {
		processChangeTab (newTabValue) {
			this.initTab = newTabValue
		}
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-post-general .page-analysis-panel {

	.aioseo-alert {
		margin-bottom: 12px;
	}

	.aioseo-tabs {
		background: #fff;

		.var-tab {
			display: flex;
			font-weight: 700;
		}
	}
}
</style>