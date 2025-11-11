<template>
	<div
		class="aioseo-toc-container"
	>
		<div
			:class="[
				'aioseo-toc-menu',
				{ 'aioseo-toc-placeholder' : 0 === blockAttributes?.headings?.length }
			]"
			>
			<toc-header
				:block-attributes="blockAttributes"
				:load-actions="true"
				@showModal="showModal = true"
			/>

			<div v-if="!blockAttributes.collapsed">
				<div
					v-if="0 !== blockAttributes?.headings?.length"
					class="aioseo-toc-content"
				>
					<List
						:headings="blockAttributes?.headings"
						:client-id="clientId"
					/>
				</div>
			</div>

			<Reorder
				:show="showModal"
				:headings="blockAttributes?.headings"
				:client-id="clientId"
				@closeModal="showModal = false"
			/>
		</div>

		<div
			v-if="0 !== blockAttributes?.headings?.length"
			class="aioseo-toc-rendered"
		>
			<toc-header
				:block-attributes="blockAttributes"
			/>

			<div v-if="!blockAttributes.collapsed">
				<ListRendered
					:headings="blockAttributes?.headings"
					:client-id="clientId"
				/>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

import TocHeader from './partials/Header'
import List from './List'
import ListRendered from './ListRendered'
import Reorder from './Reorder'

const props = defineProps({
	clientId : {
		type     : String,
		required : true
	}
})

const blockAttributes = ref(window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId))
const showModal = ref(false)

onMounted(() => {
	nextTick(() => {
		window.aioseoBus.$on('updateToc' + props.clientId, () => {
			blockAttributes.value = window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId)
		})
	})
})
</script>