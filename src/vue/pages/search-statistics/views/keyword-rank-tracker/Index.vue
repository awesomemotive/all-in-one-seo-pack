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
import { ref } from 'vue'

import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import KeywordRankings from '../keyword-rankings/Index'
import RankTracker from './RankTracker'

import { __ } from '@/vue/plugins/translations'

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
</script>