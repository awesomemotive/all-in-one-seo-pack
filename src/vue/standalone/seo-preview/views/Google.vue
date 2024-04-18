<template>
  <div class="preview-wrapper">
    <google-serp-wireframe
        :device="device"
        :search-text="rootStore.aioseo?.keyphrases?.focus?.keyphrase ?? ''"
    >
      <template #serp-snippet>
        <core-google-search-preview
			:focus-keyphrase="rootStore.aioseo?.keyphrases?.focus?.keyphrase ?? ''"
            :device="device"
            :url="googleData.url"
            :title="googleData.title"
            :description="googleData.description"
            :rich-results="richResults"
        />
      </template>
    </google-serp-wireframe>
  </div>
</template>

<script>
import {
	useRootStore,
	useSeoPreviewStore
} from '@/vue/stores'

import { getDomGoogleSerpData, truncate } from '@/vue/utils/html'
import CoreGoogleSearchPreview from '@/vue/components/common/core/GoogleSearchPreview'
import GoogleSerpWireframe from './partials/GoogleSerpWireframe'

export default {
	setup () {
		return {
			rootStore       : useRootStore(),
			seoPreviewStore : useSeoPreviewStore()
		}
	},
	components : {
		CoreGoogleSearchPreview,
		GoogleSerpWireframe
	},
	computed : {
		// Get rich results for the front-end preview.
		richResults () {
			let anchorLinks = document.querySelector('[class*="aioseo-table-of-contents"] ul')
			if (anchorLinks) {
				anchorLinks = anchorLinks.innerText.split('\n')
					.slice(0, 4)
					.map(item => truncate(item.trim(), 30))
					.filter(item => item)
			}

			const schemaOutput = (document.querySelector('script.aioseo-schema') || {})?.textContent || ''

			return {
				anchorLinks   : anchorLinks || [],
				reviewSnippet : this.seoPreviewStore.extractReviewSnippet(schemaOutput),
				faq           : this.seoPreviewStore.extractFaq(schemaOutput)
			}
		}
	},
	props : {
		device : {
			type    : String,
			default : 'desktop'
		}
	},
	data () {
		return {
			googleData : getDomGoogleSerpData()
		}
	}
}
</script>

<style lang="scss" scoped>
.preview-wrapper {
  .google-serp-wireframe-wrapper {
    margin: 20px;

    &--mobile {
      margin: 0 20px 0;
    }
  }
}
</style>