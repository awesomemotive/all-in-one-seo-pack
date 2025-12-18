<template>
	<core-score-button
		:show-score="truSeoShouldAnalyze()"
		:score="currentPost.seo_score"
		:class="['']"
		@click.prevent="$emit('click')"
	>
		<template #icon>
			<svg-aioseo-logo-gear
				width="16"
				height="16"
			/>
		</template>
	</core-score-button>
</template>

<script setup>
import { defineEmits } from 'vue'
import { storeToRefs } from 'pinia'
import { truSeoShouldAnalyze } from '@/vue/plugins/tru-seo/components/helpers'

import {
	usePostEditorStore
} from '@/vue/stores'

import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'

defineEmits([ 'click' ])

const { currentPost } = storeToRefs(usePostEditorStore())
</script>