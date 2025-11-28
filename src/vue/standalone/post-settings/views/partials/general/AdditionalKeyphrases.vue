<template>
	<div class="additional-keyphrases-panel">
		<additional-keyphrases-display
			v-if="hasLicense"
			:keyphrases="postEditorStore.currentPost.keyphrases.additional"
			:selected-keyphrase="selectedKeyphrase"
			:loading="postEditorStore.currentPost.loading.additional"
			@saved="handleSaved"
			@deleted="handleDeleted"
			@selected="handleSelectedKeyphrase"
		/>

		<additional-keyphrase-input
			v-if="hasLicense"
			:screen-context="$root.$data.screenContext"
			:max-reached="maxReached"
			:max-additional-keyphrases="postEditorStore.currentPost.maxAdditionalKeyphrases"
			@add="handleAdd"
		/>

		<additional-keyphrases-upsell
			v-if="!hasLicense"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import {
	updateAdditionalKeyphrase,
	deleteAdditionalKeyphraseByIndex,
	addAdditionalKeyphraseFromInput
} from '@/vue/utils/additionalKeyphrasesManager'

import AdditionalKeyphrasesDisplay from './additional-keyphrases/AdditionalKeyphrasesDisplay'
import AdditionalKeyphraseInput from './additional-keyphrases/AdditionalKeyphraseInput'
import AdditionalKeyphrasesUpsell from './additional-keyphrases/AdditionalKeyphrasesUpsell'

export default {
	setup () {
		return {
			licenseStore    : useLicenseStore(),
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore(),
			truSeo          : new TruSeo()
		}
	},
	components : {
		AdditionalKeyphrasesDisplay,
		AdditionalKeyphraseInput,
		AdditionalKeyphrasesUpsell
	},
	data () {
		return {
			selectedKeyphrase : 0
		}
	},
	watch : {
		'postEditorStore.currentPost.keyphrases.additional' () {
			if (this.postEditorStore.currentPost.keyphrases.additional && !this.postEditorStore.currentPost.keyphrases.additional[this.selectedKeyphrase]) {
				this.selectedKeyphrase = 0
			}
		}
	},
	computed : {
		hasLicense () {
			return this.rootStore.isPro && this.licenseStore.license.isActive
		},
		maxReached () {
			return this.postEditorStore.currentPost.maxAdditionalKeyphrases <= (this.postEditorStore.currentPost.keyphrases?.additional?.length || 0)
		}
	},
	methods : {
		handleSelectedKeyphrase (index) {
			this.selectedKeyphrase = index
		},
		handleSaved (payload) {
			const { index, value } = payload
			updateAdditionalKeyphrase({
				postEditorStore : this.postEditorStore,
				truSeo          : this.truSeo,
				index           : index,
				value           : value,
				onSuccess       : (updatedIndex) => {
					this.selectedKeyphrase = updatedIndex
				}
			})
		},
		handleDeleted (index) {
			deleteAdditionalKeyphraseByIndex({
				postEditorStore : this.postEditorStore,
				truSeo          : this.truSeo,
				index           : index
			})
		},
		handleAdd () {
			const index = addAdditionalKeyphraseFromInput({
				postEditorStore : this.postEditorStore,
				truSeo          : this.truSeo,
				screenContext   : this.$root.$data.screenContext,
				onSuccess       : (newIndex) => {
					this.selectedKeyphrase = newIndex
				}
			})

			if (null !== index) {
				this.selectedKeyphrase = index
			}
		}
	},
	mounted () {
		this.selectedKeyphrase = (this.postEditorStore.currentPost.keyphrases.additional?.length - 1) ?? 0
	}
}
</script>

<style lang="scss" scoped>
.aioseo-description.additional-keyphrases-description {
	margin: 0 0 12px;
}

.edit-post-sidebar .aioseo-app,
.editor-sidebar .aioseo-app {
	.aioseo-description.additional-keyphrases-description {
		margin: 0 0 12px;
	}
}

.aioseo-row {
	.aioseo-keyphrase-tag {
		margin-bottom: 22px;
	}
}

.additional-keyphrases-panel {
	.aioseo-tooltip {
		margin-left: 0 !important;

		svg {
			cursor: pointer;
		}
	}
}
</style>