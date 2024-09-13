import { merge } from 'lodash-es'
import { useSchemaStore } from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export const useSchema = () => {
	const graphs = [
		{
			slug      : 'article',
			graphName : 'Article',
			label     : __('Article', td)
		},
		{
			slug      : 'book',
			graphName : 'Book',
			label     : __('Book', td)
		},
		{
			slug      : 'course',
			graphName : 'Course',
			label     : __('Course', td)
		},
		{
			slug      : 'dataset',
			graphName : 'Dataset',
			label     : __('Dataset', td)
		},
		{
			slug      : 'event',
			graphName : 'Event',
			label     : __('Event', td)
		},
		{
			slug      : 'faq-page',
			graphName : 'FAQPage',
			label     : __('FAQ', td)
		},
		{
			slug      : 'fact-check',
			graphName : 'FactCheck',
			label     : __('Fact Check', td)
		},
		{
			slug      : 'how-to',
			graphName : 'HowTo',
			label     : __('How To', td)
		},
		{
			slug      : 'job-posting',
			graphName : 'JobPosting',
			label     : __('Job Posting', td)
		},
		{
			slug      : 'movie',
			graphName : 'Movie',
			label     : __('Movie', td)
		},
		{
			slug      : 'music',
			graphName : 'Music',
			label     : __('Music', td)
		},
		{
			slug      : 'person',
			graphName : 'Person',
			label     : __('Person', td)
		},
		{
			slug      : 'product',
			graphName : 'Product',
			label     : __('Product', td)
		},
		{
			slug      : 'product-review',
			graphName : 'ProductReview',
			label     : __('Product Review', td)
		},
		{
			slug      : 'car',
			graphName : 'Car',
			label     : __('Car', td)
		},
		{
			slug      : 'recipe',
			graphName : 'Recipe',
			label     : __('Recipe', td)
		},
		{
			slug      : 'service',
			graphName : 'Service',
			label     : __('Service', td)
		},
		{
			slug      : 'software-application',
			graphName : 'SoftwareApplication',
			label     : __('Software', td)
		},
		{
			slug      : 'video',
			graphName : 'Video',
			label     : __('Video', td)
		},
		{
			slug      : 'web-page',
			graphName : 'WebPage',
			label     : __('Web Page', td)
		}
	]

	const childGraphs = {
		Article : [
			{
				childGraphName : 'BlogPosting',
				label          : __('Blog Post', td)
			},
			{
				childGraphName : 'NewsArticle',
				label          : __('News Article', td)
			}
		],
		Music : [
			{
				childGraphName : 'MusicAlbum',
				label          : __('Music Album', td)
			},
			{
				childGraphName : 'MusicGroup',
				label          : __('Music Group', td)
			}
		],
		WebPage : [
			{
				childGraphName : 'WebPage',
				label          : __('Web Page', td)
			},
			{
				childGraphName : 'AboutPage',
				label          : __('About Page', td)
			},
			{
				childGraphName : 'CheckoutPage',
				label          : __('Checkout Page', td)
			},
			{
				childGraphName : 'CollectionPage',
				label          : __('Collection Page', td)
			},
			{
				childGraphName : 'ContactPage',
				label          : __('Contact Page', td)
			},
			{
				childGraphName : 'ItemPage',
				label          : __('Item Page', td)
			},
			{
				childGraphName : 'MedicalWebPage',
				label          : __('Medical Page', td)
			},
			{
				childGraphName : 'ProfilePage',
				label          : __('Profile Page', td)
			},
			{
				childGraphName : 'RealEstateListing',
				label          : __('Real Estate Listing', td)
			},
			{
				childGraphName : 'SearchResultsPage',
				label          : __('Search Results Page', td)
			}
		]
	}

	const initSchemaEditor = (defaults) => {
		const schemaStore = useSchemaStore()

		// Merge new defaults into the existing graph.
		const originalGraph = JSON.stringify(schemaStore.graph)
		schemaStore.graph   = merge(defaults, schemaStore.graph)
		const newGraph      = JSON.stringify(schemaStore.graph)

		// If the graph has changed, mark it as such.
		if (JSON.stringify(originalGraph) !== JSON.stringify(newGraph)) {
			schemaStore.isDirty = true
		}
	}

	const getGraphObject = (graphData, skipChildren = false) => {
		if (!skipChildren && graphData?.properties?.type !== graphData.graphName) {
			if (childGraphs[graphData.graphName]) {
				return childGraphs[graphData.graphName].find(x => x.childGraphName === graphData.properties.type)
			}
		}

		return graphs.find((graph) => graph.slug === graphData.slug)
	}

	const getParentGraphName = (graphName) => {
		// First, check if the graph is a child.
		let parentGraph = ''
		Object.entries(childGraphs).forEach((graphs) => {
			const parentGraphName = graphs[0]
			graphs[1].forEach((graphObject) => {
				if (parentGraph) {
					return
				}

				// If it is a child, grab the parent's label and also grab the child's label.
				if (graphName === graphObject.childGraphName) {
					parentGraph = parentGraphName
				}
			})
		})

		return parentGraph || graphName
	}

	return {
		childGraphs,
		graphs,
		initSchemaEditor,
		getGraphObject,
		getParentGraphName
	}
}