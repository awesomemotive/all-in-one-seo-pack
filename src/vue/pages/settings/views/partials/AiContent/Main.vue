<template>
	<div>
    <div class="credit-counter-hero">
      <core-alert :type="alertColor">
        <credit-counter
          :isSettingsPage="true"
        />
      </core-alert>

      <span class="description">
        {{ strings.description }}

        <span v-html="links.getDocLink(GLOBAL_STRINGS.learnMore, 'aiBuyingCredits', true)" />
      </span>
    </div>

    <core-settings-row
      :name="strings.connect"
      class="aioseo-ai-content-settings__connect"
    >
      <template #content>
        <buy-or-connect-actions />
      </template>
    </core-settings-row>

    <core-settings-row :name="strings.tone">
      <template #content>
        <base-select size="medium" :options="aiContent.toneOptions"
          :modelValue="aiContent.toneOptions.find(o => o.value === optionsStore.options.aiContent.tone)"
          @update:modelValue="value => optionsStore.options.aiContent.tone = value.value" />

        <div class="aioseo-description">
          {{ strings.toneDescription }}
        </div>
      </template>
    </core-settings-row>

    <core-settings-row :name="strings.audience">
      <template #content>
        <base-select size="medium" :options="aiContent.audienceOptions"
          :modelValue="aiContent.audienceOptions.find(o => o.value === optionsStore.options.aiContent.audience)"
          @update:modelValue="value => optionsStore.options.aiContent.audience = value.value" />

        <div class="aioseo-description">
          {{ strings.audienceDescription }}
        </div>
      </template>
    </core-settings-row>

	<core-settings-row :name="aiContent.strings.imageQuality">
      <template #content>
        <base-select
			size="medium"
			:options="aiContent.imageQualityOptions"
			:modelValue="aiContent.imageQualityOptions.find(o => o.value === optionsStore.options.aiContent.imageQuality)"
			@update:modelValue="value => optionsStore.options.aiContent.imageQuality = value.value"
		/>

        <div class="aioseo-description">
			{{ strings.imageQualityDescription }}
        </div>
      </template>
    </core-settings-row>

	<core-settings-row :name="aiContent.strings.imageStyle">
      <template #content>
        <base-select
			size="medium"
			:options="aiContent.imageStyleOptions"
			:modelValue="aiContent.imageStyleOptions.find(o => o.value === optionsStore.options.aiContent.imageStyle)"
			@update:modelValue="value => optionsStore.options.aiContent.imageStyle = value.value"
		/>

        <div class="aioseo-description">
			{{ strings.imageStyleDescription }}
        </div>
      </template>
    </core-settings-row>

	<core-settings-row :name="aiContent.strings.imageAspectRatio">
      <template #content>
        <base-select
			size="medium"
			:options="aiContent.imageAspectRatioOptions"
			:modelValue="aiContent.imageAspectRatioOptions.find(o => o.value === optionsStore.options.aiContent.imageAspectRatio)"
			@update:modelValue="value => optionsStore.options.aiContent.imageAspectRatio = value.value"
		  />

        <div class="aioseo-description">
			{{ strings.imageAspectRatioDescription }}
        </div>
      </template>
    </core-settings-row>
  </div>
</template>

<script>
import {
	useAiStore,
	useOptionsStore
} from '@/vue/stores'

import { computed } from 'vue'

import { getAssetUrl } from '@/vue/utils/helpers'
import { useAiContent } from '@/vue/composables/AiContent'
import links from '@/vue/utils/links'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'
import BuyOrConnectActions from '@/vue/components/common/ai/BuyOrConnectButtons'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const optionsStore = useOptionsStore()
		const alertColor  = computed(() => (20 < optionsStore.aiCreditPercentage) ? 'blue' : 'red')

		return {
			aiStore      : useAiStore(),
			optionsStore : useOptionsStore(),
			aiContent    : useAiContent(),
			alertColor,
			GLOBAL_STRINGS,
			links
		}
	},
	components : {
		CoreAlert,
		CoreSettingsRow,
		CreditCounter,
		BuyOrConnectActions
	},
	data () {
		return {
			faqSvg              : 'faq',
			keyPointsSvg        : 'key-points',
			repurposeContentSvg : 'repurpose-content',
			strings             : {
				description                 : __('Get advanced AI suggestions for meta tags, social media posts, FAQs, key points, and more — all perfectly tailored to your content.', td),
				tone                        : __('Tone', td),
				toneDescription             : __('The default tone that characterizes your content.', td),
				audience                    : __('Audience', td),
				audienceDescription         : __('The default audience that typically reads your content.', td),
				imageQualityDescription     : __('The default image quality that is used by the AI Image Generator.', td),
				imageStyleDescription       : __('The default image style that is used by the AI Image Generator.', td),
				imageAspectRatioDescription : __('The default image aspect ratio that is used by the AI Image Generator.', td),
				connect                     : __('Connect to AI Content', td)
			}
		}
	},
	methods : {
		getAssetUrl
	}
}
</script>

<style lang="scss">
.aioseo-ai-content-settings {
	$blueBackground: #B2D1FF;

	.aioseo-ai-credit-counter {
		display: flex;
		flex-direction: column;

		.purchase-credits,
		.credit-count {
			font-size: 14px;
		}
	}

	.credit-counter-hero {
		border-bottom: 1px solid $border;
		margin-bottom: 20px;

		.description {
			display: block;
			margin: 20px 0;
			font-size: 14px;
		}
	}

	.aioseo-settings-row {
		.aioseo-select {
			max-width: 445px
		}
	}
}
</style>