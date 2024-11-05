<template>
	<div
		class="aioseo-writing-assistant-report"
		:class="{loading: writingAssistantStore.processing && !writingAssistantStore.polling}"
	>
		<div v-if="writingAssistantStore.hasReport && !writingAssistantStore.polling">
			<core-main-tabs
				:tabs="tabs"
				:showSaveButton="false"
				:active="tab"
				internal
				disableMobile
				@changed="value => processChangeTab(value)"
			>
				<template #extra>
					<reports-remaining />
					<generate-report
						button-size="small"
					/>
				</template>
			</core-main-tabs>
			<div class="aioseo-writing-assistant-report__tab-content">
				<transition name="route-fade" mode="out-in">
					<component
						:is="tab"
						@change-tab="value => processChangeTab(value)"
					/>
				</transition>
			</div>
		</div>

		<div v-if="!writingAssistantStore.hasReport && !writingAssistantStore.polling">
			<div class="aioseo-writing-assistant-report__tab-content">
				<first-report />
			</div>
		</div>

		<div v-if="writingAssistantStore.polling">
			<div class="aioseo-writing-assistant-report__tab-content">
				<processing />
			</div>
		</div>
	</div>
</template>

<script>
import { useWritingAssistantStore } from '@/vue/stores'
import Overview from './Overview'
// import KeywordSearch from './KeywordSearch' ( future development )
import Competitors from './Competitors'
import OptimizationWizard from './OptimizationWizard'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import ReportsRemaining from '@/vue/standalone/writing-assistant/views/partials/seoboost/ReportsRemaining'
import GenerateReport from '@/vue/standalone/writing-assistant/views/report/GenerateReport'
import FirstReport from '@/vue/standalone/writing-assistant/views/report/FirstReport'
import Processing from '@/vue/standalone/writing-assistant/views/report/Processing'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			writingAssistantStore : useWritingAssistantStore()
		}
	},
	components : {
		Overview,
		// KeywordSearch, ( future development )
		Competitors,
		OptimizationWizard,
		CoreMainTabs,
		ReportsRemaining,
		GenerateReport,
		FirstReport,
		Processing
	},
	data () {
		return {
			tab  : 'overview',
			tabs : [
				{
					slug      : 'overview',
					name      : __('Overview', td),
					component : Overview
				},
				/* ( future development )
				{
					slug      : 'keyword-search',
					name      : 'Keyword Search',
					component : KeywordSearch
				},
				*/
				{
					slug      : 'optimization-wizard',
					name      : __('Optimization Wizard', td),
					component : OptimizationWizard
				},
				{
					slug      : 'competitors',
					name      : __('Competitors', td),
					component : Competitors
				}
			]
		}
	},
	methods : {
		processChangeTab (newTabValue) {
			this.tab = newTabValue
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant-report {
	margin: 0 -20px;

	&.loading {
		opacity: 0.5;
		pointer-events: none;
	}

	.aioseo-tabs {
		.tabs-extra {
			display: flex;
			align-items: center;
			gap: 12px;
			padding-right: 10px;
		}
	}

	.md-tabs {
		.md-button {
			height: 50px !important;
		}
	}

	&__tab-content {
		padding: 32px 20px;
	}

}
</style>