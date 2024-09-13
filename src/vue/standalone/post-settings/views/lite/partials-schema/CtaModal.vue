<template>
	<core-modal
		:classes="[ 'aioseo-post-schema-modal-cta' ]"
		@close="$emit('close')"
	>
		<template #headerTitle>
			<div class="title">{{ strings.schemaGenerator }}</div>

			<core-tooltip>
				<svg-circle-question-mark />

				<template #tooltip>
					<span v-html="strings.headerTooltip" />
				</template>
			</core-tooltip>
		</template>

		<template #body>
			<core-blur>
				<core-main-tabs
					:tabs="tabs"
					:active="'schema-templates'"
					:showSaveButton="false"
				>
					<template #var-tab-icon="{ tab }">
						<component :is="tab.icon" />
					</template>
				</core-main-tabs>

				<div class="schema-templates">
					<core-main-tabs
						:tabs="templatesTabs"
						:active="'schema-catalog'"
						:showSaveButton="false"
					>
						<template #var-tab-icon="{ tab }">
							<component
								:is="tab.icon"
							/>
						</template>
					</core-main-tabs>

				<div class="main">
					<base-input
						class="search"
						size="medium"
						prependIcon="search"
						:placeholder="strings.searchSchema"
					/>

						<div class="schema-catalog">
							<div class="graphs">
								<graph-card
									v-for="(graph, graphIndex) in graphs"
									:key="graph.graphName + graphIndex"
									:graph="graph"
								>
									<template #buttons>
										<base-button
											class="small"
											type="gray"
										>
											<svg-circle-plus/>
										</base-button>
									</template>
								</graph-card>
							</div>
						</div>
					</div>
				</div>
			</core-blur>

			<cta
				:cta-link="links.getPricingUrl('schema-generator', 'schema-generator-upsell')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('schema-generator', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
				:feature-list="features"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>

				<template #description>
					{{ strings.ctaDescription }}
				</template>
			</cta>
		</template>
	</core-modal>
</template>

<script setup>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'

import {
	useRootStore
} from '@/vue/stores'

import links from '@/vue/utils/links'
import { useSchema } from '@/vue/standalone/post-settings/composables/schema'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import Cta from '@/vue/components/common/cta/Index'
import GraphCard from '../../partials/GraphCard'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgFile from '@/vue/components/common/svg/File'
import SvgFileEdit from '@/vue/components/common/svg/schema/FileEdit'
import SvgTerminal from '@/vue/components/common/svg/schema/Terminal'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const { graphs } = useSchema()

const rootStore = useRootStore()

const strings = {
	schemaGenerator : __('Schema Generator', td),
	headerTooltip   : sprintf(
		// Translators: 1 - Learn more link.
		__('Use our powerful Schema Generator to configure Schema Markup for your content. Search Engines use structured data to better understand what your site is about as well as to display rich snippets in search results. %1$s', td),
		links.getDocLink(GLOBAL_STRINGS.learnMore, 'schema', true)
	),
	searchSchema   : __('Search Schema', td),
	ctaDescription : __('Easily generate unlimited schema markup for your content to help you rank higher in search results. Our schema validator ensures your schema works out of the box.', td),
	ctaButtonText  : __('Unlock Schema Generator', td),
	ctaHeader      : sprintf(
		// Translators: 1 - "PRO".
		__('Schema Generator is a %1$s Feature', td),
		'PRO'
	)
}

const features = [
	__('Unlimited Schema', td),
	__('Validate with Google', td),
	__('Increase Rankings', td),
	__('Additional Schema Types', td)
]
const tabs = [
	{
		slug      : 'schema-templates',
		icon      : SvgFile,
		name      : __('Schema Templates', td),
		component : 'templates'
	},
	{
		slug      : 'custom-schema',
		icon      : SvgFileEdit,
		name      : __('Custom Schema', td),
		component : 'custom'
	},
	{
		slug      : 'schema-validation',
		icon      : SvgTerminal,
		name      : __('Schema Validation', td),
		component : 'validation'
	}
]
const templatesTabs = [
	{
		slug : 'schema-catalog',
		name : __('Schema Catalog', td)
	},
	{
		slug : 'your-templates',
		name : __('Your Templates', td)
	}
]
</script>

<style lang="scss">
.aioseo-modal.aioseo-post-schema-modal-cta {
	.modal-wrapper {
		.modal-container {
			display: flex;
			flex : 1;
			flex-direction: column;

			@media (min-height: 768px) {
				min-height: 658px;
			}

			.modal-body {
				display: flex;
				flex : 1;
				flex-direction: column;

				&> .aioseo-tabs {
					margin-bottom: 0;
					background-color: white;
				}

				.aioseo-tabs {
					margin-bottom: 0px;

					.tabs-scroller {
						.var-tabs {
							.var-tab {
								display: flex;
								align-items: center;

								font-size: 14px;
								font-weight: 700;

								svg {
									display: inline;

									width: 20px;
									height: 20px;
									margin-right: 8.75px;
									color: $black;
								}
							}
						}
					}
				}

				.main {
					.search {
						margin-top: 20px;
					}
				}

				.aioseo-cta {
					.header-text {
						font-size: 18px;
					}
				}

				.aioseo-settings-row {
					padding-bottom: 0;
					border-bottom: none;

					div.settings-name div.name {
						font-weight: 700;
						font-size: 14px;
						line-height: 22px;
					}

					div.settings-content {
						font-size: 14px;
						line-height: 22px;

						input,
						.aioseo-editor .ql-editor p,
						.aioseo-button,
						.multiselect__option,
						.multiselect__placeholder {
							font-size: 14px;
						}

						.aioseo-radio {
							margin-right: 20px;
						}
					}

					&.image-field {
						margin-bottom: 16px;

						.image-upload {
							display: flex;

							@media (max-width: 912px) {
								display: block;
							}

							.aioseo-input-container {
								display: flex;
								max-width: 445px;
								margin-right: 8px;

								position: relative;
								width: 100%;

								.aioseo-input {
									width: 100%;
								}
							}

							.insert-image {
								min-width: 214px;
								margin-right: 8px;

								svg.aioseo-circle-plus {
									width: 13px;
									height: 13px;
									margin-right: 10px;
								}
							}
						}

						.image-preview {
							margin-top: 20px;
							width: auto;
							max-width: 525px;
							max-height: 525px;
							height: auto;
						}
					}

					.aioseo-editor {
						background-color: white;
					}

					.aioseo-html-tags-editor {
						font-size: 14px;

						.add-tags .aioseo-add-template-tag {
							font-size: 14px;
						}
					}

					.aioseo-select {
						.multiselect__tags {
							padding: 7px 40px 7px 12px;

							input,
							span {
								font-size: 14px;
							}
						}
					}
				}

				.buttons {
					display: flex;

					padding-top: 12px;
					border-top: 1px solid $gray;

					&.new-graph {
						justify-content: end;
					}

					.left {
						flex: 1 0 auto;

						button.red {
							background-color: #DF2A4A;
							color: white;

							&:hover {
								background-color: #F83C5D;
							}
						}
					}

					.right {
						.aioseo-button {
							&:first-of-type {
								margin-right: 16px;
							}
						}
					}
				}

				.cm-editor {
					border: 1px solid #D0D1D7;
					font-size: 14px;
				}
			}
		}
	}

	.schema-templates {
		display: flex;
		flex-direction: column;
		flex-wrap: wrap;
		flex: 1;

		padding: 20px;

		.aioseo-tabs {
			margin-bottom: 20px;
			background-color: white;
		}

		.main {
			display: flex;
			flex-direction: column;
			flex-wrap: wrap;
			flex: 1;

			&> .aioseo-input {
				input.medium {
					padding-left: 40px;

					&::placeholder {
						font-size: 14px;
					}
				}

				.prepend-icon {
					margin-left: 5px;
				}
			}

			.schema-catalog,
			.your-templates {
				.graphs {
					display: flex;
					flex-direction: row;
					flex-wrap: wrap;

					margin-top: 20px;
					gap: 20px 16px;

					.graph {
						.action-buttons {
							.aioseo-tooltip:last-of-type {
								margin: 0;
							}
						}
					}
				}
			}

			.graph-editor {
				display: flex;
				flex-direction: column;
				flex-wrap: wrap;
				flex : 1;

				.graph-editor-fields-container {
					flex: 1;
					margin-right: -16px;
					padding-right: 16px;

					overflow-y: auto;
					overflow-x: hidden;

					max-height: calc(80vh - 250px);

					.graph-selector,
					.template-name {
						margin-bottom: 16px;

						.multiselect__content {
							li {
								display: flex;
								align-items: center;

								height: 38px;

								font-size: 14px;
								font-weight: 700;
								line-height: 22px;

								&:hover {
									height: 38px;
								}

								span {
									width: 100%;
									padding: 4px 8px;
									font-size: 14px;

									svg {
										min-width: 15px;
										max-width: 15px;
									}
								}
							}
						}

						.multiselect__single,
						.multiselect__option {
							display: flex;
							align-items: center;
							font-size: 14px;
						}

						.multiselect__content,
						.multiselect__single {
							svg {
								margin: 0 10px 0 13px;
							}
						}
					}

					.template-name {
						margin-top: 1px;
						margin-left: 1px;

						input {
							padding-left: 40px;
							font-size: 14px;
						}

						.prepend-icon {
							margin: 0 0 0 6px;
							color: $black;

							svg {
								width: 15px;
								height: 15px;
							}
						}
					}
				}
			}
		}
	}
}
</style>