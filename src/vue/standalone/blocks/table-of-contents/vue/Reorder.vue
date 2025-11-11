<template>
	<core-modal
		:show="show"
		noHeader
		:classes="[ 'aioseo-toc-modal' ]"
		@close="$emit('closeModal')"
	>
		<template #body >
			<header class="aioseo-toc-header">
				<div class="aioseo-toc-header-title">{{ strings.header }}</div>

				<div class="aioseo-toc-header-buttons">
					<a
						class="aioseo-button-link aioseo-button-link--primary"
						href="#"
						@click.prevent="$emit('closeModal')"
					>
						{{ strings.done }}
					</a>
				</div>
			</header>

			<List
				:headings="headings"
				:client-id="clientId"
				allowReorder
			/>
		</template>
	</core-modal>
</template>

<script setup>
import CoreModal from '@/vue/components/common/core/modal/Index'
import List from './List'
import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

defineProps({
	headings : Array,
	show     : Boolean,
	clientId : String
})

defineEmits([ 'closeModal' ])

const strings = {
	header : sprintf(
		// Translators: 1 - The plugin short name ("AIOSEO").
		__('%1$s Table of Contents', td),
		import.meta.env.VITE_SHORT_NAME
	),
	done : __('Done', td)
}
</script>