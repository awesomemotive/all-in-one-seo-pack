<template>
	<div class="aioseo-writing-assistant__keyword-not-analyzed">
		<core-tooltip>
			<svg-info />
			<template #tooltip>
				{{ strings.tooltip }}
			</template>
		</core-tooltip>
	</div>
</template>

<script setup>
import { __ } from '@/vue/plugins/translations.js'
import CoreTooltip from '@/vue/components/common/core/Tooltip.vue'
import SvgInfo from '@/vue/components/common/svg/Info.vue'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	tooltip : __('This URL was unreachable when the report was run and could not be analyzed with the rest of the articles.', td)
}
</script>

<style>
.aioseo-writing-assistant__keyword-not-analyzed {
	position: relative;
	top: 4px;
	display: flex;

	.aioseo-tooltip {
		margin: 0;
	}

	svg {
		width: 20px;
		height: 20px;
	}
}
</style>