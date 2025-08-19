<template>
	<div class="aioseo-seo-site-analysis-issues">
		<template v-if="filteredResults?.basic?.length > 0">
			<div class="group-header">
				{{ groupName('basic') }}
			</div>

			<div
				v-for="(result, idx) in filteredResults.basic"
				:key="`${idx}-${result.code}`"
				class="aioseo-seo-site-analysis-issues-item"
			>
				<row :result="result" />
			</div>
		</template>

		<template v-if="filteredResults?.advanced?.length > 0">
			<div class="group-header">
				{{ groupName('advanced') }}
			</div>

			<div
				v-for="(result, idx) in filteredResults.basic"
				:key="`${idx}-${result.code}`"
				class="aioseo-seo-site-analysis-issues-item"
			>
				<row :result="result" />
			</div>
		</template>

		<template v-if="filteredResults?.performance?.length > 0">
			<div class="group-header">
				{{ groupName('performance') }}
			</div>

			<div
				v-for="(result, idx) in filteredResults.basic"
				:key="`${idx}-${result.code}`"
				class="aioseo-seo-site-analysis-issues-item"
			>
				<row :result="result" />
			</div>
		</template>
	</div>
</template>

<script setup>
import { computed } from 'vue'
import SeoAnalysis from '@/vue/classes/SeoAnalysis/SeoAnalysis'
import Row from './Row'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	section : {
		type     : String,
		required : true
	},
	allResults : {
		type     : Object,
		required : true
	}
})

const seoAnalysis = SeoAnalysis
const filteredResults = computed(() => seoAnalysis.getFilteredResults(props.allResults, props.section))

function groupName (group) {
	switch (group) {
		case 'basic':
			return __('Basic SEO', td)
		case 'advanced':
			return __('Advanced SEO', td)
		case 'performance':
			return __('Performance SEO', td)
	}
}
</script>

<style lang="scss">
.aioseo-seo-site-analysis-issues {
	.aioseo-google-search-preview {
		border: 1px solid $border;
		padding: 16px;
	}

	.group-header {
		font-size: 16px;
		line-height: 24px;
		font-weight: 600;
		padding: 12px;
		background-color: $blue4;
		border-radius: 4px;
		margin-bottom: 12px;
		margin-top: 20px;
	}

	.aioseo-seo-site-analysis-issues-item {
		border: 1px solid $gray;
		margin-top: 8px;

		&__row-actions {
			display: flex;
			align-items: center;
			justify-content: flex-end;
			text-align: end;
			gap: 12px;
		}

		&__header {
			height: 48px;
			padding: 0 13px;
			display: flex;
			align-items: center;

			.result-status {
				width: 8px;
				height: 8px;
				border-radius: 50%;
				margin-right: 14px;

				&.passed {
					background-color: $green;
				}

				&.error {
					background-color: $red;
				}

				&.warning {
					background-color: $orange;
				}
			}

			.result-toggle {
				width: 30px;
				height: 26px;
				border: 1px solid $gray;
				border-radius: 3px;
				display: flex;
				align-items: center;
				justify-content: center;
				cursor: pointer;

				&.active,
				&:hover {
					background-color: $blue;

					svg {
						color: #fff;
					}
				}

				&.active {
					svg {
						transform: rotate(-180deg);
					}
				}

				svg {
					width: 100%;
					max-width: 20px;
					height: auto;
					color: $black;
					transform: rotate(-90deg);
					transition: transform 0.3s;
				}
			}
		}

		&__content {
				font-size: $font-md;
				line-height: 22px;
				font-weight: 600;
				flex: 1;
				display: flex;
				align-items: center;
			}

		&__post-title {
			font-weight: bold;
			display: flex;
			gap: 8px;
		}
	}
}
</style>