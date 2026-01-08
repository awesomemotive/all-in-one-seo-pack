<template>
	<div
		class="aioseo-ai-content-feature-card"
		:class="{
			'aioseo-ai-content-feature-card--metabox': 'metabox' === parentComponentContext
		}"
	>
		<div class="aioseo-ai-content-feature-card-header">
			<div class="aioseo-ai-content-feature-card-header-title">
				<component :is="`svg-${feature.svg}`" />

				<span class="aioseo-ai-content-feature-card-header-title-text">{{ feature.strings.name }}</span>
			</div>

			<div
				v-if="feature.isPopular"
			    class="popular-badge"
			>
				<span v-if="'sidebar' !== parentComponentContext">Popular</span>

				<svg-star-outline
					v-else
					width="15"
					height="15"
				/>
			</div>
		</div>

		<div
			v-if="'sidebar' !== parentComponentContext"
			class="aioseo-ai-content-feature-card-description"
		>
			{{ feature.strings.description }}
		</div>

		<div>
			<core-tooltip
				v-if="isAiAssistantHiddenByUser"
				type="action"
				placement="top"
				:offset="'sidebar' === parentComponentContext ? '-80px, 0' : '25px, 0'"
			>
				<base-button
					size="small"
					type="blue"
					disabled
					class="aioseo-ai-content-feature-card-btn"
				>
					<span v-if="'sidebar' !== parentComponentContext">{{ feature.strings.buttonSubmit }}</span>

					<span v-else>&rarr;</span>
				</base-button>

				<template #tooltip>
					{{ strings.blockHiddenWarning }}
				</template>
			</core-tooltip>

			<base-button
				v-else
				size="small"
				type="blue"
				:disabled="!optionsStore.internalOptions.internal.ai.credits.remaining || buttonDisabled"
				@click="feature?.clickCallback ? feature.clickCallback() : (aiStore.isModalOpened = feature.slug)"
				class="aioseo-ai-content-feature-card-btn"
			>
				<span v-if="'sidebar' !== parentComponentContext">{{ feature.strings.buttonSubmit }}</span>

				<span v-else>&rarr;</span>
			</base-button>
		</div>

		<component
		    v-if="!feature.clickCallback"
			:is="`${feature.slug}-modal`"
			:feature="feature"
			:show="aiStore.isModalOpened === feature.slug"
			@closeModal="aiStore.isModalOpened = null"
			:modal-name="`ai-content-${feature.slug}-modal`"
		/>
	</div>
</template>

<script>
import { useAiContent } from '@/vue/composables/AiContent'

import {
	useAiAssistantStore,
	useAiStore,
	useOptionsStore
} from '@/vue/stores'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import FaqsModal from './FaqsModal'
import ImageGeneratorModal from './ImageGeneratorModal'
import KeyPointsModal from './KeyPointsModal'
import MetaDescriptionModal from './MetaDescriptionModal'
import MetaTitleModal from './MetaTitleModal'
import SocialPostsModal from './SocialPostsModal'

import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import SvgFaq from '@/vue/components/common/svg/ai/Faq'
import SvgImageGenerator from '@/vue/components/common/svg/ai/ImageGenerator'
import SvgKeyPoints from '@/vue/components/common/svg/ai/KeyPoints'
import SvgMetaDescription from '@/vue/components/common/svg/ai/MetaDescription'
import SvgMetaTitle from '@/vue/components/common/svg/ai/MetaTitle'
import SvgRepurposeContent from '@/vue/components/common/svg/ai/RepurposeContent'
import SvgStarOutline from '@/vue/components/common/svg/StarOutline'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			aiAssistantStore : useAiAssistantStore(),
			aiContent        : useAiContent(),
			aiStore          : useAiStore(),
			optionsStore     : useOptionsStore()
		}
	},
	components : {
		CoreTooltip,
		FaqsModal,
		ImageGeneratorModal,
		KeyPointsModal,
		MetaDescriptionModal,
		MetaTitleModal,
		SocialPostsModal,
		SvgAiContent,
		SvgFaq,
		SvgImageGenerator,
		SvgKeyPoints,
		SvgMetaDescription,
		SvgMetaTitle,
		SvgRepurposeContent,
		SvgStarOutline
	},
	props : {
		parentComponentContext : String,
		feature                : {
			type     : Object,
			required : true
		},
		buttonDisabled : {
			type     : Boolean,
			required : false
		}
	},
	data () {
		return {
			strings : {
				// Translators: 1 - The word "Preferences" from WordPress core translations.
				blockHiddenWarning : sprintf(__('Block hidden in %1$s.', td), __('Preferences'))
			}
		}
	},
	computed : {
		isAiAssistantHiddenByUser () {
			return 'ai-assistant' === this.feature.slug && this.aiAssistantStore.isBlockHiddenByUser
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-content-feature-card {
	&--metabox {
		--feature-card-grid-template-columns: 1fr;
		--feature-card-padding: 12px;
		--feature-card-header-title-text-font-size: 14px;
		--feature-card-header-title-svg-size: 20px;
		--feature-card-header-justify-content: space-between;
		--feature-card-popular-badge-padding: 3px 8px;
		--feature-card-btn-width: auto;
	}

	background: #fff;
	display: grid;
	grid-template-columns: var(--feature-card-grid-template-columns, auto auto);
	justify-content: space-between;
	gap: 12px;
	padding: var(--feature-card-padding, 5px 5px 5px 12px);
	border: 1px solid $border;
	border-radius: 4px;
	width: 100%;

	.aioseo-ai-content-feature-card-header {
		display: flex;
		align-items: center;
		justify-content: var(--feature-card-header-justify-content, flex-start);
		gap: 8px;
	}

	.aioseo-ai-content-feature-card-header-title {
		display: flex;
		align-items: center;
		gap: 8px;

		svg {
			color: #8C8F9A;
			width: var(--feature-card-header-title-svg-size, 18px);
			height: var(--feature-card-header-title-svg-size, 18px);
		}
	}

	.aioseo-ai-content-feature-card-header-title-text {
		font-size: var(--feature-card-header-title-text-font-size, 12px);
		font-weight: 700;
		color: $black;
	}

	.aioseo-ai-content-feature-card-description {
		font-size: 14px;
	}

	.popular-badge {
		background: #FEF3C7;
		border: 1px solid #FBBF24;
		border-radius: 4px;
		display: inline-flex;
		padding: var(--feature-card-popular-badge-padding, 2px);
		font-weight: 700;
		font-size: 12px;
		line-height: normal;
		color: #D4790D;
	}

	.aioseo-ai-content-feature-card-btn {
		width: var(--feature-card-btn-width, 32px);
	}

	.aioseo-tooltip {
		margin: 0;
	}
}

.aioseo-modal.aioseo-ai-content-feature-modal {
	color: $font-color;

	.modal-wrapper .modal-container {
		max-width: 900px;
	}

	.modal-header {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: flex-start;

		.header-left {
			display: flex;
			flex-direction: row;
			align-items: center;

			svg {
				&.aioseo-arrow-back {
					width: 26px;
					height: 26px;
					padding-right: 10px;
					border-right: 1px solid $border;
					margin-right: 10px;
					cursor: pointer;
				}

				&.aioseo-ai-content-feature-modal-icon {
					width: 25px;
					height: 25px;
					margin-right: 10px;
					color: $blue;
				}
			}
		}
	}

	.aioseo-ai-content-feature-modal-body {
		.aioseo-ai-content-feature-modal-body-main {
			padding: 20px;
		}
	}

	.modal-container__footer {
		display: flex;
		flex-direction: row;
		padding: 12px 20px;
		justify-content: space-between;
		align-items: center;

		.button-icon {
			width: 20px;
			height: 20px;
			margin-right: 8px;
		}

		.footer-left {
			display: flex;
			flex-direction: row;
			gap: 12px;

			.aioseo-ai-credit-counter {
				display: flex;
				align-items: center;

				.counter-container {
					display: flex;
					flex-direction: row;
					align-items: center;

					.aioseo-tooltip {
						display: none;
					}
				}
			}

			.rephrase-button {
				svg.aioseo-ai-rephrase {
					width: 20px;
					height: 20px;
					margin-right: 4px;
				}
			}
		}

		.footer-right {
			display: flex;
			flex-direction: row;
			gap: 12px;

			.copy-button {
				svg {
					width: 20px;
					height: 20px;
					margin-right: 4px;
				}
			}
		}
	}
}
</style>