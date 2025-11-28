<template>
	<div class="aioseo-redirects-logs-blur">
		<core-blur>
			<core-main-tabs
				:tabs="tabs"
				active="logs"
				:showSaveButton="false"
			/>

			<core-wp-table
				:filters="[]"
				:totals="{
					total : 0,
					pages : 0,
					page  : 1
				}"
				:columns="columns"
				:rows="[]"
			/>
		</core-blur>

		<cta
			v-if="!license.hasCoreFeature('redirects', 'logs')"
			:cta-link="links.getPricingUrl('redirects', 'redirects', 'redirect-logs', 'pricing')"
			:button-text="strings.ctaButtonText"
			align-top
			:learn-more-link="links.getUpsellUrl('redirects', 'redirect-logs', 'pricing')"
			:feature-list="[
				strings.trackRedirectsHits,
				strings.track404Errors,
				strings.logHttpHeader,
				strings.logIpAddress
			]"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>

			<template #description>
				<div v-html="strings.ctaDescription"></div>
			</template>
		</cta>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import links from '@/vue/utils/links'
import license from '@/vue/utils/license'

import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import Cta from '@/vue/components/common/cta/Index'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreWpTable from '@/vue/components/common/core/wp/Table'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = computed(() => {
	return {
		ctaButtonText      : __('Unlock Redirect Logs', td),
		ctaHeader          : __('Redirect Logs are a PRO Feature', td),
		ctaDescription     : __('Gain full visibility into your site\'s redirects and 404 errors through detailed logs.<br />Monitor traffic patterns and identify issues early.', td),
		trackRedirectsHits : __('Track Redirect Hits', td),
		track404Errors     : __('Track 404 Errors', td),
		logHttpHeader      : __('Log HTTP Headers', td),
		logIpAddress       : __('Log IP Addresses', td)
	}
})

const tabs = computed(() => {
	return [
		{
			slug : 'logs',
			name : __('Redirect Logs', td)
		},
		{
			slug : 'logs-404',
			name : __('404 Logs', td)
		}
	]
})

const columns = computed(() => {
	return [
		{
			slug  : 'url',
			label : __('URL', td)
		},
		{
			slug  : 'hits',
			label : __('Hits', td),
			width : '97px'
		},
		{
			slug  : 'last_accessed',
			label : __('Last Accessed', td),
			width : '210px'
		},
		{
			slug  : 'actions',
			label : __('Actions', td),
			width : '70px'
		}
	]
})
</script>