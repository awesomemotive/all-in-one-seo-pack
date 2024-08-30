<template>
	<core-blur>
		<core-card
			slug="keywordRankTracker"
			:hide-header="true"
			:toggles="false"
			no-slide
		>
			<template #tabs>
				<core-main-tabs
					:tabs="tabs"
					:active="'rank-tracker'"
					:show-save-button="false"
					@changed="value => { activeTab = value }"
					internal
				/>
			</template>

			<keyword-rank-tracker-tabs :active-tab="'keywords'">
				<template #tab-content>
					<keywords/>
				</template>
			</keyword-rank-tracker-tabs>
		</core-card>
	</core-blur>
</template>

<script setup>
import { inject } from 'vue'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import KeywordRankTrackerTabs from '../../keyword-rank-tracker/partials/Tabs'
import Keywords from '../../keyword-rank-tracker/partials/Keywords'

const $t = inject('$t')
const $td = inject('$td')

const tabs = [
	{
		slug : 'rank-tracker',
		name : $t.__('Rank Tracker', $td)
	},
	{
		slug : 'all-keywords',
		name : $t.__('All Keywords', $td)
	}
]
</script>