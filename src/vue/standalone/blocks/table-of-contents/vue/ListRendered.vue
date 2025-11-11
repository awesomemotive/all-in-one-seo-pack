<template>
	<div>
		<component
			class="aioseo-toc-list--rendered"
			:is="blockAttributes.listStyle"
		>
			<li
				class="aioseo-toc-list-item--rendered"
				:class="[
					{ 'hidden' : heading.hidden }
				]"
				v-for="(heading, index) in headings"
				:key="index"
			>
				<a :href="`#${heading.anchor}`">{{ heading.editedContent || heading.content }}</a>

				<ListRendered
					class="aioseo-toc-list-nested--rendered"
					v-if="heading.headings"
					:headings="heading.headings"
					:client-id="clientId"
				/>
			</li>
		</component>
	</div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
	headings : {
		required : true,
		type     : Array
	},
	clientId : {
		required : true,
		type     : String
	}
})

const blockAttributes = ref(window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId))

onMounted(() => {
	nextTick(() => {
		window.aioseoBus.$on('updateToc' + props.clientId, () => {
			blockAttributes.value = window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId)
		})
	})
})
</script>