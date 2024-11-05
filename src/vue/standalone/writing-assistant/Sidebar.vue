<template>
	<div>
		<div class="aioseo-app">
			<div class="aioseo-writing-assistant aioseo-writing-assistant-sidebar">
				<div v-if="writingAssistantStore.seoBoost.isLoggedIn">
					<div v-if="writingAssistantStore.hasReport && !writingAssistantStore.polling">
						<current-keyword />
						<optimization-wizard/>
					</div>

					<div
						v-if="! writingAssistantStore.hasReport && ! writingAssistantStore.polling">
						<div class="aioseo-writing-assistant-sidebar__tab-content">
							<first-report/>
						</div>
					</div>

					<div v-if="writingAssistantStore.polling">
						<div class="aioseo-writing-assistant-sidebar__tab-content">
							<processing/>
						</div>
					</div>
				</div>

				<div v-if="!writingAssistantStore.seoBoost.isLoggedIn">
					<div class="aioseo-writing-assistant-sidebar__tab-content">
						<authenticate-seoboost></authenticate-seoboost>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import OptimizationWizard from '@/vue/standalone/writing-assistant/views/sidebar/OptimizationWizard'
import AuthenticateSeoboost from '@/vue/standalone/writing-assistant/views/partials/authenticate/Seoboost'
import FirstReport from '@/vue/standalone/writing-assistant/views/report/FirstReport'
import Processing from '@/vue/standalone/writing-assistant/views/report/Processing'
import CurrentKeyword from '@/vue/standalone/writing-assistant/views/report/CurrentKeyword'

import { useWritingAssistantStore } from '@/vue/stores'

const writingAssistantStore = useWritingAssistantStore()
</script>

<style lang="scss">
.aioseo-writing-assistant-sidebar {
	padding: 16px;

	.aioseo-writing-assistant-report-keyword {
		margin-bottom: 20px;
	}

	.aioseo-tabs {
		--tabs-item-horizontal-height: 43px;
		background: $background;

		.tabs-scroller {
			width: 100%;
		}

		.var-tab {
			padding: 0;
		}

		svg {
			width: 12px;
			height: 12px;
			margin-right: 12px;
		}
	}

	.aioseo-writing-assistant-keyword {
		> .aioseo-col:first-child {
			display: none;
		}

		.button-percentage {
			flex-direction: column;
		}

		.button-percentage > div, .aioseo-button {
			width: 100% !important;
		}
	}
}
</style>