<template>
	<div class="additional-keyphrases-panel">
		<div v-if="postEditorStore.currentPost.keyphrases.additional && postEditorStore.currentPost.keyphrases?.additional?.length && (rootStore.isPro && licenseStore.license.isActive)">
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
					v-if="postEditorStore.currentPost.loading.additional[selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[selectedKeyphrase].keyphrase"
					dark
				/>
				<metaboxAnalysisDetail
					v-if="!postEditorStore.currentPost.loading.additional[selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[selectedKeyphrase] &&
						postEditorStore.currentPost.keyphrases.additional[selectedKeyphrase].keyphrase"
					:analysisItems="postEditorStore.currentPost.keyphrases.additional[selectedKeyphrase].analysis"
				/>
			</div>
		</div>
		<base-input
			v-if="rootStore.isPro && licenseStore.license.isActive"
			size="medium"
			:class="`add-keyphrase-${$root.$data.screenContext}-input`"
			@keydown.enter="pressEnter"
		/>

		<base-button
			v-if="rootStore.isPro && licenseStore.license.isActive && postEditorStore.currentPost.maxAdditionalKeyphrases > postEditorStore.currentPost.keyphrases?.additional?.length"
			id="add-additional-keyphrase"
			class="add-keyphrase gray medium"
			@click="addKeyphraseEv"
		>
			<svg-circle-plus width="14" height="14" />
			{{ strings.addKeyphrase }}
		</base-button>

		<core-tooltip v-if="rootStore.isPro && licenseStore.license.isActive && postEditorStore.currentPost.maxAdditionalKeyphrases <= postEditorStore.currentPost.keyphrases?.additional?.length">
			<base-button
				id="add-additional-keyphrase"
				class="add-keyphrase gray medium"
				:disabled="true"
				@click="addKeyphraseEv"
			>
				<svg-circle-plus width="14" height="14" />
				{{ strings.addKeyphrase }}
			</base-button>

			<template #tooltip>
				<span>{{ strings.maxAmountReached }}</span>
			</template>
		</core-tooltip>

		<template
			v-if="!rootStore.isPro || !licenseStore.license.isActive"
		>
			<div class="aioseo-description additional-keyphrases-description">
				{{ strings.keyphraseDocumentation }}
			</div>

			<core-alert
				class="inline-upsell"
				type="blue"
			>
				<div v-html="strings.upsell" />
			</core-alert>
		</template>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreKeyphrase from '@/vue/components/common/core/Keyphrase'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import metaboxAnalysisDetail from './MetaboxAnalysisDetail'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

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
		CoreAlert,
		CoreKeyphrase,
		CoreLoader,
		CoreTooltip,
		SvgCirclePlus,
		metaboxAnalysisDetail
	},
	data () {
		return {
			selectedKeyphrase : 0,
			strings           : {
				additional             : __('Additional Keyphrases', td),
				addKeyphrase           : __('Add Additional Keyphrases', td),
				keyphraseDocumentation : __('Improve your SEO rankings with additional keyphrases.', td),
				upsell                 : sprintf(
					// Translators: 1 - "Pro" string, 2 - "Learn more link".
					__('Additional Keyphrases are a %1$s feature. %2$s', td),
					'PRO',
					links.getUpsellLink('post-settings', GLOBAL_STRINGS.learnMore, 'additional-keywords', true)
				),
				maxAmountReached : sprintf(
					// Translators: 1 - Number of maximum keywords.
					__('You have reached the maximum of %1$s additional keyphrases.', td),
					this.postEditorStore.currentPost.maxAdditionalKeyphrases
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
			this.truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
			this.selectedKeyphrase = index
		},
		onDeleted (index) {
			const additionalCopy = [ ...this.postEditorStore.currentPost.keyphrases.additional ]
			additionalCopy.splice(index, 1)
			this.postEditorStore.currentPost.keyphrases.additional = null

			setTimeout(() => {
				this.postEditorStore.currentPost.keyphrases.additional = additionalCopy
				this.postEditorStore.isDirty           = true
				this.truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
			}, 300)
		},
		addKeyphraseEv () {
			if (this.postEditorStore.currentPost.maxAdditionalKeyphrases <= this.postEditorStore.currentPost.keyphrases?.additional?.length) {
				return
			}

			const keyphraseInputComponent = document.getElementsByClassName(`add-keyphrase-${this.$root.$data.screenContext}-input`)
			const keyphraseInput          = keyphraseInputComponent[0].querySelector('.medium')
			const keyphraseInputValue     = keyphraseInput?.value.trim()
			if (keyphraseInputValue) {
				const newKeyphrase      = { keyphrase: keyphraseInputValue, score: 0 }
				const newKeyphraseIndex = this.postEditorStore.currentPost.keyphrases.additional.push(newKeyphrase)
				const keyphrasePanel    = document.getElementsByClassName('keyphrase-name')
				this.postEditorStore.currentPost.loading.additional[0] = true
				keyphraseInput.value = ''
				keyphraseInput.blur()

				this.postEditorStore.isDirty = true
				keyphrasePanel[newKeyphraseIndex]?.click()
				this.truSeo.runAnalysis({ postId: this.postEditorStore.currentPost.id, postData: this.postEditorStore.currentPost })
				this.selectedKeyphrase = (newKeyphraseIndex - 1)
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