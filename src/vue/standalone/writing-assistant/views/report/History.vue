<template>
	<div
		class="aioseo-writing-assistant__report-history"
		v-if="writingAssistantStore.reportHistory.length"
	>
		<base-collapse-box :open="props.open">
			<template #title>
				{{ strings.reportHistory }}
			</template>
			<template #body>
				<div class="aioseo-wp-table">
					<div class="wp-table">
						<table>
							<thead>
							<tr>
								<th>{{ strings.keyword }}</th>
								<th v-if="!props.hideColumns.includes('region')">{{ strings.region }}</th>
								<th v-if="!props.hideColumns.includes('language')">{{ strings.language }}</th>
							</tr>
							</thead>
							<tbody>
							<tr
								v-for="(report, index) in writingAssistantStore.reportHistory"
								:key="report.id"
								:class="{ even: index % 2 !== 0 }"
							>
								<td>
									<span
										class="link"
										@click="openReport(report)"
									>
										{{ report.title }}
									</span>
								</td>
								<td v-if="!props.hideColumns.includes('region')">{{ writingAssistantSettingsStore.getCountryLabel(report.country) }}</td>
								<td v-if="!props.hideColumns.includes('language')">{{ writingAssistantSettingsStore.getLanguageLabel(report.language) }}</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</template>
		</base-collapse-box>
	</div>
</template>

<script setup>
import { onBeforeMount } from 'vue'
import { useScrollTo } from '@/vue/composables/ScrollTo'
import {
	useWritingAssistantStore,
	useWritingAssistantSettingsStore
} from '@/vue/stores'
import { __ } from '@/vue/plugins/translations'
import BaseCollapseBox from '@/vue/components/common/base/CollapseBox'

const scrollTo = useScrollTo()
const td = import.meta.env.VITE_TEXTDOMAIN
const writingAssistantStore = useWritingAssistantStore()
const writingAssistantSettingsStore = useWritingAssistantSettingsStore()
const strings = {
	reportHistory : __('Report History', td),
	keyword       : __('Keyword', td),
	region        : __('Region', td),
	language      : __('Language', td),
	score         : __('Score', td)
}

const props = defineProps({
	open : {
		type    : Boolean,
		default : true
	},
	hideColumns : {
		type    : Array,
		default : () => []
	}
})

const openReport = (report) => {
	writingAssistantStore.openReport(report.title, report.country, report.language)
	scrollTo.scrollTo('aioseo-writing-assistant-metabox')
}

onBeforeMount(() => {
	writingAssistantStore.updateReportHistory()
})
</script>

<style lang="scss">
.aioseo-writing-assistant__report-history {
	table {
		width: 100%;
		border: 1px solid $input-border;
		border-collapse: collapse;

		th {
			text-align: left;
			color: $black;
			background: $box-background;
			font-weight: 700;
			font-size: 14px;
			padding: 12px 20px;

			&:last-child {
				padding-right: 12px;
			}
		}

		td {
			padding: 12px 20px;
			font-size: 14px;
			color: $black !important;

			&:last-child {
				padding-right: 12px;
			}

			.link {
				color: $blue;
				text-decoration: underline;
				cursor: pointer;
			}
		}
	}
}
</style>