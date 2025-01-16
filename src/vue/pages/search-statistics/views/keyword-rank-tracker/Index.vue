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
				:active="keywordRankTrackerStore.parentActiveTab"
				:show-save-button="false"
				@changed="value => { keywordRankTrackerStore.parentActiveTab = value }"
				internal
			/>
		</template>

		<transition
			name="route-fade"
			mode="out-in"
		>
			<component
				:is="'rank-tracker' === keywordRankTrackerStore.parentActiveTab ? RankTracker : KeywordRankings"/>
		</transition>

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
	</core-card>
</template>

<script setup>
import { onBeforeMount } from 'vue'
import { useRoute } from 'vue-router'

import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import AddKeywords from './partials/pro/AddKeywords'
import AssignGroups from './partials/pro/AssignGroups'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CreateGroup from './partials/pro/CreateGroup'
import DeleteGroups from './partials/pro/DeleteGroups'
import DeleteKeywords from './partials/pro/DeleteKeywords'
import KeywordRankings from '../keyword-rankings/Index'
import RankTracker from './RankTracker'
import UpdateGroup from './partials/pro/UpdateGroup'

import { __ } from '@/vue/plugins/translations'
import { removeParam } from '@/vue/utils/params'

const keywordRankTrackerStore = useKeywordRankTrackerStore()

const td = import.meta.env.VITE_TEXTDOMAIN

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
	const route  = useRoute()
	if (
		params.has('tab') ||
		route?.query?.tab
	) {
		const tab = params.get('tab') || route.query.tab
		if ('AllKeywords' === tab) {
			keywordRankTrackerStore.parentActiveTab = 'all-keywords'
		}

		route.query.tab = undefined

		removeParam('tab')
	}
})
</script>