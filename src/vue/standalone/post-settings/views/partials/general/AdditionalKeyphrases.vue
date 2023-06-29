<template>
	<div class="additional-keyphrases-panel">
		<div v-if="postEditorStore.currentPost.keyphrases.additional && postEditorStore.currentPost.keyphrases.additional.length && ($isPro && licenseStore.license.isActive)">
			<core-keyphrase
				v-for="(keyphrase, index) in postEditorStore.currentPost.keyphrases.additional"
				:key="index"
				:index="index"
				:keyphrase="keyphrase.keyphrase"
				:score="keyphrase.score"
				@saved="onSaved"
				@deleted="onDeleted"
				@selectedKeyphrase="onSelectedKeyphrase"
				class="aioseo-keyphrase-tag additional-keyphrase"
				:class="(selectedKeyphrase === index) ? 'selected' : null"
			/>
			<div class="analysis-wrapper">
				<core-loader
					class="analysis-loading"
					v-if="postEditorStore.currentPost.loading.additional[this.selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[this.selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[this.selectedKeyphrase].keyphrase"
					dark
				/>
				<metaboxAnalysisDetail
					v-if="!postEditorStore.currentPost.loading.additional[this.selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[this.selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[this.selectedKeyphrase].keyphrase"
					:analysisItems="postEditorStore.currentPost.keyphrases.additional[this.selectedKeyphrase].analysis"
				/>
			</div>
		</div>
		<base-input
			v-if="$isPro && licenseStore.license.isActive"
			size="medium"
			:class="`add-keyphrase-${this.$root._data.screenContext}-input`"
			@keydown.enter="pressEnter"
		/>
		<base-button
			v-if="$isPro && licenseStore.license.isActive"
			id="add-additional-keyphrase"
			class="add-keyphrase gray medium"
			@click="addKeyphraseEv"
		>
			<svg-circle-plus width="14" height="14" />
			{{ strings.addKeyphrase }}
		</base-button>

		<template
			v-if="!$isPro || !licenseStore.license.isActive"
		>
			<div class="aioseo-description additional-keyphrases-description">
				{{ strings.keyphraseDocumentation }}
			</div>

			<core-alert
				class="inline-upsell"
				type="yellow"
			>
				<div v-html="strings.upSell" />
			</core-alert>
		</template>
	</div>
</template>

<script>
import {
	useLicenseStore,
	usePostEditorStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreKeyphrase from '@/vue/components/common/core/Keyphrase'
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import metaboxAnalysisDetail from './MetaboxAnalysisDetail'
export default {
	setup () {
		return {
			licenseStore    : useLicenseStore(),
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		CoreAlert,
		CoreKeyphrase,
		CoreLoader,
		SvgCirclePlus,
		metaboxAnalysisDetail
	},
	data () {
		return {
			selectedKeyphrase : 0,
			strings           : {
				additional             : this.$t.__('Additional Keyphrases', this.$td),
				addKeyphrase           : this.$t.__('Add Additional Keyphrases', this.$td),
				keyphraseDocumentation : this.$t.__('Improve your SEO rankings with additional keyphrases.', this.$td),
				upSell                 : this.$t.sprintf(
					// Translators: 1 - "Pro" string, 2 - "Learn more link".
					this.$t.__('Multiple Keyphrases is a %1$s feature. %2$s', this.$td),
					'Pro',
					this.$links.getUpsellLink(
						'seo-settings',
						// Translators: The full string is "Click here to get AIOSEO Pro".
						this.$t.__('Click here to get', this.$td) + ' ' + import.meta.env.VITE_SHORT_NAME + ' Pro',
						'additional-keyphrases',
						true
					)
				)

			}
		}
	},
	watch : {
		'postEditorStore.currentPost.keyphrases.additional' () {
			if (this.postEditorStore.currentPost.keyphrases.additional && !this.postEditorStore.currentPost.keyphrases.additional[this.selectedKeyphrase]) {
				this.selectedKeyphrase = 0
			}
		}
	},
	methods : {
		onSelectedKeyphrase (panel) {
			this.selectedKeyphrase = panel
		},
		onSaved (payload) {
			const { index, value } = payload
			this.postEditorStore.currentPost.keyphrases.additional[index].keyphrase = value
			this.postEditorStore.currentPost.keyphrases.additional[index].score = 0
			this.postEditorStore.currentPost.loading.additional[index] = true

			this.postEditorStore.isDirty = true
			this.$truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
			this.selectedKeyphrase = index
		},
		onDeleted (index) {
			const additionalCopy = [ ...this.postEditorStore.currentPost.keyphrases.additional ]
			additionalCopy.splice(index, 1)
			this.postEditorStore.currentPost.keyphrases.additional = null

			setTimeout(() => {
				this.postEditorStore.currentPost.keyphrases.additional = additionalCopy
				this.postEditorStore.isDirty           = true
				this.$truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
			}, 300)
		},
		addKeyphraseEv () {
			const keyphraseInputComponent = document.getElementsByClassName(`add-keyphrase-${this.$root._data.screenContext}-input`)
			const keyphraseInput          = keyphraseInputComponent[0].querySelector('.medium')
			if (keyphraseInput.value) {
				const newKeyphrase      = { keyphrase: keyphraseInput.value, score: 0 }
				const newKeyphraseIndex = this.postEditorStore.currentPost.keyphrases.additional.push(newKeyphrase)
				const keyphrasePanel    = document.getElementsByClassName('keyphrase-name')
				this.postEditorStore.currentPost.loading.additional[0] = true
				keyphraseInput.value = ''
				keyphraseInput.blur()

				this.postEditorStore.isDirty = true
				keyphrasePanel[newKeyphraseIndex]?.click()
				this.$truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
			}
		},
		pressEnter (event) {
			const addButon = document.getElementById('add-additional-keyphrase')
			event.preventDefault()
			addButon.click()
		},
		created () {
			this.postEditorStore.currentPost.keyphrases.forEach((keyphrase, index) => {
				this.postEditorStore.currentPost.loading.additional[index] = false
			})
		}
	}
}
</script>

<style lang="scss" scoped>
.aioseo-description.additional-keyphrases-description {
	margin: 0 0 12px;
}

.edit-post-sidebar .aioseo-app {
	.aioseo-description.additional-keyphrases-description {
		margin: 0 0 12px;
	}
}
</style>