<template>
	<div class="aioseo-writing-assistant__summary-readability">
		<div class="aioseo-writing-assistant__summary-readability-grade">
			{{ getReadability }}
		</div>
		{{ strings.readability }}
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
				readability : __('readability', td)
			}
		}
	},
	computed : {
		getReadability () {
			return this.summary?.wordCount && this.summary?.readabilityGrade ? this.summary.readabilityGrade : '-'
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__summary {
	&-readability {
		font-size: 14px;

		&-grade {
			font-weight: 700;
			font-size: 15px;
		}
	}
}
</style>