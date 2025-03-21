<template>
	<div class="content unwanted-bots-settings">
		<template v-if="showDescription">
			<core-settings-row
				id="unwanted-bots-description"
				:name="strings.unwantedBotsDescriptionTitle"
				leftSize="12"
				align
			>
				<template #description>
					{{ strings.unwantedBotsDescriptionText }}
				</template>
			</core-settings-row>
		</template>

		<template v-if="showSettings">
			<core-settings-row
				:name="strings.blockAiCrawlersTitle"
				class="no-border"
			>
				<template #content>
					<div class="reset-settings">
						<grid-row
							class="settings"
						>
							<grid-column class="first-row">
								<base-checkbox
									size="medium"
									v-model="getOptions.searchAppearance.advanced.unwantedBots.all"
									@input="updateAll($event)"
								>
									{{ strings.allAiCrawlers }}
								</base-checkbox>
							</grid-column>

							<grid-column
								xl="6"
								md="6"
								sm="6"
							>
								<base-checkbox
									v-if="!getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									v-model="getOptions.searchAppearance.advanced.unwantedBots.settings.googleAdsBot"
									:disabled="getOptions.searchAppearance.advanced.unwantedBots.all"
								>
									{{ strings.googleAdsBot }}
								</base-checkbox>

								<base-checkbox
									v-if="getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									:modelValue="true"
									disabled
								>
									{{ strings.googleAdsBot }}
								</base-checkbox>

							</grid-column>

							<grid-column
								xl="6"
								md="6"
								sm="6">

								<base-checkbox
									v-if="!getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									v-model="getOptions.searchAppearance.advanced.unwantedBots.settings.openAiGptBot"
									:disabled="getOptions.searchAppearance.advanced.unwantedBots.all"
								>
									{{ strings.openAiGptBot }}
								</base-checkbox>

								<base-checkbox
									v-if="getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									:modelValue="true"
									disabled
								>
									{{ strings.openAiGptBot }}
								</base-checkbox>

							</grid-column>

							<grid-column
								xl="6"
								md="6"
								sm="6">

								<base-checkbox
									v-if="!getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									v-model="getOptions.searchAppearance.advanced.unwantedBots.settings.commonCrawlCcBot"
									:disabled="getOptions.searchAppearance.advanced.unwantedBots.all"
								>
									{{ strings.commonCrawlCcBot }}
								</base-checkbox>

								<base-checkbox
									v-if="getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									:modelValue="true"
									disabled
								>
									{{ strings.commonCrawlCcBot }}
								</base-checkbox>

							</grid-column>

							<grid-column
								xl="6"
								md="6"
								sm="6">

								<base-checkbox
									v-if="!getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									v-model="getOptions.searchAppearance.advanced.unwantedBots.settings.googleGeminiVertexAiBots"
									:disabled="getOptions.searchAppearance.advanced.unwantedBots.all"
								>
									{{ strings.googleGeminiVertexAiBots }}
								</base-checkbox>

								<base-checkbox
									v-if="getOptions.searchAppearance.advanced.unwantedBots.all"
									size="medium"
									:modelValue="true"
									disabled
								>
									{{ strings.googleGeminiVertexAiBots }}
								</base-checkbox>
							</grid-column>

						</grid-row>
					</div>

					<robots-alert
						v-if="showAlert && botBlockingEnabled"
					/>

				</template>
			</core-settings-row>
		</template>
	</div>
</template>

<script setup>
import {
	useNetworkStore,
	useOptionsStore
} from '@/vue/stores'

import { computed } from 'vue'

import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import RobotsAlert from './RobotsAlert'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

defineProps({
	showDescription : {
		type    : Boolean,
		default : true
	},
	showSettings : {
		type    : Boolean,
		default : true
	},
	showAlert : {
		type    : Boolean,
		default : true
	}
})

const networkStore = useNetworkStore()
const optionsStore = useOptionsStore()

const strings = {
	unwantedBotsDescriptionTitle : __('Block Unwanted Bots', td),
	unwantedBotsDescriptionText  : __('Lots of traffic comes from bots crawling the web. Some can benefit your site or business, while other bots don\'t. Blocking unwanted bots can save server resources, help with site performance, and protect copyrighted content.', td),
	blockAiCrawlersTitle         : __('Block AI Crawlers', td),
	allAiCrawlers                : __('All AI Crawlers', td),
	googleAdsBot                 : __('Google AdsBot', td),
	openAiGptBot                 : __('OpenAI GPTBot', td),
	commonCrawlCcBot             : __('Common Crawl CCBot', td),
	googleGeminiVertexAiBots     : __('Google Gemini & Vertex AI Bots', td)
}

const isNetwork = computed(() => 'network' === networkStore.currentSite?.blog_id)

const getOptions = computed(() => isNetwork.value ? optionsStore.networkOptions : optionsStore.options)

const botBlockingEnabled = computed(() => {
	return getOptions.value.searchAppearance.advanced.unwantedBots.all ||
		getOptions.value.searchAppearance.advanced.unwantedBots.settings.googleAdsBot ||
		getOptions.value.searchAppearance.advanced.unwantedBots.settings.openAiGptBot ||
		getOptions.value.searchAppearance.advanced.unwantedBots.settings.commonCrawlCcBot ||
		getOptions.value.searchAppearance.advanced.unwantedBots.settings.googleGeminiVertexAiBots
})

const updateAll = (value) => {
	if (value?.target.checked) {
		return
	}

	getOptions.value.searchAppearance.advanced.unwantedBots.settings.googleAdsBot = false
	getOptions.value.searchAppearance.advanced.unwantedBots.settings.openAiGptBot = false
	getOptions.value.searchAppearance.advanced.unwantedBots.settings.commonCrawlCcBot = false
	getOptions.value.searchAppearance.advanced.unwantedBots.settings.googleGeminiVertexAiBots = false
}
</script>