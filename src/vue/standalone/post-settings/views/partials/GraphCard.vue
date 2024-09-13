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

<script setup>
import { computed } from 'vue'

import { useSchema } from '@/vue/standalone/post-settings/composables/schema'

import SvgArticle from '@/vue/components/common/svg/schema/Article'
import SvgBook from '@/vue/components/common/svg/schema/Book'
import SvgCar from '@/vue/components/common/svg/schema/Car'
import SvgCustomSchema from '@/vue/components/common/svg/schema/CustomSchema'
import SvgCourse from '@/vue/components/common/svg/schema/Course'
import SvgDataset from '@/vue/components/common/svg/schema/Dataset'
import SvgEvent from '@/vue/components/common/svg/schema/Event'
import SvgFaqPage from '@/vue/components/common/svg/schema/FaqPage'
import SvgFactCheck from '@/vue/components/common/svg/schema/FactCheck'
import SvgHowTo from '@/vue/components/common/svg/schema/HowTo'
import SvgJobPosting from '@/vue/components/common/svg/schema/JobPosting'
import SvgMovie from '@/vue/components/common/svg/schema/Movie'
import SvgMusic from '@/vue/components/common/svg/schema/Music'
import SvgPerson from '@/vue/components/common/svg/schema/Person'
import SvgProduct from '@/vue/components/common/svg/schema/Product'
import SvgProductReview from '@/vue/components/common/svg/schema/ProductReview'
import SvgRecipe from '@/vue/components/common/svg/schema/Recipe'
import SvgService from '@/vue/components/common/svg/schema/Service'
import SvgSoftwareApplication from '@/vue/components/common/svg/schema/SoftwareApplication'
import SvgVideo from '@/vue/components/common/svg/schema/Video'
import SvgWebPage from '@/vue/components/common/svg/schema/WebPage'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	graph        : Object,
	customGraph  : Boolean,
	defaultGraph : String
})

const { childGraphs, graphs } = useSchema()

const graphLabel = computed(() => {
	if (props.customGraph) {
		return (__('Custom Schema', td) + ' - ' + props.graph.graphName)
	}

	if (props.defaultGraph) {
		return formatDefaultGraphName(props.defaultGraph)
	}

	if (props.graph?.label) {
		return props.graph.label
	}

	const slug  = props.graph?.slug?.toLowerCase()
	const label = graphs.find(x => x.slug === slug)?.label

	return label || __('Parsing Block Data...', td)
})

const graphIcon = computed(() => {
	if (props.customGraph) {
		return SvgCustomSchema
	}

	if (props.defaultGraph) {
		const parentGraphSlug = getParentGraphSlug(props.defaultGraph)
		if (parentGraphSlug) {
			return findGraphIcon(parentGraphSlug)
		}
		return SvgCustomSchema
	}

	return findGraphIcon(props.graph.slug)
})

const getParentGraphSlug = (graphName) => {
	// First check if the graph is a child.
	Object.entries(childGraphs).forEach((gs) => {
		const parentGraphName = gs[0]
		gs[1].forEach((graphObject) => {
			// If it is a child, use the graph name of the parent.
			if (graphName === graphObject.childGraphName) {
				graphName = parentGraphName
			}
		})
	})

	// Now, find the slug.
	let slug = ''
	graphs.forEach((object) => {
		if (object.graphName === graphName) {
			slug = object.slug
		}
	})

	return slug
}

const formatDefaultGraphName = (graphName) => {
	// First, check if the graph is a child.
	let parentGraph = '',
		childGraph  = graphName
	Object.entries(childGraphs).forEach((gs) => {
		const parentGraphName = gs[0]
		gs[1].forEach((graphObject) => {
			if (parentGraph) {
				return
			}

			// If it is a child, grab the parent's label and also grab the child's label.
			if (graphName === graphObject.childGraphName) {
				parentGraph = parentGraphName
				childGraph  = graphObject.label

				graphs.forEach((go) => {
					if (go.graphName === parentGraphName) {
						parentGraph = go.label
					}
				})
			}
		})
	})

	if (!parentGraph) {
		return graphName + ' ' + __('(Default)', td)
	}

	return parentGraph + ' - ' + childGraph + ' ' + __('(Default)', td)
}

const findGraphIcon = (slug) => {
	switch (slug) {
		case 'article':
			return SvgArticle
		case 'book':
			return SvgBook
		case 'car':
			return SvgCar
		case 'course':
			return SvgCourse
		case 'dataset':
			return SvgDataset
		case 'event':
			return SvgEvent
		case 'faq-page':
			return SvgFaqPage
		case 'fact-check':
			return SvgFactCheck
		case 'how-to':
			return SvgHowTo
		case 'job-posting':
			return SvgJobPosting
		case 'movie':
			return SvgMovie
		case 'music':
			return SvgMusic
		case 'person':
			return SvgPerson
		case 'product':
			return SvgProduct
		case 'product-review':
			return SvgProductReview
		case 'recipe':
			return SvgRecipe
		case 'service':
			return SvgService
		case 'software-application':
			return SvgSoftwareApplication
		case 'video':
			return SvgVideo
		case 'web-page':
			return SvgWebPage
		default:
			return SvgCustomSchema
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