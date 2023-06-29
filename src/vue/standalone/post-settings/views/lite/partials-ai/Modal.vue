<template>
	<core-modal-portal
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
			<div class="aioseo-ai-unlicensed-modal">
				<core-blur>
					<div class="list">
						<div class="suggestions">
							<div
								class="suggestion"
								v-for="(suggestion, index) in suggestions['title']"
								:key="index"
							>
								<base-editor
									ref="editor"
									:value="suggestion"
									single
									disabled
								>
									<template #append-button>
										<button
											type="button"
											disabled
										>
											<svg-circle-plus />
										</button>
									</template>
								</base-editor>
							</div>
						</div>
					</div>
				</core-blur>
			</div>

			<div class="cta">
				<img
					:src="getAssetUrl(OpenAiImage)"
					alt="open-ai-upsell"
				/>

				<div class="bottom">
					<div>
						<p class="title">{{ctaHeaderTitle}}</p>
						<p>{{ctaDescription}}</p>
					</div>

					<div>
						<base-button
							type="green"
							size="medium"
							tag="a"
							:href="$links.getPricingUrl('post-settings', 'general', 'open-ai')"
						>
							{{ strings.upgradeToPro }}
						</base-button>

						<span v-html="strings.learnMoreLink"/>
					</div>
				</div>
			</div>
		</template>
	</core-modal-portal>
</template>

<script>
import {
	useSettingsStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import BaseEditor from '@/vue/components/common/base/Editor'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreModalPortal from '@/vue/components/common/core/modal/Portal'
import OpenAiImage from '@/vue/assets/images/ai/open-ai.png'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
export default {
	setup () {
		return {
			settingsStore : useSettingsStore()
		}
	},
	components : {
		BaseEditor,
		CoreBlur,
		CoreModalPortal,
		SvgCirclePlus
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
			suggestions : {
				title : [
					'21 Amazing Blog Design Examples to Inspire Your Creativity',
					'Create a Good First Impression with 21 Amazing Blog Design Examples',
					'Create a Good First Impression: 21 Amazing Blog Design Examples to Spark Your Creativity',
					'Create a Good First Impression With 21 Eye-Catching Blog Design Examples',
					'Create a First Impression with 21 Eye-Catching Blog Design Examples'
				]
			},
			strings : {
				upgradeToPro  : this.$t.__('Upgrade to Pro Today', this.$td),
				learnMoreLink : this.$t.sprintf(
					'<a class="feature-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a> %3$s',
					// Translators: 1 - "Learn More" link.
					this.$links.getUpsellUrl('post-settings', 'general', 'open-ai'),
					this.$t.__('Learn more about all the features', this.$td),
					this.$links.getUpsellLink('post-settings', '', 'general', true)
				)
			}
		}
	},
	computed : {
		headerTitle () {
			if ('title' === this.type) {
				return this.$t.sprintf(
					// Translators: 1 - "Title" or "Description".
					this.$t.__('Generate New SEO Post %1$s', this.$td),
					this.$t.__('Title', this.$td)
				)
			}

			return this.$t.sprintf(
				// Translators: 1 - "Title" or "Description".
				this.$t.__('Generate New SEO Post %1$s', this.$td),
				this.$t.__('Description', this.$td)
			)
		},
		ctaHeaderTitle () {
			if ('title' === this.type) {
				return this.$t.sprintf(
					// Translators: 1 - "Titles" or "Descriptions".
					this.$t.__('Generating SEO Post %1$s is a Pro Feature', this.$td),
					this.$t.__('Titles', this.$td)
				)
			}

			return this.$t.sprintf(
				// Translators: 1 - "Titles" or "Descriptions".
				this.$t.__('Generating SEO Post %1$s is a Pro Feature', this.$td),
				this.$t.__('Descriptions', this.$td)
			)
		},
		ctaDescription () {
			if ('title' === this.type) {
				return this.$t.sprintf(
					// Translators: 1 - "titles" or "meta descriptions", 2 - "title" or "meta description".
					this.$t.__('With our Pro version, you now have access to the powerful feature that generates SEO optimized %1$s with just a click of a button. Say goodbye to manual %2$s creation and hello to increased online visibility.', this.$td),
					this.$t.__('titles', this.$td),
					this.$t.__('title', this.$td)
				)
			}

			return this.$t.sprintf(
				// Translators: 1 - "titles" or "meta descriptions", 2 - "title" or "meta description".
				this.$t.__('With our Pro version, you now have access to the powerful feature that generates SEO optimized %1$s with just a click of a button. Say goodbye to manual %2$s creation and hello to increased online visibility.', this.$td),
				this.$t.__('meta descriptions', this.$td),
				this.$t.__('description', this.$td)
			)
		},
		methods : {
			getAssetUrl
		}
	}
}
</script>

<style lang="scss">
.aioseo-ai-generator-modal {
	.modal-mask .modal-wrapper {
		.modal-container {
			max-width: 850px;

			.modal-header {
				padding: 0 0 0 16px;
			}

			.modal-body {
				position: relative;

				.aioseo-ai-unlicensed-modal {
					height: 450px;
				}

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
					max-width: 720px;
					position: absolute;
					top: 20px;
					left: 0;
					right: 0;
					margin-left: auto;
					margin-right: auto;

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