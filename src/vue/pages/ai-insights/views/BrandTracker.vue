<template>
	<div class="aioseo-brand-tracker">
		<grid-row>
			<grid-column>
				<core-card
					slug="brandTracker"
					:toggles="false"
					hide-header
					no-slide
				>
					<div class="brand-tracker-content">
						<div class="coming-soon">
							{{ strings.comingSoon }}
						</div>

						<h2 class="brand-tracker-title">
							{{ strings.brandTrackerTitle }}
						</h2>

						<p class="brand-tracker-description">
							{{ strings.brandTrackerDescription }}
						</p>

						<BrandTrackerComingSoon />

						<hr class="brand-tracker-divider" />

						<h3>{{ strings.brandTrackerSubscribeTitle }}</h3>

						<p v-if="!emailSent">{{ strings.brandTrackerSubscribeDescription }}</p>

						<div v-if="!emailSent" class="brand-tracker-subscribe">
							<base-input
								type="email"
								placeholder="Enter your email"
								v-model="email"
								size="medium"
								@keyup.enter="subscribeToNewsletter"
							/>

							<base-button
								type="blue"
								size="medium"
								@click="subscribeToNewsletter"
								:disabled="isLoading || !email"
							>
								{{ isLoading ? strings.subscribing : strings.subscribe }}
							</base-button>
						</div>

						<core-alert
							type="green"
							v-if="emailSent"
						>
							{{ strings.emailSent }}
						</core-alert>

						<core-alert
							type="red"
							v-if="error"
						>
							{{ error }}
						</core-alert>
					</div>
				</core-card>
			</grid-column>
		</grid-row>
	</div>
</template>

<script setup>
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'

import BrandTrackerComingSoon from './partials/BrandTrackerComingSoon.vue'

import { ref } from 'vue'
import { __ } from '@/vue/plugins/translations'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

const td = import.meta.env.VITE_TEXTDOMAIN

// Strings
const strings = {
	comingSoon                       : __('Coming Soon', td),
	brandTrackerTitle                : __('Track How Your Brand Performs in AI Search Results', td),
	brandTrackerDescription          : __('Monitor your brand\'s online presence, sentiment, and competitive positioning with AI-powered insights, so you can build authority, track reputation, and stay ahead of competitors.', td),
	brandTrackerSubscribeTitle       : __('Want to be the first to track your brand in AI search results?', td),
	brandTrackerSubscribeDescription : __('Sign up to our email newsletter and we will notify you when we launch.', td),
	subscribe                        : __('Subscribe', td),
	subscribing                      : __('Subscribing...', td),
	emailSent                        : __('You have been successfully subscribed to our email newsletter! We will notify you as soon as our Brand Tracker is available.', td),
	subscribeError                   : __('Failed to subscribe. Please try again later.', td)
}

const email     = ref('')
const emailSent = ref(false)
const isLoading = ref(false)
const error     = ref('')

const subscribeToNewsletter = async () => {
	if (!email.value || isLoading.value) {
		return
	}

	isLoading.value = true
	error.value     = ''

	try {
		const response = await http.post(links.restUrl('ai/insights/brand-tracker/subscribe'))
			.send({ email: email.value })
			.then(res => res.body)

		if (response.success) {
			emailSent.value = true
			email.value     = ''
		} else {
			error.value = response.message || strings.subscribeError
		}
	} catch (err) {
		error.value = strings.subscribeError
		console.error('Failed to subscribe to newsletter:', err)
	} finally {
		isLoading.value = false
	}
}
</script>

<style lang="scss">
.aioseo-brand-tracker {
	.brand-tracker-content {
		text-align: center;
		padding: 20px;

		.coming-soon {
			color: $blue;
			font-size: 14px;
			font-weight: $font-bold;
			margin-bottom: 12px;
			text-transform: uppercase;
		}

		.brand-tracker-title {
			font-size: 24px;
			font-weight: $font-bold;
			color: $black;
			margin: 0 0 16px 0;
			line-height: 1.3;
		}

		.brand-tracker-description {
			font-size: 16px;
			color: $black2;
			margin: 0 0 32px 0;
			line-height: 1.6;
			max-width: 800px;
			margin-left: auto;
			margin-right: auto;
		}

		.brand-tracker-divider {
			border: 0;
			border-top: 1px solid $gray;
			margin: 16px 0;
			width: 50px;
			margin-left: auto;
			margin-right: auto;
		}

		.brand-tracker-subscribe {
			display: flex;
			max-width: 500px;
			margin-left: auto;
			margin-right: auto;
			align-items: center;
			justify-content: center;
			gap: 12px;
		}

		.aioseo-alert {
			margin-top: 20px;
			max-width: 600px;
			margin-left: auto;
			margin-right: auto;
		}
	}
}
</style>