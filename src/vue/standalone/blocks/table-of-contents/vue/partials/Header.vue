<template>
	<header class="aioseo-toc-header">
		<div class="aioseo-toc-header-title">
			<div
				class="aioseo-toc-header-collapsible"
				v-if="'open' === blockAttributes.collapsibleType || 'closed' === blockAttributes.collapsibleType"
				@click="toggleCollapsed"
			>
				<svg v-if="blockAttributes.collapsed" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M6 8H0V6H6V0H8V6H14V8H8V14H6V8Z" fill="#005AE0"/>
				</svg>
					<svg v-else width="14" height="2" viewBox="0 0 14 2" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0 2V0H14V2H0Z" fill="#005AE0"/>
					</svg>
				</div>
			<span v-if="'off' === blockAttributes.collapsibleType">{{ strings.header }}</span>

			{{ getCollapsibleTitle }}

			<core-tooltip v-if="loadActions">
				<div class="aioseo-toc-header-info">
					<Info />
				</div>

				<template #tooltip>
					<slot name="tooltip">
						{{ strings.tooltipMainDescription }}
					</slot>
				</template>
			</core-tooltip>
		</div>

		<div
			v-if="0 === blockAttributes?.headings?.length && loadActions"
			class="aioseo-toc-header-instructions"
		>
			{{strings.instructions}}
		</div>

		<div
			v-if="0 !== blockAttributes?.headings?.length && loadActions"
			class="aioseo-toc-header-buttons"
		>
			<a
				class="aioseo-button-link"
				href="#"
				@click.prevent="emit('showModal')"
			>
				{{strings.reorder}}
			</a>

			<a
				class="aioseo-button-link aioseo-button-link--primary"
				href="#"
				@click.prevent="save"
			>
				{{strings.save}}
			</a>
		</div>
	</header>
</template>

<script setup>
import { computed } from 'vue'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import Info from '@/vue/components/common/svg/Info'

import { sprintf, __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const emit = defineEmits([ 'showModal' ])

const props = defineProps({
	blockAttributes : {
		type     : Object,
		required : true
	},
	loadActions : {
		type    : Boolean,
		default : false
	}
})

const strings = {
	header : sprintf(
		// Translators: 1 - The plugin short name ("AIOSEO").
		__('%1$s Table of Contents', td),
		import.meta.env.VITE_SHORT_NAME
	),
	instructions           : __('Add a heading block below to begin generating the Table of Contents.', td),
	tooltipMainDescription : sprintf(
		// Translators: 1 - The plugin short name ("AIOSEO").
		__('%1$s can automatically output a table of contents based on your heading tags below. Search engines sometimes use table of contents in search results or rich snippets which can help you increase your rankings.', td),
		import.meta.env.VITE_SHORT_NAME
	),
	reorder : __('Reorder', td),
	save    : __('Save', td)
}

const getCollapsibleTitle = computed(() => {
	if ('off' === props.blockAttributes.collapsibleType) {
		return ''
	}

	return props.blockAttributes.collapsed
		? props.blockAttributes.collapsedTitle
		: props.blockAttributes.expandedTitle
})

const save = () => {
	window.wp.data.dispatch('core/block-editor').selectBlock(null)
	document?.activeElement?.blur()
}

const toggleCollapsed = () => {
	const newCollapsedState = !props.blockAttributes.collapsed

	props.blockAttributes.collapsed = newCollapsedState
	window.wp.data.dispatch('core/block-editor').updateBlockAttributes(props.clientId, {
		...props.blockAttributes,
		collapsed : newCollapsedState
	})
}
</script>