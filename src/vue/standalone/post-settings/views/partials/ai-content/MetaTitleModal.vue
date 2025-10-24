<template>
	<core-modal
		:show="show"
		:classes="[
			'aioseo-ai-content-feature-modal',
			'aioseo-ai-content-meta-title-modal'
		]"
		@close="$emit('closeModal', true)"
	>
		<template #header>
			<div class="header-left">
				<svg-arrow-back
					v-if="currentScreen === 'results'"
					@click="currentScreen = 'settings'"
				/>

				<component
					:is="`svg-${feature.svg}`"
					class="aioseo-ai-content-feature-modal-icon"
				/>

				<span>{{ feature.strings.name }}</span>
			</div>

			<div class="header-right">
				<button
					class="close"
					type="button"
					@click.stop="$emit('closeModal', true)"
				>
					<svg-close @click="$emit('closeModal', true)" />
				</button>
			</div>
		</template>

		<template #body>
			<div class="aioseo-modal-body aioseo-ai-content-feature-modal-body">
				<div class="aioseo-ai-content-feature-modal-body-main">
					<template v-if="currentScreen === 'settings'">
						<div class="settings">
							<div class="settings-left">
								<step-header
									style="margin-left: -40px;"
									:header="strings.settingsHeader"
								/>

								<core-alert
									class="aioseo-ai-content-no-content-warning"
									v-if="postContentLength < 500"
									type="red"
								>
									{{ strings.noContentWarning }}
								</core-alert>

								<style-form optionsKey="metaTitle" />
							</div>
						</div>
					</template>

					<template v-if="currentScreen === 'loading'">
						<loader :loaders="loaders" />
					</template>

					<template v-if="currentScreen === 'results'">
						<div class="results">
							<titles-descriptions
								:suggestions="postEditorStore.currentPost.ai.titles"
								type="title"
								:generate-fn="generate"
								@closeModal="$emit('closeModal', true)"
							/>
						</div>
					</template>
				</div>
			</div>
		</template>

		<template #footer>
			<div class="footer-left">
				<base-button
					v-if="currentScreen === 'results'"
					class="rephrase-button"
					size="small"
					type="gray"
					@click="event => generate(true)"
					:disabled="!aiContent.hasEnoughCredits(5) || postContentLength < 500"
				>
					<svg-rephrase />

					{{ strings.rephrase }}
				</base-button>

				<credit-counter parent-component-context="modal" />
			</div>

			<div class="footer-right">
				<base-button
					v-if="currentScreen === 'settings' && 0 < postEditorStore.currentPost.ai.titles.length"
					class="view-button"
					size="small"
					type="gray"
					@click="event => currentScreen = 'results'"
				>
					<span>{{ strings.viewPreviousResults }}</span>
				</base-button>

				<base-button
					v-if="currentScreen === 'settings'"
					class="generate-button"
					size="small"
					type="blue"
					@click="event => generate(false)"
					:disabled="!aiContent.hasEnoughCredits(10) || postContentLength < 500"
				>
					{{ strings.generateButtonText }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script>
import { ref, computed } from 'vue'

import { useAiContent } from '@/vue/composables/AiContent'
import {
	useAiStore,
	usePostEditorStore
} from '@/vue/stores'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'
import TitlesDescriptions from './TitlesDescriptions.vue'

import Loader from './Loader'
import StepHeader from './StepHeader.vue'
import StyleForm from './StyleForm.vue'

import SvgArrowBack from '@/vue/components/common/svg/ArrowBack'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import SvgFaq from '@/vue/components/common/svg/ai/Faq'
import SvgMetaTitle from '@/vue/components/common/svg/ai/MetaTitle'
import SvgRephrase from '@/vue/components/common/svg/ai/Rephrase'

import { getPostEditedContent } from '@/vue/plugins/tru-seo/components/postContent'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'closeModal' ],
	setup () {
		const aiContent       = useAiContent()
		const aiStore         = useAiStore()
		const postEditorStore = usePostEditorStore()
		const currentScreen   = ref(postEditorStore.currentPost.ai.titles.length ? 'results' : 'settings')
		const error           = ref(false)

		const postContentLength = computed(() => getPostEditedContent().length)

		const generate = async (rephrase = false) => {
			error.value         = false
			currentScreen.value = 'loading'

			aiStore.generateMetaTitles(rephrase).then(() => {
				currentScreen.value = 'results'
			}).catch(() => {
				currentScreen.value = 'settings'
				error.value         = true
			})
		}

		const strings = {
			settingsHeader      : __('Select tone and audience', td),
			generateButtonText  : __('Generate SEO Titles (10 credits)', td),
			rephrase            : __('Regenerate (5 credits)', td),
			viewPreviousResults : __('View Previous Results', td),
			noContentWarning    : __('Your post is too short to generate AI content. Please add more content. For the best results, we recommend adding at least 200 words.', td)
		}

		const loaders = [
			{
				slug  : 'meta-title',
				label : __('SEO Title', td),
				icon  : 'meta-title',
				name  : __('SEO Title', td)
			}
		]

		return {
			aiContent,
			aiStore,
			postEditorStore,
			currentScreen,
			error,
			generate,
			strings,
			loaders,
			postContentLength
		}
	},
	components : {
		BaseCheckbox,
		CoreAlert,
		CoreModal,
		CreditCounter,
		Loader,
		StepHeader,
		StyleForm,
		SvgArrowBack,
		SvgClose,
		SvgCircleCheckSolid,
		SvgFaq,
		SvgMetaTitle,
		SvgRephrase,
		TitlesDescriptions
	},
	props : {
		feature : {
			type     : Object,
			required : true
		},
		show : {
			type : Boolean,
			default () {
				return false
			}
		}
	}
}
</script>