<template>
	<div class="aioseo-seo-site-score">
		<core-blur
			v-if="!optionsStore.internalOptions.internal.siteAnalysis.connectToken"
		>
			<core-site-score-dashboard
				:score="85"
				:description="description"
			/>
		</core-blur>

		<div
			v-if="!optionsStore.internalOptions.internal.siteAnalysis.connectToken"
			class="aioseo-seo-site-score-cta"
		>
			<a
				href="#"
				@click.prevent="openPopup(rootStore.aioseo.urls.connect)"
			>{{ connectWithAioseo }}</a> {{ strings.toSeeYourSiteScore }}
		</div>

		<core-site-score-dashboard
			v-if="optionsStore.internalOptions.internal.siteAnalysis.connectToken"
			:score="optionsStore.internalOptions.internal.siteAnalysis.score"
			:description="description"
			:loading="analyzerStore.analyzing"
			:summary="getSummary"
		/>
	</div>
</template>

<script setup>
import { computed, onMounted } from 'vue'

import {
	useAnalyzerStore,
	useConnectStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSiteScoreDashboard from '@/vue/components/common/core/site-score/Dashboard'

const {
	connectWithAioseo,
	description,
	strings
} = useSeoSiteScore()

const analyzerStore = useAnalyzerStore()
const connectStore  = useConnectStore()
const optionsStore  = useOptionsStore()
const rootStore     = useRootStore()

const getSummary = computed(() => {
	return {
		recommended : analyzerStore.recommendedCount(),
		critical    : analyzerStore.criticalCount(),
		good        : analyzerStore.goodCount()
	}
})

const openPopup = (url) => {
	popup(
		url,
		connectWithAioseo,
		600,
		630,
		true,
		[ 'token' ],
		completedCallback,
		closedCallback
	)
}

const completedCallback = (payload) => {
	return connectStore.saveConnectToken(payload.token)
}

const closedCallback = (reload) => {
	if (reload) {
		analyzerStore.runSiteAnalyzer()
	}

	analyzerStore.analyzing = true
}

onMounted(() => {
	if (!optionsStore.internalOptions.internal.siteAnalysis.score && optionsStore.internalOptions.internal.siteAnalysis.connectToken) {
		analyzerStore.analyzing = true
		analyzerStore.runSiteAnalyzer()
	}
})
</script>

<style lang="scss">
.aioseo-seo-site-score {
	.aioseo-blur {
		display: flex;
		align-items: center;
	}

	.aioseo-seo-site-score-cta {
		position: absolute;
		left: 50%;
		top: 50%;
		transform: translateX(-50%) translateY(-50%);
		background-color: #fff;
		padding: 20px;
		border: 1px solid $border;
		box-shadow: 0px 2px 10px rgba(0, 90, 224, 0.2);
		color: $black;
		font-size: 16px;
		font-weight: 600;
		width: 82%;
		text-align: center;
	}
}
</style>