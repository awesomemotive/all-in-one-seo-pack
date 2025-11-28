<template>
	<div class="aioseo-ai-insights-report">
		<grid-row>
			<grid-column>
				<div class="back-link">
					<router-link :to="{ name: 'keyword-reports' }">
						← {{ strings.backToReports }}
					</router-link>
				</div>

				<!-- Initial loading - centered loader only -->
				<div v-if="loading" class="loading-state">
					<core-loader :dark="true" />
				</div>

				<!-- After initial load - show title and content -->
				<template v-else>
					<h1 v-if="report" class="report-title">
						{{ polling ? strings.loadingResultsFor : strings.resultsFor }} "<strong>{{ report.keyword }}</strong>"
					</h1>

					<!-- Polling progress bar -->
					<core-loader-progress
						v-if="polling && report && (report.status === 'pending' || report.status === 'processing')"
						:duration="15"
					/>

					<div v-if="report" class="report-content">
						<!-- Only show content when report is completed -->
						<template v-if="report.status === 'completed'">
							<div class="report-section">
								<Brands :report="report" />
								<ReportResults :report="report" />
							</div>
						</template>
					</div>

					<div v-else class="error-state">
						<p>{{ strings.errorLoading }}</p>
					</div>
				</template>
			</grid-column>
		</grid-row>
	</div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreLoaderProgress from '@/vue/components/common/core/LoaderProgress'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'

import Brands from '../partials/Brands.vue'
import ReportResults from '../partials/ReportResults.vue'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const route = useRoute()

// Reactive data
const report       = ref(null)
const loading      = ref(true)
const polling      = ref(false)
const pollInterval = ref(null)

// Strings
const strings = {
	backToReports     : __('Back to Reports', td),
	resultsFor        : __('Results for:', td),
	loadingResultsFor : __('Loading results for:', td),
	errorLoading      : __('Error loading report.', td)
}

// Methods
const fetchReport = async () => {
	const uuid = route.params.uuid
	if (!uuid) {
		loading.value = false
		return
	}

	try {
		const response = await http.get(links.restUrl(`ai/insights/reports/${uuid}`))
			.then(res => res.body)

		if (response.success && response.data?.report) {
			report.value = response.data.report

			if ([ 'pending', 'processing' ].includes(report.value?.status)) {
				startPolling()
			}

			if ([ 'completed', 'failed' ].includes(report.value?.status)) {
				stopPolling()
			}
		} else {
			report.value = null
			stopPolling()
		}
	} catch (error) {
		console.error('Error fetching report:', error)
		report.value = null
		stopPolling()
	} finally {
		loading.value = false
	}
}

const startPolling = () => {
	if (polling.value) {
		return
	}

	polling.value = true
	pollInterval.value = setInterval(fetchReport, 5000)
}

const stopPolling = () => {
	polling.value = false
	if (pollInterval.value) {
		clearInterval(pollInterval.value)
		pollInterval.value = null
	}
}

// Lifecycle
onMounted(() => {
	loading.value = true
	fetchReport()
})

onBeforeUnmount(() => {
	stopPolling()
})
</script>

<style lang="scss">
.aioseo-ai-insights-report {
	.back-link {
		margin-bottom: 24px;

		a {
			color: $blue;
			text-decoration: underline;
			font-size: 14px;

			&:hover {
				color: $blue2;
				text-decoration: underline;
			}
		}
	}

	.report-title {
		margin: 0 0 16px 0;
		font-size: 24px;
		font-weight: 600;
		line-height: 1.4;

		strong {
			font-weight: 600;
		}
	}

	.loading-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		min-height: 400px;
		text-align: center;

		p {
			margin-top: 16px;
		}
	}

	.aioseo-loader-progress {
		margin-bottom: 24px;
	}

	.error-state {
		text-align: center;
		padding: 40px 20px;

		p {
			margin-top: 16px;
		}
	}
}
</style>