<template>
	<div class="aioseo-writing-assistant__summary-wordCount">
		<div class="aioseo-writing-assistant__summary-wordCount-count">
			{{ getSummary }}
		</div>
		{{ strings.wordCount }}
	</div>
</template>

<script>
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	props : {
		summary : null
	},
	data () {
		return {
			strings : {
				wordCount : __('words', td)
			}
		}
	},
	computed : {
		getSummary () {
			return 0 < this.summary?.wordCount ? this.summary.wordCount : 0
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__summary {
	&-wordCount {
		font-size: 14px;

		&-count {
			font-weight: 700;
			font-size: 15px;
		}
	}
}
</style>