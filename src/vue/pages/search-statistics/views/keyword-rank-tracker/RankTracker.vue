<template>
	<div>
		<keyword-rank-tracker-tabs
			:active-tab="activeTab"
			@update:activeTab="activeTab = $event"
		>
			<template #tab-content>
				<component
					:is="keywordRankTrackerStore[activeTab].count ? (activeTab === 'keywords' ? Keywords : Groups) : EmptyState"
					:context="activeTab"
				/>
			</template>
		</keyword-rank-tracker-tabs>

		<delete-keywords
			:modal-open="keywordRankTrackerStore.modalOpenDeleteKeywords"
			@update:modal-open="keywordRankTrackerStore.toggleModal({modal: 'modalOpenDeleteKeywords', open: $event})"
		/>

		<add-keywords
			:modal-open="keywordRankTrackerStore.modalOpenAddKeywords"
			@update:modal-open="keywordRankTrackerStore.toggleModal({modal: 'modalOpenAddKeywords', open: $event})"
		/>

		<assign-groups
			:modal-open="keywordRankTrackerStore.modalOpenAssignGroups"
			@update:modal-open="keywordRankTrackerStore.toggleModal({modal: 'modalOpenAssignGroups', open: $event})"
		/>

		<create-group
			:modal-open="keywordRankTrackerStore.modalOpenCreateGroup"
			@update:modal-open="keywordRankTrackerStore.modalOpenCreateGroup = $event"
		/>

		<update-group
			:group="keywordRankTrackerStore.groups.selected[0]"
			:modal-open="keywordRankTrackerStore.modalOpenUpdateGroup"
			@update:modal-open="keywordRankTrackerStore.modalOpenUpdateGroup = $event;"
		/>

		<delete-groups
			:modal-open="keywordRankTrackerStore.modalOpenDeleteGroups"
			@update:modal-open="keywordRankTrackerStore.modalOpenDeleteGroups = $event"
		/>
	</div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'

import {
	useKeywordRankTrackerStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import { getParams } from '@/vue/utils/params'

import AddKeywords from './partials/pro/AddKeywords'
import AssignGroups from './partials/pro/AssignGroups'
import CreateGroup from './partials/pro/CreateGroup'
import DeleteGroups from './partials/pro/DeleteGroups'
import DeleteKeywords from './partials/pro/DeleteKeywords'
import EmptyState from './partials/pro/EmptyState'
import Groups from './partials/pro/Groups'
import KeywordRankTrackerTabs from './partials/Tabs'
import Keywords from './partials/Keywords'
import UpdateGroup from './partials/pro/UpdateGroup'

const keywordRankTrackerStore = useKeywordRankTrackerStore()
const searchStatisticsStore = useSearchStatisticsStore()

const activeTab = ref('keywords')

onBeforeMount(async () => {
	if (
		searchStatisticsStore.isConnected &&
		!searchStatisticsStore.shouldShowSampleReports &&
		!keywordRankTrackerStore.keywords.all.rows.length
	) {
		try {
			const search = getParams()?.search || ''
			if (search) {
				keywordRankTrackerStore.keywords.paginated.searchTerm = search
			}

			keywordRankTrackerStore.maybeUpdateKeywords()
			keywordRankTrackerStore.maybeUpdateGroups()
		} catch (error) {
			console.error(error)
		}
	}
})
</script>