<template>
	<div class="graph-container">
		<div class="graph">
			<div class="left">
				<component :is="graphIcon"/>

				<span :title="graphLabel">{{graphLabel}}</span>
			</div>

			<div class="right action-buttons">
				<slot name="buttons" />
			</div>
		</div>
	</div>
</template>

<script>
import { useSchema } from '@/vue/standalone/post-settings/composables/schema'

import GraphsMixin from '../mixins/Graphs'

export default {
	mixins : [ GraphsMixin ],
	props  : {
		graph        : Object,
		customGraph  : Boolean,
		defaultGraph : String
	},
	setup () {
		const { childGraphs, graphs } = useSchema()

		return {
			childGraphs,
			graphs
		}
	},
	computed : {
		graphLabel () {
			if (this.customGraph) {
				return (this.$t.__('Custom Schema', this.$td) + ' - ' + this.graph.graphName)
			}

			if (this.defaultGraph) {
				return this.formatDefaultGraphName(this.defaultGraph)
			}

			if (this.graph?.label) {
				return this.graph.label
			}

			const slug       = this.graph?.slug?.toLowerCase()
			const graphLabel = this.graphs.find(x => x.slug === slug)?.label

			return graphLabel || this.$t.__('Parsing Block Data...', this.$td)
		},
		graphIcon () {
			if (this.customGraph) {
				return 'svg-custom-schema'
			}

			if (this.defaultGraph) {
				const parentGraphSlug = this.getParentGraphSlug(this.defaultGraph)
				if (parentGraphSlug) {
					return 'svg-' + parentGraphSlug
				}
				return 'svg-custom-schema'
			}

			return 'svg-' + this.graph.slug
		}
	},
	methods : {
		getParentGraphSlug (graphName) {
			// First check if the graph is a child.
			Object.entries(this.childGraphs).forEach((graphs) => {
				const parentGraphName = graphs[0]
				graphs[1].forEach((graphObject) => {
					// If it is a child, use the graph name of the parent.
					if (graphName === graphObject.childGraphName) {
						graphName = parentGraphName
					}
				})
			})

			// Now, find the slug.
			let slug = ''
			this.graphs.forEach((object) => {
				if (object.graphName === graphName) {
					slug = object.slug
				}
			})

			return slug
		},
		formatDefaultGraphName (graphName) {
			// First, check if the graph is a child.
			let parentGraph = '',
			 childGraph     = graphName
			Object.entries(this.childGraphs).forEach((graphs) => {
				const parentGraphName = graphs[0]
				graphs[1].forEach((graphObject) => {
					if (parentGraph) {
						return
					}

					// If it is a child, grab the parent's label and also grab the child's label.
					if (graphName === graphObject.childGraphName) {
						parentGraph = parentGraphName
						childGraph  = graphObject.label

						this.graphs.forEach((graphObject) => {
							if (graphObject.graphName === parentGraphName) {
								parentGraph = graphObject.label
							}
						})
					}
				})
			})

			if (!parentGraph) {
				return graphName + ' ' + this.$t.__('(Default)', this.$td)
			}

			return parentGraph + ' - ' + childGraph + ' ' + this.$t.__('(Default)', this.$td)
		}
	}
}
</script>

<style lang="scss">
.aioseo-post-schema,
.aioseo-modal.aioseo-post-schema-modal,
.aioseo-modal.aioseo-post-schema-modal-cta {
	.graph-container {
		flex: 1 30%;
		max-width: 309.33px;
		color: $font-color;

		@media (max-width: 430px) {
			flex: 1 100%;
			max-width: 100%;
		}

		.graph {
			height: 40px;
			padding: 4px 4px 4px 14px;
			border: 1px solid $input-border;
			border-radius: 4px;

			display: flex;
			align-items: center;

			.left {
				display: flex;
				align-items: center;
				overflow: hidden;

				span {
					display: inline-block;
					padding-right: 8px;

					font-size: 14px;
					line-height: 40px;

					white-space: nowrap;
					overflow: hidden;
					text-overflow: ellipsis;
				}

				svg {
					min-width: 15px;
					max-width: 15px;

					color: $black;
					margin-right: 10px;
				}
			}

			.right {
				flex: 1 0 auto;
				justify-content: end;

				&.action-buttons {
					display: inline-flex;
					gap: 5px;

					.aioseo-tooltip {
						margin-left: 4px;
					}

					button {
						line-height: 1;

						&.small {
							padding: 0 9px;
						}

						&.no-hover {
							&:hover {
								background-color: #F3F4F5;
							}
						}

						svg {
							width: 15px;
							height: 15px;
							margin: 0;
							color: $black;

							&.aioseo-circle-plus {
								width: 13.85px;
								height: 13.85px;
							}

							&.aioseo-pencil {
								width: 12.3px;
								height: 12.3px;
							}

							&.aioseo-trash {
								width: 9.4px;
								height: 12px;
							}

							&.aioseo-eye {
								width: 11px;

							}
						}
					}
				}
			}
		}
	}
}
</style>