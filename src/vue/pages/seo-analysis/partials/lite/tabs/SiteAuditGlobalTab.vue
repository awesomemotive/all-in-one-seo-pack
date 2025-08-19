<template>
	<div>
    <core-main-tabs
      :tabs="tabs"
      :showSaveButton="false"
      active="all-urls"
      internal
      skinny-tabs
    >
      <template #var-tab="{ tab }">
        <span
          v-if="tab?.analyze"
          class="icon"
          :class="tab?.analyze?.classColor"
        >
          <component :is="tab.icon" />
        </span>

        <span
					v-if="tab?.analyze"
					class="label">
					{{ tab.label }} ({{ tab.analyze.count || 0 }})
				</span>
      </template>
    </core-main-tabs>

		<site-audit-all-urls-table
			section="all-urls"
		/>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import SiteAuditAllUrlsTable from '@/vue/pages/seo-analysis/partials/lite/results/SiteAuditAllUrlsTable'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const tabs = computed(() => {
	return [
		{
			slug  : 'all-urls',
			label : __('All URLs', td),
			name  : __('All URLs', td)
		},
		{
			slug  : 'all-checks',
			label : __('All Checks', td),
			name  : __('All Checks', td)
		},
		{
			slug  : 'settings-audit',
			label : __('Settings', td),
			name  : __('Settings', td)
		}
	]
})
</script>

<style lang="scss" scoped>
.var-tab {
	> .label {
		padding-left: 8px;
	}
}
</style>