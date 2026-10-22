<template>
	<p
		v-if="0 < count"
		class="aioseo-hidden-in-view-note"
	>
		{{ strings.notShown(count) }}

		<a
			v-if="url"
			:href="url"
		>
			{{ strings.openFullView }}
		</a>
	</p>
</template>

<script setup>
import { computed } from 'vue'

import { __, _n, sprintf } from '@/vue/plugins/translations'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'
import { sanitizeUrl } from '@/vue/utils/strings'

const td = import.meta.env.VITE_TEXTDOMAIN

// Says how many of an issue's instances are in text the editor's current view doesn't show
// ({@see editors/types.js:ContentUnit.hiddenInView}), which is why they never highlight.
const props = defineProps({
	count : {
		type    : Number,
		default : 0
	}
})

const strings = {
	notShown : (count) => sprintf(
		// Translators: 1 - The number of instances.
		_n('%1$d not shown in this view.', '%1$d not shown in this view.', count, td),
		count
	),
	// Only WPBakery's back-end builder hides units ({@see editors/wpbakery.js:getFullViewUrl}).
	openFullView : __('Open the Frontend Editor', td)
}

const url = computed(() => {
	if (!props.count) {
		return null
	}

	const fullViewUrl = resolveAdapter()?.getFullViewUrl?.()

	return fullViewUrl ? sanitizeUrl(fullViewUrl) : null
})
</script>

<style lang="scss">
.aioseo-app .aioseo-hidden-in-view-note {
	margin: 0;
	color: #434960;
	font-size: 13px;
	line-height: 20px;
}
</style>