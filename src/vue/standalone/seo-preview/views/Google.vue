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

import { arrayUnique } from '@/vue/utils/helpers'

import { getDomGoogleSerpData } from '@/vue/utils/html'
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
	},
	computed : {
		// Get rich results for the front-end preview.
		richResults () {
			const anchorLinks = document.querySelectorAll('.aioseo-toc-item')
			const items = []

			if (anchorLinks) {
				anchorLinks?.forEach(i => {
					items.push(i.innerText)
				})
			}

			const schemaOutput = (document.querySelector('script.aioseo-schema') || {})?.textContent || ''

			return {
				anchorLinks   : arrayUnique(items) || [],
				reviewSnippet : this.seoPreviewStore.extractReviewSnippet(schemaOutput),
				faq           : this.seoPreviewStore.extractFaq(schemaOutput)
			}
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