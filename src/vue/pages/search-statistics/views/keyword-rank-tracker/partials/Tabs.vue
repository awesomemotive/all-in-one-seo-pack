<template>
	<div class="keyword-rank-tracker-tabs">
		<core-main-tabs
			:tabs="tabs"
			:active="activeTab"
			@changed="value => { $emit('update:activeTab', value) }"
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
					@click.exact="keywordRankTrackerStore.modalOpenCreateGroup = true"
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
import { defineEmits, defineProps, inject } from 'vue'

import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import CoreMainTabs from '@/vue/components/common/core/main/Tabs'

const $t = inject('$t')
const $td = inject('$td')

const keywordRankTrackerStore = useKeywordRankTrackerStore()

defineEmits([ 'update:activeTab' ])

defineProps({
	activeTab : {
		type    : String,
		default : 'keywords'
	}
})

const strings = {
	addKeywords : $t.__('Add Keywords', $td),
	createGroup : $t.__('Create Group', $td)
}
const tabs = [
	{
		slug : 'keywords',
		name : $t.__('Keywords', $td)
	},
	{
		slug : 'groups',
		name : $t.__('Groups', $td)
	}
]
</script>