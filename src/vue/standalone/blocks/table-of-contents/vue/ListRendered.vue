<template>
	<div>
		<component
			class="aioseo-toc-list--rendered"
			:is="blockAttributes.listStyle"
		>
			<li
				class="aioseo-toc-list-item--rendered"
				v-for="(heading, index) in visibleHeadings"
				:key="index"
			>
				<a v-safe-href="`#${heading.anchor}`">{{ heading.editedContent || heading.content }}</a>

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
import { computed, ref, onMounted, nextTick } from 'vue'

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

const blockAttributes = ref(window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId) || {})

// The frontend omits hidden headings entirely, so the preview has to drop them instead of styling them away.
const visibleHeadings = computed(() => props.headings.filter(heading => !heading.hidden))

onMounted(() => {
	nextTick(() => {
		window.aioseoBus.$on('updateToc' + props.clientId, () => {
			blockAttributes.value = window.wp.data.select('core/block-editor').getBlockAttributes(props.clientId) || {}
		})
	})
})
</script>