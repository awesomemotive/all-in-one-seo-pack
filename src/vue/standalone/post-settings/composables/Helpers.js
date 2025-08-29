import { computed } from 'vue'

import {
	useRootStore,
	useSchemaStore
} from '@/vue/stores'

import links from '@/vue/utils/links'
import license from '@/vue/utils/license'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useHelpers = () => {
	const schemaStore = useSchemaStore()
	const hasFeature = computed(() => {
		if (schemaStore.isEditingDefaultGraph) {
			return true
		}

		switch (schemaStore.graph.graphName) {
			case 'Event':
			case 'JobPosting':
				break
			default:
				// Return true for all graphs that aren't limited to specific plans.
				return true
		}

		// First convert the graph name to kebab case.
		const graphName = schemaStore.graph.graphName.replace(/(?<!^)([A-Z][a-z]|(?<=[a-z])[A-Z])/, '-$1').toLowerCase()

		return license.hasCoreFeature('schema', graphName)
	})

	const graphNotIncluded = computed(() => {
		const rootStore = useRootStore()
		const upgradeLink = links.getPricingUrl(
			'graph-' + schemaStore.graph.graphName.toLowerCase(),
			'schema-generator',
			'graph-' + schemaStore.graph.graphName.toLowerCase(),
			rootStore.isPro ? 'pricing' : 'liteUpgrade'
		)

		return __('This feature is not available in your current plan.', td) + ' ' +
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				upgradeLink,
				sprintf(
					// Translators: 1 - The name of a schema type (e.g. "Event", "Job Posting", etc.).
					__('Upgrade your plan and unlock %1$s schema', td),
					// Convert PascalCase to spaced out words.
					schemaStore.graph.graphName.replace(/([A-Z].*?)([A-Z])(?![A-Z])/, '$1 $2')
				)
			)
	})

	const isEditing = computed(() => {
		return schemaStore.isEditingCustomGraph ||
			schemaStore.isEditingCustomTemplate ||
			schemaStore.isEditingDefaultGraph ||
			schemaStore.isEditingGraph ||
			schemaStore.isEditingTemplate
	})

	return {
		hasFeature,
		graphNotIncluded,
		isEditing
	}
}