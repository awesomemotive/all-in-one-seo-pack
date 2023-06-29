import {
	useSchemaStore
} from '@/vue/stores'

import license from '@/vue/utils/license'

export default {
	computed : {
		hasFeature () {
			const schemaStore = useSchemaStore()
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
		},
		graphNotIncluded () {
			const schemaStore = useSchemaStore()
			const upgradeLink = this.$links.getPricingUrl(
				'graph-' + schemaStore.graph.graphName.toLowerCase(),
				'schema-generator',
				'graph-' + schemaStore.graph.graphName.toLowerCase(),
				'pricing'
			)

			return this.$t.__('This feature is not available in your current plan.', this.$td) + ' ' +
				this.$t.sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					upgradeLink,
					this.$t.sprintf(
						// Translators: 1 - The name of a schema type (e.g. "Event", "Job Posting", etc.).
						this.$t.__('Upgrade your plan and unlock %1$s schema', this.$td),
						// Convert PascalCase to spaced out words.
						schemaStore.graph.graphName.replace(/([A-Z].*?)([A-Z])(?![A-Z])/, '$1 $2')
					)
				)
		},
		isEditing () {
			const schemaStore = useSchemaStore()
			return schemaStore.isEditingCustomGraph ||
				schemaStore.isEditingCustomTemplate ||
				schemaStore.isEditingDefaultGraph ||
				schemaStore.isEditingGraph ||
				schemaStore.isEditingTemplate
		}
	}
}