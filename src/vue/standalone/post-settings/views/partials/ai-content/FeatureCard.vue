<template>
	<div class="aioseo-ai-content-feature-card">
		<component
			 :is="`svg-${feature.svg}`"
		/>

		<span class="header">{{ feature.strings.name }}</span>
		<span class="description">{{ feature.strings.description }}</span>

		<base-button
			size="small"
			type="blue"
			:disabled="!optionsStore.internalOptions.internal.ai.credits.remaining || buttonDisabled"
			@click="aiStore.isModalOpened = feature.slug"
		>
			{{ feature.strings.buttonSubmit }}
		</base-button>

		<component
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
	useAiStore,
	useOptionsStore
} from '@/vue/stores'

import FaqsModal from './FaqsModal'
import ImageGeneratorModal from './ImageGeneratorModal'
import KeyPointsModal from './KeyPointsModal'
import MetaDescriptionModal from './MetaDescriptionModal'
import MetaTitleModal from './MetaTitleModal'
import SocialPostsModal from './SocialPostsModal'

import SvgFaq from '@/vue/components/common/svg/ai/Faq'
import SvgImageGenerator from '@/vue/components/common/svg/ai/ImageGenerator'
import SvgKeyPoints from '@/vue/components/common/svg/ai/KeyPoints'
import SvgMetaDescription from '@/vue/components/common/svg/ai/MetaDescription'
import SvgMetaTitle from '@/vue/components/common/svg/ai/MetaTitle'
import SvgRepurposeContent from '@/vue/components/common/svg/ai/RepurposeContent'

export default {
	setup () {
		return {
			aiContent    : useAiContent(),
			aiStore      : useAiStore(),
			optionsStore : useOptionsStore()
		}
	},
	components : {
		FaqsModal,
		ImageGeneratorModal,
		KeyPointsModal,
		MetaDescriptionModal,
		MetaTitleModal,
		SocialPostsModal,
		SvgFaq,
		SvgImageGenerator,
		SvgKeyPoints,
		SvgMetaDescription,
		SvgMetaTitle,
		SvgRepurposeContent
	},
	props : {
		feature : {
			type     : Object,
			required : true
		},
		buttonDisabled : {
			type     : Boolean,
			required : false
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-content-feature-card {
	display: flex;
	flex-direction: column;
	align-items: center;

	padding: 12px;
	border: 1px solid $border;
	border-radius: 4px;
	max-width: 280px;

	svg {
		margin: 8px 0;
		width: 28px;
		height: 28px;

		&.aioseo-ai-meta-title,
		&.aioseo-ai-meta-description {
			width: 24px;
			height: 24px;
		}
	}

	.header {
		font-size: 16px;
		font-weight: 600;
		color: $black;
		margin-bottom: 12px;
	}

	.description {
		text-align: center;
		font-size: 14px;
		margin-bottom: 12px;
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

				&.aioseo-ai-content-feature-modal-icon{
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
					line-height: 22px;

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