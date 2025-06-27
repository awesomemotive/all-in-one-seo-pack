<template>
	<core-modal
		:show="show"
		:classes="[
			'aioseo-ai-content-feature-modal',
			'aioseo-ai-content-social-posts-modal'
		]"
		@close="$emit('closeModal', true)"
	>
		<template #header>
			<div class="header-left">
				<svg-arrow-back
					v-if="currentScreen === 'results'"
					@click="currentScreen = 'settings'; aiStore.socialPosts.selected = []"
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
									step=1
									:header="strings.step1"
								/>

								<style-form :optionsKey="'socialPosts'" />
							</div>

							<div class="settings-right">
								<step-header
									step=2
									:header="strings.step2"
								/>

								<div class="form-group">
									<div v-for="(social, index) in socialOptions" :key="index" class="field-item">
										<base-highlight-toggle
											:active="isSocialSelected(social)"
											size="medium"
											:name="social.name"
											type="checkbox"
											:modelValue="false"
											@update:modelValue="checked => updateSelectedSocials(checked, social)"
										>
											<component
												:is="social.icon"
											/>
											{{ social.name }}
										</base-highlight-toggle>
									</div>

									<p class="credit-disclaimer">{{ strings.creditDisclaimer }}</p>
								</div>
							</div>
						</div>
					</template>

					<template v-if="currentScreen === 'loading'">
						<loader :loaders="aiStore.socialPosts.selected" />
					</template>

					<template v-if="currentScreen === 'results'">
						<div class="results">
							<core-main-tabs
								:tabs="tabs"
								:active="activeTab"
								:showSaveButton="false"
								@changed="value => processChangeTab(value)"
							>
								<template #var-tab-icon="{ tab }">
									<component :is="tab.icon" />
								</template>
							</core-main-tabs>

							<div class="scrollable-component">
								<generic
									v-if="getActiveTabObject().slug !== 'email'"
									:slug="getActiveTabObject().slug"
								/>

								<email v-if="getActiveTabObject().slug === 'email'"/>
							</div>
						</div>
					</template>
				</div>

				<div class="aioseo-ai-content-feature-modal-body-footer">
					<div class="footer-left">
						<credit-counter />
					</div>

					<div class="footer-right">
						<base-button
							v-if="shouldShowViewPreviousResults"
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
							:disabled="aiStore.socialPosts.selected.length < 1 || !aiContent.hasEnoughCredits(10 * aiStore.socialPosts.selected.length)"
						>
							{{ generateButtonText }}
						</base-button>

						<base-button
							v-if="currentScreen === 'results'"
							class="copy-button"
							size="small"
							type="gray"
							v-clipboard:copy="copyContent"
							v-clipboard:success="onCopy"
						>
							<svg-copy
								v-if="!justCopied"
							/>

							<svg-circle-check-solid
								v-if="justCopied"
							/>

							<span>{{ copyText }}</span>
						</base-button>
					</div>
				</div>
			</div>
		</template>
	</core-modal>
</template>

<script>
import { computed, ref, onUpdated } from 'vue'

import {
	useAiStore,
	usePostEditorStore
} from '@/vue/stores'

import { useAiContent } from '@/vue/composables/AiContent'
import { sanitizeString } from '@/vue/utils/strings'

import BaseHighlightToggle from '@/vue/components/common/base/HighlightToggle'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'

import Loader from './Loader'
import StepHeader from './StepHeader.vue'
import StyleForm from './StyleForm.vue'

import SvgArrowBack from '@/vue/components/common/svg/ArrowBack'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgCopy from '@/vue/components/common/svg/ai/Copy'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import SvgRepurposeContent from '@/vue/components/common/svg/ai/RepurposeContent'

import SvgEmail from '@/vue/components/common/svg/ai/social/Email'
import SvgFacebook from '@/vue/components/common/svg/ai/social/Facebook'
import SvgInstagram from '@/vue/components/common/svg/ai/social/Instagram'
import SvgLinkedIn from '@/vue/components/common/svg/ai/social/LinkedIn'
import SvgTwitter from '@/vue/components/common/svg/ai/social/Twitter'

import Generic from './social-posts/Generic'
import Email from './social-posts/Email'

import { __, _n, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'closeModal' ],
	setup () {
		const hasSocialPosts = computed(() => {
			return 0 < Object.keys(postEditorStore.currentPost.ai.socialPosts).length &&
			Object.values(postEditorStore.currentPost.ai.socialPosts).some(value => 0 < value.length || 0 < value?.content?.length)
		})

		const aiStore         = useAiStore()
		const postEditorStore = usePostEditorStore()
		const activeTab       = ref('linkedin')
		const currentScreen   = ref(hasSocialPosts.value ? 'results' : 'settings')
		const justCopied      = ref(false)
		const error           = ref(false)

		const getActiveTabObject = () => {
			const result = tabs.value.find(tab => tab.slug === activeTab.value) || tabs.value[0]

			if (result) {
				activeTab.value = result.slug
			}

			return result
		}

		const processChangeTab = (newTabValue) => {
			activeTab.value = newTabValue
		}

		const tabs = computed(() => {
			const tabs = []
			for (const option of socialOptions) {
				if (
					Object.keys(postEditorStore.currentPost.ai.socialPosts).includes(option.slug) &&
					(
						0 < postEditorStore.currentPost.ai.socialPosts[option.slug].length ||
						0 < postEditorStore.currentPost.ai.socialPosts[option.slug]?.content?.length
					)
				) {
					tabs.push(socialOptions.find(tab => option.slug === tab.slug))
				}
			}

			return tabs
		})

		const generateButtonText = computed(() => {
			if (!aiStore.socialPosts.selected.length) {
				return __('Generate Social Posts', td)
			}

			return sprintf(
				'%1$s (%2$s)',
				__('Generate Social Posts', td),
				sprintf(
					// Translators: 1 - Number of credits.
					_n(
						'%1$d credit',
						'%1$d credits',
						aiStore.socialPosts.selected.length * 10,
						td
					),
					aiStore.socialPosts.selected.length * 10
				)
			)
		})

		const shouldShowViewPreviousResults = computed(() => {
			return 'settings' === currentScreen.value && hasSocialPosts.value
		})

		const copyText = computed(() => {
			return !justCopied.value ? strings.copy : strings.copied
		})

		const copyContent = computed(() => {
			const activeTabObject = getActiveTabObject()
			const content         = 'email' === activeTabObject.slug ? postEditorStore.currentPost.ai.socialPosts?.email.content : postEditorStore.currentPost.ai.socialPosts[activeTabObject.slug]

			return sanitizeString(content)
		})

		const onCopy = () => {
			justCopied.value = true

			setTimeout(() => {
				justCopied.value = false
			}, 2000)
		}

		const strings = {
			copy                : __('Copy', td),
			copied              : __('Copied!', td),
			step1               : __('Select tone and audience', td),
			step2               : __('Select media to generate content for', td),
			creditDisclaimer    : __('*10 credits will be charged for each selected option.', td),
			viewPreviousResults : __('View Previous Results', td)
		}

		const socialOptions = [
			{
				slug : 'linkedin',
				name : __('LinkedIn Post', td),
				icon : 'svg-linkedIn'
			},
			{
				slug : 'twitter',
				name : __('X (Twitter Post)', td),
				icon : 'svg-twitter'
			},
			{
				slug : 'email',
				name : __('Marketing Email', td),
				icon : 'svg-email'
			},
			{
				slug : 'facebook',
				name : __('Facebook Post', td),
				icon : 'svg-facebook'
			},
			{
				slug : 'instagram',
				name : __('Instagram Post', td),
				icon : 'svg-instagram'
			}
		]

		onUpdated(() => {
			aiStore.socialPosts.selected = []
		})

		return {
			aiStore,
			aiContent       : useAiContent(),
			postEditorStore : usePostEditorStore(),
			activeTab,
			tabs,
			generateButtonText,
			getActiveTabObject,
			processChangeTab,
			justCopied,
			onCopy,
			copyText,
			copyContent,
			currentScreen,
			shouldShowViewPreviousResults,
			error,
			socialOptions,
			strings
		}
	},
	components : {
		BaseHighlightToggle,
		CoreMainTabs,
		CoreModal,
		CreditCounter,
		Generic,
		Email,
		Loader,
		StepHeader,
		StyleForm,
		SvgArrowBack,
		SvgClose,
		SvgCopy,
		SvgCircleCheckSolid,
		SvgRepurposeContent,
		SvgEmail,
		SvgInstagram,
		SvgFacebook,
		SvgLinkedIn,
		SvgTwitter
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
	},
	methods : {
		updateSelectedSocials (checked, social) {
			const aiStore = useAiStore()

			if (checked) {
				if (!this.isSocialSelected(social)) {
					aiStore.socialPosts.selected.push(social)
				}
				return
			}

			aiStore.socialPosts.selected = aiStore.socialPosts.selected.filter(item => item.slug !== social.slug)
		},
		isSocialSelected (social) {
			return this.aiStore.socialPosts.selected.includes(social)
		},
		generate () {
			this.error         = false
			this.currentScreen = 'loading'

			this.aiStore.generateSocialPosts().then(() => {
				this.currentScreen = 'results'
				this.activeTab     = this.tabs[0].slug
			}).catch(() => {
				this.currentScreen                = 'settings'
				this.error                        = true
				this.aiStore.socialPosts.selected = []
			})
		}
	}
}
</script>

<style lang="scss">

.aioseo-ai-content-social-posts-modal {
	display: flex;

	.settings {
		display: flex;
		flex-direction: row;

		.settings-left {
			flex: 1 1 50%;
			padding-right: 20px;
			border-right: 1px solid $border;
		}

		.settings-right {
			flex: 1 1 50%;
			padding-left: 20px;

			.credit-disclaimer {
				font-size: 12px;
				font-style: italic;
				color: $placeholder-color;
			}
		}

		.form-group {
			margin-left: 40px;

			.field-item {
				display: flex;
				box-sizing: border-box;

				.aioseo-highlight-toggle {
					width: 100%;
					margin-bottom: 10px;

					label > svg {
						width: 16px;
						height: 16px;
						margin-right: 8px;
						margin-left: 5px;
					}
				}
			}

			.field-label {
				display: flex;
				align-items: center;
				cursor: pointer;
			}

			.field-icon {
				margin-right: 8px;
			}
		}
	}

	&> .aioseo-tabs {
		margin-bottom: 0;
		background-color: white;
	}

	.aioseo-tabs {
		.tabs-scroller {
			.var-tabs {
				.var-tab {
					display: flex;
					align-items: center;
					font-size: 14px;
					font-weight: 700;

					svg {
						display: inline;
						width: 16px;
						height: 16px;
						margin-right: 8px;
						color: $black;
					}
				}
			}
		}
	}

	.results {
		.aioseo-ai-content-social-post {
			min-height: 400px;

			pointer-events: none;
		}

		.scrollable-component {
			overflow-y: auto;
		}

		.aioseo-tabs svg {
			width: 20px;
			height: 20px;
			margin-right: 10px;
		}
	}
}
</style>