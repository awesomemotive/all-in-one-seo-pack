<template>
	<core-modal
		:classes="[ 'aioseo-ai-generator-modal' ]"
		@close="settingsStore.setModalState({
			modalName : 'aiGenerator',
			value     : false
		})"
	>
		<template #headerTitle>
			{{ headerTitle }}
		</template>

		<template #body>
			<div class="cta">
				<img
					:src="getAssetUrl(OpenAiImage)"
					alt="open-ai-upsell"
				/>

				<div class="bottom">
					<div>
						<p class="title">{{ ctaHeaderTitle }}</p>
						<p>{{ ctaDescription }}</p>
					</div>

					<div>
						<base-button
							type="green"
							size="medium"
							tag="a"
							:href="links.getPricingUrl('post-settings', 'general', 'open-ai')"
							target="_blank"
						>
							{{ strings.upgradeToPro }}
						</base-button>

						<span v-html="strings.learnMoreLink"/>
					</div>
				</div>
			</div>
		</template>
	</core-modal>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useSettingsStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import CoreModal from '@/vue/components/common/core/modal/Index'
import OpenAiImage from '@/vue/assets/images/ai/open-ai.png'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			getAssetUrl,
			settingsStore : useSettingsStore(),
			links
		}
	},
	components : {
		CoreModal
	},
	props : {
		type : {
			type     : String,
			required : true
		}
	},
	data () {
		return {
			OpenAiImage,
			strings : {
				upgradeToPro  : __('Unlock AI Generator', td),
				learnMoreLink : sprintf(
					'<a class="feature-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a> %3$s',
					// Translators: 1 - "Learn More" link.
					links.getUpsellUrl('post-settings', 'general', 'open-ai'),
					__('Learn more about all the features', td),
					links.getUpsellLink('post-settings', '', 'general', true)
				)
			}
		}
	},
	computed : {
		headerTitle () {
			if ('title' === this.type) {
				return sprintf(
					// Translators: 1 - "Title" or "Description".
					__('Generate New SEO %1$s', td),
					__('Title', td)
				)
			}

			return sprintf(
				// Translators: 1 - "Title" or "Description".
				__('Generate New SEO %1$s', td),
				__('Description', td)
			)
		},
		ctaHeaderTitle () {
			if ('title' === this.type) {
				return sprintf(
					// Translators: 1 - "Titles" or "Descriptions".
					__('Generating SEO %1$s is a Pro Feature', td),
					__('Titles', td)
				)
			}

			return sprintf(
				// Translators: 1 - "Titles" or "Descriptions".
				__('Generating SEO %1$s is a Pro Feature', td),
				__('Descriptions', td)
			)
		},
		ctaDescription () {
			if ('title' === this.type) {
				return sprintf(
					// Translators: 1 - "titles" or "meta descriptions", 2 - "title" or "meta description".
					__('With our Pro version, you now have access to the powerful feature that generates SEO optimized %1$s with just a click of a button. Say goodbye to manual %2$s creation and hello to increased online visibility.', td),
					__('titles', td),
					__('title', td)
				)
			}

			return sprintf(
				// Translators: 1 - "titles" or "meta descriptions", 2 - "title" or "meta description".
				__('With our Pro version, you now have access to the powerful feature that generates SEO optimized %1$s with just a click of a button. Say goodbye to manual %2$s creation and hello to increased online visibility.', td),
				__('meta descriptions', td),
				__('description', td)
			)
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-generator-modal {
	.modal-wrapper {
		.modal-container {
			max-width: 850px;

			.modal-header {
				padding: 0 0 0 16px;
			}

			.modal-body {
				position: relative;

				.list {
					.suggestions {
						display: flex;
						flex-direction: column;
						gap: 16px;
						padding: 16px;

						.suggestion {
							.aioseo-editor {
								.ql-disabled {
									background-color: white;
								}

								.aioseo-editor-single .ql-editor {
									padding: 7px 55px 7px 10px;
								}

								.ql-editor {
									padding: 15px 55px 15px 15px;
								}
							}

							button {
								display: flex;
								align-items: center;
								justify-content: center;

								svg {
									width: 14px;
									height: 14px;
								}
							}
						}
					}
				}

				.cta {
					padding: 0 0 40px;
					background-color: white;

					img {
						width: 100%;
					}

					div.bottom {
						padding: 0 16px;
						display: flex;
						flex-direction: column;
						align-items: center;
						text-align: center;

						div {
							width: 520px;

							p {
								font-size: 14px;
								line-height: 22px;

								&.title {
									font-size: 18px;
									font-weight: 700;
									line-height: 22px;
									color: $black;
									margin-bottom: 14px;
								}
							}

							&:nth-of-type(2) {
								margin-top: 26px;

								.aioseo-button {
									margin-right: 18px;
								}

								.feature-link,
								.no-underline {
									color: $black2;
								}
							}
						}
					}
				}
			}
		}
	}
}
</style>