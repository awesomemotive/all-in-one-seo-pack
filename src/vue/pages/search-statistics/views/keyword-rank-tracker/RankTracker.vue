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
			@update:modal-open="keywordRankTrackerStore.toggleModal({modal: 'modalOpenCreateGroup', open: $event})"
		/>

		<update-group
			:group="keywordRankTrackerStore.groups.selected[0]"
			:modal-open="keywordRankTrackerStore.modalOpenUpdateGroup"
			@update:modal-open="keywordRankTrackerStore.toggleModal({modal: 'modalOpenUpdateGroup', open: $event})"
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

import { removeParam } from '@/vue/utils/params'

const keywordRankTrackerStore = useKeywordRankTrackerStore()
const searchStatisticsStore   = useSearchStatisticsStore()

const activeTab = ref('keywords')

onBeforeMount(() => {
	if (
		searchStatisticsStore.isConnected &&
		!searchStatisticsStore.shouldShowSampleReports &&
		!keywordRankTrackerStore.keywords.all.rows.length
	) {
		try {
			const params = new URLSearchParams(document.location.search) || {}
			if (params?.get('search')) {
				keywordRankTrackerStore.keywords.paginated.searchTerm = params.get('search')

				removeParam('search')
			}

			keywordRankTrackerStore.maybeUpdateKeywords()
			keywordRankTrackerStore.maybeUpdateGroups()
		} catch (error) {
			console.error(error)
		}
	}
})
</script>