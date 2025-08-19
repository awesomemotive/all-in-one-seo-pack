<template>
	<div class="aioseo-seo-site-score">
		<core-blur
			v-if="!optionsStore.internalOptions.internal.siteAnalysis.connectToken"
		>
			<core-site-score-analyze
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

		<core-site-score-analyze
			v-if="optionsStore.internalOptions.internal.siteAnalysis.connectToken"
			:score="score"
			:description="description"
			:loading="analyzerStore.analyzing"
			:summary="getSummary"
			:parts="sortedParts"
		/>
	</div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'

import {
	useAnalyzerStore,
	useConnectStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'
import { getSortedParts } from '@/vue/pages/seo-analysis/utils'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSiteScoreAnalyze from '@/vue/components/common/core/site-score/Analyze'

const score = ref(0)

const {
	connectWithAioseo,
	description,
	strings
} = useSeoSiteScore({
	score
})

const analyzerStore = useAnalyzerStore()
const connectStore  = useConnectStore()
const optionsStore  = useOptionsStore()
const rootStore     = useRootStore()

watch(() => analyzerStore.homeResults.score, (newVal) => {
	score.value = newVal
})

const goodCount     = computed(() => analyzerStore.goodCount('homepage'))
const warningsCount = computed(() => analyzerStore.recommendedCount('homepage'))
const criticalCount = computed(() => analyzerStore.criticalCount('homepage'))
const totalCount    = computed(() => goodCount.value + warningsCount.value + criticalCount.value)

const getSummary = computed(() => {
	return {
		recommended : warningsCount.value,
		critical    : criticalCount.value,
		good        : goodCount.value
	}
})

const sortedParts = computed(() => {
	return getSortedParts({
		good     : goodCount.value,
		warnings : warningsCount.value,
		issues   : criticalCount.value,
		total    : totalCount.value
	})
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
}

onMounted(() => {
	if (!analyzerStore.homeResults.score && optionsStore.internalOptions.internal.siteAnalysis.connectToken) {
		analyzerStore.runSiteAnalyzer()
	}

	score.value = analyzerStore.homeResults.score
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
		max-width: 500px;
		text-align: center;
	}
}
</style>