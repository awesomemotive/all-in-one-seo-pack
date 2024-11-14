<template>
	<core-card
		slug="keywordRankTracker"
		:hide-header="true"
		:toggles="false"
		no-slide
	>
		<template #tabs>
			<core-main-tabs
				:tabs="tabs"
				:active="activeTab"
				:show-save-button="false"
				@changed="value => { activeTab = value }"
				internal
			/>
		</template>

		<transition
			name="route-fade"
			mode="out-in"
		>
			<component :is="'rank-tracker' === activeTab ? RankTracker : KeywordRankings"/>
		</transition>
	</core-card>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import { useRoute } from 'vue-router'

import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import KeywordRankings from '../keyword-rankings/Index'
import RankTracker from './RankTracker'

import { __ } from '@/vue/plugins/translations'
import { removeParam } from '@/vue/utils/params'

const td = import.meta.env.VITE_TEXTDOMAIN

const activeTab = ref('rank-tracker')

const tabs = [
	{
		slug : 'rank-tracker',
		name : __('Rank Tracker', td)
	},
	{
		slug : 'all-keywords',
		name : __('All Keywords', td)
	}
]

onBeforeMount(() => {
	const params = new URLSearchParams(window.location?.search || '') || {}
	const route = useRoute()
	if (
		params.has('tab') ||
		route?.query?.tab
	) {
		const tab = params.get('tab') || route.query.tab
		if ('AllKeywords' === tab) {
			activeTab.value = 'all-keywords'
		}

		route.query.tab = undefined

		removeParam('tab')
	}
})
</script>