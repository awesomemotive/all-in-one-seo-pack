<template>
	<div
		class="aioseo-tab-content aioseo-post-schema"
		:class="[
			{ 'sidebar' : isSidebar }
		]"
	>
		<core-settings-row
			:name="strings.schemaInUse"
		>
			<template #content>
				<div
					class="graphs"
				>
					<div
						class="sidebar-description"
						v-if="isSidebar"
					>
						<p class="description">{{ strings.sidebarDescription}}</p>

						<p
							class="description"
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'schema', true)"
						/>
					</div>

					<graph-card
						v-if="postEditorStore.currentPost.schema.default.graphName && postEditorStore.currentPost.schema.default.isEnabled"
						:defaultGraph="postEditorStore.currentPost.schema.default.graphName"
					>
						<template #buttons>
							<core-tooltip
								:offset="isSidebar ? '-70px,0' : '0,0'"
							>
								<base-button
									class="small no-hover"
									type="gray"
								>
									<svg-eye />
								</base-button>

								<template #tooltip>
									<span v-html="strings.defaultGraphTooltip" />
								</template>
							</core-tooltip>
						</template>
					</graph-card>
				</div>

				<div
					class="buttons"
				>
					<core-alert
						class="no-graphs"
						type="yellow"
						v-if="!postEditorStore.currentPost.schema.default.graphName || !postEditorStore.currentPost.schema.default.isEnabled"
					>
						{{strings.noGraphs}}
					</core-alert>

					<base-button
						class="medium"
						type="blue"
						@click="modalOpenMetabox = true; modalOpenSidebar = true"
					>
						{{strings.generateSchema}}
					</base-button>

					<base-button
						class="medium"
						type="gray"
						@click="modalOpenMetabox = true; modalOpenSidebar = true"
					>
						{{strings.validateSchema}}
					</base-button>
				</div>

				<cta-modal
					v-if="!isSidebar && modalOpenMetabox"
					@close="modalOpenMetabox = false"
				/>

				<cta-modal
					v-if="isSidebar && modalOpenSidebar"
					@close="modalOpenSidebar = false"
				/>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import {
	usePostEditorStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CtaModal from './partials-schema/CtaModal'
import GraphCard from '../partials/GraphCard'
import SvgEye from '@/vue/components/common/svg/Eye'

export default {
	setup () {
		return {
			postEditorStore : usePostEditorStore()
		}
	},
	components : {
		CoreAlert,
		CoreSettingsRow,
		CoreTooltip,
		CtaModal,
		GraphCard,
		SvgEye
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			modalOpenMetabox : false,
			modalOpenSidebar : false,
			strings          : {
				sidebarDescription  : this.$t.__('Configure Schema Markup for your content. Search engines use structured data to display rich results in SERPs.', this.$td),
				noGraphs            : this.$t.__('You have not added any schema yet. You can add any schema graphs you like via the Schema Generator below.', this.$td),
				schemaInUse         : this.$t.__('Schema In Use', this.$td),
				generateSchema      : this.$t.__('Generate Schema', this.$td),
				validateSchema      : this.$t.__('Validate Schema', this.$td),
				defaultGraphTooltip : this.$t.__('This is the default graph for this post type. All data for this graph will be automatically generated.', this.$td)
			}
		}
	},
	computed : {
		isSidebar () {
			return 'sidebar' === this.$root._data.screenContext
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-schema {

	.no-graphs {
		margin-bottom: 15px;
	}

	.graphs {
		max-width: 1000px;
		margin-bottom: 20px;
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
		gap: 20px 16px;

		&:empty {
			display: none;
		}
	}

	.buttons {
		margin-top: 20px;

		&.no-margin {
			margin-top: 0;
		}

		button {
			margin-right: 8px;
		}

		&:has(.no-graphs) {
			margin-top: 0;
		}
	}

	&.sidebar {
		div.sidebar-description {
			margin-bottom: 15px;
		}

		.graphs {
			display: block;

			.graph {
				margin: 0 0 12px 0;
			}
		}

		.buttons {
			button {
				width: 100%;

				&:not(:first-of-type) {
					margin-top: 8px;
				}
			}
		}
	}
}

.aioseo-post-schema-modal-cta {
	.modal-mask {
		.modal-wrapper {
			.modal-container {
				max-width: 1000px;
				overflow: visible;

				.modal-header {
					padding: 0 0 0 18px;

					display: flex;
					align-items: center;
				}

				.modal-body {
					position: unset;
				}

				svg.aioseo-circle-question-mark {
					width: 16px;
					height: 16px;
					color: $placeholder-color;

					&:hover {
						cursor: pointer;
					}
				}
			}
		}
	}
}

.aioseo-post-schema,
.aioseo-post-schema-modal,
.aioseo-post-schema-modal-cta {
		svg {
		&.aioseo-article,
		&.aioseo-dataset,
		&.aioseo-custom-schema,
		&.aioseo-web-page {
			width: 12.50px;
			height: 12.50px;
		}

		&.aioseo-book {
			width: 15px;
			height: 11px;
		}

		&.aioseo-course {
			width: 15px;
			height: 12.5px;
		}

		&.aioseo-event {
			width: 15px;
			height: 12px;
		}

		&.aioseo-faq-page {
			width: 14px;
			height: 14px;
		}

		&.aioseo-fact-check {
			margin-top: 2px;
			width: 14px;
			height: 11px;
		}

		&.aioseo-how-to {
			width: 12.5px;
			height: 14.5px;
		}

		&.aioseo-job-posting {
			width: 14px;
			height: 13.5px;
		}

		&.aioseo-movie {
			width: 14px;
			height: 11.2px;
		}

		&.aioseo-music {
			width: 8.5px;
			height: 12.5px;
		}

		&.aioseo-person {
			width: 11.2px;
			height: 11.5px;
		}

		&.aioseo-product {
			width: 13.2px;
			height: 13.5px;
		}

		&.aioseo-recipe {
			width: 12.7px;
			height: 13.7px;
		}

		&.aioseo-restaurant {
			width: 10.75px;
			height: 13.6px;
		}

		&.aioseo-service {
			width: 14.75px;
			height: 14.1px;
		}

		&.aioseo-software {
			width: 13.85px;
			height: 11.2px;
		}

		&.aioseo-video {
			width: 13.8px;
			height: 11.15px;
		}
	}
}

.aioseo-post-schema-confirmation-modal,
.aioseo-post-schema-naming-modal {
	.modal-container {
		max-width: 650px !important;
	}

	.aioseo-button:not(.close) {
		margin-top: 16px;
	}

	.aioseo-modal-body {
		padding: 20px 50px 50px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;
	}

	h3 {
		font-size: 20px;
		margin-bottom: 16px;
		text-align: center;
	}

	.reset-description {
		font-size: 16px;
		color: $black;
		margin-bottom: 16px;
		text-align: center;
		max-width: 515px;
	}

	button.close {
		position: absolute;
		right: 11px;
		top: 11px;
		width: 24px;
		height: 24px;
		background-color: #fff;
		border: none;
		display: flex;
		align-items: center;

		svg.aioseo-close {
			cursor: pointer;
			width: 14px;
			height: 14px;
		}
	}

	.aioseo-description {
		max-width: 510px;
		text-align: center;
	}
}
</style>