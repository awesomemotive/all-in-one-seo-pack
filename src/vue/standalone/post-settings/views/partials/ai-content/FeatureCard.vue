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
			@click="openModal"
		>
			{{ feature.strings.buttonSubmit }}
		</base-button>

		<component
			:is="`${feature.slug}-modal`"
			:feature="feature"
			:show="showModal"
			@closeModal="closeModal"
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
import KeyPointsModal from './KeyPointsModal'
import MetaTitleModal from './MetaTitleModal'
import MetaDescriptionModal from './MetaDescriptionModal'
import SocialPostsModal from './SocialPostsModal'

import SvgFaq from '@/vue/components/common/svg/ai/Faq'
import SvgKeyPoints from '@/vue/components/common/svg/ai/KeyPoints'
import SvgMetaTitle from '@/vue/components/common/svg/ai/MetaTitle'
import SvgMetaDescription from '@/vue/components/common/svg/ai/MetaDescription'
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
		KeyPointsModal,
		MetaTitleModal,
		MetaDescriptionModal,
		SocialPostsModal,
		SvgFaq,
		SvgKeyPoints,
		SvgMetaTitle,
		SvgMetaDescription,
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
	},
	data () {
		return {
			showModal : false
		}
	},
	methods : {
		openModal () {
			this.showModal = true
			this.aiStore.isModalOpened = true
		},
		closeModal () {
			this.showModal = false
			this.aiStore.isModalOpened = false
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

		.aioseo-ai-content-feature-modal-body-footer {
			display: flex;
			flex-direction: row;
			padding: 12px 20px;
			border-top: 1px solid $border;
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
}
</style>