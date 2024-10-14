<template>
	<div class="keyword-rank-tracker-tabs">
		<core-main-tabs
			:tabs="tabs"
			:active="activeTab"
			@changed="value => { emit('update:activeTab', value) }"
		>
			<template #button>
				<base-button
					v-if="'keywords' === activeTab"
					class="btn-add-keywords"
					size="small-table"
					type="blue"
					@click.exact="keywordRankTrackerStore.toggleModal({modal: 'modalOpenAddKeywords', open: true})"
				>
					{{ strings.addKeywords }}
				</base-button>

				<base-button
					v-if="'groups' === activeTab"
					class="btn-create-group"
					size="small-table"
					type="blue"
					@click.exact="keywordRankTrackerStore.toggleModal({modal: 'modalOpenCreateGroup', open: true})"
				>
					{{ strings.createGroup }}
				</base-button>
			</template>
		</core-main-tabs>

		<transition
			name="route-fade"
			mode="out-in"
		>
			<slot name="tab-content"/>
		</transition>
	</div>
</template>

<script setup>
import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import CoreMainTabs from '@/vue/components/common/core/main/Tabs'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const keywordRankTrackerStore = useKeywordRankTrackerStore()

const emit = defineEmits([ 'update:activeTab' ])

defineProps({
	activeTab : {
		type    : String,
		default : 'keywords'
	}
})

const strings = {
	addKeywords : __('Add Keywords', td),
	createGroup : __('Create Group', td)
}
const tabs = [
	{
		slug : 'keywords',
		name : __('Keywords', td)
	},
	{
		slug : 'groups',
		name : __('Groups', td)
	}
]
</script>