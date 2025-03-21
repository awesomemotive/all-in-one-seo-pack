<template>
	<core-settings-row
		id="search-cleanup-prevent-crawling"
		:name="strings.preventCrawling"
		align
	>
		<template #content>
			<base-radio-toggle
				v-model="getOptions.searchAppearance.advanced.searchCleanup.settings.preventCrawling"
				name="preventCrawling"
				:options="[
					{ label: GLOBAL_STRINGS.off, value: false, activeClass: 'dark' },
					{ label: GLOBAL_STRINGS.on, value: true }
				]"
			/>

			<div class="aioseo-description">
				<div v-html="strings.preventCrawlingDescription"></div>
			</div>

			<robots-alert
				v-if="showAlert && getOptions.searchAppearance.advanced.searchCleanup.settings.preventCrawling"
			/>

		</template>
	</core-settings-row>
</template>

<script setup>
import { computed } from 'vue'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import {
	useNetworkStore,
	useOptionsStore
} from '@/vue/stores'

import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import RobotsAlert from './RobotsAlert'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const networkStore = useNetworkStore()
const optionsStore = useOptionsStore()

defineProps({
	showAlert : {
		type    : Boolean,
		default : true
	}
})

const strings = {
	preventCrawling            : __('Block Crawling of Internal Site Search URLs', td),
	preventCrawlingDescription : sprintf(
		// Translators: 1 - Example URL, 2 - Example URL, 3 - Example URL.
		__('Add a \'disallow\' rule to your robots.txt file to prevent crawling of URLs like %1$s , %2$s and %3$s.', td),
		'<code>?s=</code>',
		'<code>/search/</code>',
		'<code>/page/*/?s=</code>'
	)
}

const isNetwork = computed(() => 'network' === networkStore.currentSite?.blog_id)

const getOptions = computed(() => isNetwork.value ? optionsStore.networkOptions : optionsStore.options)
</script>

<style lang="scss" scoped>
</style>