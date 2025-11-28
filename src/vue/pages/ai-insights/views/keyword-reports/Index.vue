<template>
	<div class="aioseo-ai-insights">
		<grid-row>
			<grid-column>
				<promotional-alert />

				<core-alert
					v-if="rateLimitReached"
					type="red"
					@close-alert="showRateLimitAlert = false"
				>
					{{ rateLimitReached }}
				</core-alert>

				<core-card
					slug="queryReport"
					:toggles="false"
					hide-header
					no-slide
				>
					<div class="keyword-intro">
						<h2 class="keyword-title">{{ strings.title }}</h2>
						<p class="keyword-description">{{ strings.description }}</p>

						<ai-providers />

						<div class="keyword-form">
							<base-input
								v-model="keyword"
								:placeholder="strings.placeholder"
								:disabled="!!rateLimitReached"
								@keyup.enter="analyzeKeyword"
							/>

							<base-button
								type="blue"
								@click="analyzeKeyword"
								:loading="isAnalyzing"
								:disabled="!keyword || isAnalyzing || !!rateLimitReached"
							>
								{{ strings.analyze }}
							</base-button>
						</div>

						<core-alert
							v-if="errorMessage"
							type="red"
							@close-alert="errorMessage = ''"
						>
							{{ errorMessage }}
						</core-alert>
					</div>
				</core-card>

				<reports-table />
			</grid-column>
		</grid-row>
	</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'

import { useRootStore } from '@/vue/stores/RootStore'

import AiProviders from '../partials/AiProviders.vue'
import PromotionalAlert from '../partials/PromotionalAlert.vue'
import ReportsTable from '../partials/ReportsTable.vue'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const router = useRouter()
const rootStore = useRootStore()

// Reactive data
const keyword      = ref('')
const isAnalyzing  = ref(false)
const errorMessage = ref('')

// Show rate limit alert only if rate limit is reached and not dismissed
const rateLimitReached = computed(() => {
	if (!rootStore.aioseo?.aiInsights?.rateLimit?.reached) {
		return false
	}

	return rootStore.aioseo?.aiInsights?.rateLimit?.message
})

// Strings
const strings = {
	title               : __('Discover Which Brands Rank in AI Search Results', td),
	description         : __('Get a detailed report on which brands rank for a particular query or keyword, and how they perform in AI search results.', td),
	placeholder         : __('e.g. "best dog food for puppies"', td),
	analyze             : __('Generate', td),
	errorCreatingReport : __('An error occurred while creating the report. Please try again.', td)
}

// Methods
const analyzeKeyword = async () => {
	if (!keyword.value || isAnalyzing.value) {
		return
	}

	isAnalyzing.value = true
	errorMessage.value = ''

	try {
		const response = await http.post(links.restUrl('ai/insights/reports'))
			.send({ keyword: keyword.value })
			.then(res => res.body)

		if (response.success && response.data?.uuid) {
			// Redirect to report page
			router.push({ name: 'keyword-reports', params: { uuid: response.data.uuid } })
		} else {
			// Show error
			errorMessage.value = response.data?.message || response.message || strings.errorCreatingReport
		}
	} catch (error) {
		if (error.response && error.response.body) {
			errorMessage.value = error.response.body.message || error.message
		} else {
			// Fallback for network errors or errors without a response body
			errorMessage.value = error.message || strings.errorCreatingReport
		}
	} finally {
		isAnalyzing.value = false
	}
}
</script>

<style lang="scss">
.aioseo-ai-insights {
	.keyword-intro {
		text-align: center;
		margin-left: auto;
		margin-right: auto;

		.keyword-title {
			font-size: 24px;
			font-weight: $font-bold;
			color: $black;
			margin: 0 0 16px 0;
			line-height: 1.3;
		}

		.keyword-description {
			font-size: 16px;
			color: $black;
			margin: 0 0 32px 0;
			line-height: 1.5;
		}

		.keyword-form {
			display: flex;
			gap: 12px;
			align-items: flex-start;

			.aioseo-input {
				flex: 1;
			}

			& + .aioseo-alert {
				margin-top: 20px;
			}
		}

		@media screen and (min-width: 768px) {
			max-width: 750px;
			padding: 20px;

			.keyword-form {
				padding: 0 70px;
			}
		}
	}
}
</style>