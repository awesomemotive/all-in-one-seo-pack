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
import { ref, inject } from 'vue'

import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import KeywordRankings from '../keyword-rankings/Index'
import RankTracker from './RankTracker'

const $t = inject('$t')
const $tdPro = inject('$tdPro')

const activeTab = ref('rank-tracker')

const tabs = [
	{
		slug : 'rank-tracker',
		name : $t.__('Rank Tracker', $tdPro)
	},
	{
		slug : 'all-keywords',
		name : $t.__('All Keywords', $tdPro)
	}
]
</script>