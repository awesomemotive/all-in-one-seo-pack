<template>
	<core-modal
		:show="modalOpen"
		@close="emit('update:modalOpen', false)"
		:classes="['keyword-rank-tracker-main']"
	>
		<template #headerTitle>
			{{ strings.headerTitle }}
		</template>

		<template #body>
			<div
				class="keyword-rank-tracker-main__body"
				:class="{'keyword-rank-tracker-main__body--disable-click': searchStatisticsStore.shouldShowSampleReports}"
			>
				<keyword-rank-tracker
					v-if="!licenseStore.isUnlicensed && license.hasCoreFeature('search-statistics', 'keyword-rank-tracker') && !showConnectCta"
				/>

				<keyword-rank-tracker-lite v-else/>
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import { computed } from 'vue'

import {
	useLicenseStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import CoreModal from '@/vue/components/common/core/modal/Index'
import KeywordRankTracker from './AIOSEO_VERSION/KeywordRankTracker'
import KeywordRankTrackerLite from './lite/KeywordRankTracker'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const licenseStore = useLicenseStore()
const searchStatisticsStore = useSearchStatisticsStore()

const emit = defineEmits([ 'update:modalOpen' ])

defineProps({
	modalOpen : Boolean
})

const strings = {
	headerTitle : __('Keyword Performance Tracking', td)
}

const showConnectCta = computed(() => {
	return ((license.hasCoreFeature('search-statistics') && !searchStatisticsStore.isConnected) || searchStatisticsStore.unverifiedSite)
})
</script>

<style lang="scss">
.keyword-rank-tracker-main {
	&__body {
		padding: 20px;

		&--disable-click {
			a,
			button,
			label,
			input[type="button"],
			input[type="submit"] {
				pointer-events: none !important;
			}
		}
	}
}
</style>